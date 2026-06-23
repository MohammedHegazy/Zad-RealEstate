const AVATAR_COLORS = [
  '#4f46e5', '#0891b2', '#059669', '#d97706',
  '#dc2626', '#7c3aed', '#db2777', '#2563eb',
  '#ca8a04', '#16a34a', '#9333ea', '#ea580c',
]

export function getUserName(user) {
  if (!user) return 'مستخدم'
  return `${user.fname ?? ''} ${user.lname ?? ''}`.trim() || user.username || 'مستخدم'
}

export function formatUserPhone(user) {
  if (!user?.phone) return null
  const code = user.country_code_phone ?? ''
  return `${code} ${user.phone}`.trim()
}

export function getUserInitials(user) {
  if (!user) return '?'
  const first = (user.fname ?? '')[0] ?? ''
  const last = (user.lname ?? '')[0] ?? ''
  return (first + last).toUpperCase() || (user.username?.[0] ?? '?').toUpperCase()
}

export function getAvatarColor(userId) {
  return AVATAR_COLORS[(userId ?? 0) % AVATAR_COLORS.length]
}
