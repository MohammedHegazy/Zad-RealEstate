<script setup>
import { reactive, watch } from 'vue'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import {
  ADMIN_GENDER_OPTIONS,
  USER_STATUS_OPTIONS,
  USER_TYPE_OPTIONS,
} from '@/config/admin.js'
import { COUNTRY_CODES } from '@/config/auth.js'
import { useFormErrors } from '@/composables/useFormErrors.js'

const props = defineProps({
  user: {
    type: Object,
    required: true,
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

const form = reactive({
  fname: '',
  lname: '',
  username: '',
  email: '',
  phone: '',
  country_code_phone: '+963',
  gender: 'male',
  type: 'buyer',
  status: 'active',
  is_verified: false,
  password: '',
  password_confirmation: '',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

watch(
  () => props.user,
  (user) => {
    if (!user) return
    form.fname = user.fname ?? ''
    form.lname = user.lname ?? ''
    form.username = user.username ?? ''
    form.email = user.email ?? ''
    form.phone = user.phone ?? ''
    form.country_code_phone = user.country_code_phone ?? '+963'
    form.gender = user.gender ?? 'male'
    form.type = user.type ?? 'buyer'
    form.status = user.status ?? 'active'
    form.is_verified = Boolean(user.is_verified)
    form.password = ''
    form.password_confirmation = ''
  },
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
    fname: form.fname,
    lname: form.lname,
    username: form.username,
    email: form.email,
    phone: form.phone,
    country_code_phone: form.country_code_phone,
    gender: form.gender,
    type: form.type,
    status: form.status,
    is_verified: form.is_verified,
  }

  if (form.password) {
    payload.password = form.password
    payload.password_confirmation = form.password_confirmation
  }

  emit('submit', payload)
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form class="admin-user-form" novalidate @submit.prevent="handleSubmit">
    <FormAlert v-if="generalError || errorMessage" :message="generalError || errorMessage" />

    <div class="row g-3">
      <div class="col-md-6">
        <AppFormGroup label="الاسم الأول" label-for="user-fname" :error="fieldError('fname')" required>
          <AppInput id="user-fname" v-model="form.fname" :has-error="hasError('fname')" />
        </AppFormGroup>
      </div>
      <div class="col-md-6">
        <AppFormGroup label="اسم العائلة" label-for="user-lname" :error="fieldError('lname')" required>
          <AppInput id="user-lname" v-model="form.lname" :has-error="hasError('lname')" />
        </AppFormGroup>
      </div>
      <div class="col-md-6">
        <AppFormGroup label="اسم المستخدم" label-for="user-username" :error="fieldError('username')" required>
          <AppInput id="user-username" v-model="form.username" :has-error="hasError('username')" />
        </AppFormGroup>
      </div>
      <div class="col-md-6">
        <AppFormGroup label="البريد الإلكتروني" label-for="user-email" :error="fieldError('email')" required>
          <AppInput id="user-email" v-model="form.email" type="email" :has-error="hasError('email')" />
        </AppFormGroup>
      </div>
      <div class="col-md-4">
        <AppFormGroup label="رمز الدولة" label-for="user-code">
          <AppSelect id="user-code" v-model="form.country_code_phone" :options="COUNTRY_CODES" />
        </AppFormGroup>
      </div>
      <div class="col-md-8">
        <AppFormGroup label="الهاتف" label-for="user-phone" :error="fieldError('phone')">
          <AppInput id="user-phone" v-model="form.phone" :has-error="hasError('phone')" />
        </AppFormGroup>
      </div>
      <div class="col-md-4">
        <AppFormGroup label="الجنس" label-for="user-gender">
          <AppSelect id="user-gender" v-model="form.gender" :options="ADMIN_GENDER_OPTIONS" />
        </AppFormGroup>
      </div>
      <div class="col-md-4">
        <AppFormGroup label="نوع الحساب" label-for="user-type" :error="fieldError('type')">
          <AppSelect
            id="user-type"
            v-model="form.type"
            :options="USER_TYPE_OPTIONS"
            :has-error="hasError('type')"
          />
        </AppFormGroup>
      </div>
      <div class="col-md-4">
        <AppFormGroup label="الحالة" label-for="user-status" :error="fieldError('status')">
          <AppSelect
            id="user-status"
            v-model="form.status"
            :options="USER_STATUS_OPTIONS"
            :has-error="hasError('status')"
          />
        </AppFormGroup>
      </div>
      <div class="col-12">
        <AppCheckbox id="user-verified" v-model="form.is_verified" label="حساب موثّق" />
      </div>
      <div class="col-md-6">
        <AppFormGroup
          label="كلمة مرور جديدة (اختياري)"
          label-for="user-password"
          :error="fieldError('password')"
        >
          <AppInput
            id="user-password"
            v-model="form.password"
            type="password"
            autocomplete="new-password"
            show-password-toggle
            :has-error="hasError('password')"
          />
        </AppFormGroup>
      </div>
      <div class="col-md-6">
        <AppFormGroup
          label="تأكيد كلمة المرور"
          label-for="user-password-confirm"
          :error="fieldError('password_confirmation')"
        >
          <AppInput
            id="user-password-confirm"
            v-model="form.password_confirmation"
            type="password"
            autocomplete="new-password"
            show-password-toggle
            :has-error="hasError('password_confirmation')"
          />
        </AppFormGroup>
      </div>
    </div>

    <div class="admin-user-form__actions">
      <AppButton type="submit" variant="primary" :disabled="loading">
        {{ loading ? 'جاري الحفظ...' : 'حفظ التعديلات' }}
      </AppButton>
    </div>
  </form>
</template>
