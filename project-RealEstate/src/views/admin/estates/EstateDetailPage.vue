<script setup>
import AdminEstateForm from '@/components/admin/AdminEstateForm.vue'
import AdminEstateMediaPanel from '@/components/admin/AdminEstateMediaPanel.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import EstateGallery from '@/components/estates/EstateGallery.vue'
import EstateInvestment from '@/components/estates/EstateInvestment.vue'
import EstateSpecs from '@/components/estates/EstateSpecs.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import TableActionGroup from '@/components/ui/TableActionGroup.vue'
import { ESTATE_STATUS_LABELS } from '@/config/admin.js'
import { useAdminEstateDetail } from '@/composables/useAdminEstateDetail.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getEstateLocation, getEstateOwnerName } from '@/utils/estate.js'
import { ref } from 'vue'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  saving,
  deleting,
  error,
  estate,
  isCreate,
  fetchEstate,
  saveEstate,
  updateStatus,
  removeEstate,
  refreshEstate,
} = useAdminEstateDetail()
const confirmStore = useConfirmStore()
const estateFormRef = ref(null)

async function handleSave(payload, files = {}) {
  try {
    await saveEstate(payload, files)
  } catch (err) {
    estateFormRef.value?.handleSubmitError(err)
  }
}

async function handleDelete() {
  if (!estate.value) return
  if (!(await confirmStore.show({ message: `حذف العقار «${estate.value.name}» نهائياً؟` }))) return
  await removeEstate()
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'العقارات', to: '/admin/estates' },
        { label: isCreate ? 'عقار جديد' : estate?.name ?? '...' },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert
      v-else-if="error"
      :message="error"
      @retry="fetchEstate($route.params.id)"
    />

    <template v-else-if="isCreate || estate">
      <AdminPageHeader
        :title="isCreate ? 'إضافة عقار جديد' : estate.name"
        :description="isCreate ? 'إنشاء عقار نيابةً عن مالك.' : getEstateLocation(estate)"
      >
        <template v-if="!isCreate" #actions>
          <StatusBadge :status="estate.status" :labels="ESTATE_STATUS_LABELS" />
        </template>
      </AdminPageHeader>

      <div v-if="!isCreate" class="admin-estate-detail__meta">
        <span>
          <i class="bi bi-person"></i>
          {{ getEstateOwnerName(estate) }}
        </span>
        <span v-if="estate.price">
          <i class="bi bi-cash-stack"></i>
          {{ formatPrice(estate.price) }}
        </span>
        <span>
          <i class="bi bi-eye"></i>
          {{ estate.views ?? 0 }} مشاهدة
        </span>
        <span>
          <i class="bi bi-share"></i>
          {{ estate.shares ?? 0 }} مشاركة
        </span>
      </div>

      <div v-if="!isCreate" class="admin-estate-detail__links">
        <AppButton
          v-if="estate.user_id"
          :to="`/admin/users/${estate.user_id}`"
          variant="outline"
          size="sm"
        >
          ملف المالك
        </AppButton>
        <AppButton
          v-if="estate.status === 'active'"
          :to="`/estates/${estate.id}`"
          variant="outline"
          size="sm"
        >
          معاينة عامة
        </AppButton>
      </div>

      <AdminStatsSection v-if="!isCreate" title="معاينة سريعة">
        <div class="admin-estate-detail__preview">
          <EstateGallery :estate="estate" />
          <div class="admin-estate-detail__preview-side">
            <EstateSpecs :estate="estate" />
            <EstateInvestment :estate="estate" />
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard label="المشاهدات" :value="estate.views ?? 0" icon="bi-eye" />
          <AdminStatCard label="المشاركات" :value="estate.shares ?? 0" icon="bi-share" />
          <AdminStatCard label="الصور" :value="estate.images?.length ?? 0" icon="bi-images" />
          <AdminStatCard label="الفيديوهات" :value="estate.videos?.length ?? 0" icon="bi-camera-video" />
          <AdminStatCard label="الإعلانات" :value="estate.ads?.length ?? 0" icon="bi-badge-ad" />
        </div>
      </AdminStatsSection>

      <AdminStatsSection :title="isCreate ? 'بيانات العقار' : 'تعديل العقار'">
        <AdminEstateForm
          ref="estateFormRef"
          :estate="estate"
          :is-create="isCreate"
          :loading="saving"
          @submit="handleSave"
        />
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إدارة الوسائط">
        <AdminEstateMediaPanel :estate="estate" @updated="refreshEstate" />
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إجراءات سريعة">
        <TableActionGroup>
          <TableAction
            v-if="estate.status !== 'active'"
            tone="success"
            label="تفعيل"
            :disabled="saving"
            @click="updateStatus('active')"
          />
          <TableAction
            v-if="estate.status !== 'pending'"
            tone="neutral"
            label="قيد المراجعة"
            icon="bi-hourglass-split"
            :disabled="saving"
            @click="updateStatus('pending')"
          />
          <TableAction
            v-if="estate.status !== 'rejected'"
            tone="reject"
            label="رفض"
            :disabled="saving"
            @click="updateStatus('rejected')"
          />
        </TableActionGroup>
      </AdminStatsSection>

      <section v-if="!isCreate" class="admin-user-detail__danger">
        <h3>منطقة الخطر</h3>
        <p>حذف العقار نهائياً مع كل الوسائط المرتبطة. لا يمكن التراجع.</p>
        <TableAction
          tone="danger"
          :label="deleting ? 'جاري الحذف...' : 'حذف العقار'"
          :disabled="deleting"
          @click="handleDelete"
        />
      </section>
    </template>
  </div>
</template>
