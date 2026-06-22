<script setup>
import L from 'leaflet'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

import {
  DEFAULT_MAP_CENTER,
  DEFAULT_MAP_ZOOM,
  formatCoordinates,
  hasMapCoordinates,
} from '@/utils/map.js'

const props = defineProps({
  latitude: {
    type: [String, Number],
    default: '',
  },
  longitude: {
    type: [String, Number],
    default: '',
  },
  focus: {
    type: Object,
    default: null,
  },
  focusZoom: {
    type: Number,
    default: 13,
  },
  height: {
    type: String,
    default: '320px',
  },
  hasError: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:latitude', 'update:longitude'])

const mapRoot = ref(null)
let mapInstance = null
let pickerMarker = null
let mapReady = false

const hasSelection = computed(() => {
  const lat = Number(props.latitude)
  const lng = Number(props.longitude)

  return Number.isFinite(lat) && Number.isFinite(lng)
})

const coordinatesLabel = computed(() => {
  if (!hasSelection.value) return 'لم يتم تحديد موقع بعد — انقر على الخريطة'
  return formatCoordinates(props.latitude, props.longitude)
})

function createPickerIcon() {
  return L.divIcon({
    className: '',
    html: '<span class="leaflet-map__pin leaflet-map__pin--active"></span>',
    iconSize: [28, 28],
    iconAnchor: [14, 28],
  })
}

function setCoords(lat, lng) {
  emit('update:latitude', Number(lat.toFixed(8)))
  emit('update:longitude', Number(lng.toFixed(8)))
}

function placeMarker(latlng, { pan = false } = {}) {
  if (!mapInstance) return

  if (pickerMarker) {
    pickerMarker.setLatLng(latlng)
  } else {
    pickerMarker = L.marker(latlng, {
      draggable: true,
      icon: createPickerIcon(),
      autoPan: true,
    }).addTo(mapInstance)

    pickerMarker.on('dragend', () => {
      const position = pickerMarker.getLatLng()
      setCoords(position.lat, position.lng)
    })
  }

  if (pan) {
    mapInstance.setView(latlng, Math.max(mapInstance.getZoom(), 15), { animate: true })
  }
}

function clearMarker() {
  if (!mapInstance || !pickerMarker) return
  mapInstance.removeLayer(pickerMarker)
  pickerMarker = null
}

function handleMapClick(event) {
  const { lat, lng } = event.latlng
  placeMarker([lat, lng])
  setCoords(lat, lng)
}

function getInitialCenter() {
  if (hasSelection.value) {
    return [Number(props.latitude), Number(props.longitude)]
  }

  if (hasMapCoordinates(props.focus)) {
    return [Number(props.focus.latitude), Number(props.focus.longitude)]
  }

  return [DEFAULT_MAP_CENTER.latitude, DEFAULT_MAP_CENTER.longitude]
}

function getInitialZoom() {
  if (hasSelection.value) return 16
  if (hasMapCoordinates(props.focus)) return props.focus.zoom ?? props.focusZoom
  return DEFAULT_MAP_ZOOM
}

function initMap() {
  if (!mapRoot.value || mapInstance) return

  mapInstance = L.map(mapRoot.value, {
    zoomControl: true,
    scrollWheelZoom: true,
  })

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(mapInstance)

  mapInstance.setView(getInitialCenter(), getInitialZoom())

  if (hasSelection.value) {
    placeMarker([Number(props.latitude), Number(props.longitude)])
  }

  mapInstance.on('click', handleMapClick)
  mapReady = true
}

watch(
  () => [props.latitude, props.longitude],
  () => {
    if (!mapReady) return

    if (hasSelection.value) {
      placeMarker([Number(props.latitude), Number(props.longitude)])
      return
    }

    clearMarker()
  },
)

watch(
  () => props.focus,
  (focus) => {
    if (!mapReady || !hasMapCoordinates(focus) || hasSelection.value) return

    const zoom = focus.zoom ?? props.focusZoom
    mapInstance.flyTo([Number(focus.latitude), Number(focus.longitude)], zoom, { duration: 0.6 })
  },
  { deep: true },
)

onMounted(() => {
  initMap()
  window.setTimeout(() => mapInstance?.invalidateSize(), 150)
})

onUnmounted(() => {
  mapInstance?.remove()
  mapInstance = null
  pickerMarker = null
  mapReady = false
})
</script>

<template>
  <div class="map-location-picker" :class="{ 'map-location-picker--error': hasError }">
    <p class="map-location-picker__hint">
      <i class="bi bi-cursor"></i>
      انقر على الخريطة لتحديد موقع العقار. يمكنك سحب العلامة لتعديل الموقع.
    </p>

    <div class="leaflet-map map-location-picker__map" :style="{ '--map-height': height }">
      <div ref="mapRoot" class="leaflet-map__canvas"></div>
    </div>

    <p
      class="map-location-picker__coords"
      :class="{ 'map-location-picker__coords--empty': !hasSelection }"
    >
      <i class="bi bi-geo-alt"></i>
      {{ coordinatesLabel }}
    </p>
  </div>
</template>
