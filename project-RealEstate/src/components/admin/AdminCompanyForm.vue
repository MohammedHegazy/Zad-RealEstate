<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'

import AppAutocomplete from '@/components/ui/AppAutocomplete.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import {
  COMPANY_STATUS_OPTIONS,
  WORK_DAYS_OPTIONS,
} from '@/config/admin.js'
import { adminUsersService } from '@/api/admin/users.js'
import { citiesService } from '@/api/cities.js'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { getUserName } from '@/utils/user.js'

const props = defineProps({
  company: {
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

const cities = ref([])
const places = ref([])
const profilePreview = ref('')
const bannerPreview = ref('')
const profileFile = ref(null)
const bannerFile = ref(null)

const form = reactive({
  user_id: '',
  places_id: '',
  cities_id: '',
  company_name: '',
  website: '',
  employees_num: '1',
  description: '',
  work_days: [],
  status: 'pending',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

const cityOptions = computed(() => [
  { value: '', label: 'اختر المدينة' },
  ...cities.value.map((city) => ({
    value: String(city.id),
    label: city.name,
  })),
])

const placeOptions = computed(() => [
  { value: '', label: 'اختر المنطقة' },
  ...places.value.map((place) => ({
    value: String(place.id),
    label: place.name,
  })),
])

function resetFormFromCompany(company) {
  profileFile.value = null
  bannerFile.value = null
  profilePreview.value = company?.profile_image_url ?? ''
  bannerPreview.value = company?.banner_image_url ?? ''

  if (!company) {
    Object.assign(form, {
      user_id: '',
      places_id: '',
      cities_id: '',
      company_name: '',
      website: '',
      employees_num: '1',
      description: '',
      work_days: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'],
      status: 'pending',
    })
    return
  }

  Object.assign(form, {
    user_id: company.user_id ?? '',
    places_id: company.places_id ? String(company.places_id) : '',
    cities_id: company.place?.cities_id ? String(company.place.cities_id) : '',
    company_name: company.company_name ?? '',
    website: company.website ?? '',
    employees_num: company.employees_num ?? '1',
    description: company.description ?? '',
    work_days: Array.isArray(company.work_days) ? [...company.work_days] : [],
    status: company.status ?? 'pending',
  })
}

function isWorkDaySelected(day) {
  return form.work_days.includes(day)
}

function setWorkDay(day, checked) {
  if (checked) {
    if (!form.work_days.includes(day)) {
      form.work_days = [...form.work_days, day]
    }
    return
  }

  form.work_days = form.work_days.filter((item) => item !== day)
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

function onProfileChange(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  profileFile.value = file
  profilePreview.value = URL.createObjectURL(file)
}

function onBannerChange(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  bannerFile.value = file
  bannerPreview.value = URL.createObjectURL(file)
}

watch(
  () => props.company,
  (company) => resetFormFromCompany(company),
  { immediate: true },
)

watch(
  () => props.errorMessage,
  (message) => {
    if (message) generalError.value = message
  },
)

watch(
  () => form.cities_id,
  async (cityId) => {
    if (!cityId) {
      places.value = []
      form.places_id = ''
      return
    }

    try {
      const { data } = await citiesService.places(cityId, { per_page: 100 })
      places.value = data ?? []
    } catch {
      places.value = []
    }
  },
)

onMounted(async () => {
  try {
    const { data } = await citiesService.list({ per_page: 100 })
    cities.value = data ?? []
  } catch {
    cities.value = []
  }

  if (form.cities_id) {
    const { data } = await citiesService.places(form.cities_id, { per_page: 100 }).catch(() => ({
      data: [],
    }))
    places.value = data ?? []
  }
})

function handleSubmit() {
  clearErrors()

  const payload = {
    places_id: Number(form.places_id),
    company_name: form.company_name,
    website: form.website || null,
    employees_num: Number(form.employees_num) || 1,
    description: form.description,
    work_days: form.work_days,
    status: form.status,
  }

  if (props.isCreate) {
    payload.user_id = Number(form.user_id)
  }

  emit('submit', payload, {
    profile_image: profileFile.value,
    banner_image: bannerFile.value,
  })
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form class="admin-company-form" novalidate @submit.prevent="handleSubmit">
    <FormAlert v-if="generalError || errorMessage" :message="generalError || errorMessage" />

    <div class="admin-company-form__section">
      <h4 class="admin-company-form__section-title">البيانات الأساسية</h4>
      <div class="row g-3">
        <div v-if="isCreate" class="col-md-6">
          <AppFormGroup
            label="مالك الشركة"
            label-for="company-owner"
            :error="fieldError('user_id')"
            hint="ابحث بالاسم أو اسم المستخدم أو البريد."
            required
          >
            <AppAutocomplete
              id="company-owner"
              v-model="form.user_id"
              :search-fn="searchOwners"
              :resolve-fn="resolveOwner"
              :get-label="getUserName"
              :get-value="(user) => user.id"
              :get-description="getOwnerDescription"
              placeholder="ابحث عن المالك..."
              :has-error="hasError('user_id')"
            />
          </AppFormGroup>
        </div>
        <div :class="isCreate ? 'col-md-6' : 'col-md-8'">
          <AppFormGroup
            label="اسم الشركة"
            label-for="company-name"
            :error="fieldError('company_name')"
            required
          >
            <AppInput
              id="company-name"
              v-model="form.company_name"
              :has-error="hasError('company_name')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الحالة" label-for="company-status" :error="fieldError('status')">
            <AppSelect
              id="company-status"
              v-model="form.status"
              :options="COMPANY_STATUS_OPTIONS"
              :has-error="hasError('status')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الموقع الإلكتروني" label-for="company-website" :error="fieldError('website')">
            <AppInput
              id="company-website"
              v-model="form.website"
              type="url"
              placeholder="https://"
              :has-error="hasError('website')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup
            label="عدد الموظفين"
            label-for="company-employees"
            :error="fieldError('employees_num')"
          >
            <AppInput
              id="company-employees"
              v-model="form.employees_num"
              type="number"
              min="1"
              :has-error="hasError('employees_num')"
            />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppFormGroup
            label="الوصف"
            label-for="company-description"
            :error="fieldError('description')"
            required
          >
            <AppTextarea
              id="company-description"
              v-model="form.description"
              :has-error="hasError('description')"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-company-form__section">
      <h4 class="admin-company-form__section-title">الموقع</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup label="المدينة" label-for="company-city">
            <AppSelect id="company-city" v-model="form.cities_id" :options="cityOptions" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="المنطقة" label-for="company-place" :error="fieldError('places_id')" required>
            <AppSelect
              id="company-place"
              v-model="form.places_id"
              :options="placeOptions"
              :has-error="hasError('places_id')"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-company-form__section">
      <h4 class="admin-company-form__section-title">أيام العمل</h4>
      <AppFormGroup :error="fieldError('work_days')" required>
        <div class="admin-company-form__work-days">
          <AppCheckbox
            v-for="day in WORK_DAYS_OPTIONS"
            :id="`company-day-${day.value}`"
            :key="day.value"
            :model-value="isWorkDaySelected(day.value)"
            :label="day.label"
            @update:model-value="(checked) => setWorkDay(day.value, checked)"
          />
        </div>
      </AppFormGroup>
    </div>

    <div class="admin-company-form__section">
      <h4 class="admin-company-form__section-title">الصور</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup label="شعار الشركة" label-for="company-profile-image" :error="fieldError('profile_image')">
            <div class="admin-company-form__image-field">
              <div v-if="profilePreview" class="admin-company-form__image-preview admin-company-form__image-preview--logo">
                <img :src="profilePreview" alt="شعار الشركة" />
              </div>
              <label class="admin-company-form__file-label" for="company-profile-image">
                <i class="bi bi-image"></i>
                اختيار شعار
              </label>
              <input
                id="company-profile-image"
                type="file"
                accept="image/*"
                class="admin-company-form__file-input"
                @change="onProfileChange"
              />
            </div>
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="صورة الغلاف" label-for="company-banner-image" :error="fieldError('banner_image')">
            <div class="admin-company-form__image-field">
              <div v-if="bannerPreview" class="admin-company-form__image-preview admin-company-form__image-preview--banner">
                <img :src="bannerPreview" alt="غلاف الشركة" />
              </div>
              <label class="admin-company-form__file-label" for="company-banner-image">
                <i class="bi bi-card-image"></i>
                اختيار غلاف
              </label>
              <input
                id="company-banner-image"
                type="file"
                accept="image/*"
                class="admin-company-form__file-input"
                @change="onBannerChange"
              />
            </div>
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-company-form__actions">
      <AppButton type="submit" variant="primary" :disabled="loading">
        {{ loading ? 'جاري الحفظ...' : isCreate ? 'إنشاء الشركة' : 'حفظ التعديلات' }}
      </AppButton>
    </div>
  </form>
</template>
