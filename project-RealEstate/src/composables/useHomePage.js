import { ref } from 'vue'
import { agentsService, citiesService, companiesService, estatesService, placesService } from '@/api'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useHomePage() {
  const loading = ref(true)
  const error = ref(null)

  const latestEstates = ref([])
  const cities = ref([])
  const places = ref([])
  const agents = ref([])
  const companies = ref([])
  const mapStats = ref({ total: 0, center: null })

  async function fetchHomeData() {
    loading.value = true
    error.value = null

    try {
      const [estatesRes, citiesRes, placesRes, agentsRes, companiesRes, mapRes] =
        await Promise.all([
          estatesService.list({ sort: 'latest', per_page: 8 }),
          citiesService.list({ per_page: 4 }),
          placesService.list({ per_page: 6 }),
          agentsService.list({ per_page: 4 }),
          companiesService.list({ per_page: 3 }),
          estatesService.map(),
        ])

      latestEstates.value = estatesRes.data ?? []
      cities.value = citiesRes.data ?? []
      places.value = [...(placesRes.data ?? [])].sort(
        (a, b) => (b.active_estates_count ?? 0) - (a.active_estates_count ?? 0),
      )
      agents.value = [...(agentsRes.data ?? [])].sort(
        (a, b) => (b.average_rating ?? 0) - (a.average_rating ?? 0),
      )
      companies.value = [...(companiesRes.data ?? [])].sort(
        (a, b) => (b.trust_score ?? 0) - (a.trust_score ?? 0),
      )

      mapStats.value = {
        total: mapRes.data?.total_markers ?? 0,
        center: mapRes.data?.center ?? null,
      }
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات الصفحة الرئيسية.')
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    latestEstates,
    cities,
    places,
    agents,
    companies,
    mapStats,
    fetchHomeData,
  }
}
