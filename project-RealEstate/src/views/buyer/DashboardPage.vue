<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { buyerService } from '@/api/buyer.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const error = ref(null)
const summary = ref(null)

async function fetchDashboard() {
  loading.value = true
  error.value = null
  try {
    const { data } = await buyerService.dashboard()
    summary.value = data
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل البيانات.')
  } finally {
    loading.value = false
  }
}

const totalItems = () => summary.value?.total_items ?? 0
const totalPortfolios = () => summary.value?.total_portfolios ?? 0
const totalValue = () => summary.value?.total_portfolio_value ?? 0
const annualIncome = () => summary.value?.expected_annual_income ?? 0
const avgRoi = () => summary.value?.average_roi ?? 0

onMounted(fetchDashboard)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="لوحة المستثمر"
      description="نظرة عامة على محفظتك الاستثمارية ونشاطك."
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
      <AdminStatsSection title="محفظة الاستثمار">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="إجمالي قيمة المحفظة"
            :value="formatPrice(totalValue())"
            icon="bi-cash-stack"
            variant="primary"
          />
          <AdminStatCard
            label="الدخل السنوي المتوقع"
            :value="formatPrice(annualIncome())"
            icon="bi-piggy-bank"
            variant="success"
          />
          <AdminStatCard
            label="متوسط العائد (ROI)"
            :value="avgRoi() ? `${(Number(avgRoi()) * 100).toFixed(1)}%` : '—'"
            icon="bi-graph-up-arrow"
            variant="primary"
          />
          <AdminStatCard
            label="عدد المحافظ"
            :value="totalPortfolios()"
            icon="bi-folder"
          />
          <AdminStatCard
            label="العقارات المستثمرة"
            :value="totalItems()"
            icon="bi-buildings"
          />
        </div>
      </AdminStatsSection>

      <div v-if="summary.counts_by_status" class="admin-dashboard-grid">
        <AdminStatsSection title="توزيع العقارات حسب الحالة">
          <div class="d-flex gap-3 flex-wrap">
            <div
              v-for="(count, status) in summary.counts_by_status"
              :key="status"
              class="d-flex align-items-center gap-2 border rounded p-3"
            >
              <span class="fw-bold fs-5">{{ count }}</span>
              <span class="text-muted small">{{ status }}</span>
            </div>
          </div>
        </AdminStatsSection>
      </div>

      <div v-if="summary.best_performing_property || summary.worst_performing_property" class="admin-dashboard-grid mt-3">
        <AdminStatsSection title="أفضل عقار أداءً">
          <p v-if="summary.best_performing_property" class="mb-0">
            <strong>{{ summary.best_performing_property.name }}</strong>
            <span v-if="summary.best_performing_property.roi" class="text-success me-2">
              ({{ (Number(summary.best_performing_property.roi) * 100).toFixed(1) }}% ROI)
            </span>
          </p>
          <p v-else class="text-muted mb-0">لا توجد بيانات</p>
        </AdminStatsSection>

        <AdminStatsSection title="أضعف عقار أداءً">
          <p v-if="summary.worst_performing_property" class="mb-0">
            <strong>{{ summary.worst_performing_property.name }}</strong>
            <span v-if="summary.worst_performing_property.roi" class="text-danger me-2">
              ({{ (Number(summary.worst_performing_property.roi) * 100).toFixed(1) }}% ROI)
            </span>
          </p>
          <p v-else class="text-muted mb-0">لا توجد بيانات</p>
        </AdminStatsSection>
      </div>

      <div class="d-flex gap-2 mt-4 flex-wrap">
        <AppButton to="/buyer/favorites" variant="primary" size="sm">
          <i class="bi bi-heart"></i>
          المفضلة
        </AppButton>
        <AppButton to="/buyer/recommendations" variant="outline" size="sm">
          <i class="bi bi-stars"></i>
          التوصيات
        </AppButton>
        <AppButton to="/buyer/profile" variant="outline" size="sm">
          <i class="bi bi-person-gear"></i>
          الملف الشخصي
        </AppButton>
      </div>
    </template>
  </div>
</template>
