<script setup>
import { computed } from 'vue'

import { formatArea, formatPropertyType } from '@/composables/useFormatters.js'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
})

const specs = computed(() => {
  const e = props.estate
  const items = []

  if (e.num_of_bedrooms) items.push({ icon: 'bi-door-open', label: 'غرف النوم', value: e.num_of_bedrooms })
  if (e.num_of_bathrooms) items.push({ icon: 'bi-droplet', label: 'الحمامات', value: e.num_of_bathrooms })
  if (e.num_of_livingrooms) items.push({ icon: 'bi-sofa', label: 'صالات', value: e.num_of_livingrooms })
  if (e.num_of_kitchens) items.push({ icon: 'bi-cup-hot', label: 'مطابخ', value: e.num_of_kitchens })
  if (e.num_of_balconies) items.push({ icon: 'bi-columns', label: 'شرفات', value: e.num_of_balconies })
  if (formatArea(e.space_of_estate)) items.push({ icon: 'bi-bounding-box', label: 'المساحة', value: formatArea(e.space_of_estate) })
  if (e.floor) items.push({ icon: 'bi-layers', label: 'الطابق', value: e.floor })
  if (e.date_of_build) items.push({ icon: 'bi-calendar', label: 'سنة البناء', value: e.date_of_build })
  if (e.state_of_build) items.push({ icon: 'bi-hammer', label: 'حالة البناء', value: e.state_of_build })
  if (e.type_text) items.push({ icon: 'bi-building', label: 'الفئة', value: formatPropertyType(e.type_text) })
  if (e.kind_text) items.push({ icon: 'bi-house', label: 'النوع', value: formatPropertyType(e.kind_text) })
  if (e.is_furnished) items.push({ icon: 'bi-lamp', label: 'مفروش', value: 'نعم' })

  return items
})
</script>

<template>
  <div class="estate-specs">
    <h3 class="estate-specs__title">مواصفات العقار</h3>
    <div class="estate-specs__grid">
      <div v-for="spec in specs" :key="spec.label" class="estate-specs__item">
        <i :class="['bi', spec.icon]"></i>
        <div>
          <span class="estate-specs__label">{{ spec.label }}</span>
          <strong class="estate-specs__value">{{ spec.value }}</strong>
        </div>
      </div>
    </div>
  </div>
</template>
