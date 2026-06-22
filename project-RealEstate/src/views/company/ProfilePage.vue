<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFileUpload from '@/components/ui/AppFileUpload.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import SocialLinksManager from '@/components/company/SocialLinksManager.vue'
import { companyService } from '@/api/company.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const error = ref(null)
const saving = ref(false)
const company = ref(null)

const form = ref({
  company_name: '',
  description: '',
  website: '',
  employees_num: null,
  work_days: [],
  places_id: null,
})

const profileImage = ref(null)
const bannerImage = ref(null)

async function fetchCompany() {
  loading.value = true
  error.value = null
  try {
    const { data } = await companyService.myCompany()
    company.value = data
    form.value = {
      company_name: data.company_name ?? '',
      description: data.description ?? '',
      website: data.website ?? '',
      employees_num: data.employees_num ?? null,
      work_days: data.work_days ?? [],
      places_id: data.places_id ?? null,
    }
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل ملف الشركة.')
    company.value = null
  } finally {
    loading.value = false
  }
}

async function saveCompany() {
  saving.value = true
  error.value = null
  try {
    const hasFiles = profileImage.value || bannerImage.value
    let payload

    if (hasFiles) {
      payload = new FormData()
      Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null && value !== '' && value !== undefined) {
          if (Array.isArray(value)) {
            value.forEach((item) => payload.append(`${key}[]`, item))
          } else {
            payload.append(key, value)
          }
        }
      })
      if (profileImage.value) payload.append('profile_image', profileImage.value)
      if (bannerImage.value) payload.append('banner_image', bannerImage.value)
      payload.append('_method', 'PUT')
    } else {
      payload = { ...form.value }
    }

    const { data } = await companyService.update(payload)
    company.value = data
    profileImage.value = null
    bannerImage.value = null
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر حفظ التغييرات.')
  } finally {
    saving.value = false
  }
}

async function createCompany() {
  saving.value = true
  error.value = null
  try {
    const hasFiles = profileImage.value || bannerImage.value
    let payload

    if (hasFiles) {
      payload = new FormData()
      Object.entries(form.value).forEach(([key, value]) => {
        if (value !== null && value !== '' && value !== undefined) {
          if (Array.isArray(value)) {
            value.forEach((item) => payload.append(`${key}[]`, item))
          } else {
            payload.append(key, value)
          }
        }
      })
      if (profileImage.value) payload.append('profile_image', profileImage.value)
      if (bannerImage.value) payload.append('banner_image', bannerImage.value)
    } else {
      payload = { ...form.value }
    }

    const { data } = await companyService.create(payload)
    company.value = data
    profileImage.value = null
    bannerImage.value = null
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر إنشاء الشركة.')
  } finally {
    saving.value = false
  }
}

function handleSubmit() {
  if (company.value) {
    saveCompany()
  } else {
    createCompany()
  }
}

onMounted(fetchCompany)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      :title="company ? 'تعديل ملف الشركة' : 'إنشاء شركة'"
      description="بيانات شركتك على المنصة."
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" />

    <form v-else @submit.prevent="handleSubmit">
      <div class="admin-company-form__section">
        <h3 class="admin-company-form__section-title">المعلومات الأساسية</h3>

        <AppFormGroup label="اسم الشركة" required>
          <AppInput v-model="form.company_name" placeholder="الاسم التجاري للشركة" />
        </AppFormGroup>

        <AppFormGroup label="الوصف">
          <AppTextarea v-model="form.description" rows="4" placeholder="نبذة عن الشركة وخدماتها..." />
        </AppFormGroup>

        <div class="row">
          <div class="col-md-6">
            <AppFormGroup label="الموقع الإلكتروني">
              <AppInput v-model="form.website" type="url" placeholder="https://example.com" />
            </AppFormGroup>
          </div>
          <div class="col-md-6">
            <AppFormGroup label="عدد الموظفين">
              <AppInput v-model="form.employees_num" type="number" min="1" placeholder="عدد الموظفين في الشركة" />
            </AppFormGroup>
          </div>
        </div>
      </div>

      <div class="admin-company-form__section">
        <h3 class="admin-company-form__section-title">الصور</h3>

        <div class="row">
          <div class="col-md-6">
            <AppFormGroup label="شعار الشركة">
              <AppFileUpload
                v-model="profileImage"
                accept="image/*"
                hint="JPG, PNG, WebP — حد أقصى 5MB"
              />
              <div v-if="company?.profile_image_url" class="mt-2">
                <img :src="company.profile_image_url" alt="Profile" class="admin-company-form__thumb" />
              </div>
            </AppFormGroup>
          </div>
          <div class="col-md-6">
            <AppFormGroup label="صورة الغلاف">
              <AppFileUpload
                v-model="bannerImage"
                accept="image/*"
                hint="JPG, PNG, WebP — حد أقصى 5MB"
              />
              <div v-if="company?.banner_image_url" class="mt-2">
                <img :src="company.banner_image_url" alt="Banner" class="admin-company-form__thumb" />
              </div>
            </AppFormGroup>
          </div>
        </div>
      </div>

      <SocialLinksManager />

      <div class="admin-company-form__actions">
        <AppButton type="submit" variant="primary" :loading="saving">
          {{ company ? 'حفظ التغييرات' : 'إنشاء شركة' }}
        </AppButton>
        <AppButton v-if="company" to="/company/dashboard" variant="outline">
          العودة للوحة
        </AppButton>
      </div>
    </form>
  </div>
</template>
