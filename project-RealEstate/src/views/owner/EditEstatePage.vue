<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminEstateMediaPanel from '@/components/admin/AdminEstateMediaPanel.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import EstateForm from '@/components/company/EstateForm.vue'
import EstateGallery from '@/components/estates/EstateGallery.vue'
import EstateInvestment from '@/components/estates/EstateInvestment.vue'
import EstateSpecs from '@/components/estates/EstateSpecs.vue'
import StatusBadge from '@/components/admin/StatusBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import { ESTATE_STATUS_LABELS } from '@/config/admin.js'
import { myEstatesService } from '@/api/myEstates.js'
import { placesService } from '@/api/places.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { getEstateLocation } from '@/utils/estate.js'
import { useConfirmStore } from '@/stores/confirm.js'

const route = useRoute()
const router = useRouter()
const confirmStore = useConfirmStore()
const estate = ref(null)
const places = ref([])
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const deleting = ref(false)
const estateFormRef = ref(null)

async function fetchData() {
  loading.value = true
  loadError.value = ''
  try {
    const [estateRes, placesRes] = await Promise.all([
      myEstatesService.getById(route.params.id),
      placesService.list({ per_page: 200 }),
    ])
    estate.value = estateRes.data
    places.value = placesRes.data ?? []
  } catch (err) {
    loadError.value = getErrorMessage(err, 'تعذّر تحميل بيانات العقار.')
  } finally {
    loading.value = false
  }
}

async function refreshEstate() {
  try {
    const res = await myEstatesService.getById(route.params.id)
    estate.value = res.data
  } catch {
    // silent
  }
}

async function handleSubmit(formData) {
  saving.value = true
  try {
    await myEstatesService.update(route.params.id, formData)
    router.push('/owner/estates')
  } catch (err) {
    estateFormRef.value?.handleSubmitError(err)
  } finally {
    saving.value = false
  }
}

async function handleDelete() {
  if (!estate.value) return
  if (!(await confirmStore.show({ message: `حذف العقار «${estate.value.name}» نهائياً؟` }))) return
  deleting.value = true
  try {
    await myEstatesService.remove(route.params.id)
    router.push('/owner/estates')
  } catch {
    // toast handles error
  } finally {
    deleting.value = false
  }
}

onMounted(fetchData)
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'عقاراتي', to: '/owner/estates' },
        { label: estate?.name ?? '...' },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="loadError" :message="loadError" />

    <template v-else-if="estate">
      <AdminPageHeader
        :title="estate.name"
        :description="getEstateLocation(estate)"
      >
        <template #actions>
          <StatusBadge :status="estate.status" :labels="ESTATE_STATUS_LABELS" />
        </template>
      </AdminPageHeader>

      <div class="admin-estate-detail__meta">
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

      <div class="admin-estate-detail__links">
        <AppButton
          v-if="estate.status === 'active'"
          :to="`/estates/${estate.id}`"
          variant="outline"
          size="sm"
        >
          معاينة عامة
        </AppButton>
      </div>

      <AdminStatsSection title="معاينة سريعة">
        <div class="admin-estate-detail__preview">
          <EstateGallery :estate="estate" />
          <div class="admin-estate-detail__preview-side">
            <EstateSpecs :estate="estate" />
            <EstateInvestment :estate="estate" />
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="إحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard label="المشاهدات" :value="estate.views ?? 0" icon="bi-eye" />
          <AdminStatCard label="المشاركات" :value="estate.shares ?? 0" icon="bi-share" />
          <AdminStatCard label="الصور" :value="estate.images?.length ?? 0" icon="bi-images" />
          <AdminStatCard label="الفيديوهات" :value="estate.videos?.length ?? 0" icon="bi-camera-video" />
          <AdminStatCard label="الإعلانات" :value="estate.ads?.length ?? 0" icon="bi-badge-ad" />
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="بيانات العقار">
        <EstateForm
          ref="estateFormRef"
          :initial-data="estate"
          :places="places"
          :saving="saving"
          @submit="handleSubmit"
          @cancel="router.push('/owner/estates')"
        />
      </AdminStatsSection>

      <AdminStatsSection title="إدارة الوسائط">
        <AdminEstateMediaPanel
          :estate="estate"
          :service="myEstatesService"
          @updated="refreshEstate"
        />
      </AdminStatsSection>

      <section class="admin-user-detail__danger">
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
