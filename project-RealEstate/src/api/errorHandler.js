import { ApiError, isApiError } from './errors.js'

/**
 * Normalize any thrown value into an ApiError for consistent UI handling.
 */
export function normalizeError(error) {
  if (isApiError(error)) {
    return error
  }

  if (error instanceof TypeError && error.message.includes('fetch')) {
    return new ApiError('Unable to reach the server. Check your connection.', {
      status: 0,
    })
  }

  if (error instanceof Error) {
    return new ApiError(error.message, { status: 0 })
  }

  return new ApiError('An unexpected error occurred.', { status: 0 })
}

/**
 * Extract a user-facing message from an API or network error.
 */
export function getErrorMessage(error, fallback = 'Something went wrong.') {
  return normalizeError(error).message || fallback
}

/**
 * Collect all validation messages into a flat array.
 */
export function getValidationMessages(errors) {
  if (!errors || typeof errors !== 'object') {
    return []
  }

  if (Array.isArray(errors)) {
    return errors.filter(Boolean)
  }

  return Object.values(errors).flat().filter(Boolean)
}

/**
 * Central error handler — invoke callbacks by error type.
 *
 * @example
 * try {
 *   await api.post('/login', credentials)
 * } catch (err) {
 *   handleApiError(err, {
 *     onValidation: (e) => setFormErrors(e.fieldErrors),
 *     onUnauthorized: () => router.push('/login'),
 *     onForbidden: (e) => toast.error(e.message),
 *     onNotFound: () => toast.error('Not found'),
 *     onNetwork: () => toast.error('Network error'),
 *     onDefault: (e) => toast.error(e.message),
 *   })
 * }
 */
export function handleApiError(error, handlers = {}) {
  const apiError = normalizeError(error)

  if (apiError.status === 0 && handlers.onNetwork) {
    return handlers.onNetwork(apiError)
  }

  if (apiError.isValidationError && handlers.onValidation) {
    return handlers.onValidation(apiError)
  }

  if (apiError.isUnauthorized && handlers.onUnauthorized) {
    return handlers.onUnauthorized(apiError)
  }

  if (apiError.isForbidden && handlers.onForbidden) {
    return handlers.onForbidden(apiError)
  }

  if (apiError.isNotFound && handlers.onNotFound) {
    return handlers.onNotFound(apiError)
  }

  if (handlers.onDefault) {
    return handlers.onDefault(apiError)
  }

  return apiError
}
