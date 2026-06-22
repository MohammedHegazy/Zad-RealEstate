<script setup>
import { computed } from 'vue'

import AdminBarChart from '@/components/admin/AdminBarChart.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminRecentList from '@/components/admin/AdminRecentList.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { ESTATE_STATUS_LABELS, ESTATE_STATUS_CHART_COLORS } from '@/config/admin.js'
import { useCompanyDashboard } from '@/composables/useCompanyDashboard.js'

const { loading, error, company, agents, estates, fetchDashboard } = useCompanyDashboard()

const estatesByStatus = computed(() => {
  if (!estates.value.length) return []
  const groups = {}
  estates.value.forEach((e) => {
    const s = e.status ?? 'pending'
    groups[s] = (groups[s] || 0) + 1
  })
  return Object.entries(groups).map(([key, value]) => ({
    label: ESTATE_STATUS_LABELS[key] ?? key,
    value,
    variant: ESTATE_STATUS_CHART_COLORS[key] ?? 'default',
  }))
})

const activeEstates = computed(() => estates.value.filter((e) => e.status === 'active').length)
const pendingEstates = computed(() => estates.value.filter((e) => e.status === 'pending').length)
const approvedAgents = computed(() => agents.value.filter((a) => a.status === 'approved').length)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="لوحة الشركة"
      description="نظرة عامة على شركتك — العقارات، الوسطاء، والنشاط."
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

    <template v-else-if="company">
      <AdminStatsSection title="الشركة">
        <div class="company-profile-mini">
          <div class="company-profile-mini__avatar">
            <img
              v-if="company.profile_image_url"
              :src="company.profile_image_url"
              :alt="company.company_name"
            />
            <i v-else class="bi bi-building"></i>
          </div>
          <div class="company-profile-mini__info">
            <h2>{{ company.company_name }}</h2>
            <p v-if="company.place">
              <i class="bi bi-geo-alt"></i>
              {{ company.place.name }}، {{ company.place.city?.name }}
            </p>
            <span v-if="company.website" class="company-profile-mini__website">
              <i class="bi bi-globe"></i>
              {{ company.website }}
            </span>
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="نظرة عامة">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="الوسطاء"
            :value="agents.length"
            icon="bi-person-badge"
            to="/company/agents"
          />
          <AdminStatCard
            label="وسطاء معتمدون"
            :value="approvedAgents"
            icon="bi-person-check"
            variant="success"
            to="/company/agents"
          />
          <AdminStatCard
            label="العقارات"
            :value="estates.length"
            icon="bi-buildings"
            variant="primary"
            to="/company/estates"
          />
          <AdminStatCard
            label="عقارات مفعّلة"
            :value="activeEstates"
            icon="bi-check-circle"
            variant="success"
            to="/company/estates"
          />
          <AdminStatCard
            label="قيد المراجعة"
            :value="pendingEstates"
            icon="bi-hourglass-split"
            variant="warning"
            to="/company/estates"
          />
          <AdminStatCard
            v-if="company.trust_score"
            label="درجة الثقة"
            :value="company.trust_score"
            icon="bi-shield-check"
            variant="primary"
          />
        </div>
      </AdminStatsSection>

      <div class="admin-dashboard-grid">
        <AdminStatsSection title="حالة العقارات">
          <AdminBarChart :data="estatesByStatus" />
        </AdminStatsSection>

        <AdminStatsSection title="أحدث العقارات">
          <AdminRecentList type="estates" :items="estates.slice(0, 5)" />
        </AdminStatsSection>
      </div>

      <div class="admin-dashboard-actions">
        <AppButton to="/company/agents" variant="primary" size="sm">
          إدارة الوسطاء
        </AppButton>
        <AppButton to="/company/investments" variant="primary" size="sm">
          تحليلات الاستثمار
        </AppButton>
        <AppButton to="/company/profile" variant="outline" size="sm">
          تعديل ملف الشركة
        </AppButton>
        <AppButton to="/company/estates" variant="outline" size="sm">
          عرض العقارات
        </AppButton>
      </div>
    </template>

    <template v-else-if="!loading && !error">
      <div class="admin-page">
        <AdminStatsSection title="ليس لديك شركة بعد">
          <p style="color: var(--color-text-secondary); margin: 0 0 1rem;">
            قم بإنشاء ملف شركتك للبدء في إدارة عقاراتك ووسطائك.
          </p>
          <AppButton to="/company/profile" variant="primary">
            إنشاء شركة
          </AppButton>
        </AdminStatsSection>
      </div>
    </template>
  </div>
</template>
