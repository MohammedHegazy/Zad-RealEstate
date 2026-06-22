import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminEstatesService } from '@/api/admin/estates.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'
import {
  ADMIN_ESTATE_KIND_OPTIONS,
  ADMIN_ESTATE_TYPE_OPTIONS,
  ESTATE_STATUS_OPTIONS,
} from '@/config/admin.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAdminEstatesList() {
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

  const statusFilter = computed({
    get: () => route.query.status?.toString() ?? '',
    set: (value) => updateQuery({ status: value || undefined }),
  })

  const typeFilter = computed({
    get: () => route.query.type_text?.toString() ?? '',
    set: (value) => updateQuery({ type_text: value || undefined }),
  })

  const kindFilter = computed({
    get: () => route.query.kind_text?.toString() ?? '',
    set: (value) => updateQuery({ kind_text: value || undefined }),
  })

  const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
    (params) => adminEstatesService.list(params),
    {
      perPage: 15,
      extraParams: (currentRoute) => {
        const params = {}
        if (currentRoute.query.search) params.search = currentRoute.query.search
        if (currentRoute.query.status) params.status = currentRoute.query.status
        if (currentRoute.query.type_text) params.type_text = currentRoute.query.type_text
        if (currentRoute.query.kind_text) params.kind_text = currentRoute.query.kind_text
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
    () => Boolean(search.value || statusFilter.value || typeFilter.value || kindFilter.value),
  )

  const statusFilterOptions = computed(() => [
    { value: '', label: 'كل الحالات' },
    ...ESTATE_STATUS_OPTIONS,
  ])

  const typeFilterOptions = computed(() => [
    { value: '', label: 'كل الفئات' },
    ...ADMIN_ESTATE_TYPE_OPTIONS,
  ])

  const kindFilterOptions = computed(() => [
    { value: '', label: 'كل الأنواع' },
    ...ADMIN_ESTATE_KIND_OPTIONS,
  ])

  return {
    loading,
    error,
    items,
    pagination,
    fetchItems,
    goToPage,
    search,
    statusFilter,
    typeFilter,
    kindFilter,
    hasActiveFilters,
    clearFilters,
    statusFilterOptions,
    typeFilterOptions,
    kindFilterOptions,
  }
}
