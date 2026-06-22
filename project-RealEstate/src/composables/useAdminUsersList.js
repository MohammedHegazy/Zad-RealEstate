import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminUsersService } from '@/api/admin/users.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAdminUsersList() {
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

  const typeFilter = computed({
    get: () => route.query.type?.toString() ?? '',
    set: (value) => updateQuery({ type: value || undefined }),
  })

  const statusFilter = computed({
    get: () => route.query.status?.toString() ?? '',
    set: (value) => updateQuery({ status: value || undefined }),
  })

  const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
    (params) => adminUsersService.list(params),
    {
      perPage: 15,
      extraParams: (currentRoute) => {
        const params = {}
        if (currentRoute.query.search) params.search = currentRoute.query.search
        if (currentRoute.query.type) params.type = currentRoute.query.type
        if (currentRoute.query.status) params.status = currentRoute.query.status
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

  const hasActiveFilters = computed(
    () => Boolean(search.value || typeFilter.value || statusFilter.value),
  )

  return {
    loading,
    error,
    items,
    pagination,
    fetchItems,
    goToPage,
    search,
    typeFilter,
    statusFilter,
    hasActiveFilters,
    clearFilters,
  }
}
