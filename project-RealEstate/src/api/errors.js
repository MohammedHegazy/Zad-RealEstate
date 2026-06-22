/**
 * ApiError — mirrors Laravel backend envelope:
 * { success: false, message: string, errors: Record<string, string[]> | [] }
 */
export class ApiError extends Error {
  constructor(message, { status = 0, errors = [], data = null } = {}) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
    this.data = data
  }

  get isValidationError() {
    return this.status === 422
  }

  get isUnauthorized() {
    return this.status === 401
  }

  get isForbidden() {
    return this.status === 403
  }

  get isNotFound() {
    return this.status === 404
  }

  /** Field-level validation messages, e.g. { email: ['The email field is required.'] } */
  get fieldErrors() {
    return this.errors && typeof this.errors === 'object' && !Array.isArray(this.errors)
      ? this.errors
      : {}
  }

  /** First message for a given field, or undefined */
  fieldError(field) {
    const messages = this.fieldErrors[field]
    return Array.isArray(messages) ? messages[0] : undefined
  }
}

/**
 * Build ApiError from a fetch Response and parsed JSON body.
 */
export function createApiError(response, body) {
  const message =
    body?.message ??
    response.statusText ??
    'An unexpected error occurred.'

  return new ApiError(message, {
    status: response.status,
    errors: body?.errors ?? [],
    data: body?.data ?? null,
  })
}

export function isApiError(error) {
  return error instanceof ApiError
}
