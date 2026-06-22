import { api } from './client.js'

export const agentsService = {
  list(params = {}) {
    return api.get('/agents', { params })
  },

  getById(id) {
    return api.get(`/agents/${id}`)
  },

  reviews(agentId, params = {}) {
    return api.get(`/agents/${agentId}/reviews`, { params })
  },

  reviewsSummary(agentId) {
    return api.get(`/agents/${agentId}/reviews/summary`)
  },

  trustScore(agentId) {
    return api.get(`/agents/${agentId}/trust-score`)
  },

  share(agentId) {
    return api.post(`/agents/${agentId}/share`)
  },

  listByCompany(companyId, params = {}) {
    return api.get(`/companies/${companyId}/agents`, { params })
  },
}
