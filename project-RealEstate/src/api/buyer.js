import { api } from './client.js'

export const buyerService = {
  dashboard() {
    return api.get('/my/investor-dashboard')
  },

  socialLinks() {
    return api.get('/my/social-media')
  },

  updateSocialLinks(payload) {
    return api.put('/my/social-media', payload)
  },
}
