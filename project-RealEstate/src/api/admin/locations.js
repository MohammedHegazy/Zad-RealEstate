import { appendFormValue, submitMultipartUpdate } from '@/api/formData.js'
import { adminApi } from './client.js'

export function buildCityFormData(payload, files = {}) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    appendFormValue(formData, key, value)
  })

  if (files.image) {
    formData.append('image', files.image)
  }

  return formData
}

export const adminCitiesService = {
  list(params = {}) {
    return adminApi.get('/cities', { params })
  },

  getById(id) {
    return adminApi.get(`/cities/${id}`)
  },

  create(body) {
    return adminApi.post('/cities', body)
  },

  update(id, body) {
    return submitMultipartUpdate(adminApi, `/cities/${id}`, body)
  },

  remove(id) {
    return adminApi.delete(`/cities/${id}`)
  },
}

export const adminPlacesService = {
  list(params = {}) {
    return adminApi.get('/places', { params })
  },

  getById(id) {
    return adminApi.get(`/places/${id}`)
  },

  create(body) {
    return adminApi.post('/places', body)
  },

  update(id, body) {
    return adminApi.put(`/places/${id}`, body)
  },

  remove(id) {
    return adminApi.delete(`/places/${id}`)
  },
}
