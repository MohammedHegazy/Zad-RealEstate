<script setup>
import { getAgentImage, getAgentName } from '@/utils/agent.js'

defineProps({
  agent: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article class="agent-card">
    <RouterLink :to="`/agents/${agent.id}`" class="agent-card__avatar-wrap">
      <img
        :src="getAgentImage(agent)"
        :alt="getAgentName(agent)"
        class="agent-card__avatar"
        loading="lazy"
      />
    </RouterLink>

    <div class="agent-card__body">
      <h3 class="agent-card__name">
        <RouterLink :to="`/agents/${agent.id}`">{{ getAgentName(agent) }}</RouterLink>
      </h3>

      <p v-if="agent.company?.company_name" class="agent-card__company">
        {{ agent.company.company_name }}
      </p>

      <div class="agent-card__stats">
        <span v-if="agent.average_rating">
          <i class="bi bi-star-fill"></i>
          {{ Number(agent.average_rating).toFixed(1) }}
        </span>
        <span v-if="agent.trust_score != null">
          <i class="bi bi-shield-check"></i>
          {{ agent.trust_score }}%
        </span>
        <span v-if="agent.ratings_count">
          <i class="bi bi-chat-square-text"></i>
          {{ agent.ratings_count }}
        </span>
      </div>
    </div>
  </article>
</template>
