import { ref } from 'vue'

import { estatesService } from '@/api/estates.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useEstateDetail() {
  const loading = ref(false)
  const error = ref(null)
  const estate = ref(null)
  const reviews = ref([])
  const reviewSummary = ref(null)
  const reviewsLoading = ref(false)

  async function fetchEstate(id) {
    loading.value = true
    error.value = null
    estate.value = null

    try {
      const response = await estatesService.getById(id)
      estate.value = response.data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل تفاصيل العقار.')
    } finally {
      loading.value = false
    }
  }

  async function fetchReviews(id) {
    reviewsLoading.value = true

    try {
      const [reviewsRes, summaryRes] = await Promise.all([
        estatesService.reviews(id, { per_page: 10 }),
        estatesService.reviewsSummary(id),
      ])
      reviews.value = reviewsRes.data ?? []
      reviewSummary.value = summaryRes.data ?? null
    } catch {
      reviews.value = []
      reviewSummary.value = null
    } finally {
      reviewsLoading.value = false
    }
  }

  async function shareEstate(id) {
    try {
      const response = await estatesService.share(id)
      if (estate.value?.id === Number(id)) {
        estate.value.shares = response.data?.shares ?? estate.value.shares
      }
      return response.data
    } catch {
      return null
    }
  }

  return {
    loading,
    error,
    estate,
    reviews,
    reviewSummary,
    reviewsLoading,
    fetchEstate,
    fetchReviews,
    shareEstate,
  }
}
