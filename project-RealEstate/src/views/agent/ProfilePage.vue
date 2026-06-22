<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFileUpload from '@/components/ui/AppFileUpload.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { agentService } from '@/api/agent.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const error = ref(null)
const saving = ref(false)
const agent = ref(null)

const form = ref({
  profile_image: null,
})

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const { data } = await agentService.myProfile()
    agent.value = data
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
    const payload = new FormData()
    if (form.value.profile_image) {
      payload.append('profile_image', form.value.profile_image)
    }
    const { data } = await agentService.updateMyProfile(payload)
    agent.value = data
    form.value.profile_image = null
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
    <Breadcrumbs
      :items="[
        { label: 'لوحة الوسيط', to: '/agent/dashboard' },
        { label: 'الملف الشخصي' },
      ]"
    />

    <AdminPageHeader
      title="الملف الشخصي"
      description="بياناتك الشخصية وصورة الملف."
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchProfile" />

    <form v-else-if="agent" @submit.prevent="saveProfile">
      <div class="admin-company-form__section">
        <h3 class="admin-company-form__section-title">المعلومات الأساسية</h3>

        <div class="row">
          <div class="col-md-6">
            <AppFormGroup label="الاسم الأول">
              <AppInput :model-value="agent.user?.fname" readonly />
            </AppFormGroup>
          </div>
          <div class="col-md-6">
            <AppFormGroup label="الاسم الأخير">
              <AppInput :model-value="agent.user?.lname" readonly />
            </AppFormGroup>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <AppFormGroup label="البريد الإلكتروني">
              <AppInput :model-value="agent.user?.email" readonly />
            </AppFormGroup>
          </div>
          <div class="col-md-6">
            <AppFormGroup label="رقم الهاتف">
              <AppInput :model-value="agent.user?.phone" readonly />
            </AppFormGroup>
          </div>
        </div>

        <p class="text-muted small mt-2">
          <i class="bi bi-info-circle"></i>
          لتعديل بياناتك الأساسية، انتقل إلى
          <RouterLink to="/profile">حسابي</RouterLink>.
        </p>
      </div>

      <div class="admin-company-form__section">
        <h3 class="admin-company-form__section-title">صورة الملف</h3>

        <AppFormGroup label="الصورة الشخصية">
          <AppFileUpload
            v-model="form.profile_image"
            accept="image/*"
            hint="JPG, PNG, WebP — حد أقصى 5MB"
          />
          <div v-if="agent.profile_image_url" class="mt-2">
            <img
              :src="agent.profile_image_url"
              alt="Profile"
              class="admin-company-form__thumb rounded-circle"
              style="width:100px;height:100px;object-fit:cover;"
            />
          </div>
        </AppFormGroup>
      </div>

      <div v-if="agent.company" class="admin-company-form__section">
        <h3 class="admin-company-form__section-title">الشركة</h3>
        <p class="mb-1">
          <i class="bi bi-building"></i>
          {{ agent.company.company_name }}
        </p>
      </div>

      <div class="admin-company-form__actions">
        <AppButton type="submit" variant="primary" :loading="saving">
          حفظ التغييرات
        </AppButton>
        <AppButton to="/agent/dashboard" variant="outline">
          العودة للوحة
        </AppButton>
      </div>
    </form>
  </div>
</template>
