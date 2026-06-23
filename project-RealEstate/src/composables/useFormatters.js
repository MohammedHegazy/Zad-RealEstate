const arNumber = new Intl.NumberFormat('ar-SY')

/**
 * Format a numeric price with currency label.
 */
export function formatPrice(value, { currency = 'ل.س', suffix = '' } = {}) {
  const num = Number(value)
  if (!value || Number.isNaN(num)) return 'السعر عند الطلب'

  return `${arNumber.format(num)} ${currency}${suffix}`
}

/**
 * Format area in square meters.
 */
export function formatArea(value) {
  const num = Number(value)
  if (!value || Number.isNaN(num)) return null
  return `${arNumber.format(num)} م²`
}

/**
 * Translate common property type labels for display.
 */
export function formatPropertyType(type) {
  if (!type) return type
  return type
}

/**
 * Truncate text for card excerpts.
 */
export function truncate(text, maxLength = 120) {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return `${text.slice(0, maxLength).trim()}…`
}

/**
 * Format ROI decimal as percentage.
 */
export function formatRoi(value) {
  const num = Number(value)
  if (!value || Number.isNaN(num)) return null
  return `${(num * 100).toFixed(1)}%`
}

/**
 * Format a rating to one decimal.
 */
export function formatRating(value) {
  const num = Number(value)
  if (!value || Number.isNaN(num)) return '0.0'
  return num.toFixed(1)
}

/**
 * Format ISO date/time for admin displays.
 */
export function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleString('ar-SY')
}
