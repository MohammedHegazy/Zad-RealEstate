/**
 * User journey narrative — connects pages into a coherent story.
 *
 * Flow: Discover → Explore → Decide → Connect → Manage
 */

export const JOURNEY_STEPS = [
  { id: 'discover', label: 'اكتشف', icon: 'bi-compass' },
  { id: 'explore', label: 'استكشف', icon: 'bi-search' },
  { id: 'decide', label: 'قارن', icon: 'bi-bar-chart' },
  { id: 'connect', label: 'تواصل', icon: 'bi-chat-dots' },
  { id: 'manage', label: 'أدِر', icon: 'bi-person-gear' },
]

export const PAGE_NARRATIVES = {
  cities: {
    step: 'discover',
    overline: 'رحلتك العقارية تبدأ هنا',
    title: 'اختر مدينتك',
    description:
      'كل مدينة تحمل فرصاً مختلفة. استكشف الأحياء والعقارات المتاحة وابدأ رحلة البحث عن منزل أحلامك أو استثمارك القادم.',
    cta: {
      title: 'هل تعرف الحي الذي تبحث عنه؟',
      description: 'انتقل إلى المناطق داخل المدينة واطّلع على عدد العقارات في كل حي.',
      primary: { label: 'استكشف المناطق', to: '/places' },
      secondary: { label: 'تصفح كل العقارات', to: '/estates' },
    },
  },
  places: {
    step: 'explore',
    overline: 'خطوة أعمق في رحلتك',
    title: 'تعرّف على الأحياء',
    description:
      'كل حي له طابعه وعقاراته. اكتشف الأحياء الأكثر نشاطاً وانتقل مباشرة إلى العقارات المتاحة في المنطقة التي تهمك.',
    cta: {
      title: 'جاهز لرؤية العقارات؟',
      description: 'صفِّ حسب المنطقة والسعر ونوع العقار لتجد ما يناسبك.',
      primary: { label: 'عرض العقارات', to: '/estates' },
      secondary: { label: 'العودة للمدن', to: '/cities' },
    },
  },
  agents: {
    step: 'connect',
    overline: 'خبراء بجانبك',
    title: 'وسطاء عقاريون معتمدون',
    description:
      'الوسيط المناسب يختصر عليك الوقت ويوفّر خبرة السوق المحلي. قارن التقييمات ودرجة الثقة واختر من يرافقك في قرارك.',
    cta: {
      title: 'تبحث عن شركة كاملة؟',
      description: 'تعرّف على الشركات العقارية الموثوقة وفرق الوسطاء لديها.',
      primary: { label: 'الشركات العقارية', to: '/companies' },
      secondary: { label: 'تصفح العقارات', to: '/estates' },
    },
  },
  companies: {
    step: 'connect',
    overline: 'شركاء موثوقون',
    title: 'شركات عقارية رائدة',
    description:
      'شركات معتمدة بسجل حافل ودرجة ثقة محسوبة. تصفّح فرق الوكلاء واختر الجهة التي تناسب احتياجاتك.',
    cta: {
      title: 'الخطوة التالية في رحلتك',
      description: 'بعد اختيار الشركة أو الوكيل، استكشف العقارات المتاحة وتواصل مباشرة.',
      primary: { label: 'عرض العقارات', to: '/estates' },
      secondary: { label: 'الوسطاء', to: '/agents' },
    },
  },
  estates: {
    step: 'decide',
    overline: 'قارن واختر',
    title: 'كل العقارات في مكان واحد',
    description:
      'فلتر حسب المدينة والسعر والنوع. كل بطاقة عقار تحمل تحليلات استثمارية وتقييمات لتساعدك على اتخاذ قرار واعٍ.',
  },
  favorites: {
    step: 'manage',
    overline: 'قائمتك الشخصية',
    title: 'عقاراتك المفضلة',
    description: 'احفظ العقارات التي أعجبتك وارجع إليها لاحقاً للمقارنة أو التواصل مع المالك.',
  },
  recommendations: {
    step: 'discover',
    overline: 'ذكاء اصطناعي',
    title: 'عقارات مقترحة لك',
    description: 'نقارن مفضلتك ببقية العقارات ونعرض الأقرب لذوقك.',
  },
  profile: {
    step: 'manage',
    overline: 'مركز حسابك',
    title: 'مساحتك على زاد للعقارات',
    description:
      'من هنا تدير رحلتك: مفضلتك، تفضيلاتك، ومسارات مختلفة حسب نوع حسابك — مشتري، مالك، وكيل، أو شركة.',
  },
}

export const USER_TYPE_JOURNEY = {
  buyer: {
    label: 'مشتري / مستثمر',
    links: [
      { label: 'عقاراتي المفضلة', to: '/favorites', icon: 'bi-heart' },
      { label: 'تصفح العقارات', to: '/estates', icon: 'bi-building' },
      { label: 'التوصيات الذكية', to: '/recommendations', icon: 'bi-stars' },
    ],
  },
  owner: {
    label: 'مالك عقار',
    links: [
      { label: 'تصفح السوق', to: '/estates', icon: 'bi-building' },
      { label: 'عقاراتي المفضلة', to: '/favorites', icon: 'bi-heart' },
      { label: 'إدارة عقاراتي', to: '/estates', icon: 'bi-house-gear', hint: 'قريباً' },
    ],
  },
  agent: {
    label: 'وسيط عقاري',
    links: [
      { label: 'لوحة الوسيط', to: '/agent/dashboard', icon: 'bi-speedometer2' },
      { label: 'العقارات', to: '/agent/estates', icon: 'bi-buildings' },
      { label: 'الملف الشخصي', to: '/agent/profile', icon: 'bi-person-badge' },
      { label: 'روابط التواصل', to: '/agent/social-links', icon: 'bi-share' },
    ],
  },
  company: {
    label: 'شركة عقارية',
    links: [
      { label: 'لوحة الشركة', to: '/company/dashboard', icon: 'bi-speedometer2' },
      { label: 'إدارة الوسطاء', to: '/company/agents', icon: 'bi-person-badge' },
      { label: 'عقاراتي', to: '/company/estates', icon: 'bi-buildings' },
      { label: 'التقييمات', to: '/company/reviews', icon: 'bi-chat-square-text' },
      { label: 'ملف الشركة', to: '/company/profile', icon: 'bi-building-gear' },
    ],
  },
}
