/**
 * Default landing path after login/register based on user role.
 */
export function defaultHomeForUser(user) {
  if (user?.type === 'admin') return '/admin/dashboard'
  if (user?.type === 'company') return '/company/dashboard'
  if (user?.type === 'agent') return '/agent/dashboard'
  if (user?.type === 'owner') return '/owner/dashboard'
  if (user?.type === 'buyer') return '/buyer/dashboard'
  return '/profile'
}

/**
 * Resolve where to send the user after authentication.
 * Honors ?redirect= when safe for the user's role.
 */
export function resolvePostLoginPath(user, redirect) {
  if (typeof redirect === 'string' && redirect.startsWith('/')) {
    if (redirect.startsWith('/admin') && user?.type !== 'admin') {
      return defaultHomeForUser(user)
    }
    if (redirect.startsWith('/company') && user?.type !== 'company') {
      return defaultHomeForUser(user)
    }
    if (redirect.startsWith('/agent') && user?.type !== 'agent') {
      return defaultHomeForUser(user)
    }
    if (redirect.startsWith('/buyer') && user?.type !== 'buyer') {
      return defaultHomeForUser(user)
    }
    if (redirect.startsWith('/owner') && user?.type !== 'owner') {
      return defaultHomeForUser(user)
    }
    return redirect
  }

  return defaultHomeForUser(user)
}
