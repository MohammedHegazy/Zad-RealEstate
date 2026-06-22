<script setup>
import { ref } from 'vue'

import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { adminCitiesService } from '@/api/admin/locations.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminCitiesList } from '@/composables/useAdminCitiesList.js'
import { formatLocationCoordinates, getCityPlacesCount } from '@/utils/location.js'
import { useConfirmStore } from '@/stores/confirm.js'

const actionLoading = ref(null)
const actionError = ref('')
const confirmStore = useConfirmStore()

const {
  loading,
  error,
  items,
  pagination,
  fetchItems,
  goToPage,
  search,
  hasActiveFilters,
  clearFilters,
} = useAdminCitiesList()

const columns = [
  { key: 'name', label: 'المدينة' },
  { key: 'places_count', label: 'المناطق' },
  { key: 'coordinates', label: 'الإحداثيات' },
]

async function runAction(cityId, action) {
  actionLoading.value = cityId
  actionError.value = ''

  try {
    await action()
    await fetchItems()
  } catch (err) {
    actionError.value = getErrorMessage(err, 'تعذّر تنفيذ الإجراء.')
  } finally {
    actionLoading.value = null
  }
}

async function removeCity(city) {
  if (!(await confirmStore.show({ message: `حذف مدينة «${city.name}»؟` }))) return
  await runAction(city.id, () => adminCitiesService.remove(city.id))
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="المدن"
      description="إدارة كاملة للمدن: إنشاء، تعديل، موقع على الخريطة، وحذف."
    >
      <template #actions>
        <AppButton to="/admin/cities/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة مدينة
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث باسم المدينة..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    />

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} مدينة</p>

      <AdminDataTable :columns="columns" :rows="items">
        <template #cell-name="{ row }">
          <RouterLink :to="`/admin/cities/${row.id}`" class="admin-table__link">
            {{ row.name }}
          </RouterLink>
        </template>
        <template #cell-places_count="{ row }">
          <RouterLink
            :to="`/admin/places?cities_id=${row.id}`"
            class="admin-table__link"
          >
            {{ getCityPlacesCount(row) }}
          </RouterLink>
        </template>
        <template #cell-coordinates="{ row }">
          {{ formatLocationCoordinates(row.latitude, row.longitude) }}
        </template>
        <template #actions="{ row }">
          <TableActionGroup :compact-mobile="true">
            <TableAction tone="view" label="عرض" :to="`/admin/cities/${row.id}`" />
            <TableAction
              tone="neutral"
              label="المناطق"
              icon="bi-pin-map"
              :to="`/admin/places?cities_id=${row.id}`"
            />
            <TableAction
              tone="danger"
              label="حذف"
              divider
              :disabled="actionLoading === row.id"
              @click="removeCity(row)"
            />
          </TableActionGroup>
        </template>
      </AdminDataTable>

      <Pagination
        v-if="pagination"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        @page-change="goToPage"
      />
    </template>
  </div>
</template>
