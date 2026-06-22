import { api } from './client.js'

export const favoritesService = {
  check(estateId) {
    return api.get(`/my/favorite-estates/check/${estateId}`)
  },

  add(estateId) {
    return api.post(`/estates/${estateId}/favorite`)
  },

  remove(estateId) {
    return api.delete(`/estates/${estateId}/favorite`)
  },

  list(params = {}) {
    return api.get('/my/favorite-estates', { params })
  },
}
