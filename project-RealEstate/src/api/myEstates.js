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

  uploadImage(estateId, formData) {
    return api.post(`/my/estates/${estateId}/images`, formData)
  },

  setPrimaryImage(estateId, imageId) {
    return api.patch(`/my/estates/${estateId}/images/${imageId}/primary`)
  },

  removeImage(estateId, imageId) {
    return api.delete(`/my/estates/${estateId}/images/${imageId}`)
  },

  reorderImages(estateId, imageIds) {
    return api.post(`/my/estates/${estateId}/images/reorder`, { image_ids: imageIds })
  },

  uploadVideo(estateId, formData) {
    return api.post(`/my/estates/${estateId}/videos`, formData)
  },

  removeVideo(estateId, videoId) {
    return api.delete(`/my/estates/${estateId}/videos/${videoId}`)
  },

  uploadAd(estateId, formData) {
    return api.post(`/my/estates/${estateId}/ads`, formData)
  },

  setMainAd(estateId, adId) {
    return api.patch(`/my/estates/${estateId}/ads/${adId}/main`)
  },

  removeAd(estateId, adId) {
    return api.delete(`/my/estates/${estateId}/ads/${adId}`)
  },

  reorderAds(estateId, adIds) {
    return api.post(`/my/estates/${estateId}/ads/reorder`, { ad_ids: adIds })
  },
}
