<script setup>
import { computed, ref } from 'vue'

import { getEstateImage, getEstateImages } from '@/utils/estate.js'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
})

const activeIndex = ref(0)

const media = computed(() => {
  const items = []

  const estateImages = getEstateImages(props.estate)
  if (estateImages.length) {
    estateImages.forEach((img) => items.push({ ...img, type: 'image' }))
  } else {
    items.push({ image_url: getEstateImage(props.estate), is_primary: true, type: 'image' })
  }

  if (props.estate?.videos?.length) {
    props.estate.videos.forEach((v) => items.push({ ...v, type: 'video' }))
  }

  return items
})

const activeMedia = computed(() => media.value[activeIndex.value])

const isVideo = computed(() => activeMedia.value?.type === 'video')

function selectMedia(index) {
  activeIndex.value = index
}
</script>

<template>
  <div class="estate-gallery">
    <div class="estate-gallery__main">
      <img
        v-if="!isVideo"
        :src="activeMedia?.image_url"
        :alt="estate.name"
        class="estate-gallery__hero"
      />
      <video
        v-else
        :src="activeMedia?.video_url"
        controls
        preload="metadata"
        class="estate-gallery__hero"
      ></video>
    </div>

    <div v-if="media.length > 1" class="estate-gallery__thumbs">
      <button
        v-for="(item, index) in media"
        :key="item.id ?? index"
        type="button"
        class="estate-gallery__thumb"
        :class="{
          'estate-gallery__thumb--active': index === activeIndex,
          'estate-gallery__thumb--video': item.type === 'video',
        }"
        @click="selectMedia(index)"
      >
        <img
          v-if="item.type === 'image'"
          :src="item.image_url"
          :alt="`${estate.name} - ${index + 1}`"
          loading="lazy"
        />
        <span v-else class="estate-gallery__video-thumb">
          <span class="estate-gallery__play-icon">
            <i class="bi bi-play-circle-fill"></i>
          </span>
        </span>
      </button>
    </div>
  </div>
</template>
