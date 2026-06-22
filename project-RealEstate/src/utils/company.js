import { getUserName } from '@/utils/user.js'

export function getCompanyOwnerName(company) {
  if (!company?.user) return '—'
  return getUserName(company.user)
}

export function getCompanyLocation(company) {
  const place = company?.place?.name
  const city = company?.place?.city?.name
  if (place && city) return `${place}، ${city}`
  return place || city || '—'
}
