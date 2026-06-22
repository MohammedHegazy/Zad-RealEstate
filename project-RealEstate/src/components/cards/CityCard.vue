<script setup>
const PLACEHOLDER =
  'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=600&q=80'

defineProps({
  city: {
    type: Object,
    required: true,
  },
})

function getCityImage(city) {
  return city.image_url ?? PLACEHOLDER
}
</script>

<template>
  <RouterLink :to="`/cities/${city.id}`" class="city-card">
    <img
      :src="getCityImage(city)"
      :alt="city.name"
      class="city-card__image"
      loading="lazy"
    />
    <div class="city-card__overlay"></div>
    <div class="city-card__content">
      <h3 class="city-card__name">{{ city.name }}</h3>
      <p v-if="city.places_count" class="city-card__count">
        {{ city.places_count }} منطقة
      </p>
    </div>
  </RouterLink>
</template>
