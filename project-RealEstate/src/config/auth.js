/** Registration options — mirrors backend config/realestate.php (admin excluded). */

export const USER_TYPES = [
  { value: 'buyer', label: 'مشتري / مستثمر' },
  { value: 'owner', label: 'مالك عقار' },
  { value: 'agent', label: 'وسيط عقاري' },
  { value: 'company', label: 'شركة عقارية' },
]

export const GENDER_OPTIONS = [
  { value: 'male', label: 'ذكر' },
  { value: 'female', label: 'أنثى' },
]

export const COUNTRY_CODES = [
  { value: '+963', label: '+963 سوريا' },
  { value: '+961', label: '+961 لبنان' },
  { value: '+962', label: '+962 الأردن' },
  { value: '+966', label: '+966 السعودية' },
  { value: '+971', label: '+971 الإمارات' },
  { value: '+965', label: '+965 الكويت' },
  { value: '+974', label: '+974 قطر' },
  { value: '+20', label: '+20 مصر' },
]

export const DEVICE_NAME = 'web-app'
