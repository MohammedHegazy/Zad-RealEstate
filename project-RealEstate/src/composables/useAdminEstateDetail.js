import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import {
  adminEstatesService,
  buildEstateFormData,
  estatePayloadHasFiles,
} from '@/api/admin/estates.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useAdminEstateDetail() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref(null)
  const saveError = ref(null)
  const estate = ref(null)

  const isCreate = computed(() => route.name === 'admin-estate-create')
  const estateId = computed(() => route.params.id?.toString())

  async function fetchEstate(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminEstatesService.getById(id)
      estate.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات العقار.')
      estate.value = null
    } finally {
      loading.value = false
    }
  }

  async function saveEstate(payload, files = {}) {
    saving.value = true
    saveError.value = null

    const hasFiles = estatePayloadHasFiles(files)
    const body = hasFiles ? buildEstateFormData(payload, files) : payload

    try {
      if (isCreate.value) {
        const { data } = await adminEstatesService.create(body)
        router.replace(`/admin/estates/${data.id}`)
        return true
      }

      const { data } = await adminEstatesService.update(estate.value.id, body)
      estate.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حفظ التعديلات.')
      throw err
    } finally {
      saving.value = false
    }
  }

  async function updateStatus(status) {
    if (!estate.value) return false
    saving.value = true
    saveError.value = null

    try {
      const { data } = await adminEstatesService.updateStatus(estate.value.id, status)
      estate.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر تحديث الحالة.')
      return false
    } finally {
      saving.value = false
    }
  }

  async function removeEstate() {
    if (!estate.value) return false
    deleting.value = true
    saveError.value = null

    try {
      await adminEstatesService.remove(estate.value.id)
      router.push('/admin/estates')
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حذف العقار.')
      return false
    } finally {
      deleting.value = false
    }
  }

  async function refreshEstate() {
    if (!estate.value?.id) return
    await fetchEstate(estate.value.id)
  }

  watch(
    () => route.params.id,
    (id) => {
      if (isCreate.value) {
        estate.value = null
        error.value = null
        return
      }
      if (id) fetchEstate(id)
    },
    { immediate: true },
  )

  return {
    loading,
    saving,
    deleting,
    error,
    saveError,
    estate,
    isCreate,
    estateId,
    fetchEstate,
    saveEstate,
    updateStatus,
    removeEstate,
    refreshEstate,
  }
}
