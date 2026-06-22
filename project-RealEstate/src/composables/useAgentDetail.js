import { computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'

import { agentsService } from '@/api/agents.js'
import { getErrorMessage } from '@/api/errorHandler.js'
import { useShareFeedback } from '@/composables/useShareFeedback.js'
import { getAgentEmail, getAgentName, getAgentPhone, getAgentProfileUrl } from '@/utils/agent.js'
import { shareProperty } from '@/utils/share.js'

const AGENT_SHARE_MESSAGES = {
  native: 'تم فتح قائمة المشاركة.',
  clipboard: 'تم نسخ رابط الوسيط.',
  cancelled: '',
  failed: 'تعذّر المشاركة. انسخ الرابط يدوياً من شريط العنوان.',
}

export function useAgentDetail() {
  const route = useRoute()
  const loading = ref(false)
  const error = ref(null)
  const agent = ref(null)
  const reviews = ref([])
  const reviewSummary = ref(null)
  const trust = ref(null)
  const reviewsLoading = ref(false)

  const { shareMessage, shareVariant, showShareFeedback } = useShareFeedback(AGENT_SHARE_MESSAGES)

  const agentName = computed(() => getAgentName(agent.value))
  const agentPhone = computed(() => getAgentPhone(agent.value))
  const agentEmail = computed(() => getAgentEmail(agent.value))

  async function fetchAgent(id) {
    loading.value = true
    error.value = null

    try {
      const { data } = await agentsService.getById(id)
      agent.value = data
      await fetchReviews(id)
    } catch (err) {
      error.value = getErrorMessage(err, 'تعذّر تحميل ملف الوسيط.')
      agent.value = null
    } finally {
      loading.value = false
    }
  }

  async function fetchReviews(id) {
    reviewsLoading.value = true

    try {
      const [reviewsRes, summaryRes, trustRes] = await Promise.all([
        agentsService.reviews(id, { per_page: 10 }),
        agentsService.reviewsSummary(id),
        agentsService.trustScore(id).catch(() => ({ data: null })),
      ])
      reviews.value = reviewsRes.data ?? []
      reviewSummary.value = summaryRes.data ?? null
      trust.value = trustRes.data ?? null
    } catch {
      reviews.value = []
      reviewSummary.value = null
      trust.value = null
    } finally {
      reviewsLoading.value = false
    }
  }

  function handleShare() {
    if (!agent.value) return

    const url = getAgentProfileUrl(agent.value.id)
    const title = `${agentName.value} | زاد للعقارات`
    const text = `تعرّف على الوسيط العقاري ${agentName.value} على زاد للعقارات`

    shareProperty({ title, text, url }).then((result) => {
      showShareFeedback(result)
      if (result.success && !result.cancelled) {
        agentsService.share(agent.value.id).catch(() => {})
      }
    })
  }

  watch(
    () => route.params.id,
    (id) => {
      if (id) fetchAgent(id)
    },
    { immediate: true },
  )

  return {
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
  }
}
