import { AUTH_TOKEN_KEY } from '@/config/api.js'
import { request } from '@/api/client.js'

function withAdminToken(options = {}) {
  return {
    ...options,
    token: options.token ?? localStorage.getItem(AUTH_TOKEN_KEY),
  }
}

export const adminApi = {
  get(path, options = {}) {
    return request('GET', `/admin${path}`, withAdminToken(options))
  },

  post(path, body, options = {}) {
    return request('POST', `/admin${path}`, withAdminToken({ ...options, body }))
  },

  put(path, body, options = {}) {
    return request('PUT', `/admin${path}`, withAdminToken({ ...options, body }))
  },

  patch(path, body, options = {}) {
    return request('PATCH', `/admin${path}`, withAdminToken({ ...options, body }))
  },

  delete(path, options = {}) {
    return request('DELETE', `/admin${path}`, withAdminToken(options))
  },
}
