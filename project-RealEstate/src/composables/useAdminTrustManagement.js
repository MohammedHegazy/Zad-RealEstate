import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminTrustService } from '@/api/admin/trust.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAdminTrustManagement() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const error = ref(null)
  const items = ref([])
  const pagination = ref(null)
  const search = ref(route.query.search?.toString() ?? '')
  let searchDebounceTimer

  const section = computed({
    get: () => route.query.section?.toString() ?? 'reviews',
    set: (value) => updateQuery({ section: value, page: undefined }),
  })

  const reviewType = computed({
    get: () => route.query.type?.toString() ?? 'property',
    set: (value) => updateQuery({ type: value, page: undefined }),
  })

  const statusFilter = computed({
    get: () => route.query.status?.toString() ?? '',
    set: (value) => updateQuery({ status: value || undefined, page: undefined }),
  })

  watch(
    () => route.query.search,
    (value) => {
      search.value = value?.toString() ?? ''
    },
  )

  watch(search, (value) => {
    window.clearTimeout(searchDebounceTimer)
    searchDebounceTimer = window.setTimeout(() => {
      const next = value.trim() || undefined
      const current = route.query.search?.toString() || undefined
      if (next === current) return
      updateQuery({ search: next, page: undefined })
    }, SEARCH_DEBOUNCE_MS)
  })

  function updateQuery(patch) {
    const next = { ...route.query, ...patch }
    Object.keys(next).forEach((key) => {
      if (next[key] === undefined || next[key] === '') delete next[key]
    })
    router.push({ query: next })
  }

  function goToPage(page) {
    updateQuery({ page: page > 1 ? String(page) : undefined })
  }

  async function fetchItems() {
    loading.value = true
    error.value = null

    const page = Number(route.query.page) || 1
    const params = {
      page,
      per_page: 10,
    }

    if (route.query.search) params.search = route.query.search
    if (route.query.status) params.status = route.query.status

    try {
      if (section.value === 'verifications') {
        const res = await adminTrustService.listVerifications(params)
        items.value = res.data ?? []
        pagination.value = res.pagination ?? null
      } else {
        const res = await adminTrustService.listReviews({
          ...params,
          type: reviewType.value,
        })
        items.value = res.data ?? []
        pagination.value = res.pagination ?? null
      }
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات الثقة والمراجعة.')
      items.value = []
      pagination.value = null
    } finally {
      loading.value = false
    }
  }

  watch(() => route.query, fetchItems, { immediate: true, deep: true })

  return {
    loading,
    error,
    items,
    pagination,
    search,
    section,
    reviewType,
    statusFilter,
    fetchItems,
    goToPage,
  }
}
