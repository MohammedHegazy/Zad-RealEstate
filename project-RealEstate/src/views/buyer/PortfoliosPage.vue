<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AppButton from '@/components/ui/AppButton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { portfoliosService } from '@/api/portfolios.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const router = useRouter()
const loading = ref(false)
const error = ref(null)
const items = ref([])
const pagination = ref(null)

async function fetchPortfolios() {
  loading.value = true
  error.value = null
  try {
    const { data, pagination: pag } = await portfoliosService.list({ per_page: 50 })
    items.value = data ?? []
    pagination.value = pag ?? null
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل المحافظ.')
  } finally {
    loading.value = false
  }
}

const totalBudget = () => items.value.reduce((s, p) => s + Number(p.target_budget || 0), 0)

onMounted(fetchPortfolios)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="المحافظ الاستثمارية"
      description="إدارة محافظك الاستثمارية وتتبّع عقاراتك."
    >
      <template #actions>
        <AppButton to="/buyer/portfolios/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          محفظة جديدة
        </AppButton>
      </template>
    </AdminPageHeader>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchPortfolios" />

    <template v-else-if="items.length">
      <AdminStatsSection title="نظرة عامة">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="عدد المحافظ"
            :value="items.length"
            icon="bi-folder"
            variant="primary"
          />
          <AdminStatCard
            label="إجمالي الميزانية المستهدفة"
            :value="formatPrice(totalBudget())"
            icon="bi-cash-stack"
            variant="success"
          />
          <AdminStatCard
            label="المحافظ النشطة"
            :value="items.filter((p) => p.status === 'active').length"
            icon="bi-check-circle"
            variant="primary"
          />
        </div>
      </AdminStatsSection>

      <AdminDataTable
        :columns="[
          { key: 'name', label: 'الاسم' },
          { key: 'risk_level', label: 'مستوى المخاطرة' },
          { key: 'budget', label: 'الميزانية' },
          { key: 'properties', label: 'العقارات' },
          { key: 'status', label: 'الحالة' },
        ]"
        :rows="items"
        empty-message="لا توجد محافظ بعد."
      >
        <template #cell-risk_level="{ row }">
          <span v-if="row.risk_level === 'low'" class="text-success">منخفض</span>
          <span v-else-if="row.risk_level === 'moderate'" class="text-warning">متوسط</span>
          <span v-else-if="row.risk_level === 'high'" class="text-danger">مرتفع</span>
          <span v-else>—</span>
        </template>

        <template #cell-budget="{ row }">
          {{ row.target_budget ? formatPrice(row.target_budget) : '—' }}
        </template>

        <template #cell-properties="{ row }">
          {{ row.properties_count ?? 0 }}
        </template>

        <template #cell-status="{ row }">
          <span v-if="row.status === 'active'" class="badge bg-success">نشط</span>
          <span v-else-if="row.status === 'archived'" class="badge bg-secondary">مؤرشف</span>
          <span v-else-if="row.status === 'closed'" class="badge bg-danger">مغلق</span>
          <span v-else>{{ row.status }}</span>
        </template>

        <template #actions="{ row }">
          <TableActionGroup>
            <TableAction
              tone="neutral"
              icon="bi-eye"
              label="عرض"
              @click="router.push(`/buyer/portfolios/${row.id}`)"
            />
          </TableActionGroup>
        </template>
      </AdminDataTable>

      <Pagination
        v-if="pagination"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        @page-change="(page) => { pagination.current_page = page; fetchPortfolios() }"
      />
    </template>

    <EmptyState
      v-else-if="!loading && !error"
      icon="bi-folder"
      title="لا توجد محافظ استثمارية"
      message="أنشئ محفظة جديدة لبدء تتبّع عقاراتك الاستثمارية."
    >
      <AppButton to="/buyer/portfolios/create" variant="primary">
        إنشاء محفظة
      </AppButton>
    </EmptyState>
  </div>
</template>
