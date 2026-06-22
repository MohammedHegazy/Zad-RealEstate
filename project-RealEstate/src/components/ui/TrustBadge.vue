<script setup>
import StarRating from '@/components/ui/StarRating.vue'

defineProps({
  trustScore: {
    type: Number,
    default: null,
  },
  averageRating: {
    type: Number,
    default: null,
  },
  reviewsCount: {
    type: Number,
    default: null,
  },
  isVerified: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <div class="trust-badge">
    <span v-if="isVerified" class="trust-badge__verified">
      <i class="bi bi-patch-check-fill"></i>
      موثّق
    </span>
    <span v-if="trustScore != null" class="trust-badge__score">
      <i class="bi bi-shield-check"></i>
      ثقة {{ trustScore }}%
    </span>
    <span v-if="averageRating != null" class="trust-badge__rating">
      <StarRating :rating="averageRating" size="sm" show-value />
      <span v-if="reviewsCount">({{ reviewsCount }})</span>
    </span>
  </div>
</template>
