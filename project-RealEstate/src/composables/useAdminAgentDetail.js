import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminAgentsService, buildAgentFormData } from '@/api/admin/agents.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useAdminAgentDetail() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref(null)
  const saveError = ref(null)
  const agent = ref(null)

  const isCreate = computed(() => route.name === 'admin-agent-create')
  const agentId = computed(() => route.params.id?.toString())

  async function fetchAgent(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminAgentsService.getById(id)
      agent.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات الوسيط.')
      agent.value = null
    } finally {
      loading.value = false
    }
  }

  async function saveAgent(payload, files = {}) {
    saving.value = true
    saveError.value = null

    const hasFiles = Boolean(files.profile_image)
    const body = hasFiles ? buildAgentFormData(payload, files) : payload

    try {
      if (isCreate.value) {
        const { data } = await adminAgentsService.create(body)
        router.replace(`/admin/agents/${data.id}`)
        return true
      }

      const { data } = await adminAgentsService.update(agent.value.id, body)
      agent.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حفظ التعديلات.')
      throw err
    } finally {
      saving.value = false
    }
  }

  async function removeAgent() {
    if (!agent.value) return false
    deleting.value = true
    saveError.value = null

    try {
      await adminAgentsService.remove(agent.value.id)
      router.push('/admin/agents')
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حذف الوسيط.')
      return false
    } finally {
      deleting.value = false
    }
  }

  async function refreshAgent() {
    if (!agent.value?.id) return
    await fetchAgent(agent.value.id)
  }

  watch(
    () => route.params.id,
    (id) => {
      if (isCreate.value) {
        agent.value = null
        error.value = null
        return
      }
      if (id) fetchAgent(id)
    },
    { immediate: true },
  )

  return {
    loading,
    saving,
    deleting,
    error,
    saveError,
    agent,
    isCreate,
    agentId,
    fetchAgent,
    saveAgent,
    removeAgent,
    refreshAgent,
  }
}
