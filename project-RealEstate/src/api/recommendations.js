import { api } from './client.js'

export const recommendationsService = {
  list(params = {}) {
    return api.get('/recommendations', { params })
  },

  top(params = {}) {
    return api.get('/recommendations/top', { params })
  },

  refresh(params = {}) {
    return api.post('/recommendations/refresh', null, { params })
  },
}
