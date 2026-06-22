<script setup>
import { computed } from 'vue'

import AdminBarChart from '@/components/admin/AdminBarChart.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminRecentList from '@/components/admin/AdminRecentList.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AdminTrendChart from '@/components/admin/AdminTrendChart.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import {
  COMPANY_STATUS_CHART_COLORS,
  COMPANY_STATUS_LABELS,
  ESTATE_STATUS_CHART_COLORS,
  ESTATE_STATUS_LABELS,
  USER_STATUS_LABELS,
  USER_TYPE_LABELS,
} from '@/config/admin.js'
import { useAdminDashboard } from '@/composables/useAdminDashboard.js'

const { loading, error, stats, fetchStats } = useAdminDashboard()

function mapDistribution(source, labels, colors = {}) {
  if (!source) return []
  return Object.entries(source).map(([key, value]) => ({
    label: labels[key] ?? key,
    value,
    variant: colors[key] ?? 'default',
  }))
}

const estatesChart = computed(() =>
  mapDistribution(stats.value?.estates_by_status, ESTATE_STATUS_LABELS, ESTATE_STATUS_CHART_COLORS),
)

const companiesChart = computed(() =>
  mapDistribution(
    stats.value?.companies_by_status,
    COMPANY_STATUS_LABELS,
    COMPANY_STATUS_CHART_COLORS,
  ),
)

const usersByTypeChart = computed(() =>
  mapDistribution(stats.value?.users_by_type, USER_TYPE_LABELS),
)

const usersByStatusChart = computed(() =>
  mapDistribution(stats.value?.users_by_status, USER_STATUS_LABELS),
)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="لوحة التحكم"
      description="نظرة عامة على المنصة — الأعداد، المراجعة، والنشاط الأخير."
    >
      <template #actions>
        <AppButton variant="outline" size="sm" @click="fetchStats">
          <i class="bi bi-arrow-clockwise"></i>
          تحديث
        </AppButton>
      </template>
    </AdminPageHeader>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchStats" />

    <template v-else-if="stats">
      <AdminStatsSection title="نظرة عامة">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="المستخدمون"
            :value="stats.totals.users"
            icon="bi-people"
            to="/admin/users"
          />
          <AdminStatCard
            label="الوسطاء"
            :value="stats.totals.agents"
            icon="bi-person-badge"
            to="/admin/agents"
          />
          <AdminStatCard
            label="الشركات"
            :value="stats.totals.companies"
            icon="bi-building"
            to="/admin/companies"
          />
          <AdminStatCard
            label="العقارات"
            :value="stats.totals.estates"
            icon="bi-buildings"
            variant="primary"
            to="/admin/estates"
          />
          <AdminStatCard label="المدن" :value="stats.totals.cities" icon="bi-geo" to="/admin/cities" />
          <AdminStatCard
            label="المناطق"
            :value="stats.totals.places"
            icon="bi-pin-map"
            to="/admin/places"
          />
        </div>
      </AdminStatsSection>

      <AdminStatsSection
        title="قيد المراجعة"
        description="عناصر تحتاج إجراء من المدير."
      >
        <div class="admin-stats-grid">
          <AdminStatCard
            label="عقارات معلّقة"
            :value="stats.moderation.estates_pending"
            icon="bi-hourglass-split"
            variant="warning"
            to="/admin/estates?status=pending"
          />
          <AdminStatCard
            label="عقارات مفعّلة"
            :value="stats.moderation.estates_active"
            icon="bi-check-circle"
            variant="success"
            to="/admin/estates?status=active"
          />
          <AdminStatCard
            label="عقارات مرفوضة"
            :value="stats.moderation.estates_rejected"
            icon="bi-x-circle"
            variant="danger"
            to="/admin/estates?status=rejected"
          />
          <AdminStatCard
            label="شركات معلّقة"
            :value="stats.moderation.companies_pending"
            icon="bi-building-exclamation"
            variant="warning"
            to="/admin/companies?status=pending"
          />
          <AdminStatCard
            label="تقييمات معلّقة"
            :value="stats.moderation.reviews_pending.total"
            icon="bi-chat-square-text"
            variant="warning"
            to="/admin/trust?section=reviews"
          />
          <AdminStatCard
            label="طلبات توثيق"
            :value="stats.moderation.verifications_pending"
            icon="bi-patch-check"
            variant="warning"
            to="/admin/trust?section=verifications"
          />
        </div>
      </AdminStatsSection>

      <div class="admin-dashboard-grid">
        <AdminStatsSection title="توزيع العقارات">
          <AdminBarChart :data="estatesChart" />
        </AdminStatsSection>

        <AdminStatsSection title="توزيع الشركات">
          <AdminBarChart :data="companiesChart" />
        </AdminStatsSection>

        <AdminStatsSection title="المستخدمون حسب النوع">
          <AdminBarChart :data="usersByTypeChart" />
        </AdminStatsSection>

        <AdminStatsSection title="المستخدمون حسب الحالة">
          <AdminBarChart :data="usersByStatusChart" />
        </AdminStatsSection>
      </div>

      <AdminStatsSection title="التسجيلات — آخر 7 أيام">
        <div class="admin-dashboard-grid admin-dashboard-grid--trends">
          <AdminTrendChart
            title="مستخدمون جدد"
            :data="stats.registrations.users_last_7_days"
          />
          <AdminTrendChart
            title="عقارات جديدة"
            :data="stats.registrations.estates_last_7_days"
          />
        </div>
      </AdminStatsSection>

      <div class="admin-dashboard-grid">
        <AdminStatsSection title="أحدث المستخدمين">
          <AdminRecentList type="users" :items="stats.recent_users" />
        </AdminStatsSection>

        <AdminStatsSection title="أحدث العقارات">
          <AdminRecentList type="estates" :items="stats.recent_estates" />
        </AdminStatsSection>
      </div>

      <div class="admin-dashboard-actions">
        <AppButton to="/admin/estates?status=pending" variant="primary" size="sm">
          مراجعة العقارات المعلّقة
        </AppButton>
        <AppButton to="/admin/trust" variant="outline" size="sm">
          الثقة والمراجعة
        </AppButton>
      </div>
    </template>
  </div>
</template>
