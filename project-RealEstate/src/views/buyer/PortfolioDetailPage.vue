<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { portfoliosService } from '@/api/portfolios.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useConfirmStore } from '@/stores/confirm.js'

const route = useRoute()
const confirmStore = useConfirmStore()

const loading = ref(false)
const error = ref(null)
const portfolio = ref(null)
const properties = ref([])
const totalInvested = ref(0)
const addingEstate = ref(false)
const estateIdInput = ref('')
const addError = ref('')

async function fetchPortfolio() {
  loading.value = true
  error.value = null
  try {
    const pid = route.params.id
    const [portRes, propsRes] = await Promise.all([
      portfoliosService.getById(pid),
      portfoliosService.properties(pid),
    ])
    portfolio.value = portRes.data
    properties.value = propsRes.data?.properties ?? []
    totalInvested.value = propsRes.data?.total_invested ?? 0
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل المحفظة.')
  } finally {
    loading.value = false
  }
}

async function addEstateToPortfolio() {
  const id = parseInt(estateIdInput.value, 10)
  if (!id) return
  addingEstate.value = true
  addError.value = ''
  try {
    await portfoliosService.addEstate(route.params.id, { estate_id: id })
    estateIdInput.value = ''
    await fetchPortfolio()
  } catch (err) {
    addError.value = getErrorMessage(err, 'تعذّرت إضافة العقار.')
  } finally {
    addingEstate.value = false
  }
}

async function removeItem(itemId) {
  if (!(await confirmStore.show({ message: 'هل أنت متأكد من إزالة هذا العقار من المحفظة؟' }))) return
  try {
    await portfoliosService.removeEstate(route.params.id, itemId)
    await fetchPortfolio()
  } catch (err) {
    addError.value = getErrorMessage(err, 'تعذّرت إزالة العقار.')
  }
}

function riskLabel(level) {
  if (level === 'low') return { text: 'منخفض', cls: 'success' }
  if (level === 'moderate') return { text: 'متوسط', cls: 'warning' }
  if (level === 'high') return { text: 'مرتفع', cls: 'danger' }
  return { text: level, cls: 'secondary' }
}

onMounted(fetchPortfolio)
</script>

<template>
  <div class="admin-page">
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchPortfolio" />

    <template v-else-if="portfolio">
      <AdminPageHeader
        :title="portfolio.name"
        :description="portfolio.description || 'محفظة استثمارية'"
      />

      <div class="d-flex gap-2 mb-3 flex-wrap">
        <AppBadge :variant="riskLabel(portfolio.risk_level).cls">
          {{ riskLabel(portfolio.risk_level).text }}
        </AppBadge>
        <AppBadge v-if="portfolio.status === 'active'" variant="success">نشط</AppBadge>
        <AppBadge v-else-if="portfolio.status === 'archived'" variant="secondary">مؤرشف</AppBadge>
        <AppBadge v-else-if="portfolio.status === 'closed'" variant="danger">مغلق</AppBadge>
        <AppBadge v-if="portfolio.is_default" variant="primary">افتراضي</AppBadge>
      </div>

      <AdminStatsSection title="ملخص المحفظة">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="الميزانية المستهدفة"
            :value="portfolio.target_budget ? formatPrice(portfolio.target_budget) : '—'"
            icon="bi-cash-stack"
            variant="primary"
          />
          <AdminStatCard
            label="إجمالي المستثمر"
            :value="formatPrice(totalInvested)"
            icon="bi-piggy-bank"
            variant="success"
          />
          <AdminStatCard
            label="عدد العقارات"
            :value="properties.length"
            icon="bi-buildings"
          />
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="العقارات في المحفظة">
        <div v-if="properties.length" class="d-flex flex-column gap-2">
          <div
            v-for="item in properties"
            :key="item.id"
            class="border rounded p-3 d-flex align-items-center justify-content-between"
          >
            <div>
              <RouterLink
                :to="`/estates/${item.estate_id}`"
                class="fw-semibold text-decoration-none"
              >
                {{ item.estate?.name ?? `عقار #${item.estate_id}` }}
              </RouterLink>
              <div class="small text-muted">
                <span v-if="item.status === 'tracking'">قيد التتبّع</span>
                <span v-else-if="item.status === 'invested'">مستثمر</span>
                <span v-else-if="item.status === 'sold'">مباع</span>
                <span v-if="item.investment_amount" class="me-2">
                  — {{ formatPrice(item.investment_amount) }}
                </span>
                <span v-if="item.estate?.roi">
                  ROI {{ (Number(item.estate.roi) * 100).toFixed(1) }}%
                </span>
              </div>
            </div>
            <TableActionGroup>
              <TableAction
                tone="danger"
                icon="bi-trash"
                label="إزالة"
                @click="removeItem(item.id)"
              />
            </TableActionGroup>
          </div>
        </div>

        <EmptyState
          v-else
          icon="bi-building"
          title="لا توجد عقارات في هذه المحفظة"
          message="أضف عقارات إلى المحفظة لبدء التتبّع."
        />

        <div class="mt-3">
          <FormAlert v-if="addError" :message="addError" variant="error" />
          <div class="d-flex gap-2 align-items-end">
            <AppInput
              v-model="estateIdInput"
              type="number"
              placeholder="رقم العقار"
              class="flex-grow-1"
              style="max-width:200px;"
            />
            <AppButton
              variant="primary"
              size="sm"
              :loading="addingEstate"
              :disabled="!estateIdInput"
              @click="addEstateToPortfolio"
            >
              <i class="bi bi-plus-lg"></i>
              إضافة عقار
            </AppButton>
          </div>
          <small class="text-muted">أدخل رقم العقار لإضافته إلى المحفظة (يمكنك إيجاده في صفحة العقار).</small>
        </div>
      </AdminStatsSection>

      <div class="d-flex gap-2 mt-3">
        <AppButton to="/buyer/portfolios" variant="outline">
          العودة للمحافظ
        </AppButton>
        <AppButton to="/estates" variant="outline">
          تصفح العقارات
        </AppButton>
      </div>
    </template>
  </div>
</template>
