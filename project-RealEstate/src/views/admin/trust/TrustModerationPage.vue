<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

import AdminModerationDialog from '@/components/admin/AdminModerationDialog.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import StarRating from '@/components/ui/StarRating.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import {
  REVIEW_STATUS_LABELS,
  REVIEW_STATUS_OPTIONS,
  REVIEW_TYPE_OPTIONS,
  VERIFICATION_STATUS_LABELS,
  VERIFICATION_STATUS_OPTIONS,
  VERIFICATION_TYPE_LABELS,
} from '@/config/admin.js'
import { adminTrustService, downloadVerificationDocument } from '@/api/admin/trust.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAdminTrustManagement } from '@/composables/useAdminTrustManagement.js'
import { formatDate } from '@/composables/useFormatters.js'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  error,
  items,
  pagination,
  search,
  section,
  reviewType,
  statusFilter,
  fetchItems,
  goToPage,
} = useAdminTrustManagement()

const actionLoading = ref(null)
const actionError = ref('')
const rejectDialog = ref({
  open: false,
  kind: '',
  item: null,
})
const confirmStore = useConfirmStore()

function reviewerName(user) {
  if (!user) return 'مستخدم'
  return `${user.fname ?? ''} ${user.lname ?? ''}`.trim() || user.username
}

function reviewTarget(review) {
  if (review.estate) return review.estate.name
  if (review.agent?.user) {
    return `${review.agent.user.fname ?? ''} ${review.agent.user.lname ?? ''}`.trim() || review.agent.user.username
  }
  if (review.company) return review.company.company_name
  return '—'
}

function reviewTargetLink(review) {
  if (review.estate_id) return `/admin/estates/${review.estate_id}`
  if (review.agent_id) return `/admin/agents/${review.agent_id}`
  if (review.company_id) return `/admin/companies/${review.company_id}`
  return null
}

function reviewServiceMap() {
  return {
    property: {
      approve: (id) => adminTrustService.approvePropertyReview(id),
      reject: (id, notes) => adminTrustService.rejectPropertyReview(id, notes),
      remove: (id) => adminTrustService.deletePropertyReview(id),
      recalculate: null,
    },
    agent: {
      approve: (id) => adminTrustService.approveAgentReview(id),
      reject: (id, notes) => adminTrustService.rejectAgentReview(id, notes),
      remove: (id) => adminTrustService.deleteAgentReview(id),
      recalculate: (agentId) => adminTrustService.recalculateAgentTrust(agentId),
    },
    company: {
      approve: (id) => adminTrustService.approveCompanyReview(id),
      reject: (id, notes) => adminTrustService.rejectCompanyReview(id, notes),
      remove: (id) => adminTrustService.deleteCompanyReview(id),
      recalculate: (companyId) => adminTrustService.recalculateCompanyTrust(companyId),
    },
  }
}

async function runAction(itemId, action) {
  actionLoading.value = itemId
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

async function approveReview(review) {
  const service = reviewServiceMap()[reviewType.value]
  await runAction(review.id, () => service.approve(review.id))
}

function openRejectReview(review) {
  rejectDialog.value = { open: true, kind: 'review', item: review }
}

function openRejectVerification(item) {
  rejectDialog.value = { open: true, kind: 'verification', item }
}

async function confirmReject(notes) {
  const { kind, item } = rejectDialog.value
  if (!item) return

  if (kind === 'review') {
    const service = reviewServiceMap()[reviewType.value]
    await runAction(item.id, () => service.reject(item.id, notes))
  } else {
    await runAction(item.id, () => adminTrustService.rejectVerification(item.id, notes))
  }

  rejectDialog.value = { open: false, kind: '', item: null }
}

async function removeReview(review) {
  if (!(await confirmStore.show({ message: 'حذف هذا التقييم نهائياً؟' }))) return
  const service = reviewServiceMap()[reviewType.value]
  await runAction(review.id, () => service.remove(review.id))
}

async function recalculateTrust(review) {
  const service = reviewServiceMap()[reviewType.value]
  if (!service.recalculate) return

  const entityId = review.agent_id ?? review.company_id
  await runAction(`trust-${entityId}`, () => service.recalculate(entityId))
}

async function approveVerification(item) {
  await runAction(item.id, () => adminTrustService.approveVerification(item.id))
}

async function downloadDocument(item) {
  actionLoading.value = `doc-${item.id}`
  actionError.value = ''

  try {
    await downloadVerificationDocument(item.id, `verification-${item.id}`)
  } catch (err) {
    actionError.value = getErrorMessage(err, 'تعذّر تحميل المستند.')
  } finally {
    actionLoading.value = null
  }
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="الثقة والمراجعة"
      description="إدارة التقييمات وطلبات التوثيق: مراجعة، اعتماد، رفض، وحذف."
    />

    <div class="admin-trust-tabs">
      <button
        type="button"
        class="admin-trust-tab"
        :class="{ 'admin-trust-tab--active': section === 'reviews' }"
        @click="section = 'reviews'"
      >
        التقييمات
      </button>
      <button
        type="button"
        class="admin-trust-tab"
        :class="{ 'admin-trust-tab--active': section === 'verifications' }"
        @click="section = 'verifications'"
      >
        طلبات التوثيق
      </button>
    </div>

    <DirectoryToolbar v-model:search="search" placeholder="بحث بالاسم أو نص التقييم...">
      <template #filters>
        <AppSelect
          v-if="section === 'reviews'"
          v-model="reviewType"
          size="sm"
          :options="REVIEW_TYPE_OPTIONS"
        />
        <AppSelect
          v-model="statusFilter"
          size="sm"
          :options="section === 'reviews' ? REVIEW_STATUS_OPTIONS : VERIFICATION_STATUS_OPTIONS"
        />
      </template>
    </DirectoryToolbar>

    <FormAlert v-if="actionError" :message="actionError" variant="error" />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

    <template v-else>
      <p v-if="!items.length" class="admin-table__empty">لا توجد عناصر مطابقة.</p>

      <template v-if="section === 'reviews'">
        <article v-for="review in items" :key="review.id" class="admin-moderation-card">
          <div class="admin-moderation-card__header">
            <div class="admin-moderation-card__title-group">
              <div class="admin-moderation-card__title-row">
                <strong>{{ reviewerName(review.user) }}</strong>
                <StatusBadge :status="review.status" :labels="REVIEW_STATUS_LABELS" />
              </div>
              <StarRating :rating="review.rating" size="sm" />
              <p class="admin-moderation-card__meta">
                عن:
                <RouterLink v-if="reviewTargetLink(review)" :to="reviewTargetLink(review)">
                  {{ reviewTarget(review) }}
                </RouterLink>
                <span v-else>{{ reviewTarget(review) }}</span>
              </p>
              <p class="admin-moderation-card__meta">
                {{ formatDate(review.created_at) }}
                <span v-if="review.reviewer">
                  · راجعه {{ reviewerName(review.reviewer) }}
                </span>
              </p>
            </div>
          </div>

          <p v-if="review.review" class="admin-moderation-card__text">{{ review.review }}</p>
          <p v-if="review.admin_notes" class="admin-moderation-card__notes">
            ملاحظة إدارية: {{ review.admin_notes }}
          </p>

          <TableActionGroup>
            <TableAction
              v-if="review.status === 'pending'"
              tone="success"
              label="اعتماد"
              icon="bi-patch-check"
              :disabled="Boolean(actionLoading)"
              @click="approveReview(review)"
            />
            <TableAction
              v-if="review.status === 'pending'"
              tone="reject"
              label="رفض"
              :disabled="Boolean(actionLoading)"
              @click="openRejectReview(review)"
            />
            <TableAction
              v-if="reviewType !== 'property'"
              tone="neutral"
              label="إعادة حساب الثقة"
              icon="bi-arrow-repeat"
              :disabled="Boolean(actionLoading)"
              @click="recalculateTrust(review)"
            />
            <TableAction
              tone="danger"
              label="حذف"
              :disabled="Boolean(actionLoading)"
              @click="removeReview(review)"
            />
          </TableActionGroup>
        </article>
      </template>

      <template v-else>
        <article v-for="item in items" :key="item.id" class="admin-moderation-card">
          <div class="admin-moderation-card__header">
            <div class="admin-moderation-card__title-group">
              <div class="admin-moderation-card__title-row">
                <strong>{{ reviewerName(item.user) }}</strong>
                <StatusBadge :status="item.status" :labels="VERIFICATION_STATUS_LABELS" />
              </div>
              <p class="admin-moderation-card__meta">
                {{ VERIFICATION_TYPE_LABELS[item.document_type] ?? item.document_type }}
              </p>
              <p class="admin-moderation-card__meta">
                {{ formatDate(item.created_at) }}
                <span v-if="item.reviewer"> · راجعه {{ reviewerName(item.reviewer) }}</span>
              </p>
              <p v-if="item.user" class="admin-moderation-card__meta">
                <RouterLink :to="`/admin/users/${item.user.id}`">ملف المستخدم</RouterLink>
                <span v-if="item.user.is_verified"> · موثّق</span>
              </p>
            </div>
          </div>

          <p v-if="item.admin_notes" class="admin-moderation-card__notes">
            ملاحظة إدارية: {{ item.admin_notes }}
          </p>

          <TableActionGroup>
            <TableAction
              v-if="item.has_document"
              tone="neutral"
              label="عرض المستند"
              icon="bi-file-earmark-arrow-down"
              :disabled="Boolean(actionLoading)"
              @click="downloadDocument(item)"
            />
            <TableAction
              v-if="item.status === 'pending'"
              tone="success"
              label="اعتماد"
              icon="bi-patch-check"
              :disabled="Boolean(actionLoading)"
              @click="approveVerification(item)"
            />
            <TableAction
              v-if="item.status === 'pending'"
              tone="reject"
              label="رفض"
              :disabled="Boolean(actionLoading)"
              @click="openRejectVerification(item)"
            />
          </TableActionGroup>
        </article>
      </template>

      <Pagination
        v-if="pagination"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        @page-change="goToPage"
      />
    </template>

    <AdminModerationDialog
      :open="rejectDialog.open"
      :loading="Boolean(actionLoading)"
      @close="rejectDialog = { open: false, kind: '', item: null }"
      @confirm="confirmReject"
    />
  </div>
</template>
