import { defineStore } from 'pinia'
import { ref } from 'vue'

import { authService } from '@/api/auth.js'
import { setTokenGetter } from '@/api/client.js'
import { ADMIN_TOKEN_KEY, ADMIN_USER_KEY, AUTH_TOKEN_KEY, AUTH_USER_KEY } from '@/config/api.js'

function readStoredUser() {
  try {
    const raw = localStorage.getItem(AUTH_USER_KEY) ?? localStorage.getItem(ADMIN_USER_KEY)
    return raw ? JSON.parse(raw) : null
  } catch {
    return null
  }
}

function migrateLegacyAdminSession() {
  const legacyToken = localStorage.getItem(ADMIN_TOKEN_KEY)
  const legacyUser = localStorage.getItem(ADMIN_USER_KEY)

  if (legacyToken && !localStorage.getItem(AUTH_TOKEN_KEY)) {
    localStorage.setItem(AUTH_TOKEN_KEY, legacyToken)
  }

  if (legacyUser && !localStorage.getItem(AUTH_USER_KEY)) {
    localStorage.setItem(AUTH_USER_KEY, legacyUser)
  }

  if (legacyToken || legacyUser) {
    localStorage.removeItem(ADMIN_TOKEN_KEY)
    localStorage.removeItem(ADMIN_USER_KEY)
  }
}

migrateLegacyAdminSession()

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem(AUTH_TOKEN_KEY) ?? localStorage.getItem(ADMIN_TOKEN_KEY))
  const user = ref(readStoredUser())

  setTokenGetter(() => token.value)

  function setSession({ user: nextUser, token: nextToken }) {
    user.value = nextUser
    token.value = nextToken

    if (nextToken) {
      localStorage.setItem(AUTH_TOKEN_KEY, nextToken)
    } else {
      localStorage.removeItem(AUTH_TOKEN_KEY)
    }

    if (nextUser) {
      localStorage.setItem(AUTH_USER_KEY, JSON.stringify(nextUser))
    } else {
      localStorage.removeItem(AUTH_USER_KEY)
    }

    localStorage.removeItem(ADMIN_TOKEN_KEY)
    localStorage.removeItem(ADMIN_USER_KEY)
  }

  async function login(credentials) {
    const { data } = await authService.login(credentials)
    setSession({ user: data.user, token: data.token })
    return data
  }

  async function register(payload) {
    const { data } = await authService.register(payload)
    setSession({ user: data.user, token: data.token })
    return data
  }

  async function logout() {
    try {
      await authService.logout()
    } finally {
      setSession({ user: null, token: null })
    }
  }

  async function fetchMe() {
    if (!token.value) return null

    try {
      const { data } = await authService.me()
      user.value = data
      localStorage.setItem(AUTH_USER_KEY, JSON.stringify(data))
      return data
    } catch {
      setSession({ user: null, token: null })
      return null
    }
  }

  const isAuthenticated = () => Boolean(token.value)

  const isAdmin = () => user.value?.type === 'admin'

  const isCompany = () => user.value?.type === 'company'

  const isAgent = () => user.value?.type === 'agent'

  const isRegularUser = () => {
    const t = user.value?.type
    return t === 'buyer' || t === 'owner'
  }

  return {
    token,
    user,
    login,
    register,
    logout,
    fetchMe,
    isAuthenticated,
    isAdmin,
    isCompany,
    isAgent,
    isRegularUser,
    setSession,
  }
})
