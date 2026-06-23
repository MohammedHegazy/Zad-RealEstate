<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  videoUrl: { type: String, default: '' },
})

const emit = defineEmits(['close'])

const videoRef = ref(null)

function close() {
  emit('close')
}

function onKeydown(e) {
  if (e.key === 'Escape') close()
}

watch(() => props.show, (val) => {
  if (val) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
    if (videoRef.value) {
      videoRef.value.pause()
      videoRef.value.currentTime = 0
    }
  }
})

onMounted(() => {
  document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', onKeydown)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <Transition name="video-modal">
      <div v-if="show" class="video-modal-overlay" @click.self="close">
        <div class="video-modal-container">
          <button class="video-modal-close" @click="close" aria-label="إغلاق">
            <i class="bi bi-x-lg"></i>
          </button>
          <video
            v-if="videoUrl"
            ref="videoRef"
            :src="videoUrl"
            class="video-modal-player"
            controls
            autoplay
            playsinline
          ></video>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.video-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.85);
  padding: 1rem;
}

.video-modal-container {
  position: relative;
  width: 100%;
  max-width: 900px;
  border-radius: 0.75rem;
  overflow: hidden;
}

.video-modal-close {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.25rem;
  height: 2.25rem;
  border: none;
  border-radius: 50%;
  background-color: rgba(0, 0, 0, 0.6);
  color: #fff;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.2s;
}

.video-modal-close:hover {
  background-color: rgba(0, 0, 0, 0.85);
}

.video-modal-player {
  display: block;
  width: 100%;
  max-height: 80vh;
  aspect-ratio: 16 / 9;
  background-color: #000;
}

.video-modal-enter-active,
.video-modal-leave-active {
  transition: opacity 0.25s ease;
}

.video-modal-enter-from,
.video-modal-leave-to {
  opacity: 0;
}
</style>
