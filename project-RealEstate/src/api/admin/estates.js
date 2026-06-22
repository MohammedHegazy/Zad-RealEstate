import { appendFileList, appendFormValue, submitMultipartUpdate } from '@/api/formData.js'
import { adminApi } from './client.js'

export function buildEstateFormData(payload, files = {}) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    appendFormValue(formData, key, value)
  })

  appendFileList(formData, 'images', files.images)
  if (files.images?.length) {
    appendFormValue(formData, 'primary_image_index', files.primary_image_index ?? 0)
  }

  appendFileList(formData, 'videos', files.videos)

  appendFileList(formData, 'ads', files.ads)
  if (files.ads?.length) {
    appendFormValue(formData, 'main_ad_index', files.main_ad_index ?? 0)
  }

  return formData
}

export function estatePayloadHasFiles(files = {}) {
  return Boolean(files.images?.length || files.videos?.length || files.ads?.length)
}

export const adminEstatesService = {
  list(params = {}) {
    return adminApi.get('/estates', { params })
  },

  getById(id) {
    return adminApi.get(`/estates/${id}`)
  },

  create(body) {
    return adminApi.post('/estates', body)
  },

  update(id, body) {
    return submitMultipartUpdate(adminApi, `/estates/${id}`, body)
  },

  updateStatus(id, status) {
    return adminApi.patch(`/estates/${id}/status`, { status })
  },

  remove(id) {
    return adminApi.delete(`/estates/${id}`)
  },

  uploadImage(estateId, formData) {
    return adminApi.post(`/estates/${estateId}/images`, formData)
  },

  setPrimaryImage(estateId, imageId) {
    return adminApi.patch(`/estates/${estateId}/images/${imageId}/primary`)
  },

  removeImage(estateId, imageId) {
    return adminApi.delete(`/estates/${estateId}/images/${imageId}`)
  },

  uploadVideo(estateId, formData) {
    return adminApi.post(`/estates/${estateId}/videos`, formData)
  },

  removeVideo(estateId, videoId) {
    return adminApi.delete(`/estates/${estateId}/videos/${videoId}`)
  },

  uploadAd(estateId, formData) {
    return adminApi.post(`/estates/${estateId}/ads`, formData)
  },

  setMainAd(estateId, adId) {
    return adminApi.patch(`/estates/${estateId}/ads/${adId}/main`)
  },

  removeAd(estateId, adId) {
    return adminApi.delete(`/estates/${estateId}/ads/${adId}`)
  },

  reorderImages(estateId, imageIds) {
    return adminApi.post(`/estates/${estateId}/images/reorder`, { image_ids: imageIds })
  },

  reorderAds(estateId, adIds) {
    return adminApi.post(`/estates/${estateId}/ads/reorder`, { ad_ids: adIds })
  },
}
