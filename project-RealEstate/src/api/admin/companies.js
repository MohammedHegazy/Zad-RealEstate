import { appendFormValue, submitMultipartUpdate } from '@/api/formData.js'
import { adminApi } from './client.js'

export function buildCompanyFormData(payload, files = {}) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    appendFormValue(formData, key, value)
  })

  if (files.profile_image) {
    formData.append('profile_image', files.profile_image)
  }

  if (files.banner_image) {
    formData.append('banner_image', files.banner_image)
  }

  return formData
}

export const adminCompaniesService = {
  list(params = {}) {
    return adminApi.get('/companies', { params })
  },

  getById(id) {
    return adminApi.get(`/companies/${id}`)
  },

  create(body) {
    return adminApi.post('/companies', body)
  },

  update(id, body) {
    return submitMultipartUpdate(adminApi, `/companies/${id}`, body)
  },

  updateStatus(id, status) {
    return adminApi.patch(`/companies/${id}/status`, { status })
  },

  remove(id) {
    return adminApi.delete(`/companies/${id}`)
  },

  listSocialLinks(companyId) {
    return adminApi.get(`/companies/${companyId}/social-links`)
  },

  addSocialLink(companyId, body) {
    return adminApi.post(`/companies/${companyId}/social-links`, body)
  },

  updateSocialLink(companyId, linkId, body) {
    return adminApi.put(`/companies/${companyId}/social-links/${linkId}`, body)
  },

  removeSocialLink(companyId, linkId) {
    return adminApi.delete(`/companies/${companyId}/social-links/${linkId}`)
  },
}
