import { adminApi } from './client.js'

export const AGENT_SOCIALABLE_TYPE = 'App\\Models\\Agent'

export const adminSocialLinksService = {
  list(params = {}) {
    return adminApi.get('/social-links', { params })
  },

  create(body) {
    return adminApi.post('/social-links', body)
  },

  remove(id) {
    return adminApi.delete(`/social-links/${id}`)
  },
}
