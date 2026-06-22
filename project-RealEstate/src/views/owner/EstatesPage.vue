<script setup>
import { ref } from 'vue'
import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { ESTATE_STATUS_LABELS } from '@/config/admin.js'
import { myEstatesService } from '@/api/myEstates.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { useCompanyEstatesList } from '@/composables/useCompanyEstatesList.js'
import { useConfirmStore } from '@/stores/confirm.js'

const deletingId = ref(null)
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
} = useCompanyEstatesList()

async function deleteEstate(id) {
  if (!(await confirmStore.show({ message: 'هل أنت متأكد من حذف هذا العقار؟ لا يمكن التراجع عن هذا الإجراء.' }))) return
  deletingId.value = id
  try {
    await myEstatesService.remove(id)
    items.value = items.value.filter((e) => e.id !== id)
  } finally {
    deletingId.value = null
  }
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="عقاراتي"
      description="جميع عقاراتك المسجلة على المنصة."
    >
      <template #actions>
        <AppButton to="/owner/estates/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة عقار
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث باسم العقار..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="statusFilter" size="sm" :options="statusFilterOptions" />
        <AppSelect v-model="typeFilter" size="sm" :options="typeFilterOptions" />
        <AppSelect v-model="kindFilter" size="sm" :options="kindFilterOptions" />
      </template>
    </DirectoryToolbar>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} عقار</p>

      <AdminDataTable
        :columns="[
          { key: 'name', label: 'الاسم' },
          { key: 'price', label: 'السعر' },
          { key: 'type', label: 'النوع' },
          { key: 'roi', label: 'العائد' },
          { key: 'status', label: 'الحالة' },
        ]"
        :rows="items"
        empty-message="لا تملك أي عقارات بعد."
      >
        <template #cell-name="{ row }">
          <strong>{{ row.name }}</strong>
        </template>

        <template #cell-price="{ row }">
          <span class="admin-recent-list__price">{{ formatPrice(row.price) }}</span>
        </template>

        <template #cell-type="{ row }">
          {{ row.kind ?? row.type ?? '—' }}
        </template>

        <template #cell-roi="{ row }">
          {{ row.roi ? `${(Number(row.roi) * 100).toFixed(1)}%` : '—' }}
        </template>

        <template #cell-status="{ row }">
          <StatusBadge :status="row.status ?? 'pending'" :labels="ESTATE_STATUS_LABELS" />
        </template>

        <template #actions="{ row }">
          <TableActionGroup>
            <TableAction
              tone="neutral"
              icon="bi-pencil"
              label="تعديل"
              :to="`/owner/estates/edit/${row.id}`"
            />
            <TableAction
              tone="danger"
              label="حذف"
              :disabled="deletingId === row.id"
              @click="deleteEstate(row.id)"
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
