/** Admin dashboard — mirrors backend config/realestate.php statuses. */

export const ADMIN_DEVICE_NAME = 'zad-admin-panel'

export const ADMIN_NAV = [
  { label: 'لوحة التحكم', to: '/admin/dashboard', icon: 'bi-speedometer2' },
  { label: 'المستخدمون', to: '/admin/users', icon: 'bi-people' },
  { label: 'العقارات', to: '/admin/estates', icon: 'bi-buildings' },
  { label: 'الشركات', to: '/admin/companies', icon: 'bi-building' },
  { label: 'الوسطاء', to: '/admin/agents', icon: 'bi-person-badge' },
  { label: 'المدن', to: '/admin/cities', icon: 'bi-geo' },
  { label: 'المناطق', to: '/admin/places', icon: 'bi-pin-map' },
  { label: 'الثقة والمراجعة', to: '/admin/trust', icon: 'bi-shield-check' },
  { label: 'الملف الشخصي', to: '/admin/profile', icon: 'bi-person-gear' },
  { label: 'الرسائل', to: '/inbox', icon: 'bi-chat-dots' },
]

export const USER_STATUS_LABELS = {
  active: 'نشط',
  inactive: 'غير نشط',
  suspended: 'موقوف',
}

export const USER_TYPE_LABELS = {
  admin: 'مدير',
  buyer: 'مشتري',
  owner: 'مالك',
  agent: 'وسيط',
  company: 'شركة',
}

export const USER_TYPE_OPTIONS = [
  { value: 'admin', label: 'مدير' },
  { value: 'buyer', label: 'مشتري / مستثمر' },
  { value: 'owner', label: 'مالك عقار' },
  { value: 'agent', label: 'وسيط عقاري' },
  { value: 'company', label: 'شركة عقارية' },
]

export const USER_STATUS_OPTIONS = [
  { value: 'active', label: 'نشط' },
  { value: 'inactive', label: 'غير نشط' },
  { value: 'suspended', label: 'موقوف' },
]

export const ADMIN_GENDER_OPTIONS = [
  { value: 'male', label: 'ذكر' },
  { value: 'female', label: 'أنثى' },
  { value: 'other', label: 'أخرى' },
]

export const ESTATE_STATUS_LABELS = {
  pending: 'قيد المراجعة',
  active: 'مفعّل',
  rejected: 'مرفوض',
}

export const ESTATE_STATUS_OPTIONS = [
  { value: 'pending', label: 'قيد المراجعة' },
  { value: 'active', label: 'مفعّل' },
  { value: 'rejected', label: 'مرفوض' },
]

export const ESTATE_BUILD_STATE_OPTIONS = [
  { value: 'new', label: 'جديد' },
  { value: 'good', label: 'جيد' },
  { value: 'needs_renovation', label: 'يحتاج ترميم' },
]

export const ESTATE_RENT_KIND_OPTIONS = [
  { value: '', label: 'غير محدد' },
  { value: 'monthly', label: 'شهري' },
  { value: 'annual', label: 'سنوي' },
]

export const ADMIN_ESTATE_TYPE_OPTIONS = [
  { value: 'residential', label: 'سكني' },
  { value: 'commercial', label: 'تجاري' },
]

export const ADMIN_ESTATE_KIND_OPTIONS = [
  { value: 'apartment', label: 'شقة' },
  { value: 'villa', label: 'فيلا' },
  { value: 'office', label: 'مكتب' },
]

export const COMPANY_STATUS_LABELS = {
  pending: 'قيد المراجعة',
  approved: 'معتمد',
  rejected: 'مرفوض',
  suspended: 'موقوف',
}

export const COMPANY_STATUS_OPTIONS = [
  { value: 'pending', label: 'قيد المراجعة' },
  { value: 'approved', label: 'معتمد' },
  { value: 'rejected', label: 'مرفوض' },
  { value: 'suspended', label: 'موقوف' },
]

export const WORK_DAYS_OPTIONS = [
  { value: 'Sunday', label: 'الأحد' },
  { value: 'Monday', label: 'الإثنين' },
  { value: 'Tuesday', label: 'الثلاثاء' },
  { value: 'Wednesday', label: 'الأربعاء' },
  { value: 'Thursday', label: 'الخميس' },
  { value: 'Friday', label: 'الجمعة' },
  { value: 'Saturday', label: 'السبت' },
]

export const SOCIAL_PLATFORM_OPTIONS = [
  { value: 'facebook', label: 'فيسبوك' },
  { value: 'instagram', label: 'إنستغرام' },
  { value: 'twitter', label: 'تويتر' },
  { value: 'linkedin', label: 'لينكدإن' },
  { value: 'youtube', label: 'يوتيوب' },
  { value: 'tiktok', label: 'تيك توك' },
  { value: 'whatsapp', label: 'واتساب' },
  { value: 'website', label: 'موقع' },
]

const PLATFORM_COLORS = {
  facebook: '#1877F2',
  instagram: '#E4405F',
  twitter: '#000000',
  linkedin: '#0A66C2',
  youtube: '#FF0000',
  tiktok: '#010101',
  whatsapp: '#25D366',
  website: '#6B7280',
}

const PLATFORM_ICONS = {
  facebook: 'bi-facebook',
  instagram: 'bi-instagram',
  twitter: 'bi-twitter-x',
  linkedin: 'bi-linkedin',
  youtube: 'bi-youtube',
  tiktok: 'bi-tiktok',
  whatsapp: 'bi-whatsapp',
  website: 'bi-globe',
}

export function getPlatformStyle(platform) {
  return {
    color: PLATFORM_COLORS[platform] ?? '#6B7280',
    icon: PLATFORM_ICONS[platform] ?? 'bi-link-45deg',
  }
}

export const REVIEW_TYPE_OPTIONS = [
  { value: 'property', label: 'تقييمات العقارات' },
  { value: 'agent', label: 'تقييمات الوسطاء' },
  { value: 'company', label: 'تقييمات الشركات' },
]

export const REVIEW_STATUS_LABELS = {
  pending: 'قيد المراجعة',
  approved: 'معتمد',
  rejected: 'مرفوض',
}

export const REVIEW_STATUS_OPTIONS = [
  { value: '', label: 'كل الحالات' },
  { value: 'pending', label: 'قيد المراجعة' },
  { value: 'approved', label: 'معتمد' },
  { value: 'rejected', label: 'مرفوض' },
]

export const VERIFICATION_STATUS_LABELS = {
  pending: 'قيد المراجعة',
  approved: 'معتمد',
  rejected: 'مرفوض',
}

export const VERIFICATION_STATUS_OPTIONS = [
  { value: '', label: 'كل الحالات' },
  { value: 'pending', label: 'قيد المراجعة' },
  { value: 'approved', label: 'معتمد' },
  { value: 'rejected', label: 'مرفوض' },
]

export const DASHBOARD_SECTIONS = {
  overview: 'نظرة عامة',
  moderation: 'قيد المراجعة',
  distribution: 'التوزيع',
  activity: 'النشاط الأخير',
}

export const ESTATE_STATUS_CHART_COLORS = {
  pending: 'warning',
  active: 'success',
  rejected: 'danger',
}

export const COMPANY_STATUS_CHART_COLORS = {
  pending: 'warning',
  approved: 'success',
  rejected: 'danger',
  suspended: 'muted',
}

export const VERIFICATION_TYPE_LABELS = {
  national_id: 'هوية وطنية',
  passport: 'جواز سفر',
  business_license: 'رخصة تجارية',
  other: 'أخرى',
}
