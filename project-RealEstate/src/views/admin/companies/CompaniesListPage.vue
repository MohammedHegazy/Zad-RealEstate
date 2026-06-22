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
import { COMPANY_STATUS_LABELS } from '@/config/admin.js'
import { adminCompaniesService } from '@/api/admin/companies.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminCompaniesList } from '@/composables/useAdminCompaniesList.js'
import { getCompanyLocation, getCompanyOwnerName } from '@/utils/company.js'
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
  hasActiveFilters,
  clearFilters,
  statusFilterOptions,
} = useAdminCompaniesList()

const columns = [
  { key: 'company_name', label: 'الشركة' },
  { key: 'owner', label: 'المالك' },
  { key: 'place', label: 'الموقع' },
  { key: 'employees_num', label: 'الموظفون' },
  { key: 'status', label: 'الحالة' },
]

async function runAction(companyId, action) {
  actionLoading.value = companyId
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

async function updateStatus(company, status) {
  await runAction(company.id, () => adminCompaniesService.updateStatus(company.id, status))
}

async function removeCompany(company) {
  if (!(await confirmStore.show({ message: `حذف الشركة «${company.company_name}»؟` }))) return
  await runAction(company.id, () => adminCompaniesService.remove(company.id))
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="الشركات"
      description="إدارة كاملة للشركات العقارية: مراجعة، تعديل، وسائط، وحذف."
    >
      <template #actions>
        <AppButton to="/admin/companies/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة شركة
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث باسم الشركة..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="statusFilter" size="sm" :options="statusFilterOptions" />
      </template>
    </DirectoryToolbar>

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} شركة</p>

      <AdminDataTable :columns="columns" :rows="items">
        <template #cell-company_name="{ row }">
          <RouterLink :to="`/admin/companies/${row.id}`" class="admin-table__link">
            {{ row.company_name }}
          </RouterLink>
        </template>
        <template #cell-owner="{ row }">
          <RouterLink
            v-if="row.user_id"
            :to="`/admin/users/${row.user_id}`"
            class="admin-table__link"
          >
            {{ getCompanyOwnerName(row) }}
          </RouterLink>
          <span v-else>{{ getCompanyOwnerName(row) }}</span>
        </template>
        <template #cell-place="{ row }">{{ getCompanyLocation(row) }}</template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" :labels="COMPANY_STATUS_LABELS" />
        </template>
        <template #actions="{ row }">
          <TableActionGroup :compact-mobile="true">
            <TableAction tone="view" label="عرض" :to="`/admin/companies/${row.id}`" />
            <TableAction
              v-if="row.status !== 'approved'"
              tone="success"
              label="اعتماد"
              icon="bi-patch-check"
              :disabled="actionLoading === row.id"
              @click="updateStatus(row, 'approved')"
            />
            <TableAction
              v-if="row.status !== 'suspended'"
              tone="warning"
              label="إيقاف"
              :disabled="actionLoading === row.id"
              @click="updateStatus(row, 'suspended')"
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
              @click="removeCompany(row)"
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
