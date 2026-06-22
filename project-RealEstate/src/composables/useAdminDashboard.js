import { onMounted, ref } from 'vue'

import { adminDashboardService } from '@/api/admin/dashboard.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useAdminDashboard() {
  const loading = ref(false)
  const error = ref(null)
  const stats = ref(null)

  async function fetchStats() {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminDashboardService.statistics()
      stats.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل الإحصائيات.')
      stats.value = null
    } finally {
      loading.value = false
    }
  }

  onMounted(fetchStats)

  return { loading, error, stats, fetchStats }
}
