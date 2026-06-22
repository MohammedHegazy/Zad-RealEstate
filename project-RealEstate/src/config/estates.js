/** Estate filter & sort options — aligned with backend query params. */

export const ESTATE_SORT_OPTIONS = [
  { value: 'latest', label: 'الأحدث' },
  { value: 'price_asc', label: 'السعر: من الأقل' },
  { value: 'price_desc', label: 'السعر: من الأعلى' },
]

export const ESTATE_TYPE_OPTIONS = [
  { value: '', label: 'كل الفئات' },
  { value: 'residential', label: 'سكني' },
  { value: 'commercial', label: 'تجاري' },
]

export const ESTATE_KIND_OPTIONS = [
  { value: '', label: 'كل الأنواع' },
  { value: 'apartment', label: 'شقة' },
  { value: 'villa', label: 'فيلا' },
  { value: 'office', label: 'مكتب' },
]

export const LISTING_TYPE_OPTIONS = [
  { value: '', label: 'بيع وإيجار' },
  { value: 'sale', label: 'للبيع' },
  { value: 'rent', label: 'للإيجار' },
]

export const FURNISHED_OPTIONS = [
  { value: '', label: 'الكل' },
  { value: '1', label: 'مفروش' },
  { value: '0', label: 'غير مفروش' },
]

export const PER_PAGE_OPTIONS = [12, 24, 48]

export const DEFAULT_PER_PAGE = 12
