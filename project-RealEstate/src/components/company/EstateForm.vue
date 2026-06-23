<script setup>
import { computed, onMounted, ref } from 'vue'

import MapLocationPicker from '@/components/map/MapLocationPicker.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppFileUpload from '@/components/ui/AppFileUpload.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import { ADMIN_ESTATE_TYPE_OPTIONS, ADMIN_ESTATE_KIND_OPTIONS, ESTATE_BUILD_STATE_OPTIONS, ESTATE_RENT_KIND_OPTIONS, SOCIAL_PLATFORM_OPTIONS, getPlatformStyle } from '@/config/admin.js'
import { COUNTRY_CODES } from '@/config/auth.js'
import { citiesService } from '@/api/cities.js'
import { hasMapCoordinates } from '@/utils/map.js'
import { useFormErrors } from '@/composables/useFormErrors.js'

const props = defineProps({
  initialData: {
    type: Object,
    default: null,
  },
  places: {
    type: Array,
    default: () => [],
  },
  saving: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit', 'cancel'])

const { fieldError, hasError, clearErrors, handleSubmitError } = useFormErrors()

const cities = ref([])

const form = ref({
  name: props.initialData?.name ?? '',
  description: props.initialData?.description ?? '',
  places_id: props.initialData?.places_id ?? '',
  cities_id: props.initialData?.place?.cities_id ? String(props.initialData.place.cities_id) : '',
  latitude: props.initialData?.latitude ?? '',
  longitude: props.initialData?.longitude ?? '',
  phone: props.initialData?.phone ?? '',
  country_code_phone: props.initialData?.country_code_phone ?? '+963',
  price: props.initialData?.price ?? '',
  price_of_meter: props.initialData?.price_of_meter ?? '',
  space_of_estate: props.initialData?.space_of_estate ?? '',
  monthly_rent: props.initialData?.monthly_rent ?? '',
  type_text: props.initialData?.type_text ?? 'سكني',
  kind_text: props.initialData?.kind_text ?? 'شقة',
  num_of_bedrooms: props.initialData?.num_of_bedrooms ?? '',
  num_of_bathrooms: props.initialData?.num_of_bathrooms ?? '',
  num_of_livingrooms: props.initialData?.num_of_livingrooms ?? '',
  num_of_receptions: props.initialData?.num_of_receptions ?? '',
  num_of_kitchens: props.initialData?.num_of_kitchens ?? '',
  num_of_balconies: props.initialData?.num_of_balconies ?? '',
  floor: props.initialData?.floor ?? '',
  is_furnished: props.initialData?.is_furnished ?? false,
  state_of_build: props.initialData?.state_of_build ?? '',
  date_of_build: props.initialData?.date_of_build ?? '',
  real_number: props.initialData?.real_number ?? '',
  annual_expenses: props.initialData?.annual_expenses ?? '',
  maintenance_cost: props.initialData?.maintenance_cost ?? '',
  annual_property_tax: props.initialData?.annual_property_tax ?? '',
  annual_hoa_or_service: props.initialData?.annual_hoa_or_service ?? '',
  occupancy_rate: props.initialData?.occupancy_rate ?? '100',
  rent_kind: props.initialData?.rent_kind ?? '',
  rent_description: props.initialData?.rent_description ?? '',
  facebook: props.initialData?.social_links?.find((l) => l.platform === 'facebook')?.url ?? '',
  instagram: props.initialData?.social_links?.find((l) => l.platform === 'instagram')?.url ?? '',
})



const cityOptions = computed(() =>
  cities.value.map((c) => ({ value: String(c.id), label: c.name }))
)

const filteredPlaces = computed(() => {
  if (!form.value.cities_id) return props.places
  return props.places.filter((p) => String(p.cities_id) === form.value.cities_id)
})

const placesOptions = computed(() =>
  filteredPlaces.value.map((p) => ({ value: p.id, label: p.name }))
)

const mapFocus = computed(() => {
  const place = props.places.find((p) => String(p.id) === form.value.places_id)
  if (place && hasMapCoordinates(place)) {
    return { latitude: Number(place.latitude), longitude: Number(place.longitude), zoom: 14 }
  }
  const city = cities.value.find((c) => String(c.id) === form.value.cities_id)
  if (city && hasMapCoordinates(city)) {
    return { latitude: Number(city.latitude), longitude: Number(city.longitude), zoom: 12 }
  }
  return null
})

const locationError = computed(
  () => fieldError('latitude') || fieldError('longitude'),
)

const SUBMIT_SKIP_KEYS = new Set(['cities_id'])

const socialLinks = ref(
  props.initialData?.social_links?.map((l) => ({ platform: l.platform, url: l.url })) ?? [],
)
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

function handleSubmit() {
  clearErrors()
  const formData = new FormData()
  Object.entries(form.value).forEach(([key, value]) => {
    if (SUBMIT_SKIP_KEYS.has(key)) return
    if (value !== '' && value !== null && value !== undefined) {
      if (typeof value === 'boolean') {
        formData.append(key, value ? '1' : '0')
      } else {
        formData.append(key, value)
      }
    }
  })
  socialLinks.value.forEach((link, i) => {
    if (!link.platform || !link.url) return
    formData.append(`links[${i}][platform]`, link.platform)
    formData.append(`links[${i}][url]`, link.url)
  })
  if (!props.initialData) {
    pendingImages.value.forEach((file) => formData.append('images[]', file))
    if (pendingImages.value.length) {
      formData.append('primary_image_index', primaryImageIndex.value)
    }
    pendingVideos.value.forEach((file) => formData.append('videos[]', file))
    pendingAds.value.forEach((file) => formData.append('ads[]', file))
    if (pendingAds.value.length) {
      formData.append('main_ad_index', mainAdIndex.value)
    }
  }
  emit('submit', formData)
}

onMounted(async () => {
  try {
    const { data } = await citiesService.list({ per_page: 100 })
    cities.value = data ?? []
  } catch {
    cities.value = []
  }
})

defineExpose({ handleSubmitError })
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <div class="admin-company-form__section">
      <h3 class="admin-company-form__section-title">البيانات الأساسية</h3>

      <div class="row">
        <div class="col-md-6">
          <AppFormGroup label="اسم العقار" required :error="fieldError('name')">
            <AppInput v-model="form.name" placeholder="اسم العقار" :hasError="hasError('name')" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="الفئة" required :error="fieldError('type_text')">
            <AppSelect v-model="form.type_text" :options="ADMIN_ESTATE_TYPE_OPTIONS" :hasError="hasError('type_text')" />
          </AppFormGroup>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <AppFormGroup label="النوع" required :error="fieldError('kind_text')">
            <AppSelect v-model="form.kind_text" :options="ADMIN_ESTATE_KIND_OPTIONS" :hasError="hasError('kind_text')" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="حالة البناء" :error="fieldError('state_of_build')">
            <AppSelect v-model="form.state_of_build" :options="ESTATE_BUILD_STATE_OPTIONS" placeholder="اختر" :hasError="hasError('state_of_build')" />
          </AppFormGroup>
        </div>
      </div>

      <AppFormGroup label="الوصف" required :error="fieldError('description')">
        <AppTextarea v-model="form.description" rows="4" placeholder="وصف العقار" :hasError="hasError('description')" />
      </AppFormGroup>

      <div class="row">
        <div class="col-md-6">
          <AppCheckbox v-model="form.is_furnished" label="مفروش" />
        </div>
      </div>
    </div>

    <div class="admin-company-form__section">
      <h3 class="admin-company-form__section-title">الموقع</h3>

      <div class="row">
        <div class="col-md-6">
          <AppFormGroup label="المدينة" label-for="estate-city">
            <AppSelect id="estate-city" v-model="form.cities_id" :options="cityOptions" placeholder="اختر المدينة" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="المنطقة" required :error="fieldError('places_id')">
            <AppSelect v-model="form.places_id" :options="placesOptions" placeholder="اختر المنطقة" :hasError="hasError('places_id')" />
          </AppFormGroup>
        </div>
      </div>

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
          :has-error="hasError('latitude') || hasError('longitude')"
          height="320px"
        />
      </AppFormGroup>
    </div>

    <div class="admin-company-form__section">
      <h3 class="admin-company-form__section-title">التسعير والمساحة</h3>

      <div class="row">
        <div class="col-md-4">
          <AppFormGroup label="السعر" required :error="fieldError('price')">
            <AppInput v-model="form.price" type="number" placeholder="0" :hasError="hasError('price')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="سعر المتر" :error="fieldError('price_of_meter')">
            <AppInput v-model="form.price_of_meter" type="number" placeholder="0" :hasError="hasError('price_of_meter')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="المساحة (م²)" :error="fieldError('space_of_estate')">
            <AppInput v-model="form.space_of_estate" type="number" placeholder="0" :hasError="hasError('space_of_estate')" />
          </AppFormGroup>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <AppFormGroup label="الإيجار الشهري" :error="fieldError('monthly_rent')">
            <AppInput v-model="form.monthly_rent" type="number" placeholder="0" :hasError="hasError('monthly_rent')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="نوع الإيجار" :error="fieldError('rent_kind')">
            <AppSelect v-model="form.rent_kind" :options="ESTATE_RENT_KIND_OPTIONS" :hasError="hasError('rent_kind')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="حالة البناء" :error="fieldError('state_of_build')">
            <AppSelect v-model="form.state_of_build" :options="ESTATE_BUILD_STATE_OPTIONS" placeholder="اختر" :hasError="hasError('state_of_build')" />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-company-form__section">
      <h3 class="admin-company-form__section-title">مؤشرات الاستثمار</h3>
      <p class="text-muted small">تُحسب تلقائياً عند الحفظ.</p>

      <div class="row">
        <div class="col-md-4">
          <AppFormGroup label="المصاريف السنوية" :error="fieldError('annual_expenses')">
            <AppInput v-model="form.annual_expenses" type="number" placeholder="0" :hasError="hasError('annual_expenses')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="تكاليف الصيانة" :error="fieldError('maintenance_cost')">
            <AppInput v-model="form.maintenance_cost" type="number" placeholder="0" :hasError="hasError('maintenance_cost')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="الضريبة العقارية" :error="fieldError('annual_property_tax')">
            <AppInput v-model="form.annual_property_tax" type="number" placeholder="0" :hasError="hasError('annual_property_tax')" />
          </AppFormGroup>
        </div>
      </div>

      <div class="row">
        <div class="col-md-4">
          <AppFormGroup label="رسوم الخدمات السنوية" :error="fieldError('annual_hoa_or_service')">
            <AppInput v-model="form.annual_hoa_or_service" type="number" placeholder="0" :hasError="hasError('annual_hoa_or_service')" />
          </AppFormGroup>
        </div>
        <div class="col-md-4">
          <AppFormGroup label="نسبة الإشغال (%)" :error="fieldError('occupancy_rate')">
            <AppInput v-model="form.occupancy_rate" type="number" min="0" max="100" placeholder="95" :hasError="hasError('occupancy_rate')" />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-company-form__section">
      <h3 class="admin-company-form__section-title">الغرف والتفاصيل</h3>

      <div class="row">
        <div class="col-md-3">
          <AppFormGroup label="غرف النوم" :error="fieldError('num_of_bedrooms')">
            <AppInput v-model="form.num_of_bedrooms" type="number" min="0" :hasError="hasError('num_of_bedrooms')" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="غرف المعيشة" :error="fieldError('num_of_livingrooms')">
            <AppInput v-model="form.num_of_livingrooms" type="number" min="0" :hasError="hasError('num_of_livingrooms')" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الصالات" :error="fieldError('num_of_receptions')">
            <AppInput v-model="form.num_of_receptions" type="number" min="0" :hasError="hasError('num_of_receptions')" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الحمامات" :error="fieldError('num_of_bathrooms')">
            <AppInput v-model="form.num_of_bathrooms" type="number" min="0" :hasError="hasError('num_of_bathrooms')" />
          </AppFormGroup>
        </div>
      </div>

      <div class="row">
        <div class="col-md-3">
          <AppFormGroup label="المطابخ" :error="fieldError('num_of_kitchens')">
            <AppInput v-model="form.num_of_kitchens" type="number" min="0" :hasError="hasError('num_of_kitchens')" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="البلكونات" :error="fieldError('num_of_balconies')">
            <AppInput v-model="form.num_of_balconies" type="number" min="0" :hasError="hasError('num_of_balconies')" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="الطابق" :error="fieldError('floor')">
            <AppInput v-model="form.floor" type="number" min="0" :hasError="hasError('floor')" />
          </AppFormGroup>
        </div>
        <div class="col-md-3">
          <AppFormGroup label="سنة البناء" :error="fieldError('date_of_build')">
            <AppInput v-model="form.date_of_build" placeholder="مثال: 2020" :hasError="hasError('date_of_build')" />
          </AppFormGroup>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6">
          <AppFormGroup label="الرقم العقاري" :error="fieldError('real_number')">
            <AppInput v-model="form.real_number" placeholder="رقم القيد" :hasError="hasError('real_number')" />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="وصف الإيجار" :error="fieldError('rent_description')">
            <AppTextarea v-model="form.rent_description" rows="2" placeholder="تفاصيل الإيجار" :hasError="hasError('rent_description')" />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-company-form__section">
      <h3 class="admin-company-form__section-title">معلومات الاتصال</h3>

      <div class="row">
        <div class="col-md-3">
          <AppFormGroup label="رمز الدولة" required :error="fieldError('country_code_phone')">
            <AppSelect v-model="form.country_code_phone" :options="COUNTRY_CODES" :hasError="hasError('country_code_phone')" />
          </AppFormGroup>
        </div>
        <div class="col-md-5">
          <AppFormGroup label="رقم الهاتف" required :error="fieldError('phone')">
            <AppInput v-model="form.phone" placeholder="912345678" :hasError="hasError('phone')" />
          </AppFormGroup>
        </div>
      </div>

      <div class="mb-3">
        <label class="app-form-group__label">روابط التواصل الاجتماعي</label>
        <p class="text-muted small">اختياري — أضف روابط العقار على منصات التواصل.</p>

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



    <div v-if="!initialData" class="admin-estate-form__section">
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

    <div class="admin-company-form__actions">
      <AppButton type="submit" variant="primary" :loading="saving">
        {{ initialData ? 'حفظ التغييرات' : 'إضافة عقار' }}
      </AppButton>
      <AppButton variant="outline" @click="emit('cancel')">
        إلغاء
      </AppButton>
    </div>
  </form>
</template>
