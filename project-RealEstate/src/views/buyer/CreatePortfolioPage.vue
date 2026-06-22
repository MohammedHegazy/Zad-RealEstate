<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppFormGroup from '@/components/ui/AppFormGroup.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import FormAlert from '@/components/ui/FormAlert.vue'
import { portfoliosService } from '@/api/portfolios.js'
import { getErrorMessage } from '@/api/errorHandler.js'

const router = useRouter()
const saving = ref(false)
const error = ref('')

const form = ref({
  name: '',
  description: '',
  target_budget: null,
  risk_level: 'moderate',
})

const RISK_OPTIONS = [
  { value: 'low', label: 'منخفض' },
  { value: 'moderate', label: 'متوسط' },
  { value: 'high', label: 'مرتفع' },
]

async function handleSubmit() {
  if (!form.value.name.trim()) return
  saving.value = true
  error.value = ''
  try {
    const payload = { ...form.value }
    if (payload.target_budget) payload.target_budget = Number(payload.target_budget)
    else delete payload.target_budget
    const { data } = await portfoliosService.create(payload)
    router.push(`/buyer/portfolios/${data.id}`)
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر إنشاء المحفظة.')
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="محفظة جديدة"
      description="أنشئ محفظة استثمارية جديدة لتتبّع عقاراتك."
    />

    <form @submit.prevent="handleSubmit" class="row g-3" style="max-width:600px;">
      <div class="col-12">
        <AppFormGroup label="اسم المحفظة" required>
          <AppInput v-model="form.name" placeholder="مثلاً: محفظة العقارات السكنية" />
        </AppFormGroup>
      </div>

      <div class="col-12">
        <AppFormGroup label="الوصف">
          <AppTextarea v-model="form.description" rows="3" placeholder="وصف مختصر للمحفظة وأهدافها..." />
        </AppFormGroup>
      </div>

      <div class="col-md-6">
        <AppFormGroup label="الميزانية المستهدفة">
          <AppInput v-model="form.target_budget" type="number" min="0" placeholder="مبلغ التقريب" />
        </AppFormGroup>
      </div>

      <div class="col-md-6">
        <AppFormGroup label="مستوى المخاطرة">
          <AppSelect v-model="form.risk_level" :options="RISK_OPTIONS" />
        </AppFormGroup>
      </div>

      <FormAlert v-if="error" :message="error" variant="error" />

      <div class="col-12 d-flex gap-2">
        <AppButton type="submit" variant="primary" :loading="saving">
          إنشاء المحفظة
        </AppButton>
        <AppButton to="/buyer/portfolios" variant="outline">
          إلغاء
        </AppButton>
      </div>
    </form>
  </div>
</template>
