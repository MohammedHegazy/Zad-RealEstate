<script setup>
import { ref } from 'vue'

import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { adminPlacesService } from '@/api/admin/locations.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminPlacesList } from '@/composables/useAdminPlacesList.js'
import { formatLocationCoordinates, getPlaceCityName } from '@/utils/location.js'
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
  cityFilter,
  hasActiveFilters,
  clearFilters,
  cityFilterOptions,
} = useAdminPlacesList()

const columns = [
  { key: 'name', label: 'المنطقة' },
  { key: 'city', label: 'المدينة' },
  { key: 'estates_count', label: 'العقارات' },
  { key: 'companies_count', label: 'الشركات' },
  { key: 'coordinates', label: 'الإحداثيات' },
]

async function runAction(placeId, action) {
  actionLoading.value = placeId
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

async function removePlace(place) {
  if (!(await confirmStore.show({ message: `حذف منطقة «${place.name}»؟` }))) return
  await runAction(place.id, () => adminPlacesService.remove(place.id))
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="المناطق"
      description="إدارة كاملة للمناطق داخل المدن: إنشاء، تعديل، موقع على الخريطة، وحذف."
    >
      <template #actions>
        <AppButton to="/admin/places/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة منطقة
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث باسم المنطقة..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="cityFilter" size="sm" :options="cityFilterOptions" />
      </template>
    </DirectoryToolbar>

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} منطقة</p>

      <AdminDataTable :columns="columns" :rows="items">
        <template #cell-name="{ row }">
          <RouterLink :to="`/admin/places/${row.id}`" class="admin-table__link">
            {{ row.name }}
          </RouterLink>
        </template>
        <template #cell-city="{ row }">
          <RouterLink
            v-if="row.cities_id"
            :to="`/admin/cities/${row.cities_id}`"
            class="admin-table__link"
          >
            {{ getPlaceCityName(row) }}
          </RouterLink>
          <span v-else>{{ getPlaceCityName(row) }}</span>
        </template>
        <template #cell-coordinates="{ row }">
          {{ formatLocationCoordinates(row.latitude, row.longitude) }}
        </template>
        <template #actions="{ row }">
          <TableActionGroup :compact-mobile="true">
            <TableAction tone="view" label="عرض" :to="`/admin/places/${row.id}`" />
            <TableAction
              tone="danger"
              label="حذف"
              :disabled="actionLoading === row.id"
              @click="removePlace(row)"
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
