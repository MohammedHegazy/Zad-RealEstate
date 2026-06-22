import { ref, watch } from 'vue'

export function useDebouncedAsyncSearch(searchFn, options = {}) {
  const { delay = 350, minLength = 2 } = options

  const query = ref('')
  const results = ref([])
  const loading = ref(false)
  let timer = null

  async function runSearch(value) {
    const trimmed = value.trim()

    if (trimmed.length < minLength) {
      results.value = []
      loading.value = false
      return
    }

    loading.value = true

    try {
      results.value = (await searchFn(trimmed)) ?? []
    } catch {
      results.value = []
    } finally {
      loading.value = false
    }
  }

  watch(query, (value) => {
    window.clearTimeout(timer)

    if (!value.trim()) {
      results.value = []
      loading.value = false
      return
    }

    if (value.trim().length < minLength) {
      results.value = []
      loading.value = false
      return
    }

    loading.value = true
    timer = window.setTimeout(() => runSearch(value), delay)
  })

  function reset() {
    query.value = ''
    results.value = []
    loading.value = false
    window.clearTimeout(timer)
  }

  return {
    query,
    results,
    loading,
    reset,
  }
}
