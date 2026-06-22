<script setup>
import { reactive, ref, watch } from 'vue'

import AppAutocomplete from '@/components/ui/AppAutocomplete.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import { adminCompaniesService } from '@/api/admin/companies.js'
import { adminUsersService } from '@/api/admin/users.js'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { AGENT_PLACEHOLDER_IMAGE } from '@/config/agents.js'
import { getUserName } from '@/utils/user.js'

const props = defineProps({
  agent: {
    type: Object,
    default: null,
  },
  isCreate: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['submit'])

const profilePreview = ref('')
const profileFile = ref(null)

const form = reactive({
  user_id: '',
  companies_id: '',
  trust_score: '0',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

function resetFormFromAgent(agent) {
  profileFile.value = null
  profilePreview.value = agent?.profile_image_url ?? AGENT_PLACEHOLDER_IMAGE

  if (!agent) {
    Object.assign(form, {
      user_id: '',
      companies_id: '',
      trust_score: '0',
    })
    return
  }

  Object.assign(form, {
    user_id: agent.user_id ?? '',
    companies_id: agent.companies_id ? String(agent.companies_id) : '',
    trust_score: String(agent.trust_score ?? 0),
  })
}

async function searchOwners(query) {
  const { data } = await adminUsersService.list({ search: query, per_page: 10 })
  return data ?? []
}

async function resolveOwner(id) {
  if (!id) return null
  const { data } = await adminUsersService.getById(id)
  return data ?? null
}

function getOwnerDescription(user) {
  const parts = []
  if (user?.username) parts.push(`@${user.username}`)
  if (user?.email) parts.push(user.email)
  return parts.join(' · ')
}

async function searchCompanies(query) {
  const { data } = await adminCompaniesService.list({ search: query, per_page: 10 })
  return data ?? []
}

async function resolveCompany(id) {
  if (!id) return null
  const { data } = await adminCompaniesService.getById(id)
  return data ?? null
}

function getCompanyLabel(company) {
  return company?.company_name ?? ''
}

function getCompanyDescription(company) {
  return company?.place?.name ?? company?.user?.username ? `@${company.user.username}` : ''
}

function onProfileChange(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  profileFile.value = file
  profilePreview.value = URL.createObjectURL(file)
}

watch(
  () => props.agent,
  (agent) => resetFormFromAgent(agent),
  { immediate: true },
)

watch(
  () => props.errorMessage,
  (message) => {
    if (message) generalError.value = message
  },
)

function handleSubmit() {
  clearErrors()

  const payload = {
    companies_id: Number(form.companies_id),
  }

  if (props.isCreate) {
    payload.user_id = Number(form.user_id)
  } else {
    payload.trust_score = Number(form.trust_score)
  }

  emit('submit', payload, {
    profile_image: profileFile.value,
  })
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form class="admin-agent-form" novalidate @submit.prevent="handleSubmit">
    <FormAlert v-if="generalError || errorMessage" :message="generalError || errorMessage" />

    <div class="admin-agent-form__section">
      <h4 class="admin-agent-form__section-title">بيانات الوسيط</h4>
      <div class="row g-3">
        <div v-if="isCreate" class="col-md-6">
          <AppFormGroup
            label="المستخدم"
            label-for="agent-user"
            :error="fieldError('user_id')"
            hint="ابحث بالاسم أو اسم المستخدم أو البريد. يجب ألا يكون لديه ملف وسيط مسبقاً."
            required
          >
            <AppAutocomplete
              id="agent-user"
              v-model="form.user_id"
              :search-fn="searchOwners"
              :resolve-fn="resolveOwner"
              :get-label="getUserName"
              :get-value="(user) => user.id"
              :get-description="getOwnerDescription"
              placeholder="ابحث عن المستخدم..."
              :has-error="hasError('user_id')"
            />
          </AppFormGroup>
        </div>

        <div :class="isCreate ? 'col-md-6' : 'col-md-8'">
          <AppFormGroup
            label="الشركة"
            label-for="agent-company"
            :error="fieldError('companies_id')"
            hint="ابحث باسم الشركة."
            required
          >
            <AppAutocomplete
              id="agent-company"
              v-model="form.companies_id"
              :search-fn="searchCompanies"
              :resolve-fn="resolveCompany"
              :get-label="getCompanyLabel"
              :get-value="(company) => company.id"
              :get-description="getCompanyDescription"
              placeholder="ابحث عن الشركة..."
              :has-error="hasError('companies_id')"
            />
          </AppFormGroup>
        </div>

        <div v-if="!isCreate" class="col-md-4">
          <AppFormGroup
            label="درجة الثقة"
            label-for="agent-trust"
            :error="fieldError('trust_score')"
            hint="من 0 إلى 100"
          >
            <AppInput
              id="agent-trust"
              v-model="form.trust_score"
              type="number"
              min="0"
              max="100"
              :has-error="hasError('trust_score')"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-agent-form__section">
      <h4 class="admin-agent-form__section-title">الصورة الشخصية</h4>
      <AppFormGroup label="صورة الوسيط" label-for="agent-profile-image" :error="fieldError('profile_image')">
        <div class="admin-agent-form__image-field">
          <div class="admin-agent-form__image-preview">
            <img :src="profilePreview" alt="صورة الوسيط" />
          </div>
          <label class="admin-agent-form__file-label" for="agent-profile-image">
            <i class="bi bi-image"></i>
            اختيار صورة
          </label>
          <input
            id="agent-profile-image"
            type="file"
            accept="image/*"
            class="admin-agent-form__file-input"
            @change="onProfileChange"
          />
        </div>
      </AppFormGroup>
    </div>

    <div class="admin-agent-form__actions">
      <AppButton type="submit" variant="primary" :disabled="loading">
        {{ loading ? 'جاري الحفظ...' : isCreate ? 'إنشاء الوسيط' : 'حفظ التعديلات' }}
      </AppButton>
    </div>
  </form>
</template>
