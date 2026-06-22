<script setup>
import { computed } from 'vue'

import LeafletMap from '@/components/map/LeafletMap.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { formatCoordinates, hasMapCoordinates } from '@/utils/map.js'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
})

const hasCoordinates = computed(() => hasMapCoordinates(props.estate))

const center = computed(() => ({
  latitude: Number(props.estate.latitude),
  longitude: Number(props.estate.longitude),
}))

const markers = computed(() =>
  hasCoordinates.value
    ? [
        {
          id: props.estate.id,
          name: props.estate.name,
          price: props.estate.price,
          latitude: props.estate.latitude,
          longitude: props.estate.longitude,
          place: props.estate.place,
        },
      ]
    : [],
)

const mapSearchLink = computed(() => {
  if (!hasCoordinates.value) return '/estates/map'
  return `/estates/map?lat=${props.estate.latitude}&lng=${props.estate.longitude}&zoom=15`
})
</script>

<template>
  <section v-if="hasCoordinates" id="map" class="estate-location-map">
    <div class="estate-location-map__header">
      <h2>
        <i class="bi bi-geo-alt-fill"></i>
        الموقع على الخريطة
      </h2>
      <AppButton :to="mapSearchLink" variant="outline" size="sm">
        استكشف على الخريطة
      </AppButton>
    </div>

    <LeafletMap
      :center="center"
      :zoom="15"
      :markers="markers"
      height="360px"
      :interactive="true"
      :emit-bounds="false"
    />

    <p class="estate-location-map__coords">
      <span><i class="bi bi-compass"></i> {{ formatCoordinates(estate.latitude, estate.longitude) }}</span>
    </p>
  </section>
</template>
