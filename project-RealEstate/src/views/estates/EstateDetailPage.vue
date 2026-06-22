<script setup>
import { nextTick, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

import EstateGallery from '@/components/estates/EstateGallery.vue'
import EstateLocationMap from '@/components/estates/EstateLocationMap.vue'
import EstateInvestment from '@/components/estates/EstateInvestment.vue'
import EstatePricePrediction from '@/components/estates/EstatePricePrediction.vue'
import EstateReviews from '@/components/estates/EstateReviews.vue'
import EstateSidebar from '@/components/estates/EstateSidebar.vue'
import EstateSpecs from '@/components/estates/EstateSpecs.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import { useEstateDetail } from '@/composables/useEstateDetail.js'
import { useShareFeedback } from '@/composables/useShareFeedback.js'
import { formatPropertyType } from '@/composables/useFormatters.js'
import { getEstateLocation, getListingType } from '@/utils/estate.js'
import { shareProperty } from '@/utils/share.js'

const route = useRoute()

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

async function loadEstate(id) {
  await fetchEstate(id)
  if (estate.value) {
    await fetchReviews(id)
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

onMounted(() => loadEstate(route.params.id))

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
