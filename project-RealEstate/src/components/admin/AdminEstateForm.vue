<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'

import MapLocationPicker from '@/components/map/MapLocationPicker.vue'
import AppAutocomplete from '@/components/ui/AppAutocomplete.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFileUpload from '@/components/ui/AppFileUpload.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import {
  ADMIN_ESTATE_KIND_OPTIONS,
  ADMIN_ESTATE_TYPE_OPTIONS,
  ESTATE_BUILD_STATE_OPTIONS,
  ESTATE_RENT_KIND_OPTIONS,
  ESTATE_STATUS_OPTIONS,
  SOCIAL_PLATFORM_OPTIONS,
  getPlatformStyle,
} from '@/config/admin.js'
import { COUNTRY_CODES } from '@/config/auth.js'
import { adminUsersService } from '@/api/admin/users.js'
import { citiesService } from '@/api/cities.js'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { hasMapCoordinates } from '@/utils/map.js'
import { getUserName } from '@/utils/user.js'

const props = defineProps({
  estate: {
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

const form = reactive({
  user_id: '',
  places_id: '',
  cities_id: '',
  latitude: '',
  longitude: '',
  name: '',
  phone: '',
  country_code_phone: '+963',
  space_of_estate: '',
  price_of_meter: '',
  price: '',
  monthly_rent: '',
  annual_expenses: '',
  maintenance_cost: '',
  annual_property_tax: '',
  annual_hoa_or_service: '',
  occupancy_rate: '100',
  floor: '',
  num_of_bedrooms: '',
  num_of_livingrooms: '',
  num_of_receptions: '',
  num_of_bathrooms: '',
  num_of_kitchens: '',
  num_of_balconies: '',
  status: 'pending',
  type_text: 'سكني',
  kind_text: 'شقة',
  is_furnished: false,
  description: '',
  real_number: '',
  date_of_build: '',
  state_of_build: 'good',
  rent_kind: '',
  rent_description: '',
  facebook: '',
  instagram: '',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

const socialLinks = ref([])
const editingLinkIndex = ref(-1)
const editingLink = ref({ platform: '', url: '' })
const newLinkPlatform = ref('')
const newLinkUrl = ref('')

function startEditLink(index) {
  editingLinkIndex.value = index
  editingLink.value = { ...socialLinks.value[index] }
}

function cancelEditLink() {
  editingLinkIndex.value = -1
  editingLink.value = { platform: '', url: '' }
}

function saveEditLink(index) {
  if (!editingLink.value.platform || !editingLink.value.url) return
  socialLinks.value[index] = { ...editingLink.value }
  cancelEditLink()
}

function addSocialLink() {
  if (!newLinkPlatform.value || !newLinkUrl.value) return
  socialLinks.value.push({ platform: newLinkPlatform.value, url: newLinkUrl.value })
  newLinkPlatform.value = ''
  newLinkUrl.value = ''
}

function removeSocialLink(index) {
  socialLinks.value.splice(index, 1)
  if (editingLinkIndex.value === index) cancelEditLink()
}

const pendingImages = ref([])
const pendingVideos = ref([])
const pendingAds = ref([])
const primaryImageIndex = ref(0)
const mainAdIndex = ref(0)

function removePendingFile(listRef, index, adjustPrimaryRef = null) {
  listRef.value.splice(index, 1)
  if (adjustPrimaryRef && adjustPrimaryRef.value >= listRef.value.length) {
    adjustPrimaryRef.value = Math.max(0, listRef.value.length - 1)
  }
}

function previewUrl(file) {
  return URL.createObjectURL(file)
}

const dragIndex = ref(null)
const dragOverIndex = ref(-1)

function onDragStart(index) {
  dragIndex.value = index
}

function onDragOver(e, index) {
  e.preventDefault()
  dragOverIndex.value = index
}

function onDragLeave() {
  dragOverIndex.value = -1
}

function onDrop(list, index, primaryRef) {
  if (dragIndex.value === null || dragIndex.value === index) {
    clearDragState()
    return
  }
  const from = dragIndex.value
  const to = index
  const item = list.value.splice(from, 1)[0]
  list.value.splice(to, 0, item)

  if (primaryRef) {
    if (primaryRef.value === from) {
      primaryRef.value = to
    } else if (from < primaryRef.value && to >= primaryRef.value) {
      primaryRef.value--
    } else if (from > primaryRef.value && to <= primaryRef.value) {
      primaryRef.value++
    }
  }

  clearDragState()
}

function clearDragState() {
  dragIndex.value = null
  dragOverIndex.value = -1
}

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

const mapFocus = computed(() => {
  const place = places.value.find((item) => String(item.id) === form.places_id)
  if (place && hasMapCoordinates(place)) {
    return {
      latitude: Number(place.latitude),
      longitude: Number(place.longitude),
      zoom: 14,
    }
  }

  const city = cities.value.find((item) => String(item.id) === form.cities_id)
  if (city && hasMapCoordinates(city)) {
    return {
      latitude: Number(city.latitude),
      longitude: Number(city.longitude),
      zoom: 12,
    }
  }

  return null
})

const locationError = computed(
  () => fieldError('latitude') || fieldError('longitude'),
)

const hasLocationError = computed(
  () => hasError('latitude') || hasError('longitude'),
)

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

function resetFormFromEstate(estate) {
  if (!estate) {
    Object.assign(form, {
      user_id: '',
      places_id: '',
      cities_id: '',
      latitude: '',
      longitude: '',
      name: '',
      phone: '',
      country_code_phone: '+963',
      space_of_estate: '',
      price_of_meter: '',
      price: '',
      monthly_rent: '',
      annual_expenses: '',
      maintenance_cost: '',
      annual_property_tax: '',
      annual_hoa_or_service: '',
      occupancy_rate: '100',
      floor: '',
      num_of_bedrooms: '',
      num_of_livingrooms: '',
      num_of_receptions: '',
      num_of_bathrooms: '',
      num_of_kitchens: '',
      num_of_balconies: '',
      status: 'pending',
      type_text: 'سكني',
      kind_text: 'شقة',
      is_furnished: false,
      description: '',
      real_number: '',
      date_of_build: '',
      state_of_build: 'good',
      rent_kind: '',
      rent_description: '',
      facebook: '',
      instagram: '',
    })
    socialLinks.value = []
    return
  }

  socialLinks.value = (estate.social_links ?? []).map((l) => ({ platform: l.platform, url: l.url }))

  Object.assign(form, {
    user_id: estate.user_id ?? '',
    places_id: estate.places_id ? String(estate.places_id) : '',
    cities_id: estate.place?.cities_id ? String(estate.place.cities_id) : '',
    latitude: estate.latitude ?? '',
    longitude: estate.longitude ?? '',
    name: estate.name ?? '',
    phone: estate.phone ?? '',
    country_code_phone: estate.country_code_phone ?? '+963',
    space_of_estate: estate.space_of_estate ?? '',
    price_of_meter: estate.price_of_meter ?? '',
    price: estate.price ?? '',
    monthly_rent: estate.monthly_rent ?? '',
    annual_expenses: estate.annual_expenses ?? '',
    maintenance_cost: estate.maintenance_cost ?? '',
    annual_property_tax: estate.annual_property_tax ?? '',
    annual_hoa_or_service: estate.annual_hoa_or_service ?? '',
    occupancy_rate: estate.occupancy_rate ?? '100',
    floor: estate.floor ?? '',
    num_of_bedrooms: estate.num_of_bedrooms ?? '',
    num_of_livingrooms: estate.num_of_livingrooms ?? '',
    num_of_receptions: estate.num_of_receptions ?? '',
    num_of_bathrooms: estate.num_of_bathrooms ?? '',
    num_of_kitchens: estate.num_of_kitchens ?? '',
    num_of_balconies: estate.num_of_balconies ?? '',
    status: estate.status ?? 'pending',
    type_text: estate.type_text ?? 'سكني',
    kind_text: estate.kind_text ?? 'شقة',
    is_furnished: Boolean(estate.is_furnished),
    description: estate.description ?? '',
    real_number: estate.real_number ?? '',
    date_of_build: estate.date_of_build ?? '',
    state_of_build: estate.state_of_build ?? 'good',
    rent_kind: estate.rent_kind ?? '',
    rent_description: estate.rent_description ?? '',
    facebook: '',
    instagram: '',
  })
}

watch(
  () => props.estate,
  (estate) => resetFormFromEstate(estate),
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

function toNumber(value) {
  if (value === '' || value == null) return null
  return Number(value)
}

function handleSubmit() {
  clearErrors()

  const payload = {
    places_id: Number(form.places_id),
    latitude: Number(form.latitude),
    longitude: Number(form.longitude),
    name: form.name,
    phone: form.phone,
    country_code_phone: form.country_code_phone,
    space_of_estate: Number(form.space_of_estate),
    price_of_meter: Number(form.price_of_meter),
    price: Number(form.price),
    monthly_rent: toNumber(form.monthly_rent),
    annual_expenses: toNumber(form.annual_expenses) ?? 0,
    maintenance_cost: toNumber(form.maintenance_cost) ?? 0,
    annual_property_tax: toNumber(form.annual_property_tax) ?? 0,
    annual_hoa_or_service: toNumber(form.annual_hoa_or_service) ?? 0,
    occupancy_rate: toNumber(form.occupancy_rate) ?? 100,
    floor: toNumber(form.floor),
    num_of_bedrooms: toNumber(form.num_of_bedrooms),
    num_of_livingrooms: toNumber(form.num_of_livingrooms),
    num_of_receptions: toNumber(form.num_of_receptions),
    num_of_bathrooms: toNumber(form.num_of_bathrooms),
    num_of_kitchens: toNumber(form.num_of_kitchens),
    num_of_balconies: toNumber(form.num_of_balconies),
    status: form.status,
    type_text: form.type_text,
    kind_text: form.kind_text,
    is_furnished: form.is_furnished,
    description: form.description,
    real_number: form.real_number || null,
    date_of_build: form.date_of_build || null,
    state_of_build: form.state_of_build || null,
    rent_kind: form.rent_kind || null,
    rent_description: form.rent_description || null,
    facebook: form.facebook || null,
    instagram: form.instagram || null,
    links: socialLinks.value.filter((l) => l.platform && l.url),
  }

  if (props.isCreate) {
    payload.user_id = Number(form.user_id)
  }

  const files = props.isCreate
    ? {
        images: pendingImages.value,
        videos: pendingVideos.value,
        ads: pendingAds.value,
        primary_image_index: primaryImageIndex.value,
        main_ad_index: mainAdIndex.value,
      }
    : {}

  emit('submit', payload, files)
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form class="admin-estate-form" novalidate @submit.prevent="handleSubmit">
    <FormAlert v-if="generalError || errorMessage" :message="generalError || errorMessage" />

    <div class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">البيانات الأساسية</h4>
      <div class="row g-3">
        <div v-if="isCreate" class="col-md-6">
          <AppFormGroup
            label="مالك العقار"
            label-for="estate-owner"
            :error="fieldError('user_id')"
            hint="ابحث بالاسم أو اسم المستخدم أو البريد."
            required
          >
            <AppAutocomplete
              id="estate-owner"
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
        <div :class="isCreate ? 'col-md-6' : 'col-md-12'">
          <AppFormGroup label="اسم العقار" label-for="estate-name" :error="fieldError('name')" required>
            <AppInput id="estate-name" v-model="form.name" :has-error="hasError('name')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الحالة" label-for="estate-status" :error="fieldError('status')">
            <AppSelect
              id="estate-status"
              v-model="form.status"
              :options="ESTATE_STATUS_OPTIONS"
              :has-error="hasError('status')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الفئة" label-for="estate-type" :error="fieldError('type_text')" required>
            <AppSelect
              id="estate-type"
              v-model="form.type_text"
              :options="ADMIN_ESTATE_TYPE_OPTIONS"
              :has-error="hasError('type_text')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="النوع" label-for="estate-kind" :error="fieldError('kind_text')" required>
            <AppSelect
              id="estate-kind"
              v-model="form.kind_text"
              :options="ADMIN_ESTATE_KIND_OPTIONS"
              :has-error="hasError('kind_text')"
            />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppFormGroup label="الوصف" label-for="estate-description" :error="fieldError('description')" required>
            <AppTextarea
              id="estate-description"
              v-model="form.description"
              :has-error="hasError('description')"
            />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppCheckbox id="estate-furnished" v-model="form.is_furnished" label="مفروش" />
        </div>
      </div>
    </div>

    <div class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">الموقع</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup label="المدينة" label-for="estate-city">
            <AppSelect id="estate-city" v-model="form.cities_id" :options="cityOptions" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="المنطقة" label-for="estate-place" :error="fieldError('places_id')" required>
            <AppSelect
              id="estate-place"
              v-model="form.places_id"
              :options="placeOptions"
              :has-error="hasError('places_id')"
            />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppFormGroup
            label="موقع العقار على الخريطة"
            :error="locationError"
            hint="اختر المدينة والمنطقة أولاً لتقريب الخريطة، ثم انقر لتحديد الموقع."
            required
          >
            <MapLocationPicker
              v-model:latitude="form.latitude"
              v-model:longitude="form.longitude"
              :focus="mapFocus"
              :has-error="hasLocationError"
              height="360px"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">التسعير والمساحة</h4>
      <div class="row g-3">
        <div class="col-md-4">
          <AppFormGroup label="المساحة (م²)" label-for="estate-space" :error="fieldError('space_of_estate')" required>
            <AppInput id="estate-space" v-model="form.space_of_estate" type="number" :has-error="hasError('space_of_estate')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="سعر المتر" label-for="estate-ppm" :error="fieldError('price_of_meter')" required>
            <AppInput id="estate-ppm" v-model="form.price_of_meter" type="number" :has-error="hasError('price_of_meter')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="السعر الإجمالي" label-for="estate-price" :error="fieldError('price')" required>
            <AppInput id="estate-price" v-model="form.price" type="number" :has-error="hasError('price')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الإيجار الشهري" label-for="estate-rent" :error="fieldError('monthly_rent')">
            <AppInput id="estate-rent" v-model="form.monthly_rent" type="number" :has-error="hasError('monthly_rent')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="نوع الإيجار" label-for="estate-rent-kind">
            <AppSelect id="estate-rent-kind" v-model="form.rent_kind" :options="ESTATE_RENT_KIND_OPTIONS" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="حالة البناء" label-for="estate-build-state">
            <AppSelect
              id="estate-build-state"
              v-model="form.state_of_build"
              :options="ESTATE_BUILD_STATE_OPTIONS"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">مؤشرات الاستثمار</h4>
      <p class="admin-estate-form__media-hint">تُحسب تلقائياً عند الحفظ.</p>
      <div class="row g-3">
        <div class="col-md-4">
          <AppFormGroup label="المصاريف السنوية" label-for="estate-annual-expenses">
            <AppInput id="estate-annual-expenses" v-model="form.annual_expenses" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="تكاليف الصيانة" label-for="estate-maintenance">
            <AppInput id="estate-maintenance" v-model="form.maintenance_cost" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الضريبة العقارية" label-for="estate-tax">
            <AppInput id="estate-tax" v-model="form.annual_property_tax" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="رسوم الخدمات السنوية" label-for="estate-hoa">
            <AppInput id="estate-hoa" v-model="form.annual_hoa_or_service" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="نسبة الإشغال (%)" label-for="estate-occupancy">
            <AppInput id="estate-occupancy" v-model="form.occupancy_rate" type="number" min="0" max="100" />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">الغرف والتفاصيل</h4>
      <div class="row g-3">
        <div class="col-md-3">
          <AppFormGroup label="الطابق" label-for="estate-floor">
            <AppInput id="estate-floor" v-model="form.floor" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="غرف النوم" label-for="estate-bedrooms">
            <AppInput id="estate-bedrooms" v-model="form.num_of_bedrooms" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الحمامات" label-for="estate-bathrooms">
            <AppInput id="estate-bathrooms" v-model="form.num_of_bathrooms" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="المطابخ" label-for="estate-kitchens">
            <AppInput id="estate-kitchens" v-model="form.num_of_kitchens" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الصالات" label-for="estate-livingrooms">
            <AppInput id="estate-livingrooms" v-model="form.num_of_livingrooms" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الاستقبالات" label-for="estate-receptions">
            <AppInput id="estate-receptions" v-model="form.num_of_receptions" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الشرفات" label-for="estate-balconies">
            <AppInput id="estate-balconies" v-model="form.num_of_balconies" type="number" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="سنة البناء" label-for="estate-build-year">
            <AppInput id="estate-build-year" v-model="form.date_of_build" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="الرقم العقاري" label-for="estate-real-number">
            <AppInput id="estate-real-number" v-model="form.real_number" />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppFormGroup label="وصف الإيجار" label-for="estate-rent-desc">
            <AppTextarea id="estate-rent-desc" v-model="form.rent_description" />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">التواصل</h4>
      <div class="row g-3">
        <div class="col-md-3">
          <AppFormGroup label="رمز الدولة" label-for="estate-code">
            <AppSelect id="estate-code" v-model="form.country_code_phone" :options="COUNTRY_CODES" />
          </AppFormGroup>
        </div>
        <div class="col-md-5">
          <AppFormGroup label="الهاتف" label-for="estate-phone" :error="fieldError('phone')" required>
            <AppInput id="estate-phone" v-model="form.phone" type="tel" :has-error="hasError('phone')" />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <label class="app-form-group__label">روابط التواصل الاجتماعي</label>
          <p class="text-muted small mb-2">اختياري — أضف روابط العقار على منصات التواصل.</p>

          <div v-if="socialLinks.length" class="d-flex flex-column gap-2 mb-2">
            <div
              v-for="(link, index) in socialLinks"
              :key="index"
              class="d-flex align-items-center gap-2 border rounded p-2"
            >
              <template v-if="editingLinkIndex === index">
                <AppSelect v-model="editingLink.platform" :options="SOCIAL_PLATFORM_OPTIONS" size="sm" style="min-width:120px;" />
                <AppInput v-model="editingLink.url" type="url" size="sm" class="flex-grow-1" placeholder="https://..." />
                <AppButton variant="primary" size="sm" type="button" @click="saveEditLink(index)">
                  <i class="bi bi-check"></i>
                </AppButton>
                <AppButton variant="outline" size="sm" type="button" @click="cancelEditLink">
                  <i class="bi bi-x"></i>
                </AppButton>
              </template>
              <template v-else>
                <span
                  class="d-inline-flex align-items-center justify-content-center rounded"
                  style="width:32px;height:32px;flex-shrink:0;"
                  :style="{ backgroundColor: getPlatformStyle(link.platform).color }"
                >
                  <i :class="getPlatformStyle(link.platform).icon" class="text-white"></i>
                </span>
                <span class="fw-semibold small" style="min-width:60px;">{{ SOCIAL_PLATFORM_OPTIONS.find((o) => o.value === link.platform)?.label ?? link.platform }}</span>
                <a
                  :href="link.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-truncate flex-grow-1 small text-decoration-none"
                >{{ link.url }}</a>
                <div class="d-flex gap-1 flex-shrink-0">
                  <AppButton variant="outline" size="sm" type="button" @click="startEditLink(index)">
                    <i class="bi bi-pencil"></i>
                  </AppButton>
                  <AppButton variant="outline" size="sm" type="button" @click="removeSocialLink(index)">
                    <i class="bi bi-trash"></i>
                  </AppButton>
                </div>
              </template>
            </div>
          </div>

          <div class="d-flex gap-2 align-items-end">
            <div style="min-width:130px;">
              <AppSelect v-model="newLinkPlatform" :options="SOCIAL_PLATFORM_OPTIONS" placeholder="المنصة" />
            </div>
            <AppInput v-model="newLinkUrl" type="url" placeholder="https://..." class="flex-grow-1" />
            <AppButton
              variant="primary"
              size="sm"
              type="button"
              :disabled="!newLinkPlatform || !newLinkUrl"
              @click="addSocialLink"
            >
              <i class="bi bi-plus-lg"></i>
              إضافة
            </AppButton>
          </div>
        </div>
      </div>
    </div>

    <div v-if="isCreate" class="admin-estate-form__section">
      <h4 class="admin-estate-form__section-title">الوسائط (اختياري)</h4>
      <p class="admin-estate-form__media-hint">
        يمكنك رفع الصور والفيديوهات وصور الإعلانات مع إنشاء العقار، أو إضافتها لاحقاً من صفحة التعديل.
      </p>

      <div class="admin-estate-form__media-block">
        <AppFileUpload
          accept="image/*"
          multiple
          hint="اسحب الصور أو اضغط لاختيارها"
          @update:model-value="(files) => { if (Array.isArray(files)) pendingImages.push(...files) }"
        />
        <div v-if="pendingImages.length" class="admin-estate-form__media-grid">
          <article
            v-for="(file, index) in pendingImages"
            :key="`img-${index}-${file.name}`"
            class="admin-estate-form__media-card"
            :class="{
              'admin-estate-form__media-card--primary': index === primaryImageIndex,
              'admin-estate-form__media-card--drag-over': dragOverIndex === index,
            }"
            draggable="true"
            @dragstart="onDragStart(index)"
            @dragover="(e) => onDragOver(e, index)"
            @dragleave="onDragLeave"
            @drop="onDrop(pendingImages, index, primaryImageIndex)"
            @dragend="clearDragState"
          >
            <img :src="previewUrl(file)" :alt="file.name" />
            <div class="admin-estate-form__media-card-actions">
              <AppButton
                v-if="index !== primaryImageIndex"
                variant="outline"
                size="sm"
                type="button"
                @click="primaryImageIndex = index"
              >
                رئيسية
              </AppButton>
              <AppButton
                variant="outline"
                size="sm"
                type="button"
                @click="removePendingFile(pendingImages, index, primaryImageIndex)"
              >
                حذف
              </AppButton>
            </div>
          </article>
        </div>
        <p v-else class="admin-estate-form__media-empty">لا توجد صور مرفقة.</p>
      </div>

      <div class="admin-estate-form__media-block">
        <AppFileUpload
          accept="video/*"
          multiple
          hint="اسحب الفيديوهات أو اضغط لاختيارها"
          @update:model-value="(files) => { if (Array.isArray(files)) pendingVideos.push(...files) }"
        />
        <div v-if="pendingVideos.length" class="admin-estate-form__media-videos">
          <article
            v-for="(file, index) in pendingVideos"
            :key="`vid-${index}-${file.name}`"
            class="admin-estate-form__media-video"
          >
            <video :src="previewUrl(file)" controls preload="metadata"></video>
            <AppButton
              variant="outline"
              size="sm"
              type="button"
              @click="removePendingFile(pendingVideos, index)"
            >
              حذف
            </AppButton>
          </article>
        </div>
        <p v-else class="admin-estate-form__media-empty">لا توجد فيديوهات مرفقة.</p>
      </div>

      <div class="admin-estate-form__media-block">
        <AppFileUpload
          accept="image/*"
          multiple
          hint="اسحب صور الإعلانات أو اضغط لاختيارها"
          @update:model-value="(files) => { if (Array.isArray(files)) pendingAds.push(...files) }"
        />
        <div v-if="pendingAds.length" class="admin-estate-form__media-grid">
          <article
            v-for="(file, index) in pendingAds"
            :key="`ad-${index}-${file.name}`"
            class="admin-estate-form__media-card"
            :class="{
              'admin-estate-form__media-card--primary': index === mainAdIndex,
              'admin-estate-form__media-card--drag-over': dragOverIndex === index,
            }"
            draggable="true"
            @dragstart="onDragStart(index)"
            @dragover="(e) => onDragOver(e, index)"
            @dragleave="onDragLeave"
            @drop="onDrop(pendingAds, index, mainAdIndex)"
            @dragend="clearDragState"
          >
            <img :src="previewUrl(file)" :alt="file.name" />
            <div class="admin-estate-form__media-card-actions">
              <AppButton
                v-if="index !== mainAdIndex"
                variant="outline"
                size="sm"
                type="button"
                @click="mainAdIndex = index"
              >
                رئيسي
              </AppButton>
              <AppButton
                variant="outline"
                size="sm"
                type="button"
                @click="removePendingFile(pendingAds, index, mainAdIndex)"
              >
                حذف
              </AppButton>
            </div>
          </article>
        </div>
        <p v-else class="admin-estate-form__media-empty">لا توجد صور إعلانات مرفقة.</p>
      </div>
    </div>

    <div class="admin-estate-form__actions">
      <AppButton type="submit" variant="primary" :disabled="loading">
        {{ loading ? 'جاري الحفظ...' : isCreate ? 'إنشاء العقار' : 'حفظ التعديلات' }}
      </AppButton>
    </div>
  </form>
</template>
