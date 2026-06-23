<script setup>
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import EstateGallery from '@/components/estates/EstateGallery.vue'
import EstateLocationMap from '@/components/estates/EstateLocationMap.vue'
import EstateInvestment from '@/components/estates/EstateInvestment.vue'
import EstatePricePrediction from '@/components/estates/EstatePricePrediction.vue'
import EstateReviews from '@/components/estates/EstateReviews.vue'
import EstateSidebar from '@/components/estates/EstateSidebar.vue'
import EstateSpecs from '@/components/estates/EstateSpecs.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import { useAuthStore } from '@/stores/auth.js'
import { portfoliosService } from '@/api/portfolios.js'
import { api } from '@/api/client.js'
import { useEstateDetail } from '@/composables/useEstateDetail.js'
import { useShareFeedback } from '@/composables/useShareFeedback.js'
import { formatPropertyType } from '@/composables/useFormatters.js'
import { getEstateLocation, getListingType } from '@/utils/estate.js'
import { shareProperty } from '@/utils/share.js'

const route = useRoute()
const auth = useAuthStore()

const {
  loading,
  error,
  estate,
  reviews,
  reviewSummary,
  reviewsLoading,
  fetchEstate,
  fetchReviews,
  shareEstate,
} = useEstateDetail()

const { shareMessage, shareVariant, showShareFeedback } = useShareFeedback()

const portfolios = ref([])
const portfoliosLoading = ref(false)
const showPortfolioForm = ref(false)
const selectedPortfolioId = ref('')
const addingToPortfolio = ref(false)
const portfolioStatus = ref(null)
const globalTaken = ref(false)
const canAdd = ref(true)

async function fetchPortfolioStatus(id) {
  if (!auth.isAuthenticated() || !auth.isRegularUser()) {
    portfolioStatus.value = null
    globalTaken.value = false
    canAdd.value = true
    return
  }
  try {
    const { data } = await api.get(`/my/estates/${id}/portfolio-status`)
    portfolioStatus.value = data?.portfolio_status ?? null
    globalTaken.value = data?.global_taken ?? false
    canAdd.value = data?.can_add ?? true
  } catch {
    portfolioStatus.value = null
    globalTaken.value = false
    canAdd.value = true
  }
}

async function loadEstate(id) {
  await fetchEstate(id)
  if (estate.value) {
    await fetchReviews(id)
    fetchPortfolioStatus(id)
  }
}

async function handleShare() {
  if (!estate.value) return

  const current = estate.value
  const url = window.location.href

  // Share must run immediately in the click handler — awaiting the API first
  // breaks navigator.share on mobile (user-gesture requirement).
  const result = await shareProperty({
    title: current.name,
    text: `${current.name} — ${getEstateLocation(current)}`,
    url,
  })

  showShareFeedback(result)

  if (result.success && !result.cancelled) {
    shareEstate(current.id)
  }
}

onMounted(() => {
  loadEstate(route.params.id)
  loadPortfolios()
})

watch(
  () => route.params.id,
  (id) => {
    if (id) loadEstate(id)
  },
)

watch(
  () => estate.value,
  (val) => {
    if (val && route.hash) {
      nextTick(() => {
        const el = document.querySelector(route.hash)
        if (el) el.scrollIntoView({ behavior: 'smooth' })
      })
    }
  },
)

watch(
  () => route.hash,
  (hash) => {
    if (estate.value && hash) {
      nextTick(() => {
        const el = document.querySelector(hash)
        if (el) el.scrollIntoView({ behavior: 'smooth' })
      })
    }
  },
)

const canAddToPortfolio = computed(() => {
  if (!auth.isAuthenticated() || !auth.isRegularUser()) return false
  return !portfolioStatus.value && canAdd.value
})

const portfolioStatusBadge = computed(() => {
  const ps = portfolioStatus.value
  if (!ps) return null
  if (ps === 'tracking') return { text: 'قيد التتبّع في محفظتك', variant: 'secondary' }
  if (ps === 'invested') return { text: 'مستثمر في محفظتك', variant: 'success' }
  if (ps === 'sold') return { text: 'مباع في محفظتك', variant: 'warning' }
  return null
})

async function loadPortfolios() {
  if (!canAddToPortfolio.value) return
  portfoliosLoading.value = true
  try {
    const res = await portfoliosService.list({ per_page: 100 })
    portfolios.value = res.data ?? []
    const active = portfolios.value.find(p => p.status === 'active')
    if (active) selectedPortfolioId.value = active.id
  } catch {
    // silent
  } finally {
    portfoliosLoading.value = false
  }
}

async function addToPortfolio() {
  if (!selectedPortfolioId.value) return
  addingToPortfolio.value = true
  try {
    await portfoliosService.addEstate(selectedPortfolioId.value, { estate_id: estate.value.id })
    showPortfolioForm.value = false
    await fetchPortfolioStatus(route.params.id)
  } catch {
    // toast handled by global handler
  } finally {
    addingToPortfolio.value = false
  }
}
</script>

<template>
  <div class="estate-detail-page">
    <LoadingSpinner v-if="loading" class="estate-detail-page__loader" />

    <ErrorAlert
      v-else-if="error"
      :message="error"
      class="container estate-detail-page__error"
      @retry="loadEstate(route.params.id)"
    />

    <template v-else-if="estate">
      <div class="container">
        <Breadcrumbs
          :items="[
            { label: 'الرئيسية', to: '/' },
            { label: 'العقارات', to: '/estates' },
            { label: estate.name },
          ]"
        />

        <div class="estate-detail-page__header">
          <div class="estate-detail-page__header-main">
            <div>
              <div class="estate-detail-page__badges">
                <AppBadge variant="primary">{{ getListingType(estate) }}</AppBadge>
                <AppBadge variant="default">{{ formatPropertyType(estate.kind_text) }}</AppBadge>
                <AppBadge v-if="estate.is_furnished" variant="dark">مفروش</AppBadge>
              </div>
              <h1 class="estate-detail-page__title">{{ estate.name }}</h1>
              <p class="estate-detail-page__location">
                <i class="bi bi-geo-alt"></i>
                {{ getEstateLocation(estate) }}
              </p>
            </div>

            <div class="estate-detail-page__portfolio">
              <template v-if="portfolioStatusBadge">
                <AppBadge :variant="portfolioStatusBadge.variant">
                  <i class="bi bi-check-circle"></i>
                  {{ portfolioStatusBadge.text }}
                </AppBadge>
              </template>

              <template v-else-if="canAddToPortfolio">
                <AppButton
                  v-if="!showPortfolioForm"
                  variant="outline"
                  @click="showPortfolioForm = true"
                >
                  <i class="bi bi-plus-circle"></i>
                  أضف إلى المحفظة
                </AppButton>

                <div v-else class="estate-detail-page__portfolio-form">
                  <AppSelect
                    v-model="selectedPortfolioId"
                    :options="portfolios.map(p => ({ value: p.id, label: p.name }))"
                    placeholder="اختر المحفظة"
                  />
                  <AppButton
                    variant="primary"
                    size="sm"
                    :loading="addingToPortfolio"
                    :disabled="!selectedPortfolioId"
                    @click="addToPortfolio"
                  >
                    إضافة
                  </AppButton>
                  <AppButton
                    variant="ghost"
                    size="sm"
                    @click="showPortfolioForm = false"
                  >
                    إلغاء
                  </AppButton>
                </div>

                <div v-if="portfolios.length === 0 && showPortfolioForm && !portfoliosLoading" class="estate-detail-page__portfolio-empty">
                  <span class="text-muted small">
                    لا توجد محافظ.
                    <RouterLink to="/buyer/portfolios/create">أنشئ محفظة</RouterLink>
                  </span>
                </div>
              </template>

              <AppBadge v-else-if="globalTaken" variant="secondary">
                <i class="bi bi-lock"></i>
                هذا العقار مستثمر بالفعل من قبل مستخدم آخر
              </AppBadge>
            </div>
          </div>
        </div>
      </div>

      <div class="container">
        <div class="estate-detail-page__layout">
          <div class="estate-detail-page__main">
            <EstateGallery :estate="estate" />

            <section v-if="estate.ads?.length" class="estate-detail-page__section">
              <h2>صور الإعلانات</h2>
              <div class="estate-detail-page__ads-grid">
                <img
                  v-for="ad in estate.ads"
                  :key="ad.id"
                  :src="ad.image_url"
                  :alt="`إعلان ${ad.id}`"
                  class="estate-detail-page__ad-image"
                />
              </div>
            </section>

            <section v-if="estate.description" class="estate-detail-page__section">
              <h2>الوصف</h2>
              <p class="estate-detail-page__description">{{ estate.description }}</p>
            </section>

            <section class="estate-detail-page__section">
              <EstateSpecs :estate="estate" />
            </section>

            <section class="estate-detail-page__section">
              <EstateLocationMap :estate="estate" />
            </section>

            <section class="estate-detail-page__section">
              <EstateInvestment :estate="estate" />
            </section>

            <section class="estate-detail-page__section">
              <EstatePricePrediction :estate="estate" />
            </section>

            <section class="estate-detail-page__section">
              <EstateReviews
                :estate-id="estate.id"
                :owner-user-id="estate.user_id"
                :reviews="reviews"
                :summary="reviewSummary"
                :loading="reviewsLoading"
                @refresh="fetchReviews(estate.id)"
              />
            </section>
          </div>

          <EstateSidebar
            :estate="estate"
            :share-message="shareMessage"
            :share-variant="shareVariant"
            @share="handleShare"
          />
        </div>
      </div>
    </template>
  </div>
</template>
