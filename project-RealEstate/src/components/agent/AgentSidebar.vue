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

const AGENT_NAV = [
  { label: 'لوحة الوسيط', to: '/agent/dashboard', icon: 'bi-speedometer2' },
  { label: 'العقارات', to: '/agent/estates', icon: 'bi-buildings' },
  { label: 'تحليلات الاستثمار', to: '/agent/investments', icon: 'bi-graph-up-arrow' },
  { label: 'الملف الشخصي', to: '/agent/profile', icon: 'bi-person-badge' },
  { label: 'روابط التواصل', to: '/agent/social-links', icon: 'bi-share' },
  { label: 'الرسائل', to: '/inbox', icon: 'bi-chat-dots' },
]

const agentName = computed(() => {
  const user = auth.user
  if (!user) return 'وسيط'
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
      <RouterLink to="/agent/dashboard" class="admin-sidebar__brand" @click="handleNavClick">
        <i class="bi bi-person-badge"></i>
        <span>زاد — الوسيط</span>
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
        v-for="item in AGENT_NAV"
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
      <p class="admin-sidebar__user">{{ agentName }}</p>
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
