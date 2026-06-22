<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { useAuthStore } from '@/stores/auth.js'

defineProps({
  open: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const route = useRoute()
const router = useRouter()
const auth = useAuthStore()

const BUYER_NAV = [
  { label: 'لوحة المستثمر', to: '/buyer/dashboard', icon: 'bi-speedometer2' },
  { label: 'المحافظ الاستثمارية', to: '/buyer/portfolios', icon: 'bi-folder' },
  { label: 'التحليلات الاستثمارية', to: '/buyer/investment-analyses', icon: 'bi-bar-chart-line' },
  { label: 'المفضلة', to: '/buyer/favorites', icon: 'bi-heart' },
  { label: 'التوصيات', to: '/buyer/recommendations', icon: 'bi-stars' },
  { label: 'الملف الشخصي', to: '/buyer/profile', icon: 'bi-person-gear' },
  { label: 'روابط التواصل', to: '/buyer/social-links', icon: 'bi-share' },
  { label: 'الرسائل', to: '/inbox', icon: 'bi-chat-dots' },
]

const userName = computed(() => {
  const user = auth.user
  if (!user) return 'مستخدم'
  return `${user.fname ?? ''} ${user.lname ?? ''}`.trim() || user.username
})

function isActive(path) {
  return route.path === path || route.path.startsWith(`${path}/`)
}

function handleNavClick() {
  emit('close')
}

async function handleLogout() {
  emit('close')
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <aside class="admin-sidebar" :class="{ 'admin-sidebar--open': open }">
    <div class="admin-sidebar__header">
      <RouterLink to="/buyer/dashboard" class="admin-sidebar__brand" @click="handleNavClick">
        <i class="bi bi-person-circle"></i>
        <span>زاد — المستثمر</span>
      </RouterLink>

      <button
        type="button"
        class="admin-sidebar__close"
        aria-label="إغلاق القائمة"
        @click="emit('close')"
      >
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <nav class="admin-sidebar__nav">
      <RouterLink
        v-for="item in BUYER_NAV"
        :key="item.to"
        :to="item.to"
        class="admin-sidebar__link"
        :class="{ 'admin-sidebar__link--active': isActive(item.to) }"
        @click="handleNavClick"
      >
        <i :class="['bi', item.icon]"></i>
        {{ item.label }}
      </RouterLink>
    </nav>

    <div class="admin-sidebar__footer">
      <p class="admin-sidebar__user">{{ userName }}</p>
      <RouterLink to="/" class="admin-sidebar__site-link" @click="handleNavClick">
        <i class="bi bi-box-arrow-up-left"></i>
        الموقع العام
      </RouterLink>
      <button type="button" class="admin-sidebar__logout" @click="handleLogout">
        <i class="bi bi-box-arrow-right"></i>
        تسجيل الخروج
      </button>
    </div>
  </aside>
</template>
