<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'

import MapLocationPicker from '@/components/map/MapLocationPicker.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import { adminCitiesService } from '@/api/admin/locations.js'
import { useFormErrors } from '@/composables/useFormErrors.js'
import { hasMapCoordinates } from '@/utils/map.js'

const props = defineProps({
  place: {
    type: Object,
    default: null,
  },
  isCreate: {
    type: Boolean,
    default: false,
  },
  presetCityId: {
    type: String,
    default: '',
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

const form = reactive({
  cities_id: '',
  name: '',
  latitude: '',
  longitude: '',
})

const { generalError, clearErrors, fieldError, hasError, handleSubmitError } = useFormErrors()

const cityOptions = computed(() => [
  { value: '', label: 'اختر المدينة' },
  ...cities.value.map((city) => ({
    value: String(city.id),
    label: city.name,
  })),
])

const mapFocus = computed(() => {
  const city = cities.value.find((item) => String(item.id) === form.cities_id)
  if (!city || !hasMapCoordinates(city)) return null

  return {
    latitude: Number(city.latitude),
    longitude: Number(city.longitude),
    zoom: 12,
  }
})

const locationError = computed(
  () => fieldError('latitude') || fieldError('longitude'),
)

const hasLocationError = computed(
  () => hasError('latitude') || hasError('longitude'),
)

function resetFormFromPlace(place) {
  if (!place) {
    Object.assign(form, {
      cities_id: props.presetCityId || '',
      name: '',
      latitude: '',
      longitude: '',
    })
    return
  }

  Object.assign(form, {
    cities_id: place.cities_id ? String(place.cities_id) : '',
    name: place.name ?? '',
    latitude: place.latitude ?? '',
    longitude: place.longitude ?? '',
  })
}

watch(
  () => [props.place, props.presetCityId],
  () => resetFormFromPlace(props.place),
  { immediate: true },
)

watch(
  () => props.errorMessage,
  (message) => {
    if (message) generalError.value = message
  },
)

onMounted(async () => {
  try {
    const { data } = await adminCitiesService.list({ per_page: 100 })
    cities.value = data ?? []
  } catch {
    cities.value = []
  }
})

function handleSubmit() {
  clearErrors()

  const payload = {
    cities_id: Number(form.cities_id),
    name: form.name,
  }

  if (hasMapCoordinates(form)) {
    payload.latitude = Number(form.latitude)
    payload.longitude = Number(form.longitude)
  }

  emit('submit', payload)
}

defineExpose({ handleSubmitError })
</script>

<template>
  <form class="admin-place-form" novalidate @submit.prevent="handleSubmit">
    <FormAlert v-if="generalError || errorMessage" :message="generalError || errorMessage" />

    <div class="admin-place-form__section">
      <h4 class="admin-place-form__section-title">بيانات المنطقة</h4>
      <div class="row g-3">
        <div class="col-md-6">
          <AppFormGroup label="المدينة" label-for="place-city" :error="fieldError('cities_id')" required>
            <AppSelect
              id="place-city"
              v-model="form.cities_id"
              :options="cityOptions"
              :has-error="hasError('cities_id')"
            />
          </AppFormGroup>
        </div>
        <div class="col-md-6">
          <AppFormGroup label="اسم المنطقة" label-for="place-name" :error="fieldError('name')" required>
            <AppInput
              id="place-name"
              v-model="form.name"
              :has-error="hasError('name')"
            />
          </AppFormGroup>
        </div>
        <div class="col-12">
          <AppFormGroup
            label="موقع المنطقة على الخريطة"
            :error="locationError"
            hint="اختر المدينة أولاً لتقريب الخريطة، ثم انقر لتحديد موقع المنطقة."
          >
            <MapLocationPicker
              v-model:latitude="form.latitude"
              v-model:longitude="form.longitude"
              :focus="mapFocus"
              :has-error="hasLocationError"
              height="320px"
            />
          </AppFormGroup>
        </div>
      </div>
    </div>

    <div class="admin-place-form__actions">
      <AppButton type="submit" variant="primary" :disabled="loading">
        {{ loading ? 'جاري الحفظ...' : isCreate ? 'إنشاء المنطقة' : 'حفظ التعديلات' }}
      </AppButton>
    </div>
  </form>
</template>
