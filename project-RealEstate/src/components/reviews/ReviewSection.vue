<script setup>
import { computed } from 'vue'

import ReviewForm from '@/components/reviews/ReviewForm.vue'
import StarRating from '@/components/ui/StarRating.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { formatRating } from '@/composables/useFormatters.js'

const props = defineProps({
  reviewType: {
    type: String,
    default: '',
    validator: (value) => !value || ['agent', 'company'].includes(value),
  },
  entityId: {
    type: [Number, String],
    default: null,
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
  title: {
    type: String,
    default: 'التقييمات',
  },
})

const emit = defineEmits(['refresh'])

const showReviewForm = computed(() => Boolean(props.reviewType && props.entityId))

function reviewerName(user) {
  if (!user) return 'مستخدم'
  return `${user.fname} ${user.lname}`.trim() || user.username
}
</script>

<template>
  <section class="review-section">
    <div class="review-section__header">
      <h3 class="review-section__title">
        <i class="bi bi-chat-square-text"></i>
        {{ title }}
      </h3>
      <div v-if="summary?.reviews_count" class="review-section__summary">
        <StarRating :rating="summary.average_rating" show-value />
        <span>{{ summary.reviews_count }} تقييم — {{ formatRating(summary.average_rating) }}</span>
      </div>
    </div>

    <LoadingSpinner v-if="loading" size="sm" />

    <div v-else-if="reviews.length" class="review-section__list">
      <article v-for="review in reviews" :key="review.id" class="review-section__item">
        <div class="review-section__item-header">
          <strong>{{ reviewerName(review.user) }}</strong>
          <StarRating :rating="review.rating" size="sm" />
        </div>
        <p v-if="review.review">{{ review.review }}</p>
      </article>
    </div>

    <p v-else class="review-section__empty">لا توجد تقييمات معتمدة بعد.</p>

    <ReviewForm
      v-if="showReviewForm"
      :type="reviewType"
      :entity-id="entityId"
      :owner-user-id="ownerUserId"
      @submitted="emit('refresh')"
    />
  </section>
</template>
