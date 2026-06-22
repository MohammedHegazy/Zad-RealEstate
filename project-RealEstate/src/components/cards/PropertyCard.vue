<script setup>
import AppBadge from '@/components/ui/AppBadge.vue'
import { formatArea, formatPrice, formatPropertyType } from '@/composables/useFormatters.js'
import { getEstateImage, getEstateLocation, getListingType } from '@/utils/estate.js'

defineProps({
  estate: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article class="property-card">
    <RouterLink :to="`/estates/${estate.id}`" class="property-card__media">
      <img
        :src="getEstateImage(estate)"
        :alt="estate.name"
        class="property-card__image"
        loading="lazy"
      />
      <div class="property-card__badges">
        <AppBadge variant="primary">{{ getListingType(estate) }}</AppBadge>
        <AppBadge v-if="estate.is_furnished" variant="dark">مفروش</AppBadge>
      </div>
    </RouterLink>

    <div class="property-card__body">
      <div class="property-card__meta">
        <AppBadge variant="default">{{ formatPropertyType(estate.kind_text) }}</AppBadge>
        <span v-if="estate.views" class="property-card__views">
          <i class="bi bi-eye"></i>
          {{ estate.views }}
        </span>
      </div>

      <h3 class="property-card__title">
        <RouterLink :to="`/estates/${estate.id}`">{{ estate.name }}</RouterLink>
      </h3>

      <p class="property-card__location">
        <i class="bi bi-geo-alt"></i>
        {{ getEstateLocation(estate) }}
      </p>

      <div class="property-card__specs">
        <span v-if="estate.num_of_bedrooms">
          <i class="bi bi-door-open"></i>
          {{ estate.num_of_bedrooms }} غرف
        </span>
        <span v-if="estate.num_of_bathrooms">
          <i class="bi bi-droplet"></i>
          {{ estate.num_of_bathrooms }} حمام
        </span>
        <span v-if="formatArea(estate.space_of_estate)">
          <i class="bi bi-bounding-box"></i>
          {{ formatArea(estate.space_of_estate) }}
        </span>
      </div>

      <div class="property-card__footer">
        <p class="property-card__price">
          {{
            estate.monthly_rent && Number(estate.monthly_rent) > 0
              ? formatPrice(estate.monthly_rent, { suffix: '/شهر' })
              : formatPrice(estate.price)
          }}
        </p>
        <RouterLink :to="`/estates/${estate.id}`" class="property-card__link">
          التفاصيل
          <i class="bi bi-arrow-left"></i>
        </RouterLink>
      </div>
    </div>
  </article>
</template>
