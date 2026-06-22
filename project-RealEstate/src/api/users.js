import { api } from './client.js'

export const usersService = {
  search(params = {}) {
    return api.get('/users/search', { params })
  },

  getById(id) {
    return api.get(`/users/${id}`)
  },
}
