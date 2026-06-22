/**
 * Shared helpers for multipart form submissions.
 */
export function appendFormValue(formData, key, value) {
  if (value === undefined || value === null || value === '') return

  if (typeof value === 'boolean') {
    formData.append(key, value ? '1' : '0')
    return
  }

  if (Array.isArray(value)) {
    value.forEach((item) => formData.append(`${key}[]`, item))
    return
  }

  formData.append(key, value)
}

export function appendFileList(formData, key, files) {
  if (!files?.length) return
  files.forEach((file) => formData.append(`${key}[]`, file))
}

/**
 * Use POST for multipart updates — PHP only parses file uploads on POST reliably.
 */
export function submitMultipartUpdate(apiClient, path, body) {
  if (body instanceof FormData) {
    return apiClient.post(path, body)
  }

  return apiClient.put(path, body)
}

export function buildFormData(payload, files = {}, fileKeys = {}) {
  const formData = new FormData()

  Object.entries(payload).forEach(([key, value]) => {
    appendFormValue(formData, key, value)
  })

  Object.entries(fileKeys).forEach(([key, fileKey]) => {
    const file = files[fileKey]
    if (file) {
      formData.append(key, file)
    }
  })

  return formData
}
