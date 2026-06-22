import { api } from './client.js'

/**
 * Auth endpoints — all requests go through the shared api client.
 */
export const authService = {
  login(credentials) {
    return api.post('/login', credentials)
  },

  register(payload) {
    return api.post('/register', payload)
  },

  logout() {
    return api.post('/logout')
  },

  me() {
    return api.get('/me')
  },
}
