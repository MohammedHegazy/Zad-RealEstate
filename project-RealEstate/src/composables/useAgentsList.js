import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { agentsService } from '@/api/agents.js'
import { companiesService } from '@/api/companies.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAgentsList() {
  const route = useRoute()
  const router = useRouter()
  const companies = ref([])
  const companiesLoading = ref(false)
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

  const companiesId = computed({
    get: () => route.query.companies_id?.toString() ?? '',
    set: (value) => updateQuery({ companies_id: value || undefined }),
  })

  const sort = computed({
    get: () => route.query.sort?.toString() ?? 'latest',
    set: (value) => updateQuery({ sort: value === 'latest' ? undefined : value }),
  })

  const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
    (params) => agentsService.list(params),
    {
      perPage: 12,
      extraParams: (currentRoute) => {
        const params = {}
        if (currentRoute.query.search) params.search = currentRoute.query.search
        if (currentRoute.query.companies_id) {
          params.companies_id = Number(currentRoute.query.companies_id)
        }
        return params
      },
    },
  )

  const sortedItems = computed(() => {
    const list = [...items.value]
    const sortKey = sort.value

    if (sortKey === 'trust') {
      return list.sort((a, b) => (b.trust_score ?? 0) - (a.trust_score ?? 0))
    }

    if (sortKey === 'rating') {
      return list.sort((a, b) => (b.average_rating ?? 0) - (a.average_rating ?? 0))
    }

    return list
  })

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
    () => Boolean(search.value || companiesId.value || sort.value !== 'latest'),
  )

  async function loadCompanies() {
    companiesLoading.value = true
    try {
      const { data } = await companiesService.list({ per_page: 50 })
      companies.value = data ?? []
    } catch {
      companies.value = []
    } finally {
      companiesLoading.value = false
    }
  }

  onMounted(loadCompanies)

  return {
    loading,
    error,
    items: sortedItems,
    pagination,
    fetchItems,
    goToPage,
    companies,
    companiesLoading,
    search,
    companiesId,
    sort,
    hasActiveFilters,
    clearFilters,
  }
}
