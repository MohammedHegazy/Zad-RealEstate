<script setup>
import { onMounted, ref } from 'vue'

import RecommendationCard from '@/components/cards/RecommendationCard.vue'
import AppButton from '@/components/ui/AppButton.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { recommendationsService } from '@/api/recommendations.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const loading = ref(false)
const refreshing = ref(false)
const error = ref('')
const items = ref([])
const pagination = ref(null)
const summary = ref(null)

async function fetchRecommendations(page = 1, refresh = false) {
  if (refresh) {
    refreshing.value = true
  } else {
    loading.value = true
  }

  error.value = ''

  try {
    const response = await recommendationsService.list({
      page,
      per_page: 12,
      ...(refresh ? { refresh: 1 } : {}),
    })

    items.value = response.data?.recommendations ?? []
    pagination.value = response.pagination ?? null
    summary.value = response.data?.match_summary ?? null
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل التوصيات.')
    items.value = []
    pagination.value = null
  } finally {
    loading.value = false
    refreshing.value = false
  }
}

function goToPage(page) {
  fetchRecommendations(page)
}

onMounted(() => fetchRecommendations(1, true))
</script>

<template>
  <div class="directory-page recommendations-page">
    <div class="container">
      <PageIntro
        overline="ذكاء اصطناعي"
        title="عقارات مقترحة لك"
        description="نحلّل عقاراتك المفضلة ونقارنها بباقي العقارات لنعرض الأقرب لذوقك."
        step="discover"
        icon="bi-stars"
      />

      <div v-if="summary" class="recommendations-page__summary">
        <p v-if="summary.message">{{ summary.message }}</p>
        <p v-if="summary.favorite_estates_count">
          <i class="bi bi-heart-fill"></i>
          مبني على {{ summary.favorite_estates_count }} عقار في المفضلة
        </p>
      </div>

      <div class="recommendations-page__actions">
        <AppButton
          variant="outline"
          :disabled="refreshing || loading"
          @click="fetchRecommendations(pagination?.current_page ?? 1, true)"
        >
          <i class="bi bi-arrow-repeat"></i>
          {{ refreshing ? 'جاري التحديث…' : 'تحديث التوصيات' }}
        </AppButton>
        <AppButton to="/favorites" variant="ghost">عرض المفضلة</AppButton>
      </div>

      <FormAlert
        v-if="summary && !summary.has_favorite_estates"
        message="أضف عقارات إلى المفضلة أولاً لنستطيع اقتراح عقارات مشابهة."
        variant="info"
      />

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="() => fetchRecommendations(1, true)" />

      <EmptyState
        v-else-if="!items.length"
        icon="bi-stars"
        title="لا توجد توصيات حالياً"
        message="أضف عقارات إلى المفضلة ثم اضغط تحديث التوصيات."
      >
        <AppButton to="/estates" variant="primary">تصفح العقارات</AppButton>
      </EmptyState>

      <template v-else>
        <div class="row g-4">
          <div
            v-for="item in items"
            :key="item.id"
            class="col-sm-6 col-lg-4 col-xl-3"
          >
            <RecommendationCard :recommendation="item" />
          </div>
        </div>

        <Pagination
          v-if="pagination"
          :current-page="pagination.current_page"
          :last-page="pagination.last_page"
          :total="pagination.total"
          @page-change="goToPage"
        />
      </template>

      <CtaBanner
        title="كلما زادت مفضلتك، تحسّنت التوصيات"
        description="احفظ العقارات التي تعجبك وسنعرض لك خيارات أقرب لاهتماماتك."
        :primary="{ label: 'تصفح العقارات', to: '/estates' }"
        :secondary="{ label: 'المفضلة', to: '/favorites' }"
      />
    </div>
  </div>
</template>
