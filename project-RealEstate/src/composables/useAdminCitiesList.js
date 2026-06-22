import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminCitiesService } from '@/api/admin/locations.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAdminCitiesList() {
  const route = useRoute()
  const router = useRouter()
  const search = ref(route.query.search?.toString() ?? '')
  let searchDebounceTimer

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
      updateQuery({ search: next })
    }, SEARCH_DEBOUNCE_MS)
  })

  const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
    (params) => adminCitiesService.list(params),
    {
      perPage: 15,
      extraParams: (currentRoute) => {
        const params = {}
        if (currentRoute.query.search) params.search = currentRoute.query.search
        return params
      },
    },
  )

  function updateQuery(patch) {
    router.push({
      query: {
        ...route.query,
        ...patch,
        page: 1,
      },
    })
  }

  function clearFilters() {
    router.push({ query: {} })
  }

  const hasActiveFilters = computed(() => Boolean(search.value))

  return {
    loading,
    error,
    items,
    pagination,
    fetchItems,
    goToPage,
    search,
    hasActiveFilters,
    clearFilters,
  }
}
