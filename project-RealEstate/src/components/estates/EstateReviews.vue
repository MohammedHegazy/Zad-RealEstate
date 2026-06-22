<script setup>
import ReviewForm from '@/components/reviews/ReviewForm.vue'
import StarRating from '@/components/ui/StarRating.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { formatRating } from '@/composables/useFormatters.js'

defineProps({
  estateId: {
    type: [Number, String],
    required: true,
  },
  ownerUserId: {
    type: [Number, String],
    default: null,
  },
  reviews: {
    type: Array,
    default: () => [],
  },
  summary: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['refresh'])

function reviewerName(user) {
  if (!user) return 'مستخدم'
  return `${user.fname} ${user.lname}`.trim() || user.username
}
</script>

<template>
  <section class="estate-reviews">
    <div class="estate-reviews__header">
      <h3 class="estate-reviews__title">
        <i class="bi bi-chat-square-text"></i>
        التقييمات
      </h3>

      <div v-if="summary?.reviews_count" class="estate-reviews__summary">
        <StarRating :rating="summary.average_rating" show-value size="lg" />
        <span class="estate-reviews__count">
          {{ summary.reviews_count }} تقييم — متوسط {{ formatRating(summary.average_rating) }}
        </span>
      </div>
    </div>

    <LoadingSpinner v-if="loading" size="sm" label="جاري تحميل التقييمات…" />

    <div v-else-if="reviews.length" class="estate-reviews__list">
      <article v-for="review in reviews" :key="review.id" class="estate-reviews__item">
        <div class="estate-reviews__item-header">
          <strong>{{ reviewerName(review.user) }}</strong>
          <StarRating :rating="review.rating" size="sm" />
        </div>
        <p v-if="review.review" class="estate-reviews__text">{{ review.review }}</p>
      </article>
    </div>

    <p v-else class="estate-reviews__empty">لا توجد تقييمات معتمدة بعد.</p>

    <ReviewForm
      type="property"
      :entity-id="estateId"
      :owner-user-id="ownerUserId"
      @submitted="emit('refresh')"
    />
  </section>
</template>
