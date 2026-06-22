import { defineStore } from 'pinia'
import { ref } from 'vue'

let resolveCallback = null

export const useConfirmStore = defineStore('confirm', () => {
  const visible = ref(false)
  const title = ref('')
  const message = ref('')
  const confirmText = ref('تأكيد')
  const cancelText = ref('إلغاء')
  const variant = ref('danger')

  function show(options = {}) {
    title.value = options.title ?? 'تأكيد الإجراء'
    message.value = options.message ?? 'هل أنت متأكد؟'
    confirmText.value = options.confirmText ?? 'تأكيد'
    cancelText.value = options.cancelText ?? 'إلغاء'
    variant.value = options.variant ?? 'danger'
    visible.value = true

    return new Promise((resolve) => {
      resolveCallback = resolve
    })
  }

  function resolve() {
    visible.value = false
    if (resolveCallback) resolveCallback(true)
    resolveCallback = null
  }

  function cancel() {
    visible.value = false
    if (resolveCallback) resolveCallback(false)
    resolveCallback = null
  }

  return { visible, title, message, confirmText, cancelText, variant, show, resolve, cancel }
})
