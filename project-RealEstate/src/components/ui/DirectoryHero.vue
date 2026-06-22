<script setup>
import { computed } from 'vue'

import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'

const DEFAULT_IMAGE =
  'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=1200&q=80'

const props = defineProps({
  image: {
    type: String,
    default: '',
  },
  fallbackImage: {
    type: String,
    default: DEFAULT_IMAGE,
  },
  breadcrumbItems: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
})

const resolvedImage = computed(
  () => props.image || props.fallbackImage || DEFAULT_IMAGE,
)
</script>

<template>
  <header
    class="directory-hero"
    :style="{ '--hero-image': `url(${resolvedImage})` }"
  >
    <div class="container directory-hero__content">
      <Breadcrumbs :items="breadcrumbItems" />
      <h1 class="directory-hero__title">{{ title }}</h1>
      <p v-if="$slots.default" class="directory-hero__meta">
        <slot />
      </p>
    </div>
  </header>
</template>
