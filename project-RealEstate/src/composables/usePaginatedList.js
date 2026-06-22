import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { getErrorMessage } from '@/api/errorHandler.js'

/**
 * Generic paginated list — syncs with route query `page` and `per_page`.
 */
export function usePaginatedList(fetcher, { perPage = 12, extraParams = () => ({}) } = {}) {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const error = ref(null)
  const items = ref([])
  const pagination = ref(null)

  async function fetchItems() {
    loading.value = true
    error.value = null

    try {
      const params = {
        page: Number(route.query.page) || 1,
        per_page: Number(route.query.per_page) || perPage,
        ...extraParams(route),
      }

      const response = await fetcher(params)
      items.value = response.data ?? []
      pagination.value = response.pagination ?? null
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل البيانات.')
      items.value = []
      pagination.value = null
    } finally {
      loading.value = false
    }
  }

  function goToPage(page) {
    router.push({ query: { ...route.query, page } })
  }

  watch(() => route.query, fetchItems, { immediate: true, deep: true })

  return { loading, error, items, pagination, fetchItems, goToPage }
}
