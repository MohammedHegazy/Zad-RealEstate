import { API_BASE_URL, AUTH_TOKEN_KEY } from '@/config/api.js'
import { createApiError } from '@/api/errors.js'
import { adminApi } from './client.js'

export const adminTrustService = {
  listReviews(params = {}) {
    return adminApi.get('/trust/reviews', { params })
  },

  pendingReviews(params = {}) {
    return adminApi.get('/trust/reviews/pending', { params })
  },

  getPropertyReview(id) {
    return adminApi.get(`/trust/property-reviews/${id}`)
  },

  getAgentReview(id) {
    return adminApi.get(`/trust/agent-reviews/${id}`)
  },

  getCompanyReview(id) {
    return adminApi.get(`/trust/company-reviews/${id}`)
  },

  listVerifications(params = {}) {
    return adminApi.get('/trust/verifications', { params })
  },

  pendingVerifications(params = {}) {
    return adminApi.get('/trust/verifications/pending', { params })
  },

  getVerification(id) {
    return adminApi.get(`/trust/verifications/${id}`)
  },

  approvePropertyReview(id) {
    return adminApi.post(`/trust/property-reviews/${id}/approve`)
  },

  rejectPropertyReview(id, adminNotes = '') {
    return adminApi.post(`/trust/property-reviews/${id}/reject`, { admin_notes: adminNotes })
  },

  deletePropertyReview(id) {
    return adminApi.delete(`/trust/property-reviews/${id}`)
  },

  approveAgentReview(id) {
    return adminApi.post(`/trust/agent-reviews/${id}/approve`)
  },

  rejectAgentReview(id, adminNotes = '') {
    return adminApi.post(`/trust/agent-reviews/${id}/reject`, { admin_notes: adminNotes })
  },

  deleteAgentReview(id) {
    return adminApi.delete(`/trust/agent-reviews/${id}`)
  },

  approveCompanyReview(id) {
    return adminApi.post(`/trust/company-reviews/${id}/approve`)
  },

  rejectCompanyReview(id, adminNotes = '') {
    return adminApi.post(`/trust/company-reviews/${id}/reject`, { admin_notes: adminNotes })
  },

  deleteCompanyReview(id) {
    return adminApi.delete(`/trust/company-reviews/${id}`)
  },

  approveVerification(id, adminNotes = '') {
    return adminApi.post(`/trust/verifications/${id}/approve`, { admin_notes: adminNotes })
  },

  rejectVerification(id, adminNotes = '') {
    return adminApi.post(`/trust/verifications/${id}/reject`, { admin_notes: adminNotes })
  },

  recalculateAgentTrust(agentId) {
    return adminApi.post(`/trust/agents/${agentId}/recalculate-trust`)
  },

  recalculateCompanyTrust(companyId) {
    return adminApi.post(`/trust/companies/${companyId}/recalculate-trust`)
  },
}

export async function downloadVerificationDocument(id, filename = 'verification-document') {
  const token = localStorage.getItem(AUTH_TOKEN_KEY)
  const base = API_BASE_URL.replace(/\/$/, '')
  const response = await fetch(`${base}/admin/trust/verifications/${id}/document`, {
    headers: {
      Accept: 'application/octet-stream',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  })

  const contentType = response.headers.get('content-type') ?? ''
  const payload = contentType.includes('application/json') ? await response.json() : null

  if (!response.ok || (payload && payload.success === false)) {
    throw createApiError(response, payload)
  }

  const blob = await response.blob()
  const disposition = response.headers.get('content-disposition') ?? ''
  const match = disposition.match(/filename="?([^"]+)"?/)
  const resolvedName = match?.[1] ?? filename

  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = resolvedName
  link.click()
  URL.revokeObjectURL(url)
}
