<script setup>
import { useConfirmStore } from '@/stores/confirm.js'
import AppButton from './AppButton.vue'

const store = useConfirmStore()
</script>

<template>
  <Teleport to="body">
    <Transition name="confirm-fade">
      <div v-if="store.visible" class="confirm-overlay" @click.self="store.cancel">
        <div class="confirm-dialog">
          <div class="confirm-dialog__header">
            <i
              class="bi fs-3"
              :class="{
                'bi-exclamation-triangle-fill text-danger': store.variant === 'danger',
                'bi-question-circle-fill text-primary': store.variant === 'primary',
                'bi-info-circle-fill text-info': store.variant === 'info',
                'bi-exclamation-circle-fill text-warning': store.variant === 'warning',
              }"
            ></i>
          </div>
          <h5 class="confirm-dialog__title">{{ store.title }}</h5>
          <p class="confirm-dialog__message">{{ store.message }}</p>
          <div class="confirm-dialog__actions">
            <AppButton variant="outline" @click="store.cancel">
              {{ store.cancelText }}
            </AppButton>
            <AppButton :variant="store.variant" @click="store.resolve">
              {{ store.confirmText }}
            </AppButton>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.confirm-overlay {
  position: fixed;
  inset: 0;
  background: var(--color-surface-overlay);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1060;
}

.confirm-dialog {
  background: var(--color-surface);
  border-radius: 0.75rem;
  padding: 2rem;
  max-width: 400px;
  width: 90%;
  text-align: center;
  box-shadow: 0 16px 48px var(--color-card-shadow);
}

.confirm-dialog__header {
  margin-bottom: 1rem;
}

.confirm-dialog__title {
  margin-bottom: 0.5rem;
  font-weight: 600;
}

.confirm-dialog__message {
  color: var(--color-text-secondary);
  margin-bottom: 1.5rem;
}

.confirm-dialog__actions {
  display: flex;
  gap: 0.75rem;
  justify-content: center;
}

.confirm-fade-enter-active,
.confirm-fade-leave-active {
  transition: opacity 0.2s ease;
}

.confirm-fade-enter-from,
.confirm-fade-leave-to {
  opacity: 0;
}
</style>
