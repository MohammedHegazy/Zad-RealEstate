import { api } from './client.js'

export const estatesService = {
  list(params = {}) {
    return api.get('/estates', { params })
  },

  getById(id) {
    return api.get(`/estates/${id}`)
  },

  map(params = {}) {
    return api.get('/estates/map', { params })
  },

  nearby(params) {
    return api.get('/estates/nearby', { params })
  },

  reviews(estateId, params = {}) {
    return api.get(`/estates/${estateId}/reviews`, { params })
  },

  reviewsSummary(estateId) {
    return api.get(`/estates/${estateId}/reviews/summary`)
  },

  share(estateId) {
    return api.post(`/estates/${estateId}/share`)
  },
}
