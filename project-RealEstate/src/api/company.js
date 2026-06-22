import { api } from './client.js'

export const companyService = {
  myCompany() {
    return api.get('/my/company')
  },

  create(payload) {
    const isFormData = payload instanceof FormData
    return isFormData ? api.post('/my/company', payload) : api.post('/my/company', payload)
  },

  update(payload) {
    const isFormData = payload instanceof FormData
    if (isFormData) {
      payload.append('_method', 'PUT')
      return api.post('/my/company', payload)
    }
    return api.put('/my/company', payload)
  },

  remove() {
    return api.delete('/my/company')
  },

  agents(params = {}) {
    return api.get('/my/company/agents', { params })
  },

  allEstates(params = {}) {
    return api.get('/my/company/all-estates', { params })
  },

  createAgent(payload) {
    return api.post('/my/company/agents', payload)
  },

  updateAgent(agentId, payload) {
    return api.put(`/my/company/agents/${agentId}`, payload)
  },

  removeAgent(agentId) {
    return api.delete(`/my/company/agents/${agentId}`)
  },

  approveAgent(agentId) {
    return api.post(`/my/company/agents/${agentId}/approve`)
  },

  rejectAgent(agentId) {
    return api.post(`/my/company/agents/${agentId}/reject`)
  },

  socialLinks() {
    return api.get('/my/company/social-links')
  },

  addSocialLink(payload) {
    return api.post('/my/company/social-links', payload)
  },

  updateSocialLink(linkId, payload) {
    return api.put(`/my/company/social-links/${linkId}`, payload)
  },

  removeSocialLink(linkId) {
    return api.delete(`/my/company/social-links/${linkId}`)
  },
}
