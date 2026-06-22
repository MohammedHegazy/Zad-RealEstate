import { reactive, ref } from 'vue'

import { handleApiError } from '@/api/errorHandler.js'

/**
 * Manage field-level and general form errors from API validation responses.
 */
export function useFormErrors() {
  const errors = reactive({})
  const generalError = ref('')

  function clearErrors() {
    Object.keys(errors).forEach((key) => delete errors[key])
    generalError.value = ''
  }

  function setFieldErrors(fieldErrors = {}) {
    Object.entries(fieldErrors).forEach(([field, messages]) => {
      errors[field] = Array.isArray(messages) ? messages[0] : messages
    })
  }

  function fieldError(field) {
    return errors[field] ?? ''
  }

  function hasError(field) {
    return Boolean(errors[field])
  }

  function handleSubmitError(error) {
    handleApiError(error, {
      onValidation: (apiError) => {
        setFieldErrors(apiError.fieldErrors)
      },
      onUnauthorized: (apiError) => {
        generalError.value = apiError.message
      },
      onForbidden: (apiError) => {
        generalError.value = apiError.message
      },
      onNetwork: (apiError) => {
        generalError.value = apiError.message
      },
      onDefault: (apiError) => {
        generalError.value = apiError.message
      },
    })
  }

  return {
    errors,
    generalError,
    clearErrors,
    setFieldErrors,
    fieldError,
    hasError,
    handleSubmitError,
  }
}
