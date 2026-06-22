import { adminApi } from './client.js'

export const adminDashboardService = {
  statistics() {
    return adminApi.get('/statistics')
  },
}
