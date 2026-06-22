<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { investmentsService } from '@/api/investments.js'
import { useEstateDetail } from '@/composables/useEstateDetail.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAuthStore } from '@/stores/auth.js'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const { loading: estateLoading, error: estateError, estate, fetchEstate } = useEstateDetail()

const saving = ref(false)
const saveError = ref('')

const form = ref({
  property_price: null,
  monthly_rent: 0,
  annual_expenses: 0,
  maintenance_cost: 0,
  tax_cost: 0,
  occupancy_rate: 100,
})

const calculatedRoi = ref(null)
const calculatedIncome = ref(null)
const calculatedPayback = ref(null)

function recalc() {
  const price = Number(form.value.property_price)
  const rent = Number(form.value.monthly_rent)
  const expenses = Number(form.value.annual_expenses)
  const maintenance = Number(form.value.maintenance_cost)
  const tax = Number(form.value.tax_cost)
  const occupancy = Number(form.value.occupancy_rate) / 100

  if (!price || price <= 0) {
    calculatedRoi.value = null
    calculatedIncome.value = null
    calculatedPayback.value = null
    return
  }

  const annualIncome = (rent * 12 * occupancy) - expenses - maintenance - tax
  calculatedIncome.value = annualIncome
  const roi = annualIncome / price
  calculatedRoi.value = roi
  calculatedPayback.value = roi > 0 ? price / annualIncome : null
}

function fillFromEstate() {
  const e = estate.value
  if (!e) return
  form.value = {
    property_price: e.price ?? null,
    monthly_rent: e.monthly_rent ?? 0,
    annual_expenses: e.annual_expenses ?? 0,
    maintenance_cost: e.maintenance_cost ?? 0,
    tax_cost: e.annual_property_tax ?? 0,
    occupancy_rate: e.occupancy_rate ?? 100,
  }
}

watch(form, recalc, { deep: true })

async function handleSave() {
  if (!canSave() || !estate.value) return
  saving.value = true
  saveError.value = ''
  try {
    await investmentsService.storeByEstate(estate.value.id, {
      ...form.value,
    })
    router.push('/buyer/investment-analyses')
  } catch (err) {
    saveError.value = getErrorMessage(err, 'تعذّر حفظ التحليل.')
  } finally {
    saving.value = false
  }
}

function canSave() {
  return form.value.property_price && Number(form.value.property_price) > 0
}

const isAuthenticated = computed(() => authStore.isAuthenticated)
const isRegularUser = computed(() => authStore.isRegularUser())

onMounted(async () => {
  await fetchEstate(route.params.id)
  if (estate.value) fillFromEstate()
})
</script>

<template>
  <div>
    <LoadingSpinner v-if="estateLoading" />
    <ErrorAlert v-else-if="estateError" :message="estateError" @retry="fetchEstate(route.params.id)" />

    <template v-else-if="estate">
      <div class="analysis-hero">
        <div class="analysis-hero__content">
          <nav class="analysis-hero__breadcrumb">
            <RouterLink to="/estates">العقارات</RouterLink>
            <span>/</span>
            <RouterLink :to="`/estates/${estate.id}`">{{ estate.name }}</RouterLink>
            <span>/</span>
            <span>تحليل استثماري</span>
          </nav>
          <h1 class="analysis-hero__title">تحليل استثماري</h1>
          <p class="analysis-hero__desc">
            احسب العائد المتوقع على استثمارك في <strong>{{ estate.name }}</strong> بناءً على المعطيات المالية التي تدخلها.
          </p>
        </div>
      </div>

      <div class="container py-4">
        <div class="row g-4">
          <div class="col-lg-5">
            <div class="analysis-card">
              <div class="analysis-card__header">
                <i class="bi bi-sliders"></i>
                <h3>معطيات الحسبة</h3>
              </div>

              <form @submit.prevent="handleSave">
                <div class="row g-3">
                  <div class="col-12">
                    <AppFormGroup label="سعر العقار" required>
                      <AppInput v-model="form.property_price" type="number" min="0" placeholder="مثلاً 50000000" />
                    </AppFormGroup>
                  </div>

                  <div class="col-md-6">
                    <AppFormGroup label="الإيجار الشهري">
                      <AppInput v-model="form.monthly_rent" type="number" min="0" placeholder="0" />
                    </AppFormGroup>
                  </div>

                  <div class="col-md-6">
                    <AppFormGroup label="نسبة الإشغال (%)">
                      <AppInput v-model="form.occupancy_rate" type="number" min="0" max="100" placeholder="100" />
                    </AppFormGroup>
                  </div>

                  <div class="col-md-4">
                    <AppFormGroup label="المصاريف السنوية">
                      <AppInput v-model="form.annual_expenses" type="number" min="0" placeholder="0" />
                    </AppFormGroup>
                  </div>

                  <div class="col-md-4">
                    <AppFormGroup label="تكاليف الصيانة">
                      <AppInput v-model="form.maintenance_cost" type="number" min="0" placeholder="0" />
                    </AppFormGroup>
                  </div>

                  <div class="col-md-4">
                    <AppFormGroup label="الضرائب">
                      <AppInput v-model="form.tax_cost" type="number" min="0" placeholder="0" />
                    </AppFormGroup>
                  </div>
                </div>

                <FormAlert v-if="saveError" :message="saveError" variant="error" class="mt-3" />

                <hr class="my-3" />

                <AppButton
                  v-if="isRegularUser"
                  type="submit"
                  variant="primary"
                  class="w-100 analysis-save-btn"
                  :loading="saving"
                  :disabled="!canSave()"
                >
                  <i class="bi bi-save"></i>
                  حفظ التحليل إلى محفظتي
                </AppButton>
                <AppButton
                  v-else-if="!isAuthenticated"
                  :to="`/login?redirect=${encodeURIComponent(route.fullPath)}`"
                  variant="primary"
                  class="w-100 analysis-save-btn"
                >
                  <i class="bi bi-box-arrow-in-right"></i>
                  سجّل الدخول لحفظ التحليل
                </AppButton>
              </form>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="analysis-card">
              <div class="analysis-card__header">
                <i class="bi bi-graph-up"></i>
                <h3>نتائج التحليل</h3>
              </div>

              <div v-if="!canSave()" class="analysis-placeholder">
                <i class="bi bi-calculator"></i>
                <p>أدخل سعر العقار لبدء الحساب</p>
              </div>

              <template v-else>
                <div class="analysis-results-grid">
                  <div class="analysis-result-card">
                    <i class="bi bi-graph-up-arrow analysis-result-icon--roi"></i>
                    <span class="analysis-result-card__label">العائد على الاستثمار</span>
                    <strong class="analysis-result-card__value">
                      {{ calculatedRoi !== null ? `${(calculatedRoi * 100).toFixed(2)}%` : '—' }}
                    </strong>
                    <span class="analysis-result-card__desc">نسبة العائد السنوي</span>
                  </div>

                  <div class="analysis-result-card">
                    <i class="bi bi-cash-stack analysis-result-icon--income"></i>
                    <span class="analysis-result-card__label">الدخل السنوي المتوقع</span>
                    <strong class="analysis-result-card__value">
                      {{ calculatedIncome !== null ? formatPrice(calculatedIncome) : '—' }}
                    </strong>
                    <span class="analysis-result-card__desc">صافي الدخل بعد المصاريف</span>
                  </div>

                  <div class="analysis-result-card">
                    <i class="bi bi-hourglass-split analysis-result-icon--payback"></i>
                    <span class="analysis-result-card__label">فترة الاسترداد</span>
                    <strong class="analysis-result-card__value">
                      {{ calculatedPayback !== null ? `${calculatedPayback.toFixed(1)} سنة` : '—' }}
                    </strong>
                    <span class="analysis-result-card__desc">الوقت لاسترداد رأس المال</span>
                  </div>

                  <div class="analysis-result-card">
                    <i class="bi bi-currency-dollar analysis-result-icon--price"></i>
                    <span class="analysis-result-card__label">سعر العقار</span>
                    <strong class="analysis-result-card__value">
                      {{ form.property_price ? formatPrice(form.property_price) : '—' }}
                    </strong>
                    <span class="analysis-result-card__desc">قيمة العقار المدخلة</span>
                  </div>
                </div>

                <div v-if="calculatedRoi !== null" class="analysis-summary">
                  <i class="bi bi-info-circle"></i>
                  <p>
                    بناءً على المعطيات المدخلة، إذا اشتريت العقار بسعر
                    <strong>{{ formatPrice(form.property_price) }}</strong>
                    وكان الإيجار الشهري <strong>{{ formatPrice(form.monthly_rent) }}</strong>،
                    فإنّ العائد السنوي المتوقع هو
                    <strong class="analysis-summary__roi">{{ (calculatedRoi * 100).toFixed(2) }}%</strong>
                    مع دخل سنوي صافي يقارب <strong>{{ calculatedIncome !== null ? formatPrice(calculatedIncome) : '—' }}</strong>،
                    ويمكنك استرداد رأس مالك خلال
                    <strong>{{ calculatedPayback !== null ? `${calculatedPayback.toFixed(1)} سنة` : '—' }}</strong>.
                  </p>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.analysis-hero {
  background: var(--color-surface-sunken);
  border-bottom: 1px solid var(--color-border);
  padding: 2rem 1.5rem;
}

.analysis-hero__content {
  max-width: 1200px;
  margin: 0 auto;
}

.analysis-hero__breadcrumb {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  margin-bottom: 1rem;
}

.analysis-hero__breadcrumb a {
  color: var(--color-text-muted);
  text-decoration: none;
  transition: color 0.2s;
}

.analysis-hero__breadcrumb a:hover {
  color: var(--color-text-link);
}

.analysis-hero__title {
  margin: 0 0 0.5rem;
  font-size: 1.75rem;
  color: var(--color-text-primary);
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.analysis-hero__desc {
  color: var(--color-text-secondary);
  margin: 0;
  font-size: 1rem;
  max-width: 600px;
}

.analysis-hero__desc strong {
  color: var(--color-text-primary);
}

.analysis-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 1rem;
  padding: 1.5rem;
}

.analysis-card__header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid var(--color-border);
}

.analysis-card__header i {
  font-size: 1.25rem;
  color: var(--color-primary);
}

.analysis-card__header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--color-text-primary);
}

.analysis-card hr {
  border-color: var(--color-border);
  opacity: 0.5;
}

.analysis-save-btn {
  padding: 0.75rem 1.5rem;
  font-weight: 600;
  font-size: 0.95rem;
}

.analysis-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem 1rem;
  color: var(--color-text-muted);
  text-align: center;
}

.analysis-placeholder i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--color-border-strong);
}

.analysis-placeholder p {
  margin: 0;
  font-size: 1rem;
}

.analysis-results-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.analysis-result-card {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 1.25rem;
  border-radius: 0.75rem;
  background: var(--color-background-muted);
  border: 1px solid var(--color-border-subtle);
}

.analysis-result-card i {
  font-size: 1.35rem;
  margin-bottom: 0.25rem;
}

.analysis-result-icon--roi { color: var(--color-success); }
.analysis-result-icon--income { color: var(--color-info); }
.analysis-result-icon--payback { color: var(--color-warning); }
.analysis-result-icon--price { color: var(--color-text-muted); }

.analysis-result-card__label {
  font-size: var(--text-xs);
  color: var(--color-text-secondary);
}

.analysis-result-card__value {
  font-family: var(--font-family-price);
  font-size: 1.3rem;
  color: var(--color-text-primary);
}

.analysis-result-card__desc {
  font-size: 0.7rem;
  color: var(--color-text-muted);
}

.analysis-summary {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
  padding: 1rem;
  background: var(--color-background-muted);
  border: 1px solid var(--color-border-subtle);
  border-radius: 0.75rem;
  font-size: var(--text-sm);
  color: var(--color-text-secondary);
  line-height: 1.7;
}

.analysis-summary i {
  font-size: 1.25rem;
  color: var(--color-primary);
  flex-shrink: 0;
  margin-top: 0.15rem;
}

.analysis-summary p {
  margin: 0;
}

.analysis-summary strong {
  color: var(--color-text-primary);
}

.analysis-summary__roi {
  color: var(--color-success);
}
</style>
