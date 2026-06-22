import { formatCoordinates, hasMapCoordinates } from '@/utils/map.js'

export function getCityPlacesCount(city) {
  return city?.places_count ?? city?.places?.length ?? 0
}

export function getPlaceCityName(place) {
  return place?.city?.name ?? '—'
}

export function formatLocationCoordinates(latitude, longitude) {
  if (!hasMapCoordinates({ latitude, longitude })) return 'لم يُحدد الموقع بعد'
  return formatCoordinates(latitude, longitude)
}
