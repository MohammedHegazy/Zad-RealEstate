<script setup>
import { reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { DEVICE_NAME } from '@/config/auth.js'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { useAuthStore } from '@/stores/auth.js'
import { resolvePostLoginPath } from '@/utils/authRedirect.js'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const loading = ref(false)

const form = reactive({
  email: '',
  password: '',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

async function handleSubmit() {
  clearErrors()
  loading.value = true

  try {
    const data = await auth.login({
      email: form.email,
      password: form.password,
      device_name: DEVICE_NAME,
    })

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
    title="تسجيل الدخول"
    subtitle="مرحباً بعودتك! سجّل دخولك للوصول إلى حسابك."
  >
    <FormAlert v-if="generalError" :message="generalError" />

    <form class="auth-form" novalidate @submit.prevent="handleSubmit">
      <AppFormGroup
        label="البريد الإلكتروني"
        label-for="login-email"
        :error="fieldError('email')"
        required
      >
        <AppInput
          id="login-email"
          v-model="form.email"
          type="email"
          icon="bi-envelope"
          placeholder="name@example.com"
          autocomplete="email"
          :has-error="hasError('email')"
        />
      </AppFormGroup>

      <AppFormGroup
        label="كلمة المرور"
        label-for="login-password"
        :error="fieldError('password')"
        required
      >
        <AppInput
          id="login-password"
          v-model="form.password"
          type="password"
          icon="bi-lock"
          placeholder="أدخل كلمة المرور"
          autocomplete="current-password"
          show-password-toggle
          :has-error="hasError('password')"
        />
      </AppFormGroup>

      <AppButton type="submit" variant="primary" size="lg" block :disabled="loading">
        <span v-if="loading">جاري تسجيل الدخول…</span>
        <span v-else>تسجيل الدخول</span>
      </AppButton>
    </form>

    <p class="auth-form__footer">
      ليس لديك حساب؟
      <RouterLink to="/register">إنشاء حساب جديد</RouterLink>
    </p>
  </AuthLayout>
</template>
