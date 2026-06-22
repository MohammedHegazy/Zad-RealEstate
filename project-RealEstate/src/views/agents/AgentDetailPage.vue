<script setup>
import { useRoute } from 'vue-router'

import AgentProfileHero from '@/components/agents/AgentProfileHero.vue'
import AgentSidebar from '@/components/agents/AgentSidebar.vue'
import ReviewSection from '@/components/reviews/ReviewSection.vue'
import Breadcrumbs from '@/components/ui/Breadcrumbs.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import { useAgentDetail } from '@/composables/useAgentDetail.js'

const route = useRoute()

const {
  loading,
  error,
  agent,
  reviews,
  reviewSummary,
  trust,
  reviewsLoading,
  agentName,
  agentPhone,
  agentEmail,
  shareMessage,
  shareVariant,
  fetchAgent,
  handleShare,
} = useAgentDetail()
</script>

<template>
  <div class="directory-page directory-page--detail agents-page agents-page--detail">
    <LoadingSpinner v-if="loading" />
    <ErrorAlert
      v-else-if="error"
      :message="error"
      class="container"
      @retry="fetchAgent(route.params.id)"
    />

    <template v-else-if="agent">
      <div class="container agents-page__breadcrumbs">
        <Breadcrumbs
          :items="[
            { label: 'الرئيسية', to: '/' },
            { label: 'الوسطاء', to: '/agents' },
            { label: agentName },
          ]"
        />
      </div>

      <div class="container agents-page__layout">
        <div class="row g-4">
          <div class="col-lg-8">
            <AgentProfileHero
              :agent="agent"
              :trust="trust"
              :review-summary="reviewSummary"
            />

            <ReviewSection
              review-type="agent"
              :entity-id="agent.id"
              :owner-user-id="agent.user_id"
              :reviews="reviews"
              :summary="reviewSummary"
              :loading="reviewsLoading"
              title="تقييمات الزوار"
              @refresh="fetchAgent(route.params.id)"
            />

            <CtaBanner
              class="agents-page__cta"
              title="هل تريد رؤية عقارات هذا الوسيط؟"
              description="تصفح العقارات المتاحة وتواصل مع الوسيط المناسب لك."
              :primary="{ label: 'تصفح العقارات', to: '/estates' }"
              :secondary="{ label: 'كل الوسطاء', to: '/agents' }"
            />
          </div>

          <div class="col-lg-4">
            <AgentSidebar
              :agent="agent"
              :agent-name="agentName"
              :agent-phone="agentPhone"
              :agent-email="agentEmail"
              :trust="trust"
              :share-message="shareMessage"
              :share-variant="shareVariant"
              @share="handleShare"
            />
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
