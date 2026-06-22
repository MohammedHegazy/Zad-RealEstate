import { api } from './client.js'

export const investmentsService = {
  myAnalyses(params = {}) {
    return api.get('/my/investment-analyses', { params })
  },

  getById(id) {
    return api.get(`/my/investment-analyses/${id}`)
  },

  storeByEstate(estateId, payload) {
    return api.post(`/estates/${estateId}/investment-analyses`, payload)
  },

  store(payload) {
    return api.post('/investment-analyses', payload)
  },

  update(id, payload) {
    return api.put(`/investment-analyses/${id}`, payload)
  },

  remove(id) {
    return api.delete(`/investment-analyses/${id}`)
  },
}
