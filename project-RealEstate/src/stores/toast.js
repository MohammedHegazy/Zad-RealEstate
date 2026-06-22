import { defineStore } from 'pinia'

let toastId = 0

export const useToastStore = defineStore('toast', {
  state: () => ({
    toasts: [],
  }),
  actions: {
    addToast(type, message, options = {}) {
      const id = ++toastId
      const duration = options.duration ?? 4000
      this.toasts.push({ id, type, message, duration })
      if (duration > 0) {
        setTimeout(() => this.removeToast(id), duration)
      }
    },
    removeToast(id) {
      const idx = this.toasts.findIndex((t) => t.id === id)
      if (idx !== -1) this.toasts.splice(idx, 1)
    },
    success(message, options) {
      this.addToast('success', message, options)
    },
    error(message, options) {
      this.addToast('error', message, options)
    },
    info(message, options) {
      this.addToast('info', message, options)
    },
    warning(message, options) {
      this.addToast('warning', message, options)
    },
    clear() {
      this.toasts.splice(0)
    },
  },
})
