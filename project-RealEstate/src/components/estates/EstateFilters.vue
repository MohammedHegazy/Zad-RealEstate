<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'

import AppButton from '@/components/ui/AppButton.vue'
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
  <form class="estate-filters-toolbar" @submit.prevent="handleApply">
    <div class="estate-filters-toolbar__row">
      <div class="estate-filters-toolbar__group estate-filters-toolbar__group--search">
        <AppSearchInput
          v-model="local.search"
          placeholder="اسم أو وصف العقار"
        />
      </div>

      <div class="estate-filters-toolbar__group">
        <i class="bi bi-geo-alt estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.cities_id" :options="cityOptions" />
      </div>

      <div v-if="places.length" class="estate-filters-toolbar__group">
        <i class="bi bi-pin-map estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.places_id" :options="placeOptions" />
      </div>

      <div class="estate-filters-toolbar__group">
        <i class="bi bi-grid estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.type_text" :options="ESTATE_TYPE_OPTIONS" />
      </div>

      <div class="estate-filters-toolbar__group">
        <i class="bi bi-house estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.kind_text" :options="ESTATE_KIND_OPTIONS" />
      </div>

      <div class="estate-filters-toolbar__group">
        <i class="bi bi-tag estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.listing_type" :options="LISTING_TYPE_OPTIONS" />
      </div>

      <div class="estate-filters-toolbar__group">
        <i class="bi bi-lamp estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.is_furnished" :options="FURNISHED_OPTIONS" />
      </div>

      <div class="estate-filters-toolbar__group estate-filters-toolbar__group--price">
        <AppInput v-model="local.min_price" type="number" placeholder="أقل سعر" icon="bi-currency-exchange" />
      </div>

      <div class="estate-filters-toolbar__group estate-filters-toolbar__group--price">
        <AppInput v-model="local.max_price" type="number" placeholder="أعلى سعر" icon="bi-currency-exchange" />
      </div>

      <div class="estate-filters-toolbar__group estate-filters-toolbar__group--bedrooms">
        <AppInput v-model="local.min_bedrooms" type="number" placeholder="الغرف" icon="bi-door-open" />
      </div>

      <div class="estate-filters-toolbar__group">
        <i class="bi bi-sort-down estate-filters-toolbar__icon"></i>
        <AppSelect v-model="local.sort" :options="ESTATE_SORT_OPTIONS" />
      </div>

      <div class="estate-filters-toolbar__actions">
        <AppButton type="submit" variant="primary" size="sm">
          <i class="bi bi-funnel" />
        </AppButton>
        <AppButton type="button" variant="outline" size="sm" @click="handleReset">
          <i class="bi bi-x-lg" />
        </AppButton>
      </div>
    </div>
  </form>
</template>
