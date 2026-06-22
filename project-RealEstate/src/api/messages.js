import { api } from './client.js'

export const messagesService = {
  listConversations(params = {}) {
    return api.get('/messages', { params })
  },

  getConversation(userId, params = {}) {
    return api.get(`/messages/conversation/${userId}`, { params })
  },

  send(receiverId, text) {
    return api.post('/messages', { receiver_id: receiverId, text })
  },

  getById(messageId) {
    return api.get(`/messages/${messageId}`)
  },

  markAsRead(messageId) {
    return api.patch(`/messages/${messageId}/read`)
  },

  remove(messageId) {
    return api.delete(`/messages/${messageId}`)
  },

  getUnreadCount() {
    return api.get('/messages/unread-count')
  },

  getOnlineStatus(userId) {
    return api.get(`/users/${userId}/online`)
  },
}
