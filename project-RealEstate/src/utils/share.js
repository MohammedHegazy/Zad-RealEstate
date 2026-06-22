/**
 * Build a Web Share API payload, falling back to url-only when needed.
 */
function buildSharePayload({ title, text, url }) {
  const candidates = [
    { title, text, url },
    { title, url },
    { text, url },
    { url },
  ]

  if (typeof navigator.canShare === 'function') {
    return candidates.find((payload) => navigator.canShare(payload)) ?? { url }
  }

  return { title, text, url }
}

/**
 * Copy text using the Clipboard API or execCommand fallback (works on HTTP LAN).
 */
async function copyToClipboard(text) {
  if (navigator.clipboard?.writeText) {
    try {
      await navigator.clipboard.writeText(text)
      return true
    } catch {
      // fall through
    }
  }

  const textarea = document.createElement('textarea')
  textarea.value = text
  textarea.setAttribute('readonly', '')
  textarea.style.position = 'fixed'
  textarea.style.opacity = '0'
  document.body.appendChild(textarea)
  textarea.select()

  try {
    return document.execCommand('copy')
  } finally {
    document.body.removeChild(textarea)
  }
}

/**
 * Share a property link — native sheet on mobile when available, clipboard otherwise.
 * Must be called directly from a click handler (no await before navigator.share).
 */
export async function shareProperty({ title, text, url }) {
  const payload = buildSharePayload({ title, text, url })

  if (typeof navigator.share === 'function') {
    try {
      await navigator.share(payload)
      return { success: true, method: 'native' }
    } catch (error) {
      if (error?.name === 'AbortError') {
        return { success: false, cancelled: true, method: 'native' }
      }
    }
  }

  const copied = await copyToClipboard(url)
  if (copied) {
    return { success: true, method: 'clipboard' }
  }

  return { success: false, method: 'none' }
}
