<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import AgentCard from '@/components/cards/AgentCard.vue'
import ReviewSection from '@/components/reviews/ReviewSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import DirectoryHero from '@/components/ui/DirectoryHero.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TrustBadge from '@/components/ui/TrustBadge.vue'
import { companiesService } from '@/api/companies.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const route = useRoute()
const loading = ref(false)
const error = ref(null)
const company = ref(null)
const agents = ref([])
const reviews = ref([])
const reviewSummary = ref(null)
const trust = ref(null)
const reviewsLoading = ref(false)

const heroImage = computed(() => {
  return company.value?.banner_image_url ?? company.value?.profile_image_url ?? ''
})

async function fetchCompany(id) {
  loading.value = true
  error.value = null
  try {
    const { data } = await companiesService.getById(id)
    company.value = data
    agents.value = data.agents ?? []
    await fetchReviews(id)
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل الشركة.')
  } finally {
    loading.value = false
  }
}

async function fetchReviews(id) {
  reviewsLoading.value = true
  try {
    const [reviewsRes, summaryRes, trustRes] = await Promise.all([
      companiesService.reviews(id, { per_page: 10 }),
      companiesService.reviewsSummary(id),
      companiesService.trustScore(id).catch(() => ({ data: null })),
    ])
    reviews.value = reviewsRes.data ?? []
    reviewSummary.value = summaryRes.data ?? null
    trust.value = trustRes.data ?? null
  } catch {
    reviews.value = []
    reviewSummary.value = null
  } finally {
    reviewsLoading.value = false
  }
}

onMounted(() => fetchCompany(route.params.id))
watch(() => route.params.id, (id) => id && fetchCompany(id))
</script>

<template>
  <div class="directory-page directory-page--detail">
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" class="container" @retry="fetchCompany(route.params.id)" />

    <template v-else-if="company">
      <DirectoryHero
        :image="heroImage"
        :breadcrumb-items="[
          { label: 'الرئيسية', to: '/' },
          { label: 'الشركات', to: '/companies' },
          { label: company.company_name },
        ]"
        :title="company.company_name"
      >
        <template v-if="company.place">
          <i class="bi bi-geo-alt"></i>
          {{ company.place.name }}، {{ company.place.city?.name }}
        </template>
      </DirectoryHero>

      <div class="container directory-page__body">
        <TrustBadge
          :trust-score="trust?.trust_score ?? company.trust_score"
          :average-rating="reviewSummary?.average_rating"
          :reviews-count="reviewSummary?.reviews_count"
          :is-verified="trust?.is_verified"
        />

        <p v-if="company.description" class="directory-detail-description">
          {{ company.description }}
        </p>

        <div v-if="company.employees_num || company.work_days?.length" class="directory-stats">
          <div v-if="company.employees_num" class="directory-stats__item">
            <strong>{{ company.employees_num }}</strong>
            <span>موظف</span>
          </div>
          <div v-if="company.work_days?.length" class="directory-stats__item">
            <strong>{{ company.work_days.length }}</strong>
            <span>أيام عمل</span>
          </div>
        </div>

        <section v-if="agents.length" class="directory-section">
          <h2>فريق الوسطاء</h2>
          <div class="row g-4">
            <div v-for="agent in agents" :key="agent.id" class="col-sm-6 col-lg-4">
              <AgentCard :agent="agent" />
            </div>
          </div>
        </section>

        <ReviewSection
          review-type="company"
          :entity-id="company.id"
          :owner-user-id="company.user_id"
          :reviews="reviews"
          :summary="reviewSummary"
          :loading="reviewsLoading"
          @refresh="fetchReviews(company.id)"
        />

        <div class="directory-actions">
          <AppButton to="/estates" variant="primary" size="lg">تصفح العقارات</AppButton>
          <AppButton to="/agents" variant="outline" size="lg">كل الوسطاء</AppButton>
        </div>

        <CtaBanner
          title="جاهز للخطوة التالية؟"
          description="استكشف العقارات وتواصل مع فريق الشركة."
          :primary="{ label: 'عرض العقارات', to: '/estates' }"
          :secondary="{ label: 'العودة للشركات', to: '/companies' }"
        />
      </div>
    </template>
  </div>
</template>
