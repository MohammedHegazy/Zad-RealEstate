<script setup>
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AdminUserForm from '@/components/admin/AdminUserForm.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { USER_STATUS_LABELS, USER_TYPE_LABELS } from '@/config/admin.js'
import { useAdminUserDetail } from '@/composables/useAdminUserDetail.js'
import { formatUserPhone } from '@/utils/user.js'
import { ref } from 'vue'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  saving,
  deleting,
  error,
  user,
  userName,
  fetchUser,
  saveUser,
  updateStatus,
  removeUser,
} = useAdminUserDetail()
const confirmStore = useConfirmStore()
const userFormRef = ref(null)

function formatDate(value) {
  if (!value) return '—'
  return new Date(value).toLocaleString('ar-SY')
}

async function handleSave(payload) {
  try {
    await saveUser(payload)
  } catch (err) {
    userFormRef.value?.handleSubmitError(err)
  }
}

async function handleDelete() {
  if (!(await confirmStore.show({ message: `حذف المستخدم «${userName}» نهائياً؟` }))) return
  await removeUser()
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'المستخدمون', to: '/admin/users' },
        { label: userName },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchUser($route.params.id)" />

    <template v-else-if="user">
      <AdminPageHeader :title="userName" :description="user.email">
        <template #actions>
          <StatusBadge :status="user.type" :labels="USER_TYPE_LABELS" />
          <StatusBadge :status="user.status" :labels="USER_STATUS_LABELS" />
          <span v-if="user.is_verified" class="admin-user-detail__verified">
            <i class="bi bi-patch-check-fill"></i>
            موثّق
          </span>
        </template>
      </AdminPageHeader>

      <div class="admin-user-detail__meta">
        <span><i class="bi bi-at"></i> {{ user.username }}</span>
        <span v-if="formatUserPhone(user)">
          <i class="bi bi-telephone"></i>
          {{ formatUserPhone(user) }}
        </span>
        <span><i class="bi bi-calendar3"></i> انضم {{ formatDate(user.created_at) }}</span>
      </div>

      <AdminStatsSection v-if="user.counts" title="نشاط المستخدم">
        <div class="admin-stats-grid">
          <AdminStatCard label="العقارات" :value="user.counts.estates" icon="bi-buildings" />
          <AdminStatCard
            label="المفضلة"
            :value="user.counts.favorite_estates"
            icon="bi-heart"
          />
          <AdminStatCard
            label="التقييمات"
            :value="user.counts.property_reviews"
            icon="bi-chat-square-text"
          />
          <AdminStatCard
            label="الإشعارات"
            :value="user.counts.notifications"
            icon="bi-bell"
          />
        </div>
      </AdminStatsSection>

      <div v-if="user.agent || user.company" class="admin-user-detail__links">
        <AppButton
          v-if="user.agent"
          :to="`/admin/agents/${user.agent.id}`"
          variant="outline"
          size="sm"
        >
          ملف الوسيط العام
        </AppButton>
        <AppButton
          v-if="user.company"
          :to="`/companies/${user.company.id}`"
          variant="outline"
          size="sm"
        >
          {{ user.company.company_name }}
        </AppButton>
      </div>

      <AdminStatsSection title="تعديل الحساب">
        <AdminUserForm
          ref="userFormRef"
          :user="user"
          :loading="saving"
          @submit="handleSave"
        />
      </AdminStatsSection>

      <AdminStatsSection title="إجراءات سريعة">
        <TableActionGroup>
          <TableAction
            v-if="user.status !== 'active'"
            tone="success"
            label="تفعيل الحساب"
            :disabled="saving"
            @click="updateStatus('active')"
          />
          <TableAction
            v-if="user.status !== 'inactive'"
            tone="neutral"
            label="تعطيل"
            icon="bi-slash-circle"
            :disabled="saving"
            @click="updateStatus('inactive')"
          />
          <TableAction
            v-if="user.status !== 'suspended'"
            tone="warning"
            label="إيقاف"
            :disabled="saving"
            @click="updateStatus('suspended')"
          />
        </TableActionGroup>
      </AdminStatsSection>

      <section class="admin-user-detail__danger">
        <h3>منطقة الخطر</h3>
        <p>حذف المستخدم نهائياً من المنصة. لا يمكن التراجع عن هذا الإجراء.</p>
        <TableAction
          tone="danger"
          :label="deleting ? 'جاري الحذف...' : 'حذف المستخدم'"
          :disabled="deleting"
          @click="handleDelete"
        />
      </section>
    </template>
  </div>
</template>
