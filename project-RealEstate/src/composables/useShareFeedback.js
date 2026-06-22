import { ref } from 'vue'

const SHARE_MESSAGES = {
  native: 'تم فتح قائمة المشاركة.',
  clipboard: 'تم نسخ رابط العقار.',
  cancelled: '',
  failed: 'تعذّر المشاركة. انسخ الرابط يدوياً من شريط العنوان.',
}

export function useShareFeedback(messages = SHARE_MESSAGES) {
  const shareMessage = ref('')
  const shareVariant = ref('success')

  function showShareFeedback(result) {
    if (result.cancelled) {
      shareMessage.value = ''
      return
    }

    if (result.success) {
      shareMessage.value = messages[result.method] ?? messages.native
      shareVariant.value = 'success'
    } else {
      shareMessage.value = messages.failed
      shareVariant.value = 'error'
    }

    window.clearTimeout(showShareFeedback._timer)
    showShareFeedback._timer = window.setTimeout(() => {
      shareMessage.value = ''
    }, 4000)
  }

  return { shareMessage, shareVariant, showShareFeedback }
}
