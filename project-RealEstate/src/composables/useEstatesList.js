import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { estatesService } from '@/api/estates.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { DEFAULT_PER_PAGE } from '@/config/estates.js'
import { matchesListingType } from '@/utils/estate.js'

function parseQuery(routeQuery) {
  return {
    search: routeQuery.search ?? '',
    cities_id: routeQuery.cities_id ?? '',
    places_id: routeQuery.places_id ?? '',
    type_text: routeQuery.type_text ?? '',
    kind_text: routeQuery.kind_text ?? '',
    listing_type: routeQuery.listing_type ?? '',
    is_furnished: routeQuery.is_furnished ?? '',
    min_price: routeQuery.min_price ?? '',
    max_price: routeQuery.max_price ?? '',
    min_bedrooms: routeQuery.min_bedrooms ?? '',
    sort: routeQuery.sort ?? 'latest',
    page: Number(routeQuery.page) || 1,
    per_page: Number(routeQuery.per_page) || DEFAULT_PER_PAGE,
  }
}

function buildApiParams(filters) {
  const params = {
    page: filters.page,
    per_page: filters.per_page,
    sort: filters.sort,
  }

  if (filters.search) params.search = filters.search
  if (filters.cities_id) params.cities_id = filters.cities_id
  if (filters.places_id) params.places_id = filters.places_id
  if (filters.type_text) params.type_text = filters.type_text
  if (filters.kind_text) params.kind_text = filters.kind_text
  if (filters.is_furnished !== '') params.is_furnished = filters.is_furnished
  if (filters.min_price) params.min_price = filters.min_price
  if (filters.max_price) params.max_price = filters.max_price
  if (filters.min_bedrooms) params.min_bedrooms = filters.min_bedrooms

  return params
}

export function useEstatesList() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const error = ref(null)
  const estates = ref([])
  const pagination = ref(null)

  const filters = computed(() => parseQuery(route.query))
  const hasListingTypeFilter = computed(() => Boolean(filters.value.listing_type))

  const displayedEstates = computed(() => {
    if (!hasListingTypeFilter.value) return estates.value
    return estates.value.filter((estate) =>
      matchesListingType(estate, filters.value.listing_type),
    )
  })

  async function fetchEstates() {
    loading.value = true
    error.value = null

    try {
      const response = await estatesService.list(buildApiParams(filters.value))
      estates.value = response.data ?? []
      pagination.value = response.pagination ?? null
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل العقارات.')
      estates.value = []
      pagination.value = null
    } finally {
      loading.value = false
    }
  }

  function applyFilters(nextFilters) {
    const query = { ...nextFilters, page: 1 }
    Object.keys(query).forEach((key) => {
      if (query[key] === '' || query[key] === null || query[key] === undefined) {
        delete query[key]
      }
    })
    router.push({ name: 'estates', query })
  }

  function goToPage(page) {
    router.push({ name: 'estates', query: { ...route.query, page } })
  }

  watch(() => route.query, fetchEstates, { immediate: true, deep: true })

  return {
    loading,
    error,
    estates,
    displayedEstates,
    pagination,
    filters,
    hasListingTypeFilter,
    fetchEstates,
    applyFilters,
    goToPage,
  }
}
