<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { placesService } from '@/api/places.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const route = useRoute()
const loading = ref(false)
const error = ref(null)
const place = ref(null)

async function fetchPlace(id) {
  loading.value = true
  error.value = null
  try {
    const { data } = await placesService.getById(id)
    place.value = data
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل المنطقة.')
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchPlace(route.params.id))
watch(() => route.params.id, (id) => id && fetchPlace(id))
</script>

<template>
  <div class="directory-page directory-page--detail">
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" class="container" @retry="fetchPlace(route.params.id)" />

    <template v-else-if="place">
      <div class="container directory-page__body">
        <Breadcrumbs
          :items="[
            { label: 'الرئيسية', to: '/' },
            { label: 'المناطق', to: '/places' },
            { label: place.name },
          ]"
        />

        <header class="directory-detail-header">
          <h1>{{ place.name }}</h1>
          <p>
            <i class="bi bi-geo-alt"></i>
            {{ place.city?.name }}
          </p>
        </header>

        <div class="directory-stats">
          <div v-if="place.active_estates_count" class="directory-stats__item">
            <strong>{{ place.active_estates_count }}</strong>
            <span>عقار نشط</span>
          </div>
          <div v-if="place.companies_count" class="directory-stats__item">
            <strong>{{ place.companies_count }}</strong>
            <span>شركة</span>
          </div>
        </div>

        <div class="directory-actions">
          <AppButton
            :to="{ path: '/estates', query: { places_id: place.id } }"
            variant="primary"
            size="lg"
          >
            عقارات {{ place.name }}
          </AppButton>
          <AppButton
            v-if="place.city"
            :to="`/cities/${place.city.id}`"
            variant="outline"
            size="lg"
          >
            عودة إلى {{ place.city.name }}
          </AppButton>
        </div>

        <CtaBanner
          title="وجدت الحي المناسب؟"
          description="استعرض العقارات في هذه المنطقة وقارن الأسعار والمواصفات."
          :primary="{ label: 'عرض العقارات', to: { path: '/estates', query: { places_id: place.id } } }"
          :secondary="{ label: 'الوكلاء المعتمدون', to: '/agents' }"
        />
      </div>
    </template>
  </div>
</template>
