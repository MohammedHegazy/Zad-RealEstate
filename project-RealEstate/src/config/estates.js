/** Estate filter & sort options — aligned with backend query params. */

export const ESTATE_SORT_OPTIONS = [
  { value: 'latest', label: 'الأحدث' },
  { value: 'price_asc', label: 'السعر: من الأقل' },
  { value: 'price_desc', label: 'السعر: من الأعلى' },
]

export const ESTATE_TYPE_OPTIONS = [
  { value: '', label: 'كل الفئات' },
  { value: 'سكني', label: 'سكني' },
  { value: 'تجاري', label: 'تجاري' },
]

export const ESTATE_KIND_OPTIONS = [
  { value: '', label: 'كل الأنواع' },
  { value: 'شقة', label: 'شقة' },
  { value: 'فيلا', label: 'فيلا' },
  { value: 'دوبلكس', label: 'دوبلكس' },
  { value: 'تاون هاوس', label: 'تاون هاوس' },
  { value: 'استوديو', label: 'استوديو' },
  { value: 'مكتب', label: 'مكتب' },
  { value: 'متجر', label: 'متجر' },
  { value: 'مستودع', label: 'مستودع' },
  { value: 'عمارة', label: 'عمارة' },
  { value: 'مركز تجاري', label: 'مركز تجاري' },
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
