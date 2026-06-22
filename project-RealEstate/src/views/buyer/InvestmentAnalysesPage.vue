<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AppButton from '@/components/ui/AppButton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { investmentsService } from '@/api/investments.js'
import { formatPrice, formatRoi } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useConfirmStore } from '@/stores/confirm.js'

const router = useRouter()
const confirmStore = useConfirmStore()
const loading = ref(false)
const error = ref(null)
const items = ref([])
const pagination = ref(null)

async function fetchAnalyses() {
  loading.value = true
  error.value = null
  try {
    const { data, pagination: pag } = await investmentsService.myAnalyses({ per_page: 50 })
    items.value = data ?? []
    pagination.value = pag ?? null
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل التحليلات.')
  } finally {
    loading.value = false
  }
}

async function deleteAnalysis(id) {
  if (!(await confirmStore.show({ message: 'هل أنت متأكد من حذف هذا التحليل؟' }))) return
  try {
    await investmentsService.remove(id)
    items.value = items.value.filter((a) => a.id !== id)
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر حذف التحليل.')
  }
}

onMounted(fetchAnalyses)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="التحليلات الاستثمارية"
      description="التحليلات التي قمت بحفظها للعقارات."
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchAnalyses" />

    <template v-else-if="items.length">
      <AdminDataTable
        :columns="[
          { key: 'estate', label: 'العقار' },
          { key: 'price', label: 'السعر' },
          { key: 'roi', label: 'العائد (ROI)' },
          { key: 'income', label: 'الدخل السنوي' },
          { key: 'payback', label: 'الاسترداد' },
        ]"
        :rows="items"
        empty-message="لا توجد تحليلات محفوظة."
      >
        <template #cell-estate="{ row }">
          <RouterLink
            v-if="row.estate"
            :to="`/estates/${row.estate_id}`"
            class="text-decoration-none fw-semibold"
          >
            {{ row.estate.name }}
          </RouterLink>
          <span v-else class="text-muted">عقار #{{ row.estate_id }}</span>
        </template>

        <template #cell-price="{ row }">
          {{ row.property_price ? formatPrice(row.property_price) : '—' }}
        </template>

        <template #cell-roi="{ row }">
          {{ row.roi ? formatRoi(row.roi) : '—' }}
        </template>

        <template #cell-income="{ row }">
          {{ row.expected_annual_income ? formatPrice(row.expected_annual_income) : '—' }}
        </template>

        <template #cell-payback="{ row }">
          {{ row.payback_period ? `${Number(row.payback_period).toFixed(1)} سنة` : '—' }}
        </template>

        <template #actions="{ row }">
          <TableActionGroup>
            <TableAction
              tone="neutral"
              icon="bi-eye"
              label="عرض"
              @click="router.push(`/buyer/investment-analyses/${row.id}`)"
            />
            <TableAction
              tone="danger"
              icon="bi-trash"
              label="حذف"
              @click="deleteAnalysis(row.id)"
            />
          </TableActionGroup>
        </template>
      </AdminDataTable>

      <Pagination
        v-if="pagination"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        @page-change="(page) => { pagination.current_page = page; fetchAnalyses() }"
      />
    </template>

    <EmptyState
      v-else-if="!loading && !error"
      icon="bi-bar-chart-line"
      title="لا توجد تحليلات محفوظة"
      message="تصفّح العقارات واحسب العائد الاستثماري واحفظ التحليل لمتابعته."
    >
      <AppButton to="/estates" variant="primary">تصفح العقارات</AppButton>
    </EmptyState>
  </div>
</template>
