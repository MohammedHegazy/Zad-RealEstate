<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSearchInput from '@/components/ui/AppSearchInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import { citiesService } from '@/api/cities.js'
import {
  ESTATE_KIND_OPTIONS,
  ESTATE_SORT_OPTIONS,
  ESTATE_TYPE_OPTIONS,
  FURNISHED_OPTIONS,
  LISTING_TYPE_OPTIONS,
} from '@/config/estates.js'

const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['apply', 'reset'])

const cities = ref([])
const places = ref([])

const local = reactive({ ...props.modelValue })

watch(
  () => props.modelValue,
  (value) => Object.assign(local, value),
  { deep: true },
)

watch(
  () => local.cities_id,
  async (cityId) => {
    local.places_id = ''
    places.value = []

    if (!cityId) return

    try {
      const { data } = await citiesService.places(cityId, { per_page: 50 })
      places.value = data ?? []
    } catch {
      places.value = []
    }
  },
)

onMounted(async () => {
  try {
    const { data } = await citiesService.list({ per_page: 50 })
    cities.value = data ?? []
  } catch {
    cities.value = []
  }

  if (local.cities_id) {
    const { data } = await citiesService.places(local.cities_id, { per_page: 50 }).catch(() => ({
      data: [],
    }))
    places.value = data ?? []
  }
})

const cityOptions = computed(() => [
  { value: '', label: 'كل المدن' },
  ...cities.value.map((city) => ({
    value: String(city.id),
    label: city.name,
  })),
])

const placeOptions = computed(() => [
  { value: '', label: 'كل المناطق' },
  ...places.value.map((place) => ({
    value: String(place.id),
    label: place.name,
  })),
])

function handleApply() {
  emit('apply', { ...local })
}

function handleReset() {
  emit('reset')
}
</script>

<template>
  <aside class="estate-filters">
    <h3 class="estate-filters__title">
      <i class="bi bi-funnel"></i>
      تصفية النتائج
    </h3>

    <form class="estate-filters__form" @submit.prevent="handleApply">
      <AppFormGroup label="بحث" label-for="filter-search">
        <AppSearchInput
          id="filter-search"
          v-model="local.search"
          placeholder="اسم أو وصف العقار"
        />
      </AppFormGroup>

      <AppFormGroup label="المدينة" label-for="filter-city">
        <AppSelect id="filter-city" v-model="local.cities_id" :options="cityOptions" />
      </AppFormGroup>

      <AppFormGroup v-if="places.length" label="المنطقة" label-for="filter-place">
        <AppSelect id="filter-place" v-model="local.places_id" :options="placeOptions" />
      </AppFormGroup>

      <AppFormGroup label="الفئة" label-for="filter-type">
        <AppSelect id="filter-type" v-model="local.type_text" :options="ESTATE_TYPE_OPTIONS" />
      </AppFormGroup>

      <AppFormGroup label="النوع" label-for="filter-kind">
        <AppSelect id="filter-kind" v-model="local.kind_text" :options="ESTATE_KIND_OPTIONS" />
      </AppFormGroup>

      <AppFormGroup label="الغرض" label-for="filter-listing">
        <AppSelect id="filter-listing" v-model="local.listing_type" :options="LISTING_TYPE_OPTIONS" />
      </AppFormGroup>

      <AppFormGroup label="مفروش" label-for="filter-furnished">
        <AppSelect id="filter-furnished" v-model="local.is_furnished" :options="FURNISHED_OPTIONS" />
      </AppFormGroup>

      <div class="row g-2">
        <div class="col-6">
          <AppFormGroup label="أقل سعر" label-for="filter-min-price">
            <AppInput id="filter-min-price" v-model="local.min_price" type="number" placeholder="0" />
          </AppFormGroup>
        </div>
        <div class="col-6">
          <AppFormGroup label="أعلى سعر" label-for="filter-max-price">
            <AppInput id="filter-max-price" v-model="local.max_price" type="number" placeholder="∞" />
          </AppFormGroup>
        </div>
      </div>

      <AppFormGroup label="الحد الأدنى للغرف" label-for="filter-bedrooms">
        <AppInput id="filter-bedrooms" v-model="local.min_bedrooms" type="number" placeholder="0" />
      </AppFormGroup>

      <AppFormGroup label="الترتيب" label-for="filter-sort">
        <AppSelect id="filter-sort" v-model="local.sort" :options="ESTATE_SORT_OPTIONS" />
      </AppFormGroup>

      <div class="estate-filters__actions">
        <AppButton type="submit" variant="primary" block>تطبيق</AppButton>
        <AppButton type="button" variant="outline" block @click="handleReset">مسح الفلاتر</AppButton>
      </div>
    </form>
  </aside>
</template>
