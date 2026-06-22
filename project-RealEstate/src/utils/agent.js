import { AGENT_PLACEHOLDER_IMAGE } from '@/config/agents.js'

export function getAgentName(agent) {
  const user = agent?.user
  if (!user) return 'وسيط عقاري'
  return `${user.fname ?? ''} ${user.lname ?? ''}`.trim() || user.username || 'وسيط عقاري'
}

export function getAgentImage(agent) {
  return agent?.profile_image_url ?? AGENT_PLACEHOLDER_IMAGE
}

export function getAgentPhone(agent) {
  return agent?.user?.phone ?? null
}

export function getAgentEmail(agent) {
  return agent?.user?.email ?? null
}

export function getAgentProfileUrl(agentId) {
  if (typeof window === 'undefined') return `/agents/${agentId}`
  return `${window.location.origin}/agents/${agentId}`
}
