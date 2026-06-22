<script setup>
import { ref, watch } from 'vue'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'

const props = defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'رفض مع ملاحظة',
  },
  description: {
    type: String,
    default: 'يمكنك إضافة ملاحظة إدارية تُحفظ مع قرار الرفض.',
  },
  confirmLabel: {
    type: String,
    default: 'تأكيد الرفض',
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'confirm'])

const notes = ref('')

watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) notes.value = ''
  },
)

function handleConfirm() {
  emit('confirm', notes.value.trim())
}
</script>

<template>
  <div v-if="open" class="admin-moderation-dialog" @click.self="emit('close')">
    <div class="admin-moderation-dialog__panel" role="dialog" aria-modal="true">
      <h3 class="admin-moderation-dialog__title">{{ title }}</h3>
      <p class="admin-moderation-dialog__description">{{ description }}</p>

      <AppFormGroup label="ملاحظة إدارية" label-for="moderation-notes">
        <AppTextarea
          id="moderation-notes"
          v-model="notes"
          rows="4"
          placeholder="سبب الرفض أو ملاحظات للمراجعة الداخلية..."
        />
      </AppFormGroup>

      <div class="admin-moderation-dialog__actions">
        <AppButton variant="outline" :disabled="loading" @click="emit('close')">
          إلغاء
        </AppButton>
        <AppButton variant="primary" :disabled="loading" @click="handleConfirm">
          {{ loading ? 'جاري الحفظ...' : confirmLabel }}
        </AppButton>
      </div>
    </div>
  </div>
</template>
