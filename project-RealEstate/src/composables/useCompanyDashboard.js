import { onMounted, ref } from 'vue'

import { companyService } from '@/api/company.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useCompanyDashboard() {
  const loading = ref(false)
  const error = ref(null)
  const company = ref(null)
  const agents = ref([])
  const estates = ref([])
  const reviewsSummary = ref(null)

  async function fetchDashboard() {
    loading.value = true
    error.value = null

    try {
      const [companyRes, agentsRes, estatesRes] = await Promise.all([
        companyService.myCompany(),
        companyService.agents({ per_page: 100 }),
        companyService.allEstates({ per_page: 100 }),
      ])

      company.value = companyRes.data
      agents.value = agentsRes.data ?? []
      estates.value = estatesRes.data ?? []
      reviewsSummary.value = companyRes.data?.reviews_summary ?? null
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات الشركة.')
      company.value = null
    } finally {
      loading.value = false
    }
  }

  onMounted(fetchDashboard)

  return { loading, error, company, agents, estates, reviewsSummary, fetchDashboard }
}
