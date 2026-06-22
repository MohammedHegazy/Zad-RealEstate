<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { ownerService } from '@/api/owner.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const error = ref(null)
const summary = ref(null)

async function fetchDashboard() {
  loading.value = true
  error.value = null
  try {
    const { data } = await ownerService.dashboard()
    summary.value = data
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل البيانات.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="لوحة المالك"
      description="نظرة عامة على عقاراتك ونشاطك."
    >
      <template #actions>
        <AppButton variant="outline" size="sm" @click="fetchDashboard">
          <i class="bi bi-arrow-clockwise"></i>
          تحديث
        </AppButton>
      </template>
    </AdminPageHeader>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchDashboard" />

    <template v-else-if="summary">
      <AdminStatsSection title="عقاراتي">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="إجمالي العقارات"
            :value="summary.total_estates"
            icon="bi-buildings"
            variant="primary"
          />
          <AdminStatCard
            label="نشط"
            :value="summary.active_estates"
            icon="bi-check-circle"
            variant="success"
          />
          <AdminStatCard
            label="قيد المراجعة"
            :value="summary.pending_estates"
            icon="bi-clock"
            variant="warning"
          />
          <AdminStatCard
            label="مرفوض"
            :value="summary.rejected_estates"
            icon="bi-x-circle"
            variant="danger"
          />
        </div>
      </AdminStatsSection>

      <div v-if="summary.recent_estates?.length" class="mt-3">
        <AdminStatsSection title="أحدث العقارات">
          <div class="list-group">
            <RouterLink
              v-for="estate in summary.recent_estates"
              :key="estate.id"
              :to="`/estates/${estate.id}`"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
            >
              <span>{{ estate.name }}</span>
              <small class="text-muted">{{ estate.status }}</small>
            </RouterLink>
          </div>
        </AdminStatsSection>
      </div>

      <div v-if="summary.recent_interactions?.length" class="mt-3">
        <AdminStatsSection title="آخر الاستفسارات">
          <div class="list-group">
            <div
              v-for="interaction in summary.recent_interactions"
              :key="interaction.id"
              class="list-group-item"
            >
              <div class="d-flex justify-content-between">
                <span>
                  <strong>{{ interaction.user?.fname }} {{ interaction.user?.lname }}</strong>
                  — {{ interaction.estate?.name }}
                </span>
                <small class="text-muted">{{ interaction.type }}</small>
              </div>
            </div>
          </div>
        </AdminStatsSection>
      </div>

      <div class="d-flex gap-2 mt-4 flex-wrap">
        <AppButton to="/owner/estates" variant="primary" size="sm">
          <i class="bi bi-buildings"></i>
          كل العقارات
        </AppButton>
        <AppButton to="/owner/profile" variant="outline" size="sm">
          <i class="bi bi-person-gear"></i>
          الملف الشخصي
        </AppButton>
      </div>
    </template>
  </div>
</template>
