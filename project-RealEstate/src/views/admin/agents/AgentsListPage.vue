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
import { adminAgentsService } from '@/api/admin/agents.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminAgentsList } from '@/composables/useAdminAgentsList.js'
import { getAgentName } from '@/utils/agent.js'
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
  companyFilter,
  hasActiveFilters,
  clearFilters,
  companyFilterOptions,
} = useAdminAgentsList()

const columns = [
  { key: 'name', label: 'الوسيط' },
  { key: 'company', label: 'الشركة' },
  { key: 'trust_score', label: 'الثقة' },
  { key: 'views', label: 'المشاهدات' },
  { key: 'ratings', label: 'التقييم' },
]

async function runAction(agentId, action) {
  actionLoading.value = agentId
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

async function removeAgent(agent) {
  if (!(await confirmStore.show({ message: `حذف الوسيط «${getAgentName(agent)}»؟` }))) return
  await runAction(agent.id, () => adminAgentsService.remove(agent.id))
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="الوسطاء"
      description="إدارة كاملة لملفات الوسطاء: إنشاء، تعديل، وربطهم بالشركات."
    >
      <template #actions>
        <AppButton to="/admin/agents/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة وسيط
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث بالاسم أو الشركة..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="companyFilter" size="sm" :options="companyFilterOptions" />
      </template>
    </DirectoryToolbar>

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} وسيط</p>

      <AdminDataTable :columns="columns" :rows="items">
        <template #cell-name="{ row }">
          <RouterLink :to="`/admin/agents/${row.id}`" class="admin-table__link">
            {{ getAgentName(row) }}
          </RouterLink>
        </template>
        <template #cell-company="{ row }">
          <RouterLink
            v-if="row.companies_id"
            :to="`/admin/companies/${row.companies_id}`"
            class="admin-table__link"
          >
            {{ row.company?.company_name ?? '—' }}
          </RouterLink>
          <span v-else>{{ row.company?.company_name ?? '—' }}</span>
        </template>
        <template #cell-trust_score="{ row }">
          {{ row.trust_score != null ? `${row.trust_score}%` : '—' }}
        </template>
        <template #cell-ratings="{ row }">
          {{ row.average_rating != null ? `${row.average_rating} (${row.ratings_count ?? 0})` : '—' }}
        </template>
        <template #actions="{ row }">
          <TableActionGroup :compact-mobile="true">
            <TableAction tone="view" label="عرض" :to="`/admin/agents/${row.id}`" />
            <TableAction
              tone="danger"
              label="حذف"
              :disabled="actionLoading === row.id"
              @click="removeAgent(row)"
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
