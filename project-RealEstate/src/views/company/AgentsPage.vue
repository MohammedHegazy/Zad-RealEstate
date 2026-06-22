<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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
import { companyService } from '@/api/company.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'
import { useConfirmStore } from '@/stores/confirm.js'
import { AGENT_STATUS_LABELS, AGENT_STATUS_OPTIONS } from '@/config/agents.js'

const route = useRoute()
const router = useRouter()
const deletingId = ref(null)
const togglingId = ref(null)
const search = ref(route.query.search?.toString() ?? '')
let searchDebounceTimer
const confirmStore = useConfirmStore()

const statusFilter = computed({
  get: () => route.query.status?.toString() ?? '',
  set: (value) => {
    router.push({ query: { ...route.query, status: value || undefined, page: 1 } })
  },
})

watch(
  () => route.query.search,
  (value) => {
    search.value = value?.toString() ?? ''
  },
)

watch(search, (value) => {
  window.clearTimeout(searchDebounceTimer)
  searchDebounceTimer = window.setTimeout(() => {
    const next = value.trim() || undefined
    const current = route.query.search?.toString() || undefined
    if (next === current) return
    router.push({ query: { ...route.query, search: next, page: 1 } })
  }, 400)
})

const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => companyService.agents(params),
  {
    perPage: 15,
    extraParams: (currentRoute) => {
      const params = {}
      if (currentRoute.query.search) params.search = currentRoute.query.search
      if (currentRoute.query.status) params.status = currentRoute.query.status
      return params
    },
  },
)

const hasActiveFilters = computed(() => Boolean(search.value || statusFilter.value))

function clearFilters() {
  router.push({ query: {} })
}

async function deleteAgent(agentId) {
  if (!(await confirmStore.show({ message: 'هل أنت متأكد من حذف هذا الوسيط؟' }))) return
  deletingId.value = agentId
  try {
    await companyService.removeAgent(agentId)
    items.value = items.value.filter((a) => a.id !== agentId)
  } finally {
    deletingId.value = null
  }
}

async function toggleApproval(agent) {
  togglingId.value = agent.id
  try {
    if (agent.status === 'approved') {
      await companyService.rejectAgent(agent.id)
    } else {
      await companyService.approveAgent(agent.id)
    }
    fetchItems()
  } finally {
    togglingId.value = null
  }
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="الوسطاء"
      description="إدارة فريق الوسطاء في شركتك واعتمادهم."
    >
      <template #actions>
        <AppButton to="/company/agents/create" variant="primary" size="sm">
          <i class="bi bi-plus-lg"></i>
          إضافة وسيط
        </AppButton>
      </template>
    </AdminPageHeader>

    <DirectoryToolbar
      v-model:search="search"
      search-placeholder="بحث باسم الوسيط..."
      :show-clear="hasActiveFilters"
      @clear="clearFilters"
    >
      <template #filters>
        <AppSelect v-model="statusFilter" size="sm" :options="AGENT_STATUS_OPTIONS" />
      </template>
    </DirectoryToolbar>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="pagination?.total" class="admin-page__meta">{{ pagination.total }} وسيط</p>

      <AdminDataTable
        :columns="[
          { key: 'name', label: 'الاسم' },
          { key: 'phone', label: 'الهاتف' },
          { key: 'estates_count', label: 'العقارات' },
          { key: 'rating', label: 'التقييم' },
          { key: 'status', label: 'الحالة' },
        ]"
        :rows="items"
        empty-message="لا يوجد وسطاء في شركتك بعد."
      >
        <template #cell-name="{ row }">
          <strong>{{ row.user ? `${row.user.fname} ${row.user.lname}`.trim() || row.user.username : '—' }}</strong>
        </template>

        <template #cell-phone="{ row }">
          {{ row.user?.phone ?? '—' }}
        </template>

        <template #cell-rating="{ row }">
          {{ row.approved_reviews_avg_rating ? Number(row.approved_reviews_avg_rating).toFixed(1) : '—' }}
        </template>

        <template #cell-status="{ row }">
          <StatusBadge :status="row.status ?? 'pending'" :labels="AGENT_STATUS_LABELS" />
        </template>

        <template #actions="{ row }">
          <TableActionGroup>
            <TableAction
              v-if="row.status !== 'approved'"
              tone="success"
              icon="bi-check-circle"
              label="اعتماد"
              :disabled="togglingId === row.id"
              @click="toggleApproval(row)"
            />
            <TableAction
              v-if="row.status === 'approved'"
              tone="warning"
              icon="bi-x-circle"
              label="إلغاء الاعتماد"
              :disabled="togglingId === row.id"
              @click="toggleApproval(row)"
            />
            <TableAction
              tone="neutral"
              icon="bi-pencil"
              label="تعديل"
              :to="`/company/agents/edit/${row.id}`"
            />
            <TableAction
              tone="danger"
              label="حذف"
              :disabled="deletingId === row.id"
              @click="deleteAgent(row.id)"
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
