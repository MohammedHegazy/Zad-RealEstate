<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import DirectoryHero from '@/components/ui/DirectoryHero.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { citiesService } from '@/api/cities.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const route = useRoute()
const loading = ref(false)
const error = ref(null)
const city = ref(null)

async function fetchCity(id) {
  loading.value = true
  error.value = null
  try {
    const { data } = await citiesService.getById(id)
    city.value = data
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل المدينة.')
  } finally {
    loading.value = false
  }
}

onMounted(() => fetchCity(route.params.id))
watch(() => route.params.id, (id) => id && fetchCity(id))
</script>

<template>
  <div class="directory-page directory-page--detail">
    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" class="container" @retry="fetchCity(route.params.id)" />

    <template v-else-if="city">
      <DirectoryHero
        :image="city.image_url ?? ''"
        :breadcrumb-items="[
          { label: 'الرئيسية', to: '/' },
          { label: 'المدن', to: '/cities' },
          { label: city.name },
        ]"
        :title="city.name"
      >
        <template v-if="city.places_count">
          {{ city.places_count }} منطقة · {{ city.places?.length ?? 0 }} حي مسجّل
        </template>
      </DirectoryHero>

      <div class="container directory-page__body">
        <section class="directory-section">
          <h2>الأحياء والمناطق</h2>
          <div v-if="city.places?.length" class="directory-chips">
            <RouterLink
              v-for="place in city.places"
              :key="place.id"
              :to="`/places/${place.id}`"
              class="directory-chips__item"
            >
              {{ place.name }}
            </RouterLink>
          </div>
          <p v-else class="directory-section__empty">لا توجد مناطق مسجّلة في هذه المدينة.</p>
        </section>

        <div class="directory-actions">
          <AppButton :to="{ path: '/estates', query: { cities_id: city.id } }" variant="primary" size="lg">
            عقارات {{ city.name }}
          </AppButton>
          <AppButton :to="{ path: '/places', query: { cities_id: city.id } }" variant="outline" size="lg">
            كل المناطق
          </AppButton>
        </div>

        <CtaBanner
          title="الخطوة التالية: قارن العقارات"
          description="انتقل إلى قائمة العقارات مفلترة حسب هذه المدينة وابدأ المقارنة."
          :primary="{ label: `عقارات ${city.name}`, to: { path: '/estates', query: { cities_id: city.id } } }"
          :secondary="{ label: 'كل العقارات', to: '/estates' }"
        />
      </div>
    </template>
  </div>
</template>
