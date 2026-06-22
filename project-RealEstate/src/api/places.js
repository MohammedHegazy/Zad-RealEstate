import { api } from './client.js'

export const placesService = {
  list(params = {}) {
    return api.get('/places', { params })
  },

  getById(id) {
    return api.get(`/places/${id}`)
  },
}
