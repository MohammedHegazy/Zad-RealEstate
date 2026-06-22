<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AgentForm from '@/components/company/AgentForm.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import { companyService } from '@/api/company.js'

const router = useRouter()
const saving = ref(false)
const agentFormRef = ref(null)

async function handleSubmit(formData) {
  saving.value = true
  try {
    const payload = { user_id: formData.user_id }
    await companyService.createAgent(payload)
    router.push('/company/agents')
  } catch (err) {
    agentFormRef.value?.handleSubmitError(err)
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'الوسطاء', to: '/company/agents' },
        { label: 'إضافة وسيط' },
      ]"
    />

    <AdminPageHeader
      title="إضافة وسيط"
      description="أضف وسيطاً جديداً إلى شركتك."
    />
    <AgentForm ref="agentFormRef" @submit="handleSubmit" @cancel="router.push('/company/agents')" />
  </div>
</template>
