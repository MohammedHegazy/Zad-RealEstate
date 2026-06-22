import { api } from './client.js'

export const citiesService = {
  list(params = {}) {
    return api.get('/cities', { params })
  },

  getById(id) {
    return api.get(`/cities/${id}`)
  },

  places(cityId, params = {}) {
    return api.get(`/cities/${cityId}/places`, { params })
  },
}
