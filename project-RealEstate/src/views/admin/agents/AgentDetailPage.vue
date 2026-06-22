<script setup>
import AdminAgentForm from '@/components/admin/AdminAgentForm.vue'
import AdminAgentSocialPanel from '@/components/admin/AdminAgentSocialPanel.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AdminStatCard from '@/components/admin/AdminStatCard.vue'
import AdminStatsSection from '@/components/admin/AdminStatsSection.vue'
import AppButton from '@/components/ui/AppButton.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import TableAction from '@/components/ui/TableAction.vue'
import { useAdminAgentDetail } from '@/composables/useAdminAgentDetail.js'
import { getAgentImage, getAgentName } from '@/utils/agent.js'
import { ref } from 'vue'
import { useConfirmStore } from '@/stores/confirm.js'

const {
  loading,
  saving,
  deleting,
  error,
  agent,
  isCreate,
  fetchAgent,
  saveAgent,
  removeAgent,
  refreshAgent,
} = useAdminAgentDetail()
const confirmStore = useConfirmStore()
const agentFormRef = ref(null)

async function handleSave(payload, files) {
  try {
    await saveAgent(payload, files)
  } catch (err) {
    agentFormRef.value?.handleSubmitError(err)
  }
}

async function handleDelete() {
  if (!agent.value) return
  if (!(await confirmStore.show({ message: `حذف ملف الوسيط «${getAgentName(agent.value)}» نهائياً؟` }))) return
  await removeAgent()
}
</script>

<template>
  <div class="admin-page">
    <Breadcrumbs
      :items="[
        { label: 'الوسطاء', to: '/admin/agents' },
        { label: isCreate ? 'وسيط جديد' : getAgentName(agent) ?? '...' },
      ]"
    />

    <LoadingSpinner v-if="loading" />
    <ErrorAlert
      v-else-if="error"
      :message="error"
      @retry="fetchAgent($route.params.id)"
    />

    <template v-else-if="isCreate || agent">
      <AdminPageHeader
        :title="isCreate ? 'إضافة وسيط جديد' : getAgentName(agent)"
        :description="isCreate ? 'ربط مستخدم بشركة عقارية كوسيط.' : agent.company?.company_name ?? '—'"
      />

      <div v-if="!isCreate" class="admin-agent-detail__meta">
        <span>
          <i class="bi bi-building"></i>
          {{ agent.company?.company_name ?? '—' }}
        </span>
        <span>
          <i class="bi bi-shield-check"></i>
          درجة الثقة {{ agent.trust_score ?? 0 }}%
        </span>
        <span>
          <i class="bi bi-eye"></i>
          {{ agent.views ?? 0 }} مشاهدة
        </span>
        <span>
          <i class="bi bi-share"></i>
          {{ agent.shares ?? 0 }} مشاركة
        </span>
        <span v-if="agent.average_rating != null">
          <i class="bi bi-star-fill"></i>
          {{ agent.average_rating }} ({{ agent.ratings_count ?? 0 }} تقييم)
        </span>
      </div>

      <div v-if="!isCreate" class="admin-agent-detail__links">
        <AppButton
          v-if="agent.user_id"
          :to="`/admin/users/${agent.user_id}`"
          variant="outline"
          size="sm"
        >
          ملف المستخدم
        </AppButton>
        <AppButton
          v-if="agent.companies_id"
          :to="`/admin/companies/${agent.companies_id}`"
          variant="outline"
          size="sm"
        >
          ملف الشركة
        </AppButton>
        <AppButton :to="`/agents/${agent.id}`" variant="outline" size="sm">
          معاينة عامة
        </AppButton>
      </div>

      <AdminStatsSection v-if="!isCreate" title="معاينة سريعة">
        <div class="admin-agent-detail__preview">
          <img :src="getAgentImage(agent)" :alt="getAgentName(agent)" class="admin-agent-detail__avatar" />
          <div class="admin-agent-detail__preview-body">
            <h3>{{ getAgentName(agent) }}</h3>
            <p v-if="agent.user?.email">
              <i class="bi bi-envelope"></i>
              {{ agent.user.email }}
            </p>
            <p v-if="agent.user?.phone">
              <i class="bi bi-telephone"></i>
              {{ agent.user.phone }}
            </p>
          </div>
        </div>
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="إحصائيات">
        <div class="admin-stats-grid">
          <AdminStatCard label="المشاهدات" :value="agent.views ?? 0" icon="bi-eye" />
          <AdminStatCard label="المشاركات" :value="agent.shares ?? 0" icon="bi-share" />
          <AdminStatCard label="درجة الثقة" :value="`${agent.trust_score ?? 0}%`" icon="bi-shield-check" />
          <AdminStatCard
            label="التقييمات"
            :value="agent.ratings_count ?? 0"
            icon="bi-star"
          />
        </div>
      </AdminStatsSection>

      <AdminStatsSection :title="isCreate ? 'بيانات الوسيط' : 'تعديل الوسيط'">
        <AdminAgentForm
          ref="agentFormRef"
          :agent="agent"
          :is-create="isCreate"
          :loading="saving"
          @submit="handleSave"
        />
      </AdminStatsSection>

      <AdminStatsSection v-if="!isCreate" title="روابط التواصل الاجتماعي">
        <AdminAgentSocialPanel :agent="agent" @updated="refreshAgent" />
      </AdminStatsSection>

      <section v-if="!isCreate" class="admin-user-detail__danger">
        <h3>منطقة الخطر</h3>
        <p>حذف ملف الوسيط نهائياً. لا يمكن التراجع.</p>
        <TableAction
          tone="danger"
          :label="deleting ? 'جاري الحذف...' : 'حذف الوسيط'"
          :disabled="deleting"
          @click="handleDelete"
        />
      </section>
    </template>
  </div>
</template>
