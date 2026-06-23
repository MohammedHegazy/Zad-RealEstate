<script setup>
import { onMounted, ref } from 'vue'

import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { agentService } from '@/api/agent.js'
import { getErrorMessage } from '@/api/errorHandler.js'
const loading = ref(false)
const error = ref(null)
const agent = ref(null)

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const { data } = await agentService.myProfile()
    agent.value = data
  } catch (err) {
    error.value = getErrorMessage(err, 'تعذّر تحميل الملف الشخصي.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchProfile)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="لوحة الوسيط"
      description="نظرة عامة على ملفك الشخصي وإحصائياتك."
    >
      <template #actions>
        <AppButton variant="outline" size="sm" @click="fetchProfile">
          <i class="bi bi-arrow-clockwise"></i>
          تحديث
        </AppButton>
      </template>
    </AdminPageHeader>

    <LoadingSpinner v-if="loading" />
    <ErrorAlert v-else-if="error" :message="error" @retry="fetchProfile" />

    <template v-else-if="agent">
      <AdminStatsSection title="الملف الشخصي">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="rounded-circle overflow-hidden flex-shrink-0" style="width:80px;height:80px;">
            <img
              v-if="agent.profile_image_url"
              :src="agent.profile_image_url"
              :alt="agent.user?.fname"
              class="w-100 h-100 object-fit-cover"
            />
            <i v-else class="bi bi-person-circle d-flex align-items-center justify-content-center w-100 h-100" style="font-size:3rem;color:var(--color-text-muted);"></i>
          </div>
          <div>
            <h5 class="mb-1">{{ agent.user?.fname }} {{ agent.user?.lname }}</h5>
            <p class="mb-0 text-muted small">{{ agent.user?.email }}</p>
            <p v-if="agent.company" class="mb-0 text-muted small">
              <i class="bi bi-building"></i>
              {{ agent.company.company_name }}
            </p>
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection title="الإحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard
            v-if="agent.views !== undefined"
            label="المشاهدات"
            :value="agent.views"
            icon="bi-eye"
          />
          <AdminStatCard
            v-if="agent.shares !== undefined"
            label="المشاركات"
            :value="agent.shares"
            icon="bi-share"
          />
          <AdminStatCard
            v-if="agent.average_rating"
            label="التقييم"
            :value="agent.average_rating"
            icon="bi-star"
            variant="warning"
          />
          <AdminStatCard
            v-if="agent.ratings_count !== undefined"
            label="عدد التقييمات"
            :value="agent.ratings_count"
            icon="bi-chat-square-text"
          />
          <AdminStatCard
            v-if="agent.trust_score"
            label="درجة الثقة"
            :value="agent.trust_score"
            icon="bi-shield-check"
            variant="success"
          />
        </div>
      </AdminStatsSection>

      <div class="d-flex gap-2 mt-4 flex-wrap">
        <AppButton variant="primary" size="sm" to="/agent/profile">
          <i class="bi bi-pencil"></i>
          تعديل الملف الشخصي
        </AppButton>
        <AppButton variant="outline" size="sm" to="/agent/estates">
          <i class="bi bi-buildings"></i>
          العقارات
        </AppButton>
        <AppButton variant="outline" size="sm" to="/agent/social-links">
          <i class="bi bi-share"></i>
          روابط التواصل
        </AppButton>
      </div>
    </template>
  </div>
</template>
