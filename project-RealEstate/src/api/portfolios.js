import { api } from './client.js'

export const portfoliosService = {
  list(params = {}) {
    return api.get('/investment-portfolios', { params })
  },

  getById(id) {
    return api.get(`/investment-portfolios/${id}`)
  },

  create(payload) {
    return api.post('/investment-portfolios', payload)
  },

  properties(portfolioId) {
    return api.get(`/investment-portfolios/${portfolioId}/properties`)
  },

  addEstate(portfolioId, payload) {
    return api.post(`/investment-portfolios/${portfolioId}/properties`, payload)
  },

  removeEstate(portfolioId, propertyId) {
    return api.delete(`/investment-portfolios/${portfolioId}/properties/${propertyId}`)
  },

  items(params = {}) {
    return api.get('/my/portfolio-items', { params })
  },

  addItem(payload) {
    return api.post('/my/portfolio-items', payload)
  },

  updateItem(id, payload) {
    return api.put(`/my/portfolio-items/${id}`, payload)
  },

  removeItem(id) {
    return api.delete(`/my/portfolio-items/${id}`)
  },
}
