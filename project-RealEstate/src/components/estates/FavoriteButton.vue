<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import { favoritesService } from '@/api/favorites.js'
import { useAuthStore } from '@/stores/auth.js'

const props = defineProps({
  estateId: {
    type: [Number, String],
    required: true,
  },
  size: {
    type: String,
    default: 'md',
  },
  block: {
    type: Boolean,
    default: false,
  },
  iconOnly: {
    type: Boolean,
    default: false,
  },
})

const auth = useAuthStore()
const router = useRouter()

const favorited = ref(false)
const loading = ref(false)

async function checkFavorite() {
  if (!auth.isAuthenticated()) return

  try {
    const { data } = await favoritesService.check(props.estateId)
    favorited.value = Boolean(data?.favorited)
  } catch {
    favorited.value = false
  }
}

async function toggleFavorite() {
  if (!auth.isAuthenticated()) {
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
    return
  }

  loading.value = true

  try {
    if (favorited.value) {
      await favoritesService.remove(props.estateId)
      favorited.value = false
    } else {
      await favoritesService.add(props.estateId)
      favorited.value = true
    }
  } catch {
    // silent — user sees no change
  } finally {
    loading.value = false
  }
}

onMounted(checkFavorite)
</script>

<template>
  <button
    type="button"
    class="favorite-btn"
    :class="[`favorite-btn--${size}`, { 'favorite-btn--active': favorited, 'favorite-btn--block': block }]"
    :disabled="loading"
    :aria-label="favorited ? 'إزالة من المفضلة' : 'إضافة للمفضلة'"
    @click="toggleFavorite"
  >
    <i :class="favorited ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
    <span v-if="!iconOnly">{{ favorited ? 'في المفضلة' : 'أضف للمفضلة' }}</span>
  </button>
</template>
