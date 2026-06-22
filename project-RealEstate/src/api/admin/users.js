import { adminApi } from './client.js'

export const adminUsersService = {
  list(params = {}) {
    return adminApi.get('/users', { params })
  },

  getById(id) {
    return adminApi.get(`/users/${id}`)
  },

  update(id, body) {
    return adminApi.put(`/users/${id}`, body)
  },

  remove(id) {
    return adminApi.delete(`/users/${id}`)
  },
}
