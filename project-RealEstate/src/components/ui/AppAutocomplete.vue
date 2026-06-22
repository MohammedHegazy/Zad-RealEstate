<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

import { useDebouncedAsyncSearch } from '@/composables/useDebouncedAsyncSearch.js'

const model = defineModel({ type: [String, Number], default: '' })

const props = defineProps({
  id: {
    type: String,
    default: '',
  },
  searchFn: {
    type: Function,
    required: true,
  },
  resolveFn: {
    type: Function,
    default: null,
  },
  getLabel: {
    type: Function,
    default: (item) => item?.label ?? '',
  },
  getValue: {
    type: Function,
    default: (item) => item?.value ?? '',
  },
  getDescription: {
    type: Function,
    default: (item) => item?.description ?? '',
  },
  placeholder: {
    type: String,
    default: 'ابحث...',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'md',
    validator: (value) => ['sm', 'md', 'lg'].includes(value),
  },
  minChars: {
    type: Number,
    default: 2,
  },
  debounceMs: {
    type: Number,
    default: 350,
  },
  emptyText: {
    type: String,
    default: 'لا توجد نتائج',
  },
  minCharsText: {
    type: String,
    default: 'اكتب حرفين على الأقل للبحث',
  },
})

const root = ref(null)
const inputRef = ref(null)
const isOpen = ref(false)
const focusedIndex = ref(-1)
const selectedItem = ref(null)
const resolving = ref(false)

const { query, results, loading, reset } = useDebouncedAsyncSearch(props.searchFn, {
  delay: props.debounceMs,
  minLength: props.minChars,
})

const hasSelection = computed(
  () => model.value !== '' && model.value != null && selectedItem.value,
)

const fieldClasses = computed(() => [
  'field-control',
  `field-control--${props.size}`,
  'app-autocomplete__input',
  {
    'field-control--error': props.hasError,
    'field-control--icon-start': true,
    'field-control--action-end': hasSelection.value,
  },
])

const menuMessage = computed(() => {
  if (resolving.value || loading.value) return 'جاري البحث...'
  if (!query.value.trim()) return props.minCharsText
  if (query.value.trim().length < props.minChars) return props.minCharsText
  if (!results.value.length) return props.emptyText
  return ''
})

const showMenu = computed(
  () => isOpen.value && (loading.value || resolving.value || menuMessage.value || results.value.length > 0),
)

function valuesMatch(a, b) {
  return String(a) === String(b)
}

function openMenu() {
  if (props.disabled || isOpen.value) return
  isOpen.value = true
  focusedIndex.value = results.value.length ? 0 : -1
}

function closeMenu() {
  isOpen.value = false
  focusedIndex.value = -1
}

function selectItem(item) {
  selectedItem.value = item
  model.value = props.getValue(item)
  query.value = props.getLabel(item)
  closeMenu()
}

function clearSelection() {
  model.value = ''
  selectedItem.value = null
  reset()
  inputRef.value?.focus()
  openMenu()
}

function onInput() {
  if (selectedItem.value && query.value !== props.getLabel(selectedItem.value)) {
    model.value = ''
    selectedItem.value = null
  }

  openMenu()
}

function onFocus() {
  openMenu()
}

function moveFocus(direction) {
  if (!results.value.length) return

  let next = focusedIndex.value

  do {
    next = (next + direction + results.value.length) % results.value.length
  } while (next !== focusedIndex.value)

  focusedIndex.value = next
}

function onKeydown(event) {
  if (props.disabled) return

  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault()
      if (!isOpen.value) openMenu()
      else moveFocus(1)
      break
    case 'ArrowUp':
      event.preventDefault()
      if (!isOpen.value) openMenu()
      else moveFocus(-1)
      break
    case 'Enter':
      event.preventDefault()
      if (isOpen.value && focusedIndex.value >= 0 && results.value[focusedIndex.value]) {
        selectItem(results.value[focusedIndex.value])
      }
      break
    case 'Escape':
      event.preventDefault()
      closeMenu()
      break
    case 'Tab':
      closeMenu()
      break
    default:
      break
  }
}

function onDocumentClick(event) {
  if (root.value && !root.value.contains(event.target)) {
    closeMenu()
  }
}

async function resolveSelected(value) {
  if (!value || !props.resolveFn) return

  resolving.value = true

  try {
    const item = await props.resolveFn(value)
    if (item) {
      selectedItem.value = item
      query.value = props.getLabel(item)
    }
  } catch {
    selectedItem.value = null
    query.value = ''
  } finally {
    resolving.value = false
  }
}

watch(
  () => model.value,
  (value) => {
    if (!value) {
      selectedItem.value = null
      if (!isOpen.value) query.value = ''
      return
    }

    if (selectedItem.value && valuesMatch(props.getValue(selectedItem.value), value)) return

    resolveSelected(value)
  },
  { immediate: true },
)

watch(results, (items) => {
  if (isOpen.value && items.length) {
    focusedIndex.value = 0
  }
})

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <div
    ref="root"
    class="field-shell app-autocomplete"
    :class="{
      'app-autocomplete--open': isOpen,
      'app-autocomplete--disabled': disabled,
      'app-autocomplete--error': hasError,
    }"
  >
    <i class="bi bi-search field-shell__icon field-shell__icon--start" aria-hidden="true"></i>

    <input
      :id="id"
      ref="inputRef"
      v-model="query"
      type="text"
      :class="fieldClasses"
      :placeholder="placeholder"
      :disabled="disabled"
      role="combobox"
      aria-autocomplete="list"
      :aria-expanded="isOpen"
      :aria-controls="id ? `${id}-listbox` : undefined"
      autocomplete="off"
      @input="onInput"
      @focus="onFocus"
      @keydown="onKeydown"
    />

    <button
      v-if="hasSelection"
      type="button"
      class="field-shell__action app-autocomplete__clear"
      aria-label="مسح الاختيار"
      @click="clearSelection"
    >
      <i class="bi bi-x-lg" aria-hidden="true"></i>
    </button>

    <ul
      v-show="showMenu"
      :id="id ? `${id}-listbox` : undefined"
      class="app-autocomplete__menu"
      role="listbox"
      :aria-labelledby="id || undefined"
    >
      <li
        v-if="menuMessage"
        class="app-autocomplete__status"
        role="presentation"
      >
        <span v-if="loading || resolving" class="app-autocomplete__spinner" aria-hidden="true"></span>
        {{ menuMessage }}
      </li>

      <li
        v-for="(item, index) in results"
        :key="`${getValue(item)}-${index}`"
        role="option"
        class="app-autocomplete__option"
        :class="{
          'app-autocomplete__option--focused': focusedIndex === index,
          'app-autocomplete__option--selected': selectedItem && valuesMatch(getValue(selectedItem), getValue(item)),
        }"
        :aria-selected="selectedItem && valuesMatch(getValue(selectedItem), getValue(item))"
        @click.stop="selectItem(item)"
        @mouseenter="focusedIndex = index"
      >
        <span class="app-autocomplete__option-body">
          <span class="app-autocomplete__option-label">{{ getLabel(item) }}</span>
          <span v-if="getDescription(item)" class="app-autocomplete__option-description">
            {{ getDescription(item) }}
          </span>
        </span>
        <i
          v-if="selectedItem && valuesMatch(getValue(selectedItem), getValue(item))"
          class="bi bi-check-lg app-autocomplete__option-check"
          aria-hidden="true"
        ></i>
      </li>
    </ul>
  </div>
</template>
