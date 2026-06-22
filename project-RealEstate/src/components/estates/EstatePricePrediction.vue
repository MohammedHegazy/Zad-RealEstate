<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { pricePredictionsService } from '@/api/pricePredictions.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { formatPrice } from '@/composables/useFormatters.js'
import { useAuthStore } from '@/stores/auth.js'

const props = defineProps({
  estate: {
    type: Object,
    required: true,
  },
})

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const error = ref('')
const prediction = ref(null)

const INSIGHT_LABELS = {
  aligned_with_model: 'السعر المعروض متوافق مع تقدير النموذج',
  listed_below_prediction: 'السعر المعروض أقل من التقدير — قد يكون عرضاً جيداً',
  listed_above_prediction: 'السعر المعروض أعلى من التقدير',
}

function insightLabel(insight) {
  return INSIGHT_LABELS[insight] ?? null
}

function formatDifference(value) {
  if (value === null || value === undefined) return null
  const num = Number(value)
  if (Number.isNaN(num)) return null
  const prefix = num > 0 ? '+' : ''
  return `${prefix}${formatPrice(num)}`
}

async function fetchPrediction() {
  if (!auth.isAuthenticated()) {
    router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
    return
  }

  loading.value = true
  error.value = ''
  prediction.value = null

  try {
    const response = await pricePredictionsService.forEstate(props.estate.id)
    prediction.value = response.data ?? null
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر حساب السعر المتوقع. تأكد من تشغيل خدمة التعلم الآلي.')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="estate-price-prediction">
    <div class="estate-price-prediction__header">
      <h3 class="estate-price-prediction__title">
        <i class="bi bi-robot"></i>
        تقدير السعر بالذكاء الاصطناعي
      </h3>
      <p class="estate-price-prediction__hint">
        تقدير تقريبي بناءً على مواصفات العقار والموقع — للمقارنة مع السعر المعروض.
      </p>
    </div>

    <FormAlert v-if="error" :message="error" variant="error" />

    <LoadingSpinner v-if="loading" />

    <template v-else-if="prediction">
      <div class="estate-price-prediction__result">
        <div class="estate-price-prediction__metric estate-price-prediction__metric--primary">
          <span class="estate-price-prediction__label">السعر المتوقع</span>
          <strong class="estate-price-prediction__value">
            {{ formatPrice(prediction.predicted_price) }}
          </strong>
        </div>

        <div
          v-if="prediction.listed_price"
          class="estate-price-prediction__metric"
        >
          <span class="estate-price-prediction__label">السعر المعروض</span>
          <strong class="estate-price-prediction__value">
            {{ formatPrice(prediction.listed_price) }}
          </strong>
        </div>

        <div
          v-if="prediction.price_difference !== null"
          class="estate-price-prediction__metric"
        >
          <span class="estate-price-prediction__label">الفرق</span>
          <strong
            class="estate-price-prediction__value"
            :class="{
              'estate-price-prediction__value--up': prediction.price_difference > 0,
              'estate-price-prediction__value--down': prediction.price_difference < 0,
            }"
          >
            {{ formatDifference(prediction.price_difference) }}
            <span v-if="prediction.price_difference_percent !== null">
              ({{ prediction.price_difference_percent > 0 ? '+' : '' }}{{ prediction.price_difference_percent }}%)
            </span>
          </strong>
        </div>
      </div>

      <p
        v-if="insightLabel(prediction.valuation_insight)"
        class="estate-price-prediction__insight"
      >
        <i class="bi bi-lightbulb"></i>
        {{ insightLabel(prediction.valuation_insight) }}
      </p>

      <AppButton variant="ghost" @click="fetchPrediction">
        <i class="bi bi-arrow-repeat"></i>
        إعادة الحساب
      </AppButton>
    </template>

    <AppButton v-else variant="outline" :disabled="loading" @click="fetchPrediction">
      <i class="bi bi-calculator"></i>
      احسب السعر المتوقع
    </AppButton>
  </section>
</template>
