import { api } from './client.js'

export const pricePredictionsService = {
  forEstate(estateId) {
    return api.post(`/estates/${estateId}/price-prediction`)
  },

  preview(payload) {
    return api.post('/price-predictions/preview', payload)
  },
}
