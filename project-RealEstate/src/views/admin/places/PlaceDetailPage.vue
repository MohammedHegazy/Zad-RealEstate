<script setup>
import AdminPlaceForm from '@/components/admin/AdminPlaceForm.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import { useAdminPlaceDetail } from '@/composables/useAdminPlaceDetail.js'
import { formatLocationCoordinates, getPlaceCityName } from '@/utils/location.js'
import { ref } from 'vue'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  saving,
  deleting,
  error,
  place,
  isCreate,
  presetCityId,
  fetchPlace,
  savePlace,
  removePlace,
} = useAdminPlaceDetail()
const confirmStore = useConfirmStore()
const placeFormRef = ref(null)

async function handleSave(payload) {
  try {
    await savePlace(payload)
  } catch (err) {
    placeFormRef.value?.handleSubmitError(err)
  }
}

async function handleDelete() {
  if (!place.value) return
  if (!(await confirmStore.show({ message: `حذف منطقة «${place.value.name}» نهائياً؟` }))) return
  await removePlace()
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'المناطق', to: '/admin/places' },
        { label: isCreate ? 'منطقة جديدة' : place?.name ?? '...' },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert
      v-else-if="error"
      :message="error"
      @retry="fetchPlace($route.params.id)"
    />

    <template v-else-if="isCreate || place">
      <AdminPageHeader
        :title="isCreate ? 'إضافة منطقة جديدة' : place.name"
        :description="isCreate ? 'إنشاء منطقة داخل مدينة.' : getPlaceCityName(place)"
      />

      <div v-if="!isCreate" class="admin-place-detail__meta">
        <span>
          <i class="bi bi-geo"></i>
          {{ getPlaceCityName(place) }}
        </span>
        <span>
          <i class="bi bi-buildings"></i>
          {{ place.estates_count ?? place.estates?.length ?? 0 }} عقار
        </span>
        <span>
          <i class="bi bi-building"></i>
          {{ place.companies_count ?? place.companies?.length ?? 0 }} شركة
        </span>
        <span>
          <i class="bi bi-geo-alt"></i>
          {{ formatLocationCoordinates(place.latitude, place.longitude) }}
        </span>
      </div>

      <div v-if="!isCreate" class="admin-place-detail__links">
        <AppButton
          v-if="place.cities_id"
          :to="`/admin/cities/${place.cities_id}`"
          variant="outline"
          size="sm"
        >
          ملف المدينة
        </AppButton>
        <AppButton :to="`/places/${place.id}`" variant="outline" size="sm">
          معاينة عامة
        </AppButton>
      </div>

      <AdminStatsSection v-if="!isCreate" title="إحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard label="العقارات" :value="place.estates_count ?? 0" icon="bi-buildings" />
          <AdminStatCard label="الشركات" :value="place.companies_count ?? 0" icon="bi-building" />
        </div>
      </AdminStatsSection>

      <AdminStatsSection :title="isCreate ? 'بيانات المنطقة' : 'تعديل المنطقة'">
        <AdminPlaceForm
          ref="placeFormRef"
          :place="place"
          :is-create="isCreate"
          :preset-city-id="presetCityId"
          :loading="saving"
          @submit="handleSave"
        />
      </AdminStatsSection>

      <section v-if="!isCreate" class="admin-user-detail__danger">
        <h3>منطقة الخطر</h3>
        <p>حذف المنطقة نهائياً. لا يمكن الحذف إذا كانت مرتبطة بعقارات أو شركات.</p>
        <TableAction
          tone="danger"
          :label="deleting ? 'جاري الحذف...' : 'حذف المنطقة'"
          :disabled="deleting"
          @click="handleDelete"
        />
      </section>
    </template>
  </div>
</template>
