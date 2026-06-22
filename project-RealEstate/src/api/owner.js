import { api } from './client.js'

export const ownerService = {
  dashboard() {
    return api.get('/my/owner-dashboard')
  },
}
