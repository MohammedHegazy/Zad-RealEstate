<script setup>
import AdminCompanyForm from '@/components/admin/AdminCompanyForm.vue'
import AdminCompanySocialPanel from '@/components/admin/AdminCompanySocialPanel.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { COMPANY_STATUS_LABELS } from '@/config/admin.js'
import { useAdminCompanyDetail } from '@/composables/useAdminCompanyDetail.js'
import { getCompanyLocation, getCompanyOwnerName } from '@/utils/company.js'
import { ref } from 'vue'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  saving,
  deleting,
  error,
  company,
  isCreate,
  fetchCompany,
  saveCompany,
  updateStatus,
  removeCompany,
  refreshCompany,
} = useAdminCompanyDetail()
const confirmStore = useConfirmStore()
const companyFormRef = ref(null)

async function handleSave(payload, files) {
  try {
    await saveCompany(payload, files)
  } catch (err) {
    companyFormRef.value?.handleSubmitError(err)
  }
}

async function handleDelete() {
  if (!company.value) return
  if (!(await confirmStore.show({ message: `حذف الشركة «${company.value.company_name}» نهائياً؟` }))) return
  await removeCompany()
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'الشركات', to: '/admin/companies' },
        { label: isCreate ? 'شركة جديدة' : company?.company_name ?? '...' },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert
      v-else-if="error"
      :message="error"
      @retry="fetchCompany($route.params.id)"
    />

    <template v-else-if="isCreate || company">
      <AdminPageHeader
        :title="isCreate ? 'إضافة شركة جديدة' : company.company_name"
        :description="isCreate ? 'إنشاء شركة عقارية نيابةً عن مالك.' : getCompanyLocation(company)"
      >
        <template v-if="!isCreate" #actions>
          <StatusBadge :status="company.status" :labels="COMPANY_STATUS_LABELS" />
        </template>
      </AdminPageHeader>

      <div v-if="!isCreate" class="admin-company-detail__meta">
        <span>
          <i class="bi bi-person"></i>
          {{ getCompanyOwnerName(company) }}
        </span>
        <span v-if="company.employees_num">
          <i class="bi bi-people"></i>
          {{ company.employees_num }} موظف
        </span>
        <span>
          <i class="bi bi-shield-check"></i>
          درجة الثقة {{ company.trust_score ?? 0 }}
        </span>
        <span>
          <i class="bi bi-person-badge"></i>
          {{ company.agents?.length ?? 0 }} وسيط
        </span>
      </div>

      <div v-if="!isCreate" class="admin-company-detail__links">
        <AppButton
          v-if="company.user_id"
          :to="`/admin/users/${company.user_id}`"
          variant="outline"
          size="sm"
        >
          ملف المالك
        </AppButton>
        <AppButton
          v-if="company.status === 'approved'"
          :to="`/companies/${company.id}`"
          variant="outline"
          size="sm"
        >
          معاينة عامة
        </AppButton>
      </div>

      <AdminStatsSection v-if="!isCreate" title="معاينة سريعة">
        <div class="admin-company-detail__preview">
          <div
            class="admin-company-detail__banner"
            :style="company.banner_image_url ? { backgroundImage: `url(${company.banner_image_url})` } : undefined"
          >
            <div v-if="company.profile_image_url" class="admin-company-detail__logo">
              <img :src="company.profile_image_url" :alt="company.company_name" />
            </div>
          </div>
          <div class="admin-company-detail__preview-body">
            <p v-if="company.website">
              <i class="bi bi-globe2"></i>
              <a :href="company.website" target="_blank" rel="noopener noreferrer">{{ company.website }}</a>
            </p>
            <p class="admin-company-detail__description">{{ company.description }}</p>
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard label="الوسطاء" :value="company.agents?.length ?? 0" icon="bi-person-badge" />
          <AdminStatCard label="روابط التواصل" :value="company.social_links?.length ?? 0" icon="bi-share" />
          <AdminStatCard label="درجة الثقة" :value="company.trust_score ?? 0" icon="bi-shield-check" />
          <AdminStatCard label="الموظفون" :value="company.employees_num ?? 0" icon="bi-people" />
        </div>
      </AdminStatsSection>

      <AdminStatsSection :title="isCreate ? 'بيانات الشركة' : 'تعديل الشركة'">
        <AdminCompanyForm
          ref="companyFormRef"
          :company="company"
          :is-create="isCreate"
          :loading="saving"
          @submit="handleSave"
        />
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="روابط التواصل الاجتماعي">
        <AdminCompanySocialPanel :company="company" @updated="refreshCompany" />
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إجراءات سريعة">
        <TableActionGroup>
          <TableAction
            v-if="company.status !== 'approved'"
            tone="success"
            label="اعتماد"
            :disabled="saving"
            @click="updateStatus('approved')"
          />
          <TableAction
            v-if="company.status !== 'pending'"
            tone="neutral"
            label="قيد المراجعة"
            icon="bi-hourglass-split"
            :disabled="saving"
            @click="updateStatus('pending')"
          />
          <TableAction
            v-if="company.status !== 'suspended'"
            tone="warning"
            label="إيقاف"
            :disabled="saving"
            @click="updateStatus('suspended')"
          />
          <TableAction
            v-if="company.status !== 'rejected'"
            tone="reject"
            label="رفض"
            :disabled="saving"
            @click="updateStatus('rejected')"
          />
        </TableActionGroup>
      </AdminStatsSection>

      <section v-if="!isCreate" class="admin-user-detail__danger">
        <h3>منطقة الخطر</h3>
        <p>
          حذف الشركة نهائياً. لا يمكن الحذف إذا كانت مرتبطة بوسطاء — أعد تعيينهم أو احذفهم أولاً.
        </p>
        <TableAction
          tone="danger"
          :label="deleting ? 'جاري الحذف...' : 'حذف الشركة'"
          :disabled="deleting"
          @click="handleDelete"
        />
      </section>
    </template>
  </div>
</template>
