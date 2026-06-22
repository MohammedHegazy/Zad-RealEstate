<script setup>
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import SocialLinksManager from '@/components/company/SocialLinksManager.vue'
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'لوحة الشركة', to: '/company/dashboard' },
        { label: 'روابط التواصل الاجتماعي' },
      ]"
    />

    <AdminPageHeader
      title="روابط التواصل الاجتماعي"
      description="إدارة روابط شركتك على منصات التواصل."
    >
      <template #actions>
        <AppButton to="/company/profile" variant="outline" size="sm">
          <i class="bi bi-building-gear"></i>
          ملف الشركة
        </AppButton>
      </template>
    </AdminPageHeader>

    <SocialLinksManager />
  </div>
</template>
