export const DEFAULT_MAP_CENTER = {
  latitude: 33.5138,
  longitude: 36.2765,
}

export const DEFAULT_MAP_ZOOM = 12

export function hasMapCoordinates(item) {
  const lat = Number(item?.latitude)
  const lng = Number(item?.longitude)

  return (
    Number.isFinite(lat) &&
    Number.isFinite(lng) &&
    Math.abs(lat) >= 0.01 &&
    Math.abs(lng) >= 0.01 &&
    Math.abs(lat) <= 90 &&
    Math.abs(lng) <= 180
  )
}

export function formatCoordinates(latitude, longitude) {
  const lat = Number(latitude).toFixed(6)
  const lng = Number(longitude).toFixed(6)
  return `${lat}°, ${lng}°`
}

export function toLeafletLatLng(item) {
  return [Number(item.latitude), Number(item.longitude)]
}
