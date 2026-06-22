/** Proxy / agent (وسيط عقاري) — mirrors backend Agent domain. */

export const AGENT_PLACEHOLDER_IMAGE =
  'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400&q=80'

export const AGENT_SORT_OPTIONS = [
  { value: 'latest', label: 'الأحدث' },
  { value: 'trust', label: 'أعلى درجة ثقة' },
  { value: 'rating', label: 'أعلى تقييم' },
]

export const AGENT_STATUS_LABELS = {
  pending: 'قيد المراجعة',
  approved: 'معتمد',
  rejected: 'مرفوض',
  suspended: 'موقوف',
}

export const AGENT_STATUS_OPTIONS = [
  { value: '', label: 'كل الحالات' },
  { value: 'pending', label: 'قيد المراجعة' },
  { value: 'approved', label: 'معتمد' },
  { value: 'rejected', label: 'مرفوض' },
  { value: 'suspended', label: 'موقوف' },
]

/** Trust score factor keys from TrustScoreService::calculateAgentScore */
export const TRUST_FACTOR_LABELS = {
  verified_account: 'حساب موثّق',
  approved_properties: 'عقارات معتمدة',
  average_rating: 'متوسط التقييم',
  review_count: 'عدد التقييمات',
  platform_activity: 'نشاط على المنصة',
}
