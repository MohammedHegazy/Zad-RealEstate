const PLACEHOLDER_IMAGE =
  'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=800&q=80'

/**
 * Display name for estate owner user object.
 */
export function getEstateOwnerName(estate) {
  const user = estate?.user
  if (!user) return '—'
  const fullName = `${user.fname ?? ''} ${user.lname ?? ''}`.trim()
  return fullName || user.username || user.email || '—'
}

/**
 * Resolve the best display image for an estate listing card.
 */
export function getEstateImage(estate) {
  const images = estate?.images ?? []
  const primary = images.find((img) => img.is_primary)
  return primary?.image_url ?? images[0]?.image_url ?? PLACEHOLDER_IMAGE
}

/**
 * Build a single-line location string from place + city.
 */
export function getEstateLocation(estate) {
  const place = estate?.place?.name
  const city = estate?.place?.city?.name

  if (place && city) return `${place}، ${city}`
  return place ?? city ?? 'موقع غير محدد'
}

/**
 * Infer listing type label from rent/sale signals.
 */
export function getListingType(estate) {
  if (estate?.monthly_rent && Number(estate.monthly_rent) > 0) {
    return 'للإيجار'
  }
  return 'للبيع'
}

/**
 * All gallery images sorted with primary first.
 */
export function getEstateImages(estate) {
  const images = [...(estate?.images ?? [])]
  return images.sort((a, b) => Number(b.is_primary) - Number(a.is_primary))
}

/**
 * Primary display price and whether it is rent.
 */
export function getEstatePrice(estate) {
  const isRent = estate?.monthly_rent && Number(estate.monthly_rent) > 0
  return {
    isRent,
    value: isRent ? estate.monthly_rent : estate.price,
    suffix: isRent ? '/شهر' : '',
  }
}

/**
 * Client-side listing type filter (backend has no rent/sale param).
 */
export function matchesListingType(estate, listingType) {
  if (!listingType) return true
  const isRent = estate?.monthly_rent && Number(estate.monthly_rent) > 0
  if (listingType === 'rent') return isRent
  if (listingType === 'sale') return !isRent
  return true
}

/**
 * Build WhatsApp link from estate social links or phone.
 */
export function getWhatsAppLink(estate) {
  const whatsapp = estate?.social_links?.find((l) => l.platform === 'whatsapp')
  if (whatsapp?.url) return whatsapp.url

  const phone = estate?.phone
  const code = estate?.country_code_phone ?? ''
  if (phone) {
    const digits = `${code}${phone}`.replace(/\D/g, '')
    return `https://wa.me/${digits}`
  }

  return null
}
