<script setup>
import { computed } from 'vue'

import { JOURNEY_STEPS } from '@/config/journey.js'

const props = defineProps({
  overline: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  step: {
    type: String,
    default: '',
  },
  icon: {
    type: String,
    default: '',
  },
})

const activeStep = computed(() => JOURNEY_STEPS.find((s) => s.id === props.step))
</script>

<template>
  <section class="page-intro">
    <div v-if="step" class="page-intro__journey">
      <span
        v-for="journeyStep in JOURNEY_STEPS"
        :key="journeyStep.id"
        class="page-intro__journey-step"
        :class="{ 'page-intro__journey-step--active': journeyStep.id === step }"
      >
        <i :class="['bi', journeyStep.icon]"></i>
        {{ journeyStep.label }}
      </span>
    </div>

    <span v-if="overline" class="page-intro__overline text-overline">{{ overline }}</span>
    <h1 class="page-intro__title">
      <i v-if="icon" :class="['bi', icon, 'page-intro__title-icon']"></i>
      {{ title }}
    </h1>
    <p v-if="description" class="page-intro__description">{{ description }}</p>

    <div v-if="activeStep" class="page-intro__badge">
      <i :class="['bi', activeStep.icon]"></i>
      مرحلة: {{ activeStep.label }}
    </div>
  </section>
</template>
