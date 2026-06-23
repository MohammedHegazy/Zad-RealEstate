<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminEstateMediaPanel from '@/components/admin/AdminEstateMediaPanel.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import EstateForm from '@/components/company/EstateForm.vue'
import EstateInvestment from '@/components/estates/EstateInvestment.vue'
import EstateSpecs from '@/components/estates/EstateSpecs.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { myEstatesService } from '@/api/myEstates.js'
import { placesService } from '@/api/places.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const router = useRouter()
const places = ref([])
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const estateFormRef = ref(null)
const createdEstate = ref(null)

async function fetchPlaces() {
  try {
    const { data } = await placesService.list({ per_page: 200 })
    places.value = data ?? []
  } catch (err) {
    loadError.value = getErrorMessage(err, 'تعذّر تحميل المناطق.')
  } finally {
    loading.value = false
  }
}

async function handleSubmit(formData) {
  saving.value = true
  try {
    if (createdEstate.value?.id) {
      const res = await myEstatesService.update(createdEstate.value.id, formData)
      createdEstate.value = res.data ?? res
    } else {
      const res = await myEstatesService.create(formData)
      createdEstate.value = res.data ?? res
    }
  } catch (err) {
    estateFormRef.value?.handleSubmitError(err)
  } finally {
    saving.value = false
  }
}

async function refreshEstate() {
  if (!createdEstate.value?.id) return
  try {
    const res = await myEstatesService.getById(createdEstate.value.id)
    createdEstate.value = res.data
  } catch {
    // silent
  }
}

onMounted(fetchPlaces)
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'العقارات', to: '/company/estates' },
        { label: createdEstate ? 'تعديل عقار' : 'إضافة عقار' },
      ]"
    />

    <AdminPageHeader
      :title="createdEstate ? 'تعديل عقار' : 'إضافة عقار'"
      :description="createdEstate ? 'تم إنشاء العقار. يمكنك الآن إدارة الوسائط.' : 'سجّل عقاراً جديداً في المنصة.'"
    />
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="loadError" :message="loadError" />
    <template v-else>
      <AdminStatsSection v-if="createdEstate" title="معاينة سريعة">
        <div class="admin-estate-detail__preview">
          <div class="admin-estate-detail__preview-side">
            <EstateSpecs :estate="createdEstate" />
            <EstateInvestment :estate="createdEstate" />
          </div>
        </div>
      </AdminStatsSection>

      <EstateForm
        ref="estateFormRef"
        :initial-data="createdEstate"
        :places="places"
        :saving="saving"
        @submit="handleSubmit"
        @cancel="router.push('/company/estates')"
      />

      <AdminStatsSection v-if="createdEstate" title="إدارة الوسائط">
        <AdminEstateMediaPanel
          :estate="createdEstate"
          :service="myEstatesService"
          @updated="refreshEstate"
        />
      </AdminStatsSection>
    </template>
  </div>
</template>
