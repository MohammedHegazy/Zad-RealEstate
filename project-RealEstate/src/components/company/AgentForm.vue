<script setup>
import { ref } from 'vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFileUpload from '@/components/ui/AppFileUpload.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { usersService } from '@/api/users.js'

const props = defineProps({
  initialData: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const form = ref({
  user_id: props.initialData?.user_id ?? null,
  profile_image: null,
})

const searchQuery = ref('')
const searchResults = ref([])
const selectedUser = ref(props.initialData?.user ?? null)
const searching = ref(false)
const searchError = ref('')

const { clearErrors, fieldError, handleSubmitError } = useFormErrors()

async function searchUser() {
  const q = searchQuery.value.trim()
  if (!q) return
  searching.value = true
  searchError.value = ''
  searchResults.value = []
  try {
    const isEmail = q.includes('@')
    const params = isEmail ? { email: q } : { phone: q }
    const { data } = await usersService.search(params)
    searchResults.value = data ?? []
    if (!searchResults.value.length) {
      searchError.value = 'لم يُعثر على مستخدم. تأكد من البريد الإلكتروني أو رقم الهاتف.'
    }
  } catch (err) {
    searchError.value = getErrorMessage(err, 'تعذّر البحث عن المستخدم.')
  } finally {
    searching.value = false
  }
}

function selectUser(user) {
  selectedUser.value = user
  form.value.user_id = user.id
  searchResults.value = []
  searchQuery.value = `${user.fname ?? ''} ${user.lname ?? ''}`.trim() || user.email
}

function clearSelection() {
  selectedUser.value = null
  form.value.user_id = null
  searchQuery.value = ''
  searchResults.value = []
}

function handleSubmit() {
  clearErrors()
  if (!form.value.user_id) return
  emit('submit', { ...form.value })
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form @submit.prevent="handleSubmit" class="admin-company-form__section">
    <h3 class="admin-company-form__section-title">{{ initialData ? 'تعديل الوسيط' : 'إضافة وسيط' }}</h3>

    <div v-if="!selectedUser">
      <AppFormGroup label="البحث عن مستخدم" :error="searchError || fieldError('user_id')">
        <div class="admin-agent-form__search-row">
          <AppInput
            v-model="searchQuery"
            placeholder="البريد الإلكتروني أو رقم الهاتف..."
            icon="bi-search"
            @keyup.enter="searchUser"
          />
          <AppButton variant="primary" size="sm" :loading="searching" @click="searchUser">
            بحث
          </AppButton>
        </div>
      </AppFormGroup>

      <div v-if="searchResults.length" class="admin-agent-search__results">
        <div
          v-for="user in searchResults"
          :key="user.id"
          class="admin-agent-search__result"
          @click="selectUser(user)"
        >
          <div>
            <strong>{{ user.fname }} {{ user.lname }}</strong>
            <br />
            <small class="text-muted">{{ user.email }} — {{ user.phone }}</small>
          </div>
          <i class="bi bi-plus-circle"></i>
        </div>
      </div>
    </div>

    <div v-else class="admin-agent-search__selected">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-person-check fs-4 text-success"></i>
        <div>
          <strong>{{ selectedUser.fname }} {{ selectedUser.lname }}</strong>
          <br />
          <small class="text-muted">{{ selectedUser.email }}</small>
        </div>
        <AppButton variant="outline" size="sm" class="ms-auto" @click="clearSelection">
          <i class="bi bi-x"></i>
          تغيير
        </AppButton>
      </div>
    </div>

    <AppFormGroup label="صورة الوسيط (اختياري)">
      <AppFileUpload
        v-model="form.profile_image"
        accept="image/*"
        hint="صورة شخصية للوسيط. تقبل صيغ JPG, PNG. حد أقصى 5MB"
      />
    </AppFormGroup>

    <div v-if="initialData?.profile_image_url" class="mb-3">
      <label class="app-form-group__label">الصورة الحالية</label>
      <div>
        <img
          :src="initialData.profile_image_url"
          alt="Agent"
          class="admin-agent-form__preview"
        />
      </div>
    </div>

    <div class="admin-company-form__actions">
      <AppButton type="submit" variant="primary" :disabled="!form.user_id">
        {{ initialData ? 'حفظ التغييرات' : 'إضافة وسيط' }}
      </AppButton>
      <AppButton variant="outline" @click="emit('cancel')">
        إلغاء
      </AppButton>
    </div>
  </form>
</template>

<style scoped>
.admin-agent-form__search-row {
  display: flex;
  gap: 0.5rem;
}
.admin-agent-form__search-row > :first-child {
  flex: 1;
}
.admin-agent-search__results {
  margin-top: 0.5rem;
}
.admin-agent-search__result {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.625rem 0.75rem;
  border: 1px solid var(--color-border);
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background-color 0.15s, border-color 0.15s;
  margin-bottom: 0.25rem;
}
.admin-agent-search__result:hover {
  background-color: var(--color-surface-hover);
  border-color: var(--color-primary);
}
.admin-agent-search__result i {
  font-size: 1.25rem;
  color: var(--color-primary);
}
.admin-agent-search__selected {
  padding: 0.75rem;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 0.5rem;
  margin-bottom: 1rem;
}
</style>
