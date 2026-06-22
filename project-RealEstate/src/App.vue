<script setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'

import AdminLayout from '@/layouts/AdminLayout.vue'
import AgentLayout from '@/layouts/AgentLayout.vue'
import BuyerLayout from '@/layouts/BuyerLayout.vue'
import CompanyLayout from '@/layouts/CompanyLayout.vue'
import OwnerLayout from '@/layouts/OwnerLayout.vue'
import MainLayout from '@/layouts/MainLayout.vue'
import ToastNotification from '@/components/ui/ToastNotification.vue'
import ConfirmDialog from '@/components/ui/ConfirmDialog.vue'
import { useTheme } from '@/composables/useTheme.js'

const route = useRoute()

const layout = computed(() => route.meta.layout ?? 'main')

onMounted(() => {
  useTheme()
})
</script>

<template>
  <AdminLayout v-if="layout === 'admin'">
    <RouterView />
  </AdminLayout>

  <AgentLayout v-else-if="layout === 'agent'">
    <RouterView />
  </AgentLayout>

  <BuyerLayout v-else-if="layout === 'buyer'">
    <RouterView />
  </BuyerLayout>

  <CompanyLayout v-else-if="layout === 'company'">
    <RouterView />
  </CompanyLayout>

  <OwnerLayout v-else-if="layout === 'owner'">
    <RouterView />
  </OwnerLayout>

  <MainLayout v-else-if="layout === 'main'">
    <RouterView />
  </MainLayout>

  <RouterView v-else />

  <ToastNotification />
  <ConfirmDialog />
</template>
