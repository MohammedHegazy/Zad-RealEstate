<script setup>
import { computed, reactive, ref, watch } from 'vue'

import MapLocationPicker from '@/components/map/MapLocationPicker.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { hasMapCoordinates } from '@/utils/map.js'

const props = defineProps({
  city: {
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

const imagePreview = ref('')
const imageFile = ref(null)

const form = reactive({
  name: '',
  latitude: '',
  longitude: '',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

const locationError = computed(
  () => fieldError('latitude') || fieldError('longitude'),
)

const hasLocationError = computed(
  () => hasError('latitude') || hasError('longitude'),
)

function resetFormFromCity(city, preservePendingImage = false) {
  if (!preservePendingImage) {
    imageFile.value = null
    imagePreview.value = city?.image_url ?? ''
  }

  if (!city) {
    Object.assign(form, {
      name: '',
      latitude: '',
      longitude: '',
    })
    return
  }

  Object.assign(form, {
    name: city.name ?? '',
    latitude: city.latitude ?? '',
    longitude: city.longitude ?? '',
  })
}

function onImageChange(event) {
  const file = event.target.files?.[0]
  event.target.value = ''
  if (!file) return

  imageFile.value = file
  imagePreview.value = URL.createObjectURL(file)
}

watch(
  () => props.city,
  (city, previousCity) => {
    const preservePendingImage =
      Boolean(imageFile.value) && previousCity != null && city?.id === previousCity?.id
    resetFormFromCity(city, preservePendingImage)
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
    name: form.name,
  }

  if (hasMapCoordinates(form)) {
    payload.latitude = Number(form.latitude)
    payload.longitude = Number(form.longitude)
  }

  emit('submit', payload, {
    image: imageFile.value,
  })
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form class="admin-city-form" novalidate @submit.prevent="handleSubmit">
    <FormAlert v-if="generalError || errorMessage" :message="generalError || errorMessage" />

    <div class="admin-city-form__section">
      <h4 class="admin-city-form__section-title">البيانات الأساسية</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup label="اسم المدينة" label-for="city-name" :error="fieldError('name')" required>
            <AppInput
              id="city-name"
              v-model="form.name"
              :has-error="hasError('name')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="صورة المدينة" label-for="city-image" :error="fieldError('image')">
            <div class="admin-city-form__image-field">
              <div v-if="imagePreview" class="admin-city-form__image-preview">
                <img :src="imagePreview" alt="صورة المدينة" />
              </div>
              <label class="admin-city-form__file-label" for="city-image">
                <i class="bi bi-image"></i>
                اختيار صورة
              </label>
              <input
                id="city-image"
                type="file"
                accept="image/*"
                class="admin-city-form__file-input"
                @change="onImageChange"
              />
            </div>
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppFormGroup
            label="موقع المدينة على الخريطة"
            :error="locationError"
            hint="انقر على الخريطة لتحديد مركز المدينة."
          >
            <MapLocationPicker
              v-model:latitude="form.latitude"
              v-model:longitude="form.longitude"
              :has-error="hasLocationError"
              height="320px"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-city-form__actions">
      <AppButton type="submit" variant="primary" :disabled="loading">
        {{ loading ? 'جاري الحفظ...' : isCreate ? 'إنشاء المدينة' : 'حفظ التعديلات' }}
      </AppButton>
    </div>
  </form>
</template>
