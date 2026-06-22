import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { adminUsersService } from '@/api/admin/users.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { getUserName } from '@/utils/user.js'

export function useAdminUserDetail() {
  const route = useRoute()
  const router = useRouter()

  const loading = ref(false)
  const saving = ref(false)
  const deleting = ref(false)
  const error = ref(null)
  const saveError = ref(null)
  const user = ref(null)

  const userName = computed(() => getUserName(user.value))

  async function fetchUser(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await adminUsersService.getById(id)
      user.value = data
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل بيانات المستخدم.')
      user.value = null
    } finally {
      loading.value = false
    }
  }

  async function saveUser(payload) {
    if (!user.value) return false
    saving.value = true
    saveError.value = null

    try {
      const { data } = await adminUsersService.update(user.value.id, payload)
      user.value = data
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حفظ التعديلات.')
      throw err
    } finally {
      saving.value = false
    }
  }

  async function updateStatus(status) {
    return saveUser({ status })
  }

  async function removeUser() {
    if (!user.value) return false
    deleting.value = true
    saveError.value = null

    try {
      await adminUsersService.remove(user.value.id)
      router.push('/admin/users')
      return true
    } catch (err) {
      saveError.value = getErrorMessage(err, 'تعذّر حذف المستخدم.')
      return false
    } finally {
      deleting.value = false
    }
  }

  watch(
    () => route.params.id,
    (id) => {
      if (id) fetchUser(id)
    },
    { immediate: true },
  )

  return {
    loading,
    saving,
    deleting,
    error,
    saveError,
    user,
    userName,
    fetchUser,
    saveUser,
    updateStatus,
    removeUser,
  }
}
