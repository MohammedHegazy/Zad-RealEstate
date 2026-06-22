<script setup>
import L from 'leaflet'
import { onMounted, onUnmounted, ref, watch } from 'vue'

import { formatPrice } from '@/composables/useFormatters.js'
import { hasMapCoordinates, toLeafletLatLng } from '@/utils/map.js'

const props = defineProps({
  center: {
    type: Object,
    required: true,
  },
  zoom: {
    type: Number,
    default: 12,
  },
  markers: {
    type: Array,
    default: () => [],
  },
  tileUrl: {
    type: String,
    default: 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
  },
  attribution: {
    type: String,
    default:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  },
  height: {
    type: String,
    default: '320px',
  },
  interactive: {
    type: Boolean,
    default: true,
  },
  emitBounds: {
    type: Boolean,
    default: false,
  },
  showOrigin: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['bounds-change', 'ready'])

const mapRoot = ref(null)
let mapInstance = null
let markersLayer = null
let originMarker = null
let tileLayer = null
let boundsTimer = null
let suppressBoundsEmit = 0
let lastCenterKey = ''
let mapReady = false

function createMarkerIcon(active = false) {
  return L.divIcon({
    className: '',
    html: `<span class="leaflet-map__pin ${active ? 'leaflet-map__pin--active' : ''}"></span>`,
    iconSize: [28, 28],
    iconAnchor: [14, 28],
    popupAnchor: [0, -24],
  })
}

function popupContent(marker) {
  const price = marker.price ? formatPrice(marker.price) : '—'
  const distance = marker.distance_km != null ? `<p class="leaflet-map__popup-distance">${marker.distance_km} كم</p>` : ''
  const place = marker.place?.name ? `<p class="leaflet-map__popup-place">${marker.place.name}</p>` : ''

  return `
    <div class="leaflet-map__popup">
      <strong>${marker.name}</strong>
      ${place}
      <p class="leaflet-map__popup-price">${price}</p>
      ${distance}
      <a href="/estates/${marker.id}" class="leaflet-map__popup-link">عرض التفاصيل</a>
    </div>
  `
}

function runWithoutBoundsEmit(action, holdMs = 1000) {
  suppressBoundsEmit += 1
  action()
  window.setTimeout(() => {
    suppressBoundsEmit = Math.max(0, suppressBoundsEmit - 1)
  }, holdMs)
}

function renderMarkers() {
  if (!mapInstance || !markersLayer) return

  markersLayer.clearLayers()

  const validMarkers = props.markers.filter(hasMapCoordinates)
  const isSingle = validMarkers.length === 1

  validMarkers.forEach((marker) => {
    const leafletMarker = L.marker(toLeafletLatLng(marker), {
      icon: createMarkerIcon(isSingle),
    })

    leafletMarker.bindPopup(popupContent(marker))
    markersLayer.addLayer(leafletMarker)
  })

  if (isSingle && validMarkers.length) {
    runWithoutBoundsEmit(() => {
      mapInstance.setView(toLeafletLatLng(validMarkers[0]), Math.max(props.zoom, 15), { animate: false })
    })
  }
}

function renderOrigin() {
  if (!mapInstance) return

  if (originMarker) {
    mapInstance.removeLayer(originMarker)
    originMarker = null
  }

  if (!props.showOrigin || !hasMapCoordinates(props.center)) return

  const infoColor = getComputedStyle(document.documentElement).getPropertyValue('--color-info').trim()
  originMarker = L.circleMarker(toLeafletLatLng(props.center), {
    radius: 7,
    color: infoColor || '#1d4ed8',
    fillColor: infoColor || '#3b82f6',
    fillOpacity: 0.85,
    weight: 2,
  }).addTo(mapInstance)
}

function scheduleBoundsEmit() {
  if (!props.emitBounds || !mapInstance || suppressBoundsEmit > 0) return

  window.clearTimeout(boundsTimer)
  boundsTimer = window.setTimeout(() => {
    if (suppressBoundsEmit > 0) return
    const bounds = mapInstance.getBounds()
    emit('bounds-change', {
      north: bounds.getNorth(),
      south: bounds.getSouth(),
      east: bounds.getEast(),
      west: bounds.getWest(),
    })
  }, 450)
}

function initMap() {
  if (!mapRoot.value || mapInstance) return

  mapInstance = L.map(mapRoot.value, {
    zoomControl: true,
    scrollWheelZoom: props.interactive,
    dragging: props.interactive,
    doubleClickZoom: props.interactive,
  })

  tileLayer = L.tileLayer(props.tileUrl, {
    attribution: props.attribution,
    maxZoom: 19,
  }).addTo(mapInstance)

  markersLayer = L.layerGroup().addTo(mapInstance)

  if (hasMapCoordinates(props.center)) {
    runWithoutBoundsEmit(() => {
      mapInstance.setView(toLeafletLatLng(props.center), props.zoom)
    })
    lastCenterKey = `${props.center.latitude}|${props.center.longitude}|${props.zoom}`
  }

  renderMarkers()
  renderOrigin()

  if (props.emitBounds) {
    mapInstance.on('moveend', scheduleBoundsEmit)
    window.setTimeout(scheduleBoundsEmit, 500)
  }

  mapReady = true
  emit('ready', mapInstance)
}

function flyToCenter() {
  if (!mapInstance || !hasMapCoordinates(props.center)) return

  const nextKey = `${props.center.latitude}|${props.center.longitude}|${props.zoom}`
  if (nextKey === lastCenterKey) return
  lastCenterKey = nextKey

  runWithoutBoundsEmit(() => {
    mapInstance.flyTo(toLeafletLatLng(props.center), props.zoom, { duration: 0.8 })
  })
}

watch(
  () => props.markers,
  () => renderMarkers(),
  { deep: true },
)

watch(
  () => [props.center.latitude, props.center.longitude, props.zoom],
  () => {
    if (!mapReady) return
    flyToCenter()
    renderOrigin()
  },
)

watch(
  () => [props.tileUrl, props.attribution],
  () => {
    if (!mapInstance || !tileLayer) return
    tileLayer.setUrl(props.tileUrl)
    tileLayer.options.attribution = props.attribution
  },
)

onMounted(() => {
  initMap()
  window.setTimeout(() => mapInstance?.invalidateSize(), 120)
})

onUnmounted(() => {
  window.clearTimeout(boundsTimer)
  mapInstance?.remove()
  mapInstance = null
  mapReady = false
})
</script>

<template>
  <div class="leaflet-map" :style="{ '--map-height': height }">
    <div ref="mapRoot" class="leaflet-map__canvas"></div>
  </div>
</template>
