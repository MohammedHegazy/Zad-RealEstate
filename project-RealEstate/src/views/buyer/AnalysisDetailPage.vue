<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { investmentsService } from '@/api/investments.js'
import { formatPrice, formatRoi } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const saving = ref(false)
const error = ref(null)
const saveError = ref('')
const analysis = ref(null)

const form = ref({
  property_price: null,
  monthly_rent: 0,
  annual_expenses: 0,
  maintenance_cost: 0,
  tax_cost: 0,
  occupancy_rate: 100,
})

async function fetchAnalysis() {
  loading.value = true
  error.value = null
  try {
    const { data } = await investmentsService.getById(route.params.id)
    analysis.value = data
    form.value = {
      property_price: data.property_price ?? null,
      monthly_rent: data.monthly_rent ?? 0,
      annual_expenses: data.annual_expenses ?? 0,
      maintenance_cost: data.maintenance_cost ?? 0,
      tax_cost: data.tax_cost ?? 0,
      occupancy_rate: data.occupancy_rate ?? 100,
    }
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل التحليل.')
  } finally {
    loading.value = false
  }
}

async function saveAnalysis() {
  saving.value = true
  saveError.value = ''
  try {
    const { data } = await investmentsService.update(route.params.id, {
      ...form.value,
    })
    analysis.value = data
  } catch (err) {
    saveError.value = getErrorMessage(err, 'تعذّر حفظ التحليل.')
  } finally {
    saving.value = false
  }
}

const canSave = () =>
  form.value.property_price && Number(form.value.property_price) > 0

onMounted(fetchAnalysis)
</script>

<template>
  <div class="admin-page">
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchAnalysis" />

    <template v-else-if="analysis">
      <AdminPageHeader
        title="تحليل استثماري"
        :description="analysis.estate ? analysis.estate.name : `عقار #${analysis.estate_id}`"
      />

      <AdminStatsSection title="نتائج التحليل">
        <div class="admin-stats-grid">
          <AdminStatCard
            label="العائد على الاستثمار"
            :value="analysis.roi ? formatRoi(analysis.roi) : '—'"
            icon="bi-graph-up-arrow"
            variant="success"
          />
          <AdminStatCard
            label="الدخل السنوي المتوقع"
            :value="analysis.expected_annual_income ? formatPrice(analysis.expected_annual_income) : '—'"
            icon="bi-cash-stack"
            variant="primary"
          />
          <AdminStatCard
            label="فترة استرداد رأس المال"
            :value="analysis.payback_period ? `${Number(analysis.payback_period).toFixed(1)} سنة` : '—'"
            icon="bi-hourglass-split"
            variant="warning"
          />
          <AdminStatCard
            label="سعر العقار"
            :value="analysis.property_price ? formatPrice(analysis.property_price) : '—'"
            icon="bi-currency-dollar"
          />
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="تعديل المعطيات">
        <form @submit.prevent="saveAnalysis" class="row g-3" style="max-width:600px;">
          <div class="col-md-6">
            <AppFormGroup label="سعر العقار" required>
              <AppInput v-model="form.property_price" type="number" min="0" />
            </AppFormGroup>
          </div>

          <div class="col-md-6">
            <AppFormGroup label="الإيجار الشهري">
              <AppInput v-model="form.monthly_rent" type="number" min="0" />
            </AppFormGroup>
          </div>

          <div class="col-md-4">
            <AppFormGroup label="المصاريف السنوية">
              <AppInput v-model="form.annual_expenses" type="number" min="0" />
            </AppFormGroup>
          </div>

          <div class="col-md-4">
            <AppFormGroup label="تكاليف الصيانة">
              <AppInput v-model="form.maintenance_cost" type="number" min="0" />
            </AppFormGroup>
          </div>

          <div class="col-md-4">
            <AppFormGroup label="الضرائب">
              <AppInput v-model="form.tax_cost" type="number" min="0" />
            </AppFormGroup>
          </div>

          <div class="col-md-6">
            <AppFormGroup label="نسبة الإشغال (%)">
              <AppInput v-model="form.occupancy_rate" type="number" min="0" max="100" />
            </AppFormGroup>
          </div>

          <FormAlert v-if="saveError" :message="saveError" variant="error" />

          <div class="col-12 d-flex gap-2">
            <AppButton type="submit" variant="primary" :loading="saving" :disabled="!canSave()">
              حفظ التغييرات
            </AppButton>
            <AppButton type="button" variant="outline" @click="router.push('/buyer/investment-analyses')">
              العودة
            </AppButton>
          </div>
        </form>
      </AdminStatsSection>

      <div v-if="analysis.estate" class="mt-3">
        <AppButton :to="`/estates/${analysis.estate_id}`" variant="outline" size="sm">
          <i class="bi bi-eye"></i>
          عرض العقار
        </AppButton>
      </div>
    </template>
  </div>
</template>
