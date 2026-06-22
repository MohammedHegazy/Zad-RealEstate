<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import StarRating from '@/components/ui/StarRating.vue'
import { companyService } from '@/api/company.js'
import { companiesService } from '@/api/companies.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const error = ref(null)
const company = ref(null)
const reviews = ref([])
const summary = ref(null)

async function fetchReviews() {
  loading.value = true
  error.value = null
  try {
    const companyRes = await companyService.myCompany()
    company.value = companyRes.data

    if (companyRes.data?.id) {
      const [reviewsRes, summaryRes] = await Promise.all([
        companiesService.reviews(companyRes.data.id, { per_page: 50 }),
        companiesService.reviewsSummary(companyRes.data.id),
      ])
      reviews.value = reviewsRes.data ?? []
      summary.value = summaryRes.data ?? null
    }
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل التقييمات.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchReviews)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="التقييمات"
      description="تقييمات العملاء لشركتك."
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchReviews" />

    <template v-else-if="company">
      <AdminStatsSection title="ملخص التقييمات">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="متوسط التقييم"
            :value="summary?.average_rating ? Number(summary.average_rating).toFixed(1) : '—'"
            icon="bi-star"
            variant="primary"
          />
          <AdminStatCard
            label="عدد التقييمات"
            :value="summary?.reviews_count ?? 0"
            icon="bi-chat-square-text"
          />
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="آخر التقييمات">
        <div v-if="!reviews.length" style="color: var(--color-text-muted); font-size: var(--text-sm);">
          لا توجد تقييمات بعد.
        </div>

        <div v-for="review in reviews" :key="review.id" class="admin-moderation-card">
          <div class="admin-moderation-card__header">
            <div class="admin-moderation-card__title-group">
              <div class="admin-moderation-card__title-row">
                <strong>{{ review.user ? `${review.user.fname} ${review.user.lname}`.trim() || review.user.username : '—' }}</strong>
                <StarRating :rating="review.rating" :max="5" />
              </div>
              <p class="admin-moderation-card__meta">
                {{ new Date(review.created_at).toLocaleDateString('ar-SY') }}
              </p>
            </div>
          </div>
          <p v-if="review.content" class="admin-moderation-card__text">
            {{ review.content }}
          </p>
        </div>
      </AdminStatsSection>

      <div class="admin-dashboard-actions">
        <AppButton to="/company/profile" variant="outline" size="sm">
          تعديل ملف الشركة
        </AppButton>
      </div>
    </template>
  </div>
</template>
