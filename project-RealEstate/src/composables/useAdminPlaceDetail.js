import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminPlacesService } from '@/api/admin/locations.js'
import { getErrorMessage } from '@/api/errorHandler.js'

export function useAdminPlaceDetail() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref(null)
  const saveError = ref(null)
  const place = ref(null)

  const isCreate = computed(() => route.name === 'admin-place-create')
  const placeId = computed(() => route.params.id?.toString())
  const presetCityId = computed(() => route.query.cities_id?.toString() ?? '')

  async function fetchPlace(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminPlacesService.getById(id)
      place.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات المنطقة.')
      place.value = null
    } finally {
      loading.value = false
    }
  }

  async function savePlace(payload) {
    saving.value = true
    saveError.value = null

    try {
      if (isCreate.value) {
        const { data } = await adminPlacesService.create(payload)
        router.replace(`/admin/places/${data.id}`)
        return true
      }

      const { data } = await adminPlacesService.update(place.value.id, payload)
      place.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حفظ التعديلات.')
      throw err
    } finally {
      saving.value = false
    }
  }

  async function removePlace() {
    if (!place.value) return false
    deleting.value = true
    saveError.value = null

    try {
      await adminPlacesService.remove(place.value.id)
      router.push('/admin/places')
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حذف المنطقة.')
      return false
    } finally {
      deleting.value = false
    }
  }

  watch(
    () => route.params.id,
    (id) => {
      if (isCreate.value) {
        place.value = null
        error.value = null
        return
      }
      if (id) fetchPlace(id)
    },
    { immediate: true },
  )

  return {
    loading,
    saving,
    deleting,
    error,
    saveError,
    place,
    isCreate,
    placeId,
    presetCityId,
    fetchPlace,
    savePlace,
    removePlace,
  }
}
