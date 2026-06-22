<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import {
  COUNTRY_CODES,
  DEVICE_NAME,
  GENDER_OPTIONS,
  USER_TYPES,
} from '@/config/auth.js'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { useAuthStore } from '@/stores/auth.js'
import { resolvePostLoginPath } from '@/utils/authRedirect.js'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const loading = ref(false)

const form = reactive({
  username: '',
  fname: '',
  lname: '',
  email: '',
  password: '',
  password_confirmation: '',
  phone: '',
  countre_code_phone: '+963',
  gender: '',
  type: 'buyer',
  facebook: '',
  instagram: '',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

async function handleSubmit() {
  clearErrors()
  loading.value = true

  try {
    const payload = {
      ...form,
      device_name: DEVICE_NAME,
      facebook: form.facebook || undefined,
      instagram: form.instagram || undefined,
    }

    const data = await auth.register(payload)

    router.push(resolvePostLoginPath(data.user ?? auth.user, route.query.redirect))
  } catch (error) {
    handleSubmitError(error)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <AuthLayout
    title="إنشاء حساب"
    subtitle="انضم إلى منصة زاد للعقارات وابدأ رحلتك العقارية."
  >
    <FormAlert v-if="generalError" :message="generalError" />

    <form class="auth-form auth-form--register" novalidate @submit.prevent="handleSubmit">
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup
            label="الاسم الأول"
            label-for="register-fname"
            :error="fieldError('fname')"
            required
          >
            <AppInput
              id="register-fname"
              v-model="form.fname"
              icon="bi-person"
              placeholder="الاسم الأول"
              autocomplete="given-name"
              :has-error="hasError('fname')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="اسم العائلة"
            label-for="register-lname"
            :error="fieldError('lname')"
            required
          >
            <AppInput
              id="register-lname"
              v-model="form.lname"
              icon="bi-person"
              placeholder="اسم العائلة"
              autocomplete="family-name"
              :has-error="hasError('lname')"
            />
          </AppFormGroup>
        </div>

        <div class="col-12">
          <AppFormGroup
            label="اسم المستخدم"
            label-for="register-username"
            :error="fieldError('username')"
            hint="يُستخدم لتسجيل الدخول وعرض ملفك الشخصي."
            required
          >
            <AppInput
              id="register-username"
              v-model="form.username"
              icon="bi-at"
              placeholder="username"
              autocomplete="username"
              :has-error="hasError('username')"
            />
          </AppFormGroup>
        </div>

        <div class="col-12">
          <AppFormGroup
            label="البريد الإلكتروني"
            label-for="register-email"
            :error="fieldError('email')"
            required
          >
            <AppInput
              id="register-email"
              v-model="form.email"
              type="email"
              icon="bi-envelope"
              placeholder="name@example.com"
              autocomplete="email"
              :has-error="hasError('email')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="كلمة المرور"
            label-for="register-password"
            :error="fieldError('password')"
            hint="8 أحرف على الأقل."
            required
          >
            <AppInput
              id="register-password"
              v-model="form.password"
              type="password"
              icon="bi-lock"
              placeholder="كلمة المرور"
              autocomplete="new-password"
              show-password-toggle
              :has-error="hasError('password')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="تأكيد كلمة المرور"
            label-for="register-password-confirm"
            :error="fieldError('password_confirmation')"
            required
          >
            <AppInput
              id="register-password-confirm"
              v-model="form.password_confirmation"
              type="password"
              icon="bi-lock-fill"
              placeholder="أعد إدخال كلمة المرور"
              autocomplete="new-password"
              show-password-toggle
              :has-error="hasError('password_confirmation')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-4">
          <AppFormGroup
            label="رمز الدولة"
            label-for="register-country-code"
            :error="fieldError('countre_code_phone')"
            required
          >
            <AppSelect
              id="register-country-code"
              v-model="form.countre_code_phone"
              :options="COUNTRY_CODES"
              :has-error="hasError('countre_code_phone')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-8">
          <AppFormGroup
            label="رقم الهاتف"
            label-for="register-phone"
            :error="fieldError('phone')"
            required
          >
            <AppInput
              id="register-phone"
              v-model="form.phone"
              type="tel"
              icon="bi-telephone"
              placeholder="9xxxxxxxx"
              autocomplete="tel"
              :has-error="hasError('phone')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="الجنس"
            label-for="register-gender"
            :error="fieldError('gender')"
            required
          >
            <AppSelect
              id="register-gender"
              v-model="form.gender"
              placeholder="اختر الجنس"
              :options="GENDER_OPTIONS"
              :has-error="hasError('gender')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="نوع الحساب"
            label-for="register-type"
            :error="fieldError('type')"
            required
          >
            <AppSelect
              id="register-type"
              v-model="form.type"
              :options="USER_TYPES"
              :has-error="hasError('type')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="فيسبوك"
            label-for="register-facebook"
            :error="fieldError('facebook')"
          >
            <AppInput
              id="register-facebook"
              v-model="form.facebook"
              icon="bi-facebook"
              placeholder="رابط فيسبوك (اختياري)"
              :has-error="hasError('facebook')"
            />
          </AppFormGroup>
        </div>

        <div class="col-md-6">
          <AppFormGroup
            label="إنستغرام"
            label-for="register-instagram"
            :error="fieldError('instagram')"
          >
            <AppInput
              id="register-instagram"
              v-model="form.instagram"
              icon="bi-instagram"
              placeholder="رابط إنستغرام (اختياري)"
              :has-error="hasError('instagram')"
            />
          </AppFormGroup>
        </div>
      </div>

      <AppButton
        type="submit"
        variant="primary"
        size="lg"
        block
        class="auth-form__submit"
        :disabled="loading"
      >
        <span v-if="loading">جاري إنشاء الحساب…</span>
        <span v-else>إنشاء حساب</span>
      </AppButton>
    </form>

    <p class="auth-form__footer">
      لديك حساب بالفعل؟
      <RouterLink to="/login">تسجيل الدخول</RouterLink>
    </p>
  </AuthLayout>
</template>
