import { api } from './client.js'

export const agentService = {
  myProfile() {
    return api.get('/my/agent')
  },

  updateMyProfile(payload) {
    const isFormData = payload instanceof FormData
    if (isFormData) {
      payload.append('_method', 'PUT')
      return api.post('/my/agent', payload)
    }
    return api.put('/my/agent', payload)
  },

  socialLinks() {
    return api.get('/my/social-media')
  },

  updateSocialLinks(payload) {
    return api.put('/my/social-media', payload)
  },

  estates(params = {}) {
    return api.get('/my/estates', { params })
  },
}
