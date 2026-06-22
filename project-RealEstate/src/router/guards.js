import { useAuthStore } from '@/stores/auth.js'
import { resolvePostLoginPath } from '@/utils/authRedirect.js'

/**
 * Redirect authenticated users away from guest-only pages (login/register).
 */
export function guestGuard(to, _from, next) {
  const auth = useAuthStore()

  if (auth.isAuthenticated()) {
    next(resolvePostLoginPath(auth.user, to.query.redirect))
    return
  }

  next()
}

/**
 * Redirect unauthenticated users to login.
 */
export function authGuard(to, _from, next) {
  const auth = useAuthStore()

  if (!auth.isAuthenticated()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  next()
}

/**
 * Admin routes — require authenticated admin via the shared session.
 */
export function adminGuard(to, _from, next) {
  const auth = useAuthStore()

  if (!auth.isAuthenticated()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (!auth.isAdmin()) {
    next(resolvePostLoginPath(auth.user))
    return
  }

  next()
}

/**
 * Company routes — require authenticated company user.
 */
export function companyGuard(to, _from, next) {
  const auth = useAuthStore()

  if (!auth.isAuthenticated()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (!auth.isCompany()) {
    next(resolvePostLoginPath(auth.user))
    return
  }

  next()
}

/**
 * Agent routes — require authenticated agent user.
 */
/**
 * Buyer routes — require authenticated buyer or owner user.
 */
export function buyerGuard(to, _from, next) {
  const auth = useAuthStore()

  if (!auth.isAuthenticated()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (!auth.isRegularUser()) {
    next(resolvePostLoginPath(auth.user))
    return
  }

  next()
}

export function ownerGuard(to, _from, next) {
  const auth = useAuthStore()

  if (!auth.isAuthenticated()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (auth.user?.type !== 'owner') {
    next(resolvePostLoginPath(auth.user))
    return
  }

  next()
}

export function agentGuard(to, _from, next) {
  const auth = useAuthStore()

  if (!auth.isAuthenticated()) {
    next({ name: 'login', query: { redirect: to.fullPath } })
    return
  }

  if (!auth.isAgent()) {
    next(resolvePostLoginPath(auth.user))
    return
  }

  next()
}
