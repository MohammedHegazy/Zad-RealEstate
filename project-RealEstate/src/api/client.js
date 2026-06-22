import { API_BASE_URL, AUTH_TOKEN_KEY, AUTH_USER_KEY, DEFAULT_HEADERS } from '@/config/api.js'
import { createApiError } from './errors.js'

let tokenGetter = () => localStorage.getItem(AUTH_TOKEN_KEY)
let toastHandler = null
let consecutive401Count = 0

/**
 * Register how the client resolves the Bearer token (e.g. from a Pinia store).
 */
export function setTokenGetter(getter) {
  tokenGetter = getter
}

/**
 * Register toast callbacks for automatic success/error notifications.
 * @param {{ success: (msg: string) => void, error: (msg: string) => void }} handler
 */
export function setToastHandler(handler) {
  toastHandler = handler
}

function buildUrl(path, params) {
  const base = API_BASE_URL.replace(/\/$/, '')
  const normalizedPath = path.startsWith('/') ? path : `/${path}`
  const url = new URL(`${base}${normalizedPath}`)

  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.set(key, String(value))
      }
    })
  }

  return url.toString()
}

function buildHeaders(options = {}) {
  const headers = new Headers({
    ...DEFAULT_HEADERS,
    ...options.headers,
  })

  const token = options.token ?? tokenGetter()
  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  if (options.body instanceof FormData) {
    headers.delete('Content-Type')
  }

  return headers
}

async function parseBody(response) {
  const contentType = response.headers.get('content-type') ?? ''

  if (contentType.includes('application/json')) {
    return response.json()
  }

  const text = await response.text()
  return text ? { message: text } : null
}

/**
 * Core request — all HTTP calls go through this function.
 *
 * @returns {Promise<{ success: true, message: string, data: *, pagination?: object }>}
 */
export async function request(method, path, options = {}) {
  const { body, params, signal, ...rest } = options

  const fetchOptions = {
    method,
    headers: buildHeaders({ ...rest, body }),
    signal,
  }

  if (body !== undefined && body !== null) {
    fetchOptions.body =
      body instanceof FormData || typeof body === 'string' ? body : JSON.stringify(body)
  }

  const response = await fetch(buildUrl(path, params), fetchOptions)
  const payload = await parseBody(response)

  const isBackendError =
    !response.ok || (payload && typeof payload === 'object' && payload.success === false)

  if (isBackendError) {
    const apiError = createApiError(response, payload)
    if (toastHandler && apiError.message) {
      toastHandler.error(apiError.message)
    }
    throw apiError
  }

  if (toastHandler && method !== 'GET' && payload?.message) {
    toastHandler.success(payload.message)
  }

  return payload ?? { success: true, message: '', data: null }
}

export const api = {
  get(path, options) {
    return request('GET', path, options)
  },

  post(path, body, options = {}) {
    return request('POST', path, { ...options, body })
  },

  put(path, body, options = {}) {
    return request('PUT', path, { ...options, body })
  },

  patch(path, body, options = {}) {
    return request('PATCH', path, { ...options, body })
  },

  delete(path, options) {
    return request('DELETE', path, options)
  },
}
