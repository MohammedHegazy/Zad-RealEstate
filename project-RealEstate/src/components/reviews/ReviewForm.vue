<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import StarRating from '@/components/ui/StarRating.vue'
import StarRatingInput from '@/components/ui/StarRatingInput.vue'
import { reviewsService } from '@/api/reviews.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useAuthStore } from '@/stores/auth.js'

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value) => ['property', 'agent', 'company'].includes(value),
  },
  entityId: {
    type: [Number, String],
    required: true,
  },
  ownerUserId: {
    type: [Number, String],
    default: null,
  },
})

const emit = defineEmits(['submitted'])

const auth = useAuthStore()
const router = useRouter()

const loading = ref(false)
const submitting = ref(false)
const error = ref('')
const success = ref('')
const myReview = ref(null)
const editing = ref(false)

const form = reactive({
  rating: 0,
  review: '',
})

const STATUS_LABELS = {
  pending: 'قيد المراجعة',
  approved: 'معتمد',
  rejected: 'مرفوض',
}

const isOwner = computed(() => {
  if (!auth.user || props.ownerUserId == null) return false
  return Number(auth.user.id) === Number(props.ownerUserId)
})

const canSubmit = computed(() => auth.isAuthenticated() && !isOwner.value)

const showForm = computed(() => canSubmit.value && (!myReview.value || editing.value))

const statusLabel = computed(() => STATUS_LABELS[myReview.value?.status] ?? myReview.value?.status)

async function fetchMyReview() {
  if (!canSubmit.value) {
    myReview.value = null
    return
  }

  loading.value = true
  error.value = ''

  try {
    let response
    if (props.type === 'property') {
      response = await reviewsService.myPropertyReview(props.entityId)
    } else if (props.type === 'agent') {
      response = await reviewsService.myAgentReview(props.entityId)
    } else {
      response = await reviewsService.myCompanyReview(props.entityId)
    }

    myReview.value = response.data?.review ?? null
    editing.value = false

    if (myReview.value) {
      form.rating = myReview.value.rating
      form.review = myReview.value.review ?? ''
    } else {
      form.rating = 0
      form.review = ''
    }
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل تقييمك.')
    myReview.value = null
  } finally {
    loading.value = false
  }
}

function goToLogin() {
  router.push({ name: 'login', query: { redirect: router.currentRoute.value.fullPath } })
}

function startEdit() {
  if (!myReview.value) return
  form.rating = myReview.value.rating
  form.review = myReview.value.review ?? ''
  editing.value = true
  success.value = ''
  error.value = ''
}

function cancelEdit() {
  editing.value = false
  if (myReview.value) {
    form.rating = myReview.value.rating
    form.review = myReview.value.review ?? ''
  }
}

async function handleSubmit() {
  if (!canSubmit.value) return

  if (form.rating < 1) {
    error.value = 'يرجى اختيار تقييم من 1 إلى 5.'
    return
  }

  submitting.value = true
  error.value = ''
  success.value = ''

  const payload = {
    rating: form.rating,
    review: form.review.trim() || undefined,
  }

  try {
    let response

    if (myReview.value) {
      if (props.type === 'property') {
        response = await reviewsService.updatePropertyReview(myReview.value.id, payload)
      } else if (props.type === 'agent') {
        response = await reviewsService.updateAgentReview(myReview.value.id, payload)
      } else {
        response = await reviewsService.updateCompanyReview(myReview.value.id, payload)
      }
    } else if (props.type === 'property') {
      response = await reviewsService.submitPropertyReview(props.entityId, payload)
    } else if (props.type === 'agent') {
      response = await reviewsService.submitAgentReview(props.entityId, payload)
    } else {
      response = await reviewsService.submitCompanyReview(props.entityId, payload)
    }

    myReview.value = response.data ?? myReview.value
    editing.value = false
    success.value = myReview.value?.status === 'pending'
      ? 'تم إرسال تقييمك وهو قيد المراجعة.'
      : 'تم حفظ تقييمك بنجاح.'
    emit('submitted')
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر إرسال التقييم.')
  } finally {
    submitting.value = false
  }
}

onMounted(fetchMyReview)

watch(
  () => [props.entityId, props.type, auth.user?.id],
  () => fetchMyReview(),
)
</script>

<template>
  <div class="review-form">
    <h4 class="review-form__title">أضف تقييمك</h4>
    <p class="review-form__hint">يمكن لكل مستخدم إضافة تقييم واحد فقط.</p>

    <div v-if="!auth.isAuthenticated()" class="review-form__guest">
      <p>سجّل دخولك لإضافة تقييمك.</p>
      <AppButton variant="outline" size="sm" @click="goToLogin">تسجيل الدخول</AppButton>
    </div>

    <p v-else-if="isOwner" class="review-form__notice">
      لا يمكنك تقييم {{ type === 'property' ? 'عقارك' : type === 'agent' ? 'ملفك' : 'شركتك' }} الخاص.
    </p>

    <template v-else>
      <FormAlert v-if="error" :message="error" variant="error" />
      <FormAlert v-if="success" :message="success" variant="success" />

      <div v-if="loading" class="review-form__loading">جاري التحميل…</div>

      <div
        v-else-if="myReview && !editing"
        class="review-form__existing"
      >
        <div class="review-form__existing-header">
          <strong>تقييمك</strong>
          <span class="review-form__status" :class="`review-form__status--${myReview.status}`">
            {{ statusLabel }}
          </span>
        </div>
        <StarRating :rating="myReview.rating" size="sm" />
        <p v-if="myReview.review" class="review-form__existing-text">{{ myReview.review }}</p>
        <AppButton variant="ghost" size="sm" @click="startEdit">تعديل التقييم</AppButton>
      </div>

      <form v-else-if="showForm" class="review-form__fields" @submit.prevent="handleSubmit">
        <AppFormGroup label="التقييم" required>
          <StarRatingInput v-model="form.rating" :disabled="submitting" />
        </AppFormGroup>

        <AppFormGroup label="تعليقك (اختياري)" label-for="review-text">
          <AppTextarea
            id="review-text"
            v-model="form.review"
            rows="4"
            placeholder="شارك تجربتك مع الآخرين..."
            :disabled="submitting"
          />
        </AppFormGroup>

        <div class="review-form__actions">
          <AppButton type="submit" variant="primary" :disabled="submitting">
            {{ submitting ? 'جاري الإرسال…' : myReview ? 'حفظ التعديل' : 'إرسال التقييم' }}
          </AppButton>
          <AppButton
            v-if="myReview && editing"
            type="button"
            variant="ghost"
            :disabled="submitting"
            @click="cancelEdit"
          >
            إلغاء
          </AppButton>
        </div>
      </form>
    </template>
  </div>
</template>
