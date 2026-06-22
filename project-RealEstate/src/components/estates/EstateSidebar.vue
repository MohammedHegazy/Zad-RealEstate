<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import FavoriteButton from '@/components/estates/FavoriteButton.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import { useAuthStore } from '@/stores/auth.js'
import { formatPrice } from '@/composables/useFormatters.js'
import {
  getEstateLocation,
  getEstatePrice,
  getListingType,
  getWhatsAppLink,
} from '@/utils/estate.js'

const router = useRouter()
const auth = useAuthStore()

const props = defineProps({
  estate: {
    type: Object,
    required: true,
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

const price = () => getEstatePrice(props.estate)

const canChat = computed(() => {
  if (!auth.user || !props.estate.user_id) return false
  return String(auth.user.id) !== String(props.estate.user_id)
})

function openChat() {
  const ownerId = props.estate.user_id
  const estateName = encodeURIComponent(props.estate.name)
  router.push(`/chat/${ownerId}?estate=${estateName}`)
}

function handleShare() {
  emit('share')
}
</script>

<template>
  <aside class="estate-sidebar">
    <div class="estate-sidebar__price-card">
      <div class="estate-sidebar__price-header">
        <AppBadge variant="primary">{{ getListingType(estate) }}</AppBadge>
        <FavoriteButton :estate-id="estate.id" size="sm" class="estate-sidebar__favorite" />
      </div>
      <p class="estate-sidebar__price">
        {{ formatPrice(price().value, { suffix: price().suffix }) }}
      </p>
      <p class="estate-sidebar__location">
        <i class="bi bi-geo-alt"></i>
        {{ getEstateLocation(estate) }}
      </p>
    </div>

    <div class="estate-sidebar__stats">
      <span v-if="estate.views">
        <i class="bi bi-eye"></i>
        {{ estate.views }} مشاهدة
      </span>
      <span v-if="estate.shares">
        <i class="bi bi-share"></i>
        {{ estate.shares }} مشاركة
      </span>
    </div>

    <FormAlert
      v-if="shareMessage"
      :message="shareMessage"
      :variant="shareVariant === 'error' ? 'error' : 'success'"
    />

    <div class="estate-sidebar__actions">
      <AppButton v-if="canChat" variant="outline" block @click="openChat">
        <i class="bi bi-chat-dots"></i>
        تواصل مع المالك
      </AppButton>

      <a
        v-if="getWhatsAppLink(estate)"
        :href="getWhatsAppLink(estate)"
        target="_blank"
        rel="noopener noreferrer"
        class="app-btn app-btn--primary app-btn--md app-btn--block estate-sidebar__whatsapp"
      >
        <i class="bi bi-whatsapp"></i>
        تواصل عبر واتساب
      </a>

      <a
        v-if="estate.phone"
        :href="`tel:${estate.country_code_phone ?? ''}${estate.phone}`"
        class="app-btn app-btn--outline app-btn--md app-btn--block"
      >
        <i class="bi bi-telephone"></i>
        {{ estate.country_code_phone }} {{ estate.phone }}
      </a>

      <AppButton variant="ghost" block @click="handleShare">
        <i class="bi bi-share"></i>
        مشاركة العقار
      </AppButton>
    </div>

    <div v-if="estate.user" class="estate-sidebar__owner">
      <h4>مالك العقار</h4>
      <p>{{ estate.user.fname }} {{ estate.user.lname }}</p>
      <span class="estate-sidebar__username">@{{ estate.user.username }}</span>
    </div>
  </aside>
</template>

<style scoped>
.estate-sidebar__price-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}

.estate-sidebar__favorite :deep(.favorite-btn) {
  padding: 0.25rem 0.4rem;
  border: none;
  background: transparent;
  color: var(--color-text-muted);
  font-size: 0.85rem;
}

.estate-sidebar__favorite :deep(.favorite-btn span) {
  display: none;
}
</style>
