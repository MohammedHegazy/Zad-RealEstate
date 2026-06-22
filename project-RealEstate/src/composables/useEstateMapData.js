import { ref } from 'vue'

import { estatesService } from '@/api/estates.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { DEFAULT_MAP_CENTER, DEFAULT_MAP_ZOOM } from '@/utils/map.js'

export function useEstateMapData() {
  const loading = ref(false)
  const error = ref(null)
  const markers = ref([])
  const center = ref({ ...DEFAULT_MAP_CENTER })
  const zoom = ref(DEFAULT_MAP_ZOOM)
  const tileLayer = ref({
    url: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  })
  const mapConfigured = ref(false)

  async function loadMap(bounds = null) {
    loading.value = true
    error.value = null

    try {
      const params = bounds
        ? {
            north: bounds.north,
            south: bounds.south,
            east: bounds.east,
            west: bounds.west,
          }
        : undefined

      const { data } = await estatesService.map(params)

      markers.value = data?.markers ?? []

      // Only apply default center/zoom on the initial load — not when panning.
      if (!bounds) {
        if (data?.center) {
          center.value = {
            latitude: data.center.latitude,
            longitude: data.center.longitude,
          }
        }

        if (data?.default_zoom) {
          zoom.value = data.default_zoom
        }
      }

      if (!mapConfigured.value) {
        const provider = data?.providers?.leaflet ?? data?.providers?.openstreetmap
        if (provider?.tile_url) {
          tileLayer.value = {
            url: provider.tile_url,
            attribution: provider.attribution ?? tileLayer.value.attribution,
          }
        }
        mapConfigured.value = true
      }
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات الخريطة.')
      markers.value = []
    } finally {
      loading.value = false
    }
  }

  async function loadNearby(latitude, longitude, radiusKm = 10, limit = 30) {
    loading.value = true
    error.value = null

    try {
      const { data } = await estatesService.nearby({
        latitude,
        longitude,
        radius_km: radiusKm,
        limit,
      })

      markers.value = (data?.estates ?? []).map((estate) => ({
        id: estate.id,
        name: estate.name,
        price: estate.price,
        latitude: estate.latitude,
        longitude: estate.longitude,
        type_text: estate.type_text,
        kind_text: estate.kind_text,
        place: estate.place,
        distance_km: estate.distance_km,
      }))

      center.value = { latitude, longitude }
      zoom.value = 13
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر البحث بالقرب من هذا الموقع.')
      markers.value = []
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    markers,
    center,
    zoom,
    tileLayer,
    loadMap,
    loadNearby,
  }
}
