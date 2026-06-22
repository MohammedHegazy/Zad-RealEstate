<script setup>
import AppButton from '@/components/ui/AppButton.vue'
import AppSearchInput from '@/components/ui/AppSearchInput.vue'

defineProps({
  search: {
    type: String,
    default: '',
  },
  searchPlaceholder: {
    type: String,
    default: 'ابحث...',
  },
  showClear: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:search', 'clear'])
</script>

<template>
  <div class="directory-toolbar">
    <AppSearchInput
      :model-value="search"
      class="directory-toolbar__search"
      :placeholder="searchPlaceholder"
      size="md"
      @update:model-value="emit('update:search', $event)"
    />

    <div class="directory-toolbar__filters">
      <slot name="filters" />
    </div>

    <AppButton
      v-if="showClear"
      variant="ghost"
      size="sm"
      class="directory-toolbar__clear"
      @click="emit('clear')"
    >
      <i class="bi bi-x-circle"></i>
      مسح الفلاتر
    </AppButton>
  </div>
</template>
