import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminCitiesService, adminPlacesService } from '@/api/admin/locations.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAdminPlacesList() {
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

  const cityFilter = computed({
    get: () => route.query.cities_id?.toString() ?? '',
    set: (value) => updateQuery({ cities_id: value || undefined }),
  })

  const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
    (params) => adminPlacesService.list(params),
    {
      perPage: 15,
      extraParams: (currentRoute) => {
        const params = {}
        if (currentRoute.query.search) params.search = currentRoute.query.search
        if (currentRoute.query.cities_id) params.cities_id = currentRoute.query.cities_id
        return params
      },
    },
  )

  const cityOptions = ref([])

  async function loadCityOptions() {
    try {
      const { data } = await adminCitiesService.list({ per_page: 100 })
      cityOptions.value = data ?? []
    } catch {
      cityOptions.value = []
    }
  }

  loadCityOptions()

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
    () => Boolean(search.value || cityFilter.value),
  )

  const cityFilterOptions = computed(() => [
    { value: '', label: 'كل المدن' },
    ...cityOptions.value.map((city) => ({
      value: String(city.id),
      label: city.name,
    })),
  ])

  return {
    loading,
    error,
    items,
    pagination,
    fetchItems,
    goToPage,
    search,
    cityFilter,
    hasActiveFilters,
    clearFilters,
    cityFilterOptions,
  }
}
