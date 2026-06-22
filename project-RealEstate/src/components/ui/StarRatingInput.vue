<script setup>
const rating = defineModel({
  type: Number,
  default: 0,
})

defineProps({
  max: {
    type: Number,
    default: 5,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: 'اختر التقييم',
  },
})
</script>

<template>
  <div class="star-rating-input" role="group" :aria-label="label">
    <button
      v-for="value in max"
      :key="value"
      type="button"
      class="star-rating-input__star"
      :class="{ 'star-rating-input__star--active': value <= rating }"
      :disabled="disabled"
      :aria-label="`${value} من ${max}`"
      @click="rating = value"
    >
      <i :class="value <= rating ? 'bi bi-star-fill' : 'bi bi-star'"></i>
    </button>
  </div>
</template>
