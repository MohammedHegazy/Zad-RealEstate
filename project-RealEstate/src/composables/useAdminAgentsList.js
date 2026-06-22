import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminAgentsService } from '@/api/admin/agents.js'
import { adminCompaniesService } from '@/api/admin/companies.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const SEARCH_DEBOUNCE_MS = 400

export function useAdminAgentsList() {
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

  const companyFilter = computed({
    get: () => route.query.companies_id?.toString() ?? '',
    set: (value) => updateQuery({ companies_id: value || undefined }),
  })

  const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
    (params) => adminAgentsService.list(params),
    {
      perPage: 15,
      extraParams: (currentRoute) => {
        const params = {}
        if (currentRoute.query.search) params.search = currentRoute.query.search
        if (currentRoute.query.companies_id) params.companies_id = currentRoute.query.companies_id
        return params
      },
    },
  )

  const companyOptions = ref([])

  async function loadCompanyOptions() {
    try {
      const { data } = await adminCompaniesService.list({ per_page: 100 })
      companyOptions.value = data ?? []
    } catch {
      companyOptions.value = []
    }
  }

  loadCompanyOptions()

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
    () => Boolean(search.value || companyFilter.value),
  )

  const companyFilterOptions = computed(() => [
    { value: '', label: 'كل الشركات' },
    ...companyOptions.value.map((company) => ({
      value: String(company.id),
      label: company.company_name,
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
    companyFilter,
    hasActiveFilters,
    clearFilters,
    companyFilterOptions,
  }
}
