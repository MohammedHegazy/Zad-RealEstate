<script setup>
import { useRouter } from 'vue-router'
import { formatArea, formatPrice, formatPropertyType } from '@/composables/useFormatters.js'
import { getEstateImage, getEstateLocation, getListingType } from '@/utils/estate.js'
import FavoriteButton from '@/components/estates/FavoriteButton.vue'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
})

const router = useRouter()

const LISTING_ICONS = {
  sale: 'bi-cash-stack',
  rent: 'bi-calendar-check',
}

function goToEstate() {
  router.push(`/estates/${props.estate.id}`)
}

function goToAnalyze(event) {
  event.stopPropagation()
  router.push(`/estates/${props.estate.id}/analyze`)
}

function shareEstate(event) {
  event.stopPropagation()
  const url = `${window.location.origin}/estates/${props.estate.id}`
  if (navigator.share) {
    navigator.share({ title: props.estate.name, url })
  } else {
    navigator.clipboard?.writeText(url)
  }
}

function goToEstateSection(event, hash = '') {
  event.stopPropagation()
  router.push(`/estates/${props.estate.id}${hash}`)
}
</script>

<template>
  <article class="property-card">
    <div class="property-card__media" @click="goToEstate">
      <img
        :src="getEstateImage(estate)"
        :alt="estate.name"
        class="property-card__image"
        loading="lazy"
      />
      <div class="property-card__image-shade" />
      <div class="property-card__badges">
        <span class="property-card__badge property-card__badge--type">
          <i :class="['bi', LISTING_ICONS[estate.listing_type] ?? 'bi-house']" />
          {{ getListingType(estate) }}
        </span>
        <span v-if="estate.is_furnished" class="property-card__badge property-card__badge--furnished">
          <i class="bi bi-lamp" />
          مفروش
        </span>
      </div>
      <div class="property-card__price-float">
        <span class="property-card__price-value">
          {{
            estate.monthly_rent && Number(estate.monthly_rent) > 0
              ? formatPrice(estate.monthly_rent, { suffix: '/شهر' })
              : formatPrice(estate.price)
          }}
        </span>
      </div>

      <div class="property-card__actions" @click.stop>
        <FavoriteButton :estate-id="estate.id" size="sm" icon-only class="property-card__action-fav" />
        <button
          type="button"
          class="property-card__action-btn"
          data-action="map"
          @click="goToEstateSection($event, '#map')"
        >
          <i class="bi bi-geo-alt" />
        </button>
        <button
          type="button"
          class="property-card__action-btn"
          data-action="share"
          @click="shareEstate"
        >
          <i class="bi bi-share" />
        </button>
        <button
          type="button"
          class="property-card__action-btn"
          data-action="analyze"
          @click="goToAnalyze"
        >
          <i class="bi bi-calculator" />
        </button>
        <button
          type="button"
          class="property-card__action-btn"
          data-action="predict"
          @click="goToEstateSection($event, '#price-prediction')"
        >
          <i class="bi bi-robot" />
        </button>
      </div>
    </div>

    <div class="property-card__body">
      <div class="property-card__meta">
        <span class="property-card__type-pill">
          {{ formatPropertyType(estate.kind_text) }}
        </span>
        <span v-if="estate.views" class="property-card__views">
          <i class="bi bi-eye" />
          {{ estate.views }}
        </span>
      </div>

      <h3 class="property-card__title">
        <RouterLink :to="`/estates/${estate.id}`">{{ estate.name }}</RouterLink>
      </h3>

      <p class="property-card__location">
        <i class="bi bi-geo-alt" />
        {{ getEstateLocation(estate) }}
      </p>

      <div class="property-card__specs">
        <span v-if="estate.num_of_bedrooms" class="property-card__spec">
          <i class="bi bi-door-open" />
          <span>{{ estate.num_of_bedrooms }} <small>غرف</small></span>
        </span>
        <span v-if="estate.num_of_bathrooms" class="property-card__spec">
          <i class="bi bi-droplet" />
          <span>{{ estate.num_of_bathrooms }} <small>حمام</small></span>
        </span>
        <span v-if="formatArea(estate.space_of_estate)" class="property-card__spec">
          <i class="bi bi-bounding-box" />
          <span>{{ formatArea(estate.space_of_estate) }}</span>
        </span>
      </div>

      <div class="property-card__footer">
        <RouterLink :to="`/estates/${estate.id}`" class="property-card__link">
          <span>عرض التفاصيل</span>
          <i class="bi bi-arrow-left-short" />
        </RouterLink>
      </div>
    </div>
  </article>
</template>
