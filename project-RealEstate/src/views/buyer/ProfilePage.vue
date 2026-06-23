<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { GENDER_OPTIONS, COUNTRY_CODES } from '@/config/auth.js'
import { profileService } from '@/api/profile.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAuthStore } from '@/stores/auth.js'

const auth = useAuthStore()
const loading = ref(false)
const error = ref(null)
const saving = ref(false)
const profile = ref(null)

const form = ref({
  fname: '',
  lname: '',
  username: '',
  email: '',
  phone: '',
  country_code_phone: '+963',
  gender: '',
})

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const { data } = await profileService.me()
    profile.value = data
    auth.setSession({ user: data, token: auth.token })
    form.value = {
      fname: data.fname ?? '',
      lname: data.lname ?? '',
      username: data.username ?? '',
      email: data.email ?? '',
      phone: data.phone ?? '',
      country_code_phone: data.country_code_phone ?? '+963',
      gender: data.gender ?? '',
    }
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل الملف الشخصي.')
  } finally {
    loading.value = false
  }
}

async function saveProfile() {
  saving.value = true
  error.value = null
  try {
    const { data } = await profileService.update({
      fname: form.value.fname,
      lname: form.value.lname,
      username: form.value.username,
      email: form.value.email,
      phone: form.value.phone,
      country_code_phone: form.value.country_code_phone,
      gender: form.value.gender || null,
    })
    profile.value = data
    auth.setSession({ user: data, token: auth.token })
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر حفظ التغييرات.')
  } finally {
    saving.value = false
  }
}

onMounted(fetchProfile)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="الملف الشخصي"
      description="تعديل بياناتك الشخصية."
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchProfile" />

    <form v-else-if="profile" @submit.prevent="saveProfile">
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup label="الاسم الأول">
            <AppInput v-model="form.fname" required />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="الاسم الأخير">
            <AppInput v-model="form.lname" required />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="اسم المستخدم">
            <AppInput v-model="form.username" required />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="البريد الإلكتروني">
            <AppInput v-model="form.email" type="email" required />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="مفتاح الدولة">
            <AppSelect v-model="form.country_code_phone" :options="COUNTRY_CODES" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="رقم الهاتف">
            <AppInput v-model="form.phone" type="tel" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الجنس">
            <AppSelect v-model="form.gender" :options="GENDER_OPTIONS" />
          </AppFormGroup>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <AppButton type="submit" variant="primary" :loading="saving">
          حفظ التغييرات
        </AppButton>
        <AppButton to="/buyer/dashboard" variant="outline">
          العودة للوحة
        </AppButton>
      </div>
    </form>
  </div>
</template>
