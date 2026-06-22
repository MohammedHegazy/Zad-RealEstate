<script setup>
import AdminCityForm from '@/components/admin/AdminCityForm.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import { useAdminCityDetail } from '@/composables/useAdminCityDetail.js'
import { formatLocationCoordinates, getCityPlacesCount } from '@/utils/location.js'
import { ref } from 'vue'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  saving,
  deleting,
  error,
  city,
  isCreate,
  fetchCity,
  saveCity,
  removeCity,
} = useAdminCityDetail()
const confirmStore = useConfirmStore()
const cityFormRef = ref(null)

async function handleSave(payload, files) {
  try {
    await saveCity(payload, files)
  } catch (err) {
    cityFormRef.value?.handleSubmitError(err)
  }
}

async function handleDelete() {
  if (!city.value) return
  if (!(await confirmStore.show({ message: `حذف مدينة «${city.value.name}» نهائياً؟` }))) return
  await removeCity()
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'المدن', to: '/admin/cities' },
        { label: isCreate ? 'مدينة جديدة' : city?.name ?? '...' },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert
      v-else-if="error"
      :message="error"
      @retry="fetchCity($route.params.id)"
    />

    <template v-else-if="isCreate || city">
      <AdminPageHeader
        :title="isCreate ? 'إضافة مدينة جديدة' : city.name"
        :description="isCreate ? 'إنشاء مدينة مع موقعها على الخريطة.' : formatLocationCoordinates(city.latitude, city.longitude)"
      />

      <div v-if="!isCreate" class="admin-city-detail__meta">
        <span>
          <i class="bi bi-pin-map"></i>
          {{ getCityPlacesCount(city) }} منطقة
        </span>
        <span>
          <i class="bi bi-geo-alt"></i>
          {{ formatLocationCoordinates(city.latitude, city.longitude) }}
        </span>
      </div>

      <div v-if="!isCreate" class="admin-city-detail__links">
        <AppButton
          :to="`/admin/places?cities_id=${city.id}`"
          variant="outline"
          size="sm"
        >
          مناطق المدينة
        </AppButton>
        <AppButton
          :to="`/admin/places/create?cities_id=${city.id}`"
          variant="outline"
          size="sm"
        >
          إضافة منطقة
        </AppButton>
        <AppButton :to="`/cities/${city.id}`" variant="outline" size="sm">
          معاينة عامة
        </AppButton>
      </div>

      <AdminStatsSection v-if="!isCreate" title="معاينة سريعة">
        <div class="admin-city-detail__preview">
          <div
            class="admin-city-detail__image"
            :style="city.image_url ? { backgroundImage: `url(${city.image_url})` } : undefined"
          ></div>
          <div class="admin-city-detail__preview-body">
            <h3>{{ city.name }}</h3>
            <p>{{ formatLocationCoordinates(city.latitude, city.longitude) }}</p>
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard label="المناطق" :value="getCityPlacesCount(city)" icon="bi-pin-map" />
        </div>
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate && city.places?.length" title="المناطق التابعة">
        <ul class="admin-city-detail__places">
          <li v-for="place in city.places" :key="place.id">
            <RouterLink :to="`/admin/places/${place.id}`">{{ place.name }}</RouterLink>
          </li>
        </ul>
      </AdminStatsSection>

      <AdminStatsSection :title="isCreate ? 'بيانات المدينة' : 'تعديل المدينة'">
        <AdminCityForm
          ref="cityFormRef"
          :city="city"
          :is-create="isCreate"
          :loading="saving"
          @submit="handleSave"
        />
      </AdminStatsSection>

      <section v-if="!isCreate" class="admin-user-detail__danger">
        <h3>منطقة الخطر</h3>
        <p>حذف المدينة نهائياً. لا يمكن الحذف إذا كانت تحتوي على مناطق مرتبطة.</p>
        <TableAction
          tone="danger"
          :label="deleting ? 'جاري الحذف...' : 'حذف المدينة'"
          :disabled="deleting"
          @click="handleDelete"
        />
      </section>
    </template>
  </div>
</template>
