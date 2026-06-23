<script setup>
import AppNavbar from '@/components/layout/AppNavbar.vue'
import AppFooter from '@/components/layout/AppFooter.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
})
</script>

<template>
  <div class="auth-layout">
    <AppNavbar />
    <div class="auth-layout__body">
      <aside class="auth-layout__brand">
        <div class="auth-layout__brand-content">
          <RouterLink to="/" class="auth-layout__logo">
            <i class="bi bi-building"></i>
            زاد للعقارات
          </RouterLink>

          <h1 class="auth-layout__brand-title">بوابتك إلى عالم العقارات الذكي</h1>
          <p class="auth-layout__brand-text">
            اكتشف العقارات، تابع الاستثمارات، تواصل مع وكلاء معتمدين، واستفد من
            تحليلات السوق في منصة واحدة.
          </p>

          <ul class="auth-layout__features">
            <li><i class="bi bi-building"></i> آلاف العقارات الموثقة</li>
            <li><i class="bi bi-map"></i> خرائط تفاعلية وبحث جغرافي</li>
            <li><i class="bi bi-graph-up-arrow"></i> تحليلات عائد الاستثمار</li>
            <li><i class="bi bi-patch-check"></i> وكلاء وشركات معتمدة</li>
            <li><i class="bi bi-chat-dots"></i> تواصل فوري مع الوسطاء</li>
            <li><i class="bi bi-heart"></i> حفظ العقارات المفضلة</li>
            <li><i class="bi bi-stars"></i> توصيات ذكية مخصصة</li>
            <li><i class="bi bi-bar-chart"></i> رسوم بيانية للأسعار</li>
          </ul>
        </div>
      </aside>

      <main class="auth-layout__main">
        <div class="auth-layout__card">
          <header class="auth-layout__header">
            <h2 class="auth-layout__title">{{ title }}</h2>
            <p v-if="subtitle" class="auth-layout__subtitle">{{ subtitle }}</p>
          </header>

          <slot />
        </div>
      </main>
    </div>
    <AppFooter />
  </div>
</template>
