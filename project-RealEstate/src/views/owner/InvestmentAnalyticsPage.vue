<script setup>
import { ref, computed, onMounted } from 'vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AdminBarChart from '@/components/admin/AdminBarChart.vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { myEstatesService } from '@/api/myEstates.js'
import { formatPrice, formatRoi } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const error = ref(null)
const estates = ref([])

async function fetchEstates() {
  loading.value = true
  error.value = null
  try {
    const { data } = await myEstatesService.list({ per_page: 100 })
    estates.value = data ?? []
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل العقارات.')
  } finally {
    loading.value = false
  }
}

const investedEstates = computed(() =>
  estates.value.filter((e) => e.price || e.monthly_rent)
)

const totalValue = computed(() =>
  investedEstates.value.reduce((sum, e) => sum + Number(e.price || 0), 0)
)

const avgRoi = computed(() => {
  const withRoi = investedEstates.value.filter((e) => Number(e.roi) > 0)
  if (!withRoi.length) return 0
  const total = withRoi.reduce((sum, e) => sum + Number(e.roi), 0)
  return total / withRoi.length
})

const avgPayback = computed(() => {
  const withPayback = investedEstates.value.filter((e) => Number(e.payback_period) > 0)
  if (!withPayback.length) return 0
  const total = withPayback.reduce((sum, e) => sum + Number(e.payback_period), 0)
  return total / withPayback.length
})

const totalAnnualIncome = computed(() =>
  investedEstates.value.reduce((sum, e) => sum + Number(e.expected_annual_income || 0), 0)
)

const roiDistribution = computed(() => {
  const ranges = { '0-5%': 0, '5-10%': 0, '10-15%': 0, '15%+': 0 }
  investedEstates.value.forEach((e) => {
    const roi = Number(e.roi || 0) * 100
    if (roi <= 0) return
    if (roi <= 5) ranges['0-5%']++
    else if (roi <= 10) ranges['5-10%']++
    else if (roi <= 15) ranges['10-15%']++
    else ranges['15%+']++
  })
  return Object.entries(ranges).map(([label, value]) => ({
    label,
    value,
    variant: value > 0 ? 'primary' : 'default',
  }))
})

onMounted(fetchEstates)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="تحليلات الاستثمار"
      description="مؤشرات الأداء الاستثماري لعقاراتك."
    >
      <template #actions>
        <AppButton variant="outline" size="sm" @click="fetchEstates">
          <i class="bi bi-arrow-clockwise"></i>
          تحديث
        </AppButton>
      </template>
    </AdminPageHeader>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchEstates" />

    <template v-else-if="investedEstates.length">
      <AdminStatsSection title="نظرة عامة">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="إجمالي قيمة العقارات"
            :value="formatPrice(totalValue)"
            icon="bi-cash-stack"
            variant="primary"
          />
          <AdminStatCard
            label="متوسط العائد على الاستثمار"
            :value="formatRoi(avgRoi)"
            icon="bi-graph-up-arrow"
            variant="success"
          />
          <AdminStatCard
            label="متوسط فترة الاسترداد (سنوات)"
            :value="avgPayback ? avgPayback.toFixed(1) : '—'"
            icon="bi-clock-history"
            variant="warning"
          />
          <AdminStatCard
            label="الدخل السنوي المتوقع"
            :value="formatPrice(totalAnnualIncome)"
            icon="bi-piggy-bank"
            variant="primary"
          />
          <AdminStatCard
            label="عدد العقارات المستثمرة"
            :value="investedEstates.length"
            icon="bi-buildings"
          />
        </div>
      </AdminStatsSection>

      <div class="admin-dashboard-grid">
        <AdminStatsSection title="توزيع العائد على الاستثمار">
          <AdminBarChart :data="roiDistribution" />
        </AdminStatsSection>

        <AdminStatsSection title="جميع العقارات">
          <AdminDataTable
            :columns="[
              { key: 'name', label: 'الاسم' },
              { key: 'price', label: 'السعر' },
              { key: 'roi', label: 'العائد (ROI)' },
              { key: 'payback', label: 'الاسترداد' },
              { key: 'income', label: 'الدخل السنوي' },
            ]"
            :rows="investedEstates"
            empty-message="لا توجد عقارات."
          >
            <template #cell-name="{ row }">
              <strong>{{ row.name }}</strong>
            </template>
            <template #cell-price="{ row }">
              {{ formatPrice(row.price) }}
            </template>
            <template #cell-roi="{ row }">
              {{ row.roi ? `${(Number(row.roi) * 100).toFixed(1)}%` : '—' }}
            </template>
            <template #cell-payback="{ row }">
              {{ row.payback_period ? `${Number(row.payback_period).toFixed(1)} سنة` : '—' }}
            </template>
            <template #cell-income="{ row }">
              {{ row.expected_annual_income ? formatPrice(row.expected_annual_income) : '—' }}
            </template>
          </AdminDataTable>
        </AdminStatsSection>
      </div>
    </template>

    <div v-else-if="!loading && !error" class="admin-page">
      <AdminStatsSection title="لا توجد بيانات استثمارية">
        <p style="color: var(--color-text-secondary); margin: 0 0 1rem;">
          أضف عقارات تحتوي على معلومات السعر والإيجار الشهري لعرض التحليلات الاستثمارية.
        </p>
      </AdminStatsSection>
    </div>
  </div>
</template>
