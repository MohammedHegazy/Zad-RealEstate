import { api } from './client.js'

export const profileService = {
  me(params = {}) {
    return api.get('/me', { params })
  },

  update(payload) {
    return api.put('/profile', payload)
  },
}
