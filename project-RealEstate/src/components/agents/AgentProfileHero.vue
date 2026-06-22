<script setup>
import TrustBadge from '@/components/ui/TrustBadge.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { getAgentImage, getAgentName } from '@/utils/agent.js'

defineProps({
  agent: {
    type: Object,
    required: true,
  },
  trust: {
    type: Object,
    default: null,
  },
  reviewSummary: {
    type: Object,
    default: null,
  },
})
</script>

<template>
  <header class="agent-hero">
    <div class="agent-hero__banner"></div>

    <div class="agent-hero__content">
      <img
        :src="getAgentImage(agent)"
        :alt="getAgentName(agent)"
        class="agent-hero__avatar"
      />

      <div class="agent-hero__info">
        <p class="agent-hero__overline">وسيط عقاري معتمد</p>
        <h1>{{ getAgentName(agent) }}</h1>

        <p v-if="agent.company?.company_name" class="agent-hero__company">
          <i class="bi bi-building"></i>
          {{ agent.company.company_name }}
        </p>

        <TrustBadge
          :trust-score="trust?.trust_score ?? agent.trust_score"
          :average-rating="reviewSummary?.average_rating ?? agent.average_rating"
          :reviews-count="reviewSummary?.reviews_count ?? agent.ratings_count"
          :is-verified="trust?.is_verified"
        />

        <div class="agent-hero__stats">
          <span v-if="agent.views != null">
            <i class="bi bi-eye"></i>
            {{ agent.views }} مشاهدة
          </span>
          <span v-if="agent.shares != null">
            <i class="bi bi-share"></i>
            {{ agent.shares }} مشاركة
          </span>
        </div>

        <AppButton
          v-if="agent.company"
          :to="`/companies/${agent.company.id}`"
          variant="outline"
          size="sm"
        >
          عرض الشركة
        </AppButton>
      </div>
    </div>
  </header>
</template>
