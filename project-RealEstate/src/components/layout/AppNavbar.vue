<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth.js'
import { messagesService } from '@/api/messages.js'
import AppButton from '@/components/ui/AppButton.vue'
import { useTheme } from '@/composables/useTheme.js'

const router = useRouter()
const auth = useAuthStore()
const { current, toggle } = useTheme()
const unreadCount = ref(0)

let pollTimer = null

async function fetchUnreadCount() {
  if (!auth.isAuthenticated()) {
    unreadCount.value = 0
    return
  }
  try {
    const { data } = await messagesService.getUnreadCount()
    unreadCount.value = data?.count ?? 0
  } catch {
    unreadCount.value = 0
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

onMounted(() => {
  fetchUnreadCount()
  pollTimer = setInterval(fetchUnreadCount, 5000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})

const navLinks = [
  { label: 'الرئيسية', to: '/' },
  { label: 'العقارات', to: '/estates' },
  { label: 'المدن', to: '/cities' },
  { label: 'الوسطاء', to: '/agents' },
  { label: 'الشركات', to: '/companies' },
]

const isLoggedIn = computed(() => auth.isAuthenticated())

const userName = computed(() => {
  const user = auth.user
  if (!user) return ''
  return `${user.fname} ${user.lname}`.trim()
})
</script>

<template>
  <header class="app-navbar">
    <nav class="container navbar navbar-expand-lg app-navbar__inner">
      <RouterLink to="/" class="app-navbar__brand">
        <i class="bi bi-building"></i>
        <span>زاد للعقارات</span>
      </RouterLink>

      <button
        class="navbar-toggler app-navbar__toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#mainNav"
        aria-controls="mainNav"
        aria-expanded="false"
        aria-label="فتح القائمة"
      >
        <i class="bi bi-list"></i>
      </button>

      <div id="mainNav" class="collapse navbar-collapse app-navbar__collapse">
        <ul class="app-navbar__links">
          <li v-for="link in navLinks" :key="link.to">
            <RouterLink :to="link.to" class="app-navbar__link">{{ link.label }}</RouterLink>
          </li>
        </ul>

        <div class="app-navbar__actions">
          <button
            type="button"
            class="app-navbar__theme-btn"
            :title="current === 'dark' ? 'الوضع النهاري' : 'الوضع الليلي'"
            @click="toggle()"
          >
            <i v-if="current === 'dark'" class="bi bi-sun-fill"></i>
            <i v-else class="bi bi-moon-fill"></i>
          </button>

          <template v-if="isLoggedIn">
            <span v-if="userName" class="app-navbar__user">{{ userName }}</span>
            <AppButton v-if="auth.isAdmin()" variant="ghost" size="sm" to="/admin/dashboard">
              <i class="bi bi-speedometer2"></i>
              لوحة التحكم
            </AppButton>
            <AppButton v-else-if="auth.isRegularUser()" variant="ghost" size="sm" to="/buyer/dashboard">
              <i class="bi bi-speedometer2"></i>
              لوحة المستثمر
            </AppButton>
            <AppButton variant="ghost" size="sm" to="/favorites">
              <i class="bi bi-heart"></i>
              المفضلة
            </AppButton>
            <AppButton variant="ghost" size="sm" to="/recommendations">
              <i class="bi bi-stars"></i>
              التوصيات
            </AppButton>
            <AppButton variant="ghost" size="sm" to="/inbox" class="position-relative">
              <i class="bi bi-chat-dots"></i>
              الرسائل
              <span
                v-if="unreadCount > 0"
                class="app-navbar__badge"
              >{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
            </AppButton>
            <AppButton variant="outline" size="sm" to="/profile">حسابي</AppButton>
            <AppButton variant="ghost" size="sm" @click="handleLogout">خروج</AppButton>
          </template>
          <template v-else>
            <AppButton variant="outline" size="sm" to="/login">تسجيل الدخول</AppButton>
            <AppButton variant="primary" size="sm" to="/register">إنشاء حساب</AppButton>
          </template>
        </div>
      </div>
    </nav>
  </header>
</template>

<style scoped>
.app-navbar__badge {
  position: absolute;
  top: -4px;
  inset-inline-end: -4px;
  min-width: 18px;
  height: 18px;
  padding: 0 4px;
  border-radius: 999px;
  background: var(--color-danger, #dc3545);
  color: #fff;
  font-size: 0.65rem;
  font-weight: 700;
  line-height: 18px;
  text-align: center;
}
</style>
