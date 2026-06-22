import { api } from './client.js'

export const reviewsService = {
  myPropertyReview(estateId) {
    return api.get(`/estates/${estateId}/reviews/me`)
  },

  submitPropertyReview(estateId, payload) {
    return api.post(`/estates/${estateId}/reviews`, payload)
  },

  updatePropertyReview(reviewId, payload) {
    return api.put(`/my/property-reviews/${reviewId}`, payload)
  },

  myAgentReview(agentId) {
    return api.get(`/agents/${agentId}/reviews/me`)
  },

  submitAgentReview(agentId, payload) {
    return api.post(`/agents/${agentId}/reviews`, payload)
  },

  updateAgentReview(reviewId, payload) {
    return api.put(`/my/agent-reviews/${reviewId}`, payload)
  },

  myCompanyReview(companyId) {
    return api.get(`/companies/${companyId}/reviews/me`)
  },

  submitCompanyReview(companyId, payload) {
    return api.post(`/companies/${companyId}/reviews`, payload)
  },

  updateCompanyReview(reviewId, payload) {
    return api.put(`/my/company-reviews/${reviewId}`, payload)
  },
}
