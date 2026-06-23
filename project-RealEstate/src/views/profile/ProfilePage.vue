<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import { PAGE_NARRATIVES, USER_TYPE_JOURNEY } from '@/config/journey.js'
import { GENDER_OPTIONS, COUNTRY_CODES } from '@/config/auth.js'
import { profileService } from '@/api/profile.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAuthStore } from '@/stores/auth.js'

const router = useRouter()
const auth = useAuthStore()
const narrative = PAGE_NARRATIVES.profile

const loading = ref(false)
const error = ref(null)
const saving = ref(false)
const profile = ref(null)
const editing = ref(false)
const form = ref({
  fname: '',
  lname: '',
  username: '',
  email: '',
  phone: '',
  country_code_phone: '+963',
  gender: '',
})

const userTypeJourney = computed(() => {
  const type = profile.value?.type ?? auth.user?.type
  return USER_TYPE_JOURNEY[type] ?? USER_TYPE_JOURNEY.buyer
})

const displayName = computed(() => {
  const user = profile.value ?? auth.user
  if (!user) return ''
  return `${user.fname} ${user.lname}`.trim()
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

function startEditing() {
  editing.value = true
  error.value = null
}

function cancelEditing() {
  editing.value = false
  const data = profile.value
  form.value = {
    fname: data.fname ?? '',
    lname: data.lname ?? '',
    username: data.username ?? '',
    email: data.email ?? '',
    phone: data.phone ?? '',
    country_code_phone: data.country_code_phone ?? '+963',
    gender: data.gender ?? '',
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
    editing.value = false
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر حفظ التغييرات.')
  } finally {
    saving.value = false
  }
}

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

onMounted(fetchProfile)
</script>

<template>
  <div class="directory-page profile-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="narrative.title"
        :description="narrative.description"
        :step="narrative.step"
      />

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchProfile" />

      <template v-else-if="profile || auth.user">
        <div class="profile-card">
          <div class="profile-card__avatar">
            <i class="bi bi-person-circle"></i>
          </div>
          <div class="profile-card__info">
            <h2>{{ displayName }}</h2>
            <p class="profile-card__username">@{{ (profile ?? auth.user).username }}</p>
            <span class="profile-card__type">{{ userTypeJourney.label }}</span>
            <p class="profile-card__email">{{ (profile ?? auth.user).email }}</p>
            <p v-if="(profile ?? auth.user).phone" class="profile-card__phone">
              <span dir="ltr">{{ (profile ?? auth.user).country_code_phone ?? '' }} {{ (profile ?? auth.user).phone }}</span>
            </p>
          </div>
          <div v-if="!editing" class="profile-card__actions">
            <AppButton variant="outline" size="sm" @click="startEditing">
              <i class="bi bi-pencil"></i>
              تعديل
            </AppButton>
          </div>
        </div>

        <form v-if="editing" class="profile-edit-form" @submit.prevent="saveProfile">
          <h3>تعديل البيانات الشخصية</h3>
          <ErrorAlert v-if="error" :message="error" />

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

          <div class="d-flex gap-2 mt-3">
            <AppButton type="submit" variant="primary" :loading="saving">
              حفظ التغييرات
            </AppButton>
            <AppButton variant="outline" @click="cancelEditing">
              إلغاء
            </AppButton>
          </div>
        </form>

        <section class="profile-journey">
          <h3>مسارك على المنصة</h3>
          <div class="profile-journey__grid">
            <RouterLink
              v-for="link in userTypeJourney.links"
              :key="link.label"
              :to="link.to"
              class="profile-journey__card"
            >
              <i :class="['bi', link.icon]"></i>
              <span>{{ link.label }}</span>
              <small v-if="link.hint">{{ link.hint }}</small>
            </RouterLink>
          </div>
        </section>

        <div class="profile-actions">
          <AppButton to="/favorites" variant="primary">عقاراتي المفضلة</AppButton>
          <AppButton to="/estates" variant="outline">تصفح العقارات</AppButton>
          <AppButton variant="ghost" @click="handleLogout">تسجيل الخروج</AppButton>
        </div>

        <CtaBanner
          title="أكمل رحلتك العقارية"
          description="استكشف المدن والأحياء، قارن العقارات، وتواصل مع وكلاء معتمدين."
          :primary="{ label: 'استكشف المدن', to: '/cities' }"
          :secondary="{ label: 'الوكلاء', to: '/agents' }"
        />
      </template>
    </div>
  </div>
</template>
