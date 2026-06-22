<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AgentForm from '@/components/company/AgentForm.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { companyService } from '@/api/company.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const route = useRoute()
const router = useRouter()
const agent = ref(null)
const loading = ref(true)
const loadError = ref('')
const saving = ref(false)
const agentFormRef = ref(null)

async function fetchAgent() {
  loading.value = true
  loadError.value = ''
  try {
    const { data } = await companyService.agents({ per_page: 100 })
    const found = (data ?? []).find((a) => a.id === Number(route.params.id))
    if (!found) {
      loadError.value = 'الوسيط غير موجود.'
      return
    }
    agent.value = found
  } catch (err) {
    loadError.value = getErrorMessage(err, 'تعذّر تحميل بيانات الوسيط.')
  } finally {
    loading.value = false
  }
}

async function handleSubmit(formData) {
  saving.value = true
  try {
    const payload = new FormData()
    payload.append('_method', 'PUT')
    if (formData.profile_image) {
      payload.append('profile_image', formData.profile_image)
    }
    await companyService.updateAgent(route.params.id, payload)
    router.push('/company/agents')
  } catch (err) {
    agentFormRef.value?.handleSubmitError(err)
  } finally {
    saving.value = false
  }
}

onMounted(fetchAgent)
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'الوسطاء', to: '/company/agents' },
        { label: agent ? 'تعديل وسيط' : '...' },
      ]"
    />

    <AdminPageHeader
      title="تعديل وسيط"
      description="تحديث بيانات الوسيط في شركتك."
    />
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="loadError" :message="loadError" />
    <template v-else-if="agent">
      <AgentForm
        ref="agentFormRef"
        :initial-data="agent"
        @submit="handleSubmit"
        @cancel="router.push('/company/agents')"
      />
    </template>
  </div>
</template>
