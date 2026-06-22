import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminCompaniesService, buildCompanyFormData } from '@/api/admin/companies.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useAdminCompanyDetail() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref(null)
  const saveError = ref(null)
  const company = ref(null)

  const isCreate = computed(() => route.name === 'admin-company-create')
  const companyId = computed(() => route.params.id?.toString())

  async function fetchCompany(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminCompaniesService.getById(id)
      company.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات الشركة.')
      company.value = null
    } finally {
      loading.value = false
    }
  }

  async function saveCompany(payload, files = {}) {
    saving.value = true
    saveError.value = null

    const hasFiles = Boolean(files.profile_image || files.banner_image)
    const body = hasFiles ? buildCompanyFormData(payload, files) : payload

    try {
      if (isCreate.value) {
        const { data } = await adminCompaniesService.create(body)
        router.replace(`/admin/companies/${data.id}`)
        return true
      }

      const { data } = await adminCompaniesService.update(company.value.id, body)
      company.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حفظ التعديلات.')
      throw err
    } finally {
      saving.value = false
    }
  }

  async function updateStatus(status) {
    if (!company.value) return false
    saving.value = true
    saveError.value = null

    try {
      const { data } = await adminCompaniesService.updateStatus(company.value.id, status)
      company.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر تحديث الحالة.')
      return false
    } finally {
      saving.value = false
    }
  }

  async function removeCompany() {
    if (!company.value) return false
    deleting.value = true
    saveError.value = null

    try {
      await adminCompaniesService.remove(company.value.id)
      router.push('/admin/companies')
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حذف الشركة.')
      return false
    } finally {
      deleting.value = false
    }
  }

  async function refreshCompany() {
    if (!company.value?.id) return
    await fetchCompany(company.value.id)
  }

  watch(
    () => route.params.id,
    (id) => {
      if (isCreate.value) {
        company.value = null
        error.value = null
        return
      }
      if (id) fetchCompany(id)
    },
    { immediate: true },
  )

  return {
    loading,
    saving,
    deleting,
    error,
    saveError,
    company,
    isCreate,
    companyId,
    fetchCompany,
    saveCompany,
    updateStatus,
    removeCompany,
    refreshCompany,
  }
}
