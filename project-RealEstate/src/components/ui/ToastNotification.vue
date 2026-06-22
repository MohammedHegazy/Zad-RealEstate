<script setup>
import { useToastStore } from '@/stores/toast.js'

const store = useToastStore()

const ICON_MAP = {
  success: 'bi-check-circle-fill',
  error: 'bi-exclamation-circle-fill',
  warning: 'bi-exclamation-triangle-fill',
  info: 'bi-info-circle-fill',
}

const TYPE_MAP = {
  success: 'toast--success',
  error: 'toast--error',
  warning: 'toast--warning',
  info: 'toast--info',
}

function iconClass(type) {
  return `bi ${ICON_MAP[type] ?? ICON_MAP.info}`
}

function typeClass(type) {
  return TYPE_MAP[type] ?? TYPE_MAP.info
}
</script>

<template>
  <Teleport to="body">
    <div
      class="toast-container position-fixed top-0 start-50 translate-middle-x p-3"
      style="z-index: 9999;"
    >
      <TransitionGroup name="toast">
        <div
          v-for="toast in store.toasts"
          :key="toast.id"
          class="toast show align-items-center border-0"
          :class="typeClass(toast.type)"
          role="alert"
        >
          <div class="d-flex gap-2 align-items-center toast__body rounded shadow-sm px-3 py-2">
            <i :class="iconClass(toast.type)" class="fs-5 toast__icon"></i>
            <span class="toast-body p-0 flex-grow-1">{{ toast.message }}</span>
            <button
              type="button"
              class="btn-close btn-close-sm toast__close"
              @click="store.removeToast(toast.id)"
            ></button>
          </div>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<style scoped>
.toast-enter-active {
  transition: all 0.3s ease-out;
}
.toast-leave-active {
  transition: all 0.3s ease-in;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(-12px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(-12px);
}
.toast {
  margin-bottom: 0.5rem;
}

.toast__body {
  background-color: var(--color-surface);
  color: var(--color-text-primary);
}

.toast__icon {
  color: var(--color-text-primary);
}

.toast--success {
  border-inline-start: 4px solid var(--color-success);
}
.toast--success .toast__icon {
  color: var(--color-success);
}

.toast--error {
  border-inline-start: 4px solid var(--color-error);
}
.toast--error .toast__icon {
  color: var(--color-error);
}

.toast--warning {
  border-inline-start: 4px solid var(--color-warning);
}
.toast--warning .toast__icon {
  color: var(--color-warning);
}

.toast--info {
  border-inline-start: 4px solid var(--color-primary);
}
.toast--info .toast__icon {
  color: var(--color-primary);
}

.toast__close {
  filter: var(--color-toast-close-filter, none);
}
</style>
