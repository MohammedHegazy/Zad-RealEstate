import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminCitiesService, buildCityFormData } from '@/api/admin/locations.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useAdminCityDetail() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref(null)
  const saveError = ref(null)
  const city = ref(null)

  const isCreate = computed(() => route.name === 'admin-city-create')
  const cityId = computed(() => route.params.id?.toString())

  async function fetchCity(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminCitiesService.getById(id)
      city.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات المدينة.')
      city.value = null
    } finally {
      loading.value = false
    }
  }

  async function saveCity(payload, files = {}) {
    saving.value = true
    saveError.value = null

    const hasFiles = Boolean(files.image)
    const body = hasFiles ? buildCityFormData(payload, files) : payload

    try {
      if (isCreate.value) {
        const { data } = await adminCitiesService.create(body)
        router.replace(`/admin/cities/${data.id}`)
        return true
      }

      const { data } = await adminCitiesService.update(city.value.id, body)
      city.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حفظ التعديلات.')
      throw err
    } finally {
      saving.value = false
    }
  }

  async function removeCity() {
    if (!city.value) return false
    deleting.value = true
    saveError.value = null

    try {
      await adminCitiesService.remove(city.value.id)
      router.push('/admin/cities')
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حذف المدينة.')
      return false
    } finally {
      deleting.value = false
    }
  }

  watch(
    () => route.params.id,
    (id) => {
      if (isCreate.value) {
        city.value = null
        error.value = null
        return
      }
      if (id) fetchCity(id)
    },
    { immediate: true },
  )

  return {
    loading,
    saving,
    deleting,
    error,
    saveError,
    city,
    isCreate,
    cityId,
    fetchCity,
    saveCity,
    removeCity,
  }
}
