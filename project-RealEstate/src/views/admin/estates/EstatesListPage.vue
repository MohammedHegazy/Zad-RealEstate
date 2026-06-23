<script setup>
import { ref } from 'vue'

import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { ESTATE_STATUS_LABELS } from '@/config/admin.js'
import { adminEstatesService } from '@/api/admin/estates.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminEstatesList } from '@/composables/useAdminEstatesList.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getEstateImage, getEstateLocation, getEstateOwnerName } from '@/utils/estate.js'
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
  statusFilter,
  typeFilter,
  kindFilter,
  hasActiveFilters,
  clearFilters,
  statusFilterOptions,
  typeFilterOptions,
  kindFilterOptions,
} = useAdminEstatesList()

const columns = [
  { key: 'image', label: '' },
  { key: 'name', label: 'العقار' },
  { key: 'owner', label: 'المالك' },
  { key: 'place', label: 'الموقع' },
  { key: 'price', label: 'السعر' },
  { key: 'status', label: 'الحالة' },
]

async function runAction(estateId, action) {
  actionLoading.value = estateId
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

async function updateStatus(estate, status) {
  await runAction(estate.id, () => adminEstatesService.updateStatus(estate.id, status))
}

async function removeEstate(estate) {
  if (!(await confirmStore.show({ message: `حذف العقار «${estate.name}»؟` }))) return
  await runAction(estate.id, () => adminEstatesService.remove(estate.id))
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="العقارات"
      description="إدارة كاملة للعقارات: مراجعة، تعديل، وسائط، وحذف."
    >
      <template #actions>
        <AppButton to="/admin/estates/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة عقار
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث بالاسم أو الوصف..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="statusFilter" size="sm" :options="statusFilterOptions" />
        <AppSelect v-model="typeFilter" size="sm" :options="typeFilterOptions" />
        <AppSelect v-model="kindFilter" size="sm" :options="kindFilterOptions" />
      </template>
    </DirectoryToolbar>

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} عقار</p>

      <AdminDataTable :columns="columns" :rows="items">
        <template #cell-image="{ row }">
          <img
            :src="getEstateImage(row)"
            alt=""
            class="admin-table__thumb"
            loading="lazy"
          />
        </template>
        <template #cell-name="{ row }">
          <RouterLink :to="`/admin/estates/${row.id}`" class="admin-table__link">
            {{ row.name }}
          </RouterLink>
        </template>
        <template #cell-owner="{ row }">
          <RouterLink
            v-if="row.user_id"
            :to="`/admin/users/${row.user_id}`"
            class="admin-table__link"
          >
            {{ getEstateOwnerName(row) }}
          </RouterLink>
          <span v-else>{{ getEstateOwnerName(row) }}</span>
        </template>
        <template #cell-place="{ row }">{{ getEstateLocation(row) }}</template>
        <template #cell-price="{ row }">
          {{ row.price ? formatPrice(row.price) : '—' }}
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" :labels="ESTATE_STATUS_LABELS" />
        </template>
        <template #actions="{ row }">
          <TableActionGroup :compact-mobile="true">
            <TableAction tone="view" label="عرض" :to="`/admin/estates/${row.id}`" />
            <TableAction
              v-if="row.status !== 'active'"
              tone="success"
              label="تفعيل"
              :disabled="actionLoading === row.id"
              @click="updateStatus(row, 'active')"
            />
            <TableAction
              v-if="row.status !== 'rejected'"
              tone="reject"
              label="رفض"
              :disabled="actionLoading === row.id"
              @click="updateStatus(row, 'rejected')"
            />
            <TableAction
              tone="danger"
              label="حذف"
              divider
              :disabled="actionLoading === row.id"
              @click="removeEstate(row)"
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
