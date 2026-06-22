<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import ChatWindow from '@/components/chat/ChatWindow.vue'
import AppButton from '@/components/ui/AppButton.vue'

const route = useRoute()
const router = useRouter()

const userId = computed(() => route.params.userId)
const estateName = computed(() => route.query.estate)
</script>

<template>
  <div class="chat-page">
    <div class="chat-page__top">
      <AppButton variant="ghost" size="sm" @click="router.push('/inbox')">
        <i class="bi bi-arrow-right"></i>
        العودة للرسائل
      </AppButton>
      <span v-if="estateName" class="chat-page__context">
        <i class="bi bi-building"></i>
        {{ decodeURIComponent(estateName) }}
      </span>
    </div>

    <ChatWindow
      :key="userId"
      :user-id="userId"
      class="chat-page__window"
    />
  </div>
</template>

<style scoped>
.chat-page {
  display: flex;
  flex-direction: column;
  height: calc(100vh - 80px);
  max-width: 720px;
  margin: 0 auto;
  padding: 0 1rem 1rem;
}

.chat-page__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.5rem 0;
  flex-shrink: 0;
}

.chat-page__context {
  font-size: 0.8rem;
  color: var(--color-text-muted);
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.chat-page__window {
  flex: 1;
}
</style>
