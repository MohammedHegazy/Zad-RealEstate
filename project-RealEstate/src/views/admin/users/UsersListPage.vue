<script setup>
import { computed, ref } from 'vue'

import AdminDataTable from '@/components/admin/AdminDataTable.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import {
  USER_STATUS_LABELS,
  USER_STATUS_OPTIONS,
  USER_TYPE_LABELS,
  USER_TYPE_OPTIONS,
} from '@/config/admin.js'
import { adminUsersService } from '@/api/admin/users.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminUsersList } from '@/composables/useAdminUsersList.js'
import { getUserName } from '@/utils/user.js'
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
  typeFilter,
  statusFilter,
  hasActiveFilters,
  clearFilters,
} = useAdminUsersList()

const typeFilterOptions = computed(() => [
  { value: '', label: 'كل الأنواع' },
  ...USER_TYPE_OPTIONS,
])

const statusFilterOptions = computed(() => [
  { value: '', label: 'كل الحالات' },
  ...USER_STATUS_OPTIONS,
])

const columns = [
  { key: 'name', label: 'الاسم' },
  { key: 'email', label: 'البريد' },
  { key: 'type', label: 'النوع' },
  { key: 'status', label: 'الحالة' },
  { key: 'estates_count', label: 'العقارات' },
]

async function runAction(userId, action) {
  actionLoading.value = userId
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

async function updateStatus(user, status) {
  await runAction(user.id, () => adminUsersService.update(user.id, { status }))
}

async function removeUser(user) {
  if (!(await confirmStore.show({ message: `حذف المستخدم «${getUserName(user)}»؟` }))) return
  await runAction(user.id, () => adminUsersService.remove(user.id))
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="المستخدمون"
      description="بحث، تصفية، وتعديل حسابات المنصة."
    />

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث بالاسم، البريد، أو الهاتف..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="typeFilter" size="sm" :options="typeFilterOptions" />
        <AppSelect v-model="statusFilter" size="sm" :options="statusFilterOptions" />
      </template>
    </DirectoryToolbar>

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} مستخدم</p>

      <AdminDataTable :columns="columns" :rows="items">
        <template #cell-name="{ row }">
          <div>
            <RouterLink :to="`/admin/users/${row.id}`" class="admin-table__link">
              {{ getUserName(row) }}
            </RouterLink>
            <p v-if="row.is_verified" class="admin-table__sub">
              <i class="bi bi-patch-check-fill"></i>
              موثّق
            </p>
          </div>
        </template>
        <template #cell-type="{ row }">
          {{ USER_TYPE_LABELS[row.type] ?? row.type }}
        </template>
        <template #cell-status="{ row }">
          <StatusBadge :status="row.status" :labels="USER_STATUS_LABELS" />
        </template>
        <template #actions="{ row }">
          <TableActionGroup :compact-mobile="true">
            <TableAction tone="view" label="عرض" :to="`/admin/users/${row.id}`" />
            <TableAction
              v-if="row.status !== 'active'"
              tone="success"
              label="تفعيل"
              :disabled="actionLoading === row.id"
              @click="updateStatus(row, 'active')"
            />
            <TableAction
              v-if="row.status !== 'suspended'"
              tone="warning"
              label="إيقاف"
              :disabled="actionLoading === row.id"
              @click="updateStatus(row, 'suspended')"
            />
            <TableAction
              tone="danger"
              label="حذف"
              divider
              :disabled="actionLoading === row.id"
              @click="removeUser(row)"
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
