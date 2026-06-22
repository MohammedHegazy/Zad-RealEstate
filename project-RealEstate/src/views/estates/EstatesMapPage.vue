<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import LeafletMap from '@/components/map/LeafletMap.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSearchInput from '@/components/ui/AppSearchInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import { citiesService } from '@/api/cities.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { useEstateMapData } from '@/composables/useEstateMapData.js'
import { hasMapCoordinates } from '@/utils/map.js'

const route = useRoute()
const router = useRouter()

const search = ref('')
const cityId = ref('')
const radiusKm = ref('10')
const cities = ref([])
const locating = ref(false)
const geoError = ref('')
let skipNextBounds = false
let lastBoundsKey = ''
let initialLoadDone = false

function boundsKey(bounds) {
  return [
    bounds.north,
    bounds.south,
    bounds.east,
    bounds.west,
  ]
    .map((value) => Number(value).toFixed(3))
    .join('|')
}

const { loading, error, markers, center, zoom, tileLayer, loadMap, loadNearby } = useEstateMapData()

const radiusOptions = [
  { value: '5', label: '5 كم' },
  { value: '10', label: '10 كم' },
  { value: '25', label: '25 كم' },
  { value: '50', label: '50 كم' },
]

const cityOptions = computed(() => [
  { value: '', label: 'كل المدن' },
  ...cities.value
    .filter((city) => hasMapCoordinates(city))
    .map((city) => ({
      value: String(city.id),
      label: city.name,
    })),
])

const filteredMarkers = computed(() => {
  const query = search.value.trim().toLowerCase()
  if (!query) return markers.value

  return markers.value.filter((marker) => {
    const haystack = [
      marker.name,
      marker.place?.name,
      marker.place?.city?.name,
      marker.kind_text,
      marker.type_text,
    ]
      .filter(Boolean)
      .join(' ')
      .toLowerCase()

    return haystack.includes(query)
  })
})

function syncQuery(latitude, longitude, nextZoom = zoom.value) {
  router.replace({
    query: {
      ...route.query,
      lat: latitude,
      lng: longitude,
      zoom: nextZoom,
    },
  })
}

async function handleBoundsChange(bounds) {
  if (!initialLoadDone) return

  if (skipNextBounds) {
    skipNextBounds = false
    return
  }

  const key = boundsKey(bounds)
  if (key === lastBoundsKey) return
  lastBoundsKey = key

  await loadMap(bounds)
}

async function handleCityChange(value) {
  cityId.value = value
  if (!value) {
    await loadMap()
    return
  }

  const city = cities.value.find((item) => String(item.id) === value)
  if (!city || !hasMapCoordinates(city)) return

  center.value = {
    latitude: Number(city.latitude),
    longitude: Number(city.longitude),
  }
  zoom.value = 12
  skipNextBounds = true
  syncQuery(city.latitude, city.longitude, 12)
  await loadNearby(city.latitude, city.longitude, Number(radiusKm.value))
}

async function handleRadiusChange(value) {
  radiusKm.value = value
  if (!hasMapCoordinates(center.value)) return
  skipNextBounds = true
  await loadNearby(center.value.latitude, center.value.longitude, Number(radiusKm.value))
}

async function locateMe() {
  if (!navigator.geolocation) {
    geoError.value = 'المتصفح لا يدعم تحديد الموقع.'
    return
  }

  locating.value = true
  geoError.value = ''

  navigator.geolocation.getCurrentPosition(
    async (position) => {
      const { latitude, longitude } = position.coords
      center.value = { latitude, longitude }
      zoom.value = 13
      skipNextBounds = true
      syncQuery(latitude, longitude, 13)
      await loadNearby(latitude, longitude, Number(radiusKm.value))
      locating.value = false
    },
    () => {
      geoError.value = 'تعذّر الوصول إلى موقعك. تحقق من أذونات المتصفح.'
      locating.value = false
    },
    { enableHighAccuracy: true, timeout: 10000 },
  )
}

onMounted(async () => {
  try {
    const { data } = await citiesService.list({ per_page: 100 })
    cities.value = data ?? []
  } catch {
    cities.value = []
  }

  const queryLat = Number(route.query.lat)
  const queryLng = Number(route.query.lng)
  const queryZoom = Number(route.query.zoom)

  if (Number.isFinite(queryLat) && Number.isFinite(queryLng)) {
    center.value = { latitude: queryLat, longitude: queryLng }
    zoom.value = Number.isFinite(queryZoom) ? queryZoom : 13
    skipNextBounds = true
    await loadNearby(queryLat, queryLng, Number(radiusKm.value))
    initialLoadDone = true
    return
  }

  await loadMap()
  initialLoadDone = true
})
</script>

<template>
  <div class="estates-map-page">
    <div class="container">
      <PageIntro
        overline="استكشف"
        title="البحث على الخريطة"
        description="ابحث عن العقارات حسب المنطقة، المدينة، أو موقعك الحالي — ثم تصفّح النتائج مباشرة على الخريطة."
        step="الخريطة"
      />

      <div class="estates-map-page__toolbar">
        <AppSearchInput
          v-model="search"
          placeholder="ابحث باسم العقار أو المنطقة..."
          size="md"
        />
        <AppSelect
          :model-value="cityId"
          size="md"
          :options="cityOptions"
          @update:model-value="handleCityChange"
        />
        <AppSelect
          :model-value="radiusKm"
          size="md"
          :options="radiusOptions"
          @update:model-value="handleRadiusChange"
        />
        <AppButton variant="outline" :disabled="locating" @click="locateMe">
          <i class="bi bi-crosshair"></i>
          {{ locating ? 'جاري التحديد...' : 'موقعي' }}
        </AppButton>
        <AppButton to="/estates" variant="ghost" size="sm">
          القائمة
        </AppButton>
      </div>

      <p v-if="geoError" class="estates-map-page__hint estates-map-page__hint--error">{{ geoError }}</p>
      <p v-else class="estates-map-page__hint">
        <i class="bi bi-info-circle"></i>
        حرّك الخريطة لتحديث العقارات في المنطقة المعروضة، أو اختر مدينة / موقعك.
      </p>

      <div class="estates-map-page__layout">
        <div class="estates-map-page__map-wrap">
          <LeafletMap
            :center="center"
            :zoom="zoom"
            :markers="filteredMarkers"
            :tile-url="tileLayer.url"
            :attribution="tileLayer.attribution"
            height="min(68vh, 640px)"
            :emit-bounds="true"
            :show-origin="Boolean(cityId || route.query.lat)"
            @bounds-change="handleBoundsChange"
          />

          <LoadingSpinner v-if="loading" class="estates-map-page__loader" />

          <div v-if="error" class="estates-map-page__error">
            <ErrorAlert :message="error" @retry="loadMap" />
          </div>
        </div>

        <aside class="estates-map-page__results">
          <div class="estates-map-page__results-header">
            <h2>النتائج</h2>
            <span>{{ filteredMarkers.length }} عقار</span>
          </div>

          <p v-if="!filteredMarkers.length" class="estates-map-page__empty">
            لا توجد عقارات في هذه المنطقة. جرّب توسيع نطاق البحث أو اختيار مدينة أخرى.
          </p>

          <ul v-else class="estates-map-page__list">
            <li v-for="marker in filteredMarkers" :key="marker.id">
              <RouterLink :to="`/estates/${marker.id}`" class="estates-map-page__item">
                <div>
                  <strong>{{ marker.name }}</strong>
                  <p v-if="marker.place?.name" class="estates-map-page__item-place">
                    {{ marker.place.name }}
                  </p>
                </div>
                <div class="estates-map-page__item-meta">
                  <span>{{ marker.price ? formatPrice(marker.price) : '—' }}</span>
                  <span v-if="marker.distance_km != null">{{ marker.distance_km }} كم</span>
                </div>
              </RouterLink>
            </li>
          </ul>
        </aside>
      </div>
    </div>
  </div>
</template>
