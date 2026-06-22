import { appendFormValue, submitMultipartUpdate } from '@/api/formData.js'
import { adminApi } from './client.js'

export function buildAgentFormData(payload, files = {}) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    appendFormValue(formData, key, value)
  })

  if (files.profile_image) {
    formData.append('profile_image', files.profile_image)
  }

  return formData
}

export const adminAgentsService = {
  list(params = {}) {
    return adminApi.get('/agents', { params })
  },

  getById(id) {
    return adminApi.get(`/agents/${id}`)
  },

  create(body) {
    return adminApi.post('/agents', body)
  },

  update(id, body) {
    return submitMultipartUpdate(adminApi, `/agents/${id}`, body)
  },

  remove(id) {
    return adminApi.delete(`/agents/${id}`)
  },
}
