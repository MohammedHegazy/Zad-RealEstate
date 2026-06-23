<script setup>
import AppButton from '@/components/ui/AppButton.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import TrustScorePanel from '@/components/ui/TrustScorePanel.vue'

defineProps({
  agent: {
    type: Object,
    required: true,
  },
  agentName: {
    type: String,
    required: true,
  },
  agentPhone: {
    type: String,
    default: null,
  },
  agentEmail: {
    type: String,
    default: null,
  },
  trust: {
    type: Object,
    default: null,
  },
  shareMessage: {
    type: String,
    default: '',
  },
  shareVariant: {
    type: String,
    default: 'success',
  },
})

const emit = defineEmits(['share'])
</script>

<template>
  <aside class="agent-sidebar">
    <div class="agent-sidebar__card">
      <h3 class="agent-sidebar__title">تواصل مع الوسيط</h3>
      <p class="agent-sidebar__subtitle">اختر الطريقة الأنسب للتواصل مع {{ agentName }}</p>

      <a
        v-if="agentPhone"
        :href="`tel:${agentPhone.replace(/\s/g, '')}`"
        class="app-btn app-btn--primary app-btn--md app-btn--block agent-sidebar__action"
      >
        <i class="bi bi-telephone"></i>
        اتصال: <span dir="ltr">{{ agentPhone }}</span>
      </a>

      <a
        v-if="agentEmail"
        :href="`mailto:${agentEmail}`"
        class="app-btn app-btn--outline app-btn--md app-btn--block agent-sidebar__action"
      >
        <i class="bi bi-envelope"></i>
        {{ agentEmail }}
      </a>

      <p v-if="!agentPhone && !agentEmail" class="agent-sidebar__empty">
        لا تتوفر بيانات تواصل مباشرة حالياً.
      </p>
    </div>

    <div class="agent-sidebar__card">
      <FormAlert
        v-if="shareMessage"
        :message="shareMessage"
        :variant="shareVariant === 'error' ? 'error' : 'success'"
      />

      <AppButton variant="secondary" block @click="emit('share')">
        <i class="bi bi-share"></i>
        مشاركة الملف
      </AppButton>

      <AppButton :to="'/estates'" variant="outline" block>
        <i class="bi bi-buildings"></i>
        تصفح العقارات
      </AppButton>
    </div>

    <TrustScorePanel :trust="trust" />
  </aside>
</template>
