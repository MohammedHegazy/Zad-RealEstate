import { api } from './client.js'

export const companiesService = {
  list(params = {}) {
    return api.get('/companies', { params })
  },

  getById(id) {
    return api.get(`/companies/${id}`)
  },

  agents(companyId, params = {}) {
    return api.get(`/companies/${companyId}/agents`, { params })
  },

  reviews(companyId, params = {}) {
    return api.get(`/companies/${companyId}/reviews`, { params })
  },

  reviewsSummary(companyId) {
    return api.get(`/companies/${companyId}/reviews/summary`)
  },

  trustScore(companyId) {
    return api.get(`/companies/${companyId}/trust-score`)
  },
}
