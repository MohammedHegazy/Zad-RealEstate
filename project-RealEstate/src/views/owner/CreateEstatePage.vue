<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import EstateForm from '@/components/company/EstateForm.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { myEstatesService } from '@/api/myEstates.js'
import { placesService } from '@/api/places.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const router = useRouter()
const places = ref([])
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const estateFormRef = ref(null)

async function fetchPlaces() {
  try {
    const { data } = await placesService.list({ per_page: 200 })
    places.value = data ?? []
  } catch (err) {
    loadError.value = getErrorMessage(err, 'تعذّر تحميل المناطق.')
  } finally {
    loading.value = false
  }
}

async function handleSubmit(formData) {
  saving.value = true
  try {
    await myEstatesService.create(formData)
    router.push('/owner/estates')
  } catch (err) {
    estateFormRef.value?.handleSubmitError(err)
  } finally {
    saving.value = false
  }
}

onMounted(fetchPlaces)
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'عقاراتي', to: '/owner/estates' },
        { label: 'إضافة عقار' },
      ]"
    />

    <AdminPageHeader
      title="إضافة عقار"
      description="سجّل عقاراً جديداً في المنصة."
    />
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="loadError" :message="loadError" />
    <template v-else>
      <EstateForm
        ref="estateFormRef"
        :places="places"
        :saving="saving"
        @submit="handleSubmit"
        @cancel="router.push('/owner/estates')"
      />
    </template>
  </div>
</template>
