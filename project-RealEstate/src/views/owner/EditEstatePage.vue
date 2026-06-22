<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import EstateForm from '@/components/company/EstateForm.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { myEstatesService } from '@/api/myEstates.js'
import { placesService } from '@/api/places.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const route = useRoute()
const router = useRouter()
const estate = ref(null)
const places = ref([])
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
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

onMounted(fetchData)
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'عقاراتي', to: '/owner/estates' },
        { label: estate ? 'تعديل عقار' : '...' },
      ]"
    />

    <AdminPageHeader
      title="تعديل عقار"
      description="تحديث بيانات العقار."
    />
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="loadError" :message="loadError" />
    <template v-else-if="estate">
      <EstateForm
        ref="estateFormRef"
        :initial-data="estate"
        :places="places"
        :saving="saving"
        @submit="handleSubmit"
        @cancel="router.push('/owner/estates')"
      />
    </template>
  </div>
</template>
