import { api } from './client.js'

export const myEstatesService = {
  list(params = {}) {
    return api.get('/my/estates', { params })
  },

  getById(id) {
    return api.get(`/my/estates/${id}`)
  },

  create(payload) {
    return api.post('/my/estates', payload)
  },

  update(id, payload) {
    const isFormData = payload instanceof FormData
    if (isFormData) {
      payload.append('_method', 'PUT')
      return api.post(`/my/estates/${id}`, payload)
    }
    return api.put(`/my/estates/${id}`, payload)
  },

  remove(id) {
    return api.delete(`/my/estates/${id}`)
  },
}
