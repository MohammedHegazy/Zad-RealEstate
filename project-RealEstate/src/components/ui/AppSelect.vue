<script setup>
import { computed, Fragment, onMounted, onUnmounted, ref, useSlots, watch } from 'vue'

const model = defineModel({ type: [String, Number], default: '' })

const props = defineProps({
  id: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  hasError: {
    type: Boolean,
    default: false,
  },
  placeholder: {
    type: String,
    default: '',
  },
  options: {
    type: Array,
    default: () => [],
    // { value, label, disabled? }
  },
  size: {
    type: String,
    default: 'md',
    validator: (v) => ['sm', 'md', 'lg'].includes(v),
  },
})

const slots = useSlots()
const root = ref(null)
const isOpen = ref(false)
const focusedIndex = ref(-1)

function extractNodeText(node) {
  if (node == null || node === false) return ''
  if (typeof node === 'string' || typeof node === 'number') return String(node)
  if (Array.isArray(node)) return node.map(extractNodeText).join('')
  if (typeof node.children === 'string') return node.children
  if (typeof node.children === 'function') return extractNodeText(node.children())
  if (Array.isArray(node.children)) return node.children.map(extractNodeText).join('')
  return ''
}

function parseOptionNodes(nodes) {
  const options = []

  for (const node of nodes) {
    if (!node || typeof node !== 'object') continue

    if (node.type === 'option') {
      options.push({
        value: node.props?.value ?? '',
        label: extractNodeText(node.children) || String(node.props?.value ?? ''),
        disabled: Boolean(node.props?.disabled),
      })
      continue
    }

    if (node.type === Fragment) {
      options.push(...parseOptionNodes(node.children ?? []))
      continue
    }

    if (Array.isArray(node.children)) {
      options.push(...parseOptionNodes(node.children))
    }
  }

  return options
}

const slotOptions = computed(() => parseOptionNodes(slots.default?.() ?? []))

const resolvedOptions = computed(() => {
  if (props.options.length) return props.options
  return slotOptions.value
})

const selectedIndex = computed(() =>
  resolvedOptions.value.findIndex((opt) => valuesMatch(opt.value, model.value)),
)

const selectedOption = computed(() =>
  selectedIndex.value >= 0 ? resolvedOptions.value[selectedIndex.value] : null,
)

const displayLabel = computed(() => {
  if (selectedOption.value) return selectedOption.value.label
  if (props.placeholder && (model.value === '' || model.value == null)) return props.placeholder
  return ''
})

const isPlaceholder = computed(
  () => !selectedOption.value && Boolean(props.placeholder) && (model.value === '' || model.value == null),
)

const fieldClasses = computed(() => [
  'field-control',
  'field-control--select',
  `field-control--${props.size}`,
  {
    'field-control--error': props.hasError,
    'app-select__trigger--placeholder': isPlaceholder.value,
  },
])

function valuesMatch(a, b) {
  return String(a) === String(b)
}

function isSelected(opt) {
  return valuesMatch(opt.value, model.value)
}

function openMenu() {
  if (props.disabled || isOpen.value) return
  isOpen.value = true
  focusedIndex.value = selectedIndex.value >= 0 ? selectedIndex.value : 0
}

function closeMenu() {
  isOpen.value = false
  focusedIndex.value = -1
}

function toggleMenu() {
  if (props.disabled) return
  if (isOpen.value) closeMenu()
  else openMenu()
}

function selectOption(opt) {
  if (opt.disabled) return
  model.value = opt.value
  closeMenu()
}

function moveFocus(direction) {
  const opts = resolvedOptions.value
  if (!opts.length) return

  let next = focusedIndex.value

  do {
    next = (next + direction + opts.length) % opts.length
  } while (opts[next]?.disabled && next !== focusedIndex.value)

  focusedIndex.value = next
}

function onTriggerKeydown(event) {
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
    case ' ':
      event.preventDefault()
      if (!isOpen.value) {
        openMenu()
      } else if (focusedIndex.value >= 0) {
        selectOption(resolvedOptions.value[focusedIndex.value])
      }
      break
    case 'Escape':
      event.preventDefault()
      closeMenu()
      break
    case 'Home':
      event.preventDefault()
      if (isOpen.value) focusedIndex.value = resolvedOptions.value.findIndex((opt) => !opt.disabled)
      break
    case 'End':
      event.preventDefault()
      if (isOpen.value) {
        focusedIndex.value = resolvedOptions.value.findLastIndex((opt) => !opt.disabled)
      }
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

watch(isOpen, (open) => {
  if (open && focusedIndex.value < 0) {
    focusedIndex.value = selectedIndex.value >= 0 ? selectedIndex.value : 0
  }
})

onMounted(() => document.addEventListener('click', onDocumentClick))
onUnmounted(() => document.removeEventListener('click', onDocumentClick))
</script>

<template>
  <div
    ref="root"
    class="field-shell app-select-wrap app-select"
    :class="{
      'app-select--open': isOpen,
      'app-select--disabled': disabled,
      'app-select--error': hasError,
    }"
  >
    <button
      :id="id"
      type="button"
      class="app-select__trigger"
      :class="fieldClasses"
      :disabled="disabled"
      role="combobox"
      aria-haspopup="listbox"
      :aria-expanded="isOpen"
      :aria-controls="id ? `${id}-listbox` : undefined"
      @click="toggleMenu"
      @keydown="onTriggerKeydown"
    >
      <span class="app-select__value">{{ displayLabel }}</span>
    </button>

    <i
      class="bi bi-chevron-down app-select__chevron"
      :class="{ 'app-select__chevron--open': isOpen }"
      aria-hidden="true"
    ></i>

    <ul
      v-show="isOpen"
      :id="id ? `${id}-listbox` : undefined"
      class="app-select__menu"
      role="listbox"
      :aria-labelledby="id || undefined"
    >
      <li
        v-for="(opt, index) in resolvedOptions"
        :key="`${opt.value}-${index}`"
        role="option"
        class="app-select__option"
        :class="{
          'app-select__option--selected': isSelected(opt),
          'app-select__option--focused': focusedIndex === index,
          'app-select__option--disabled': opt.disabled,
        }"
        :aria-selected="isSelected(opt)"
        :aria-disabled="opt.disabled || undefined"
        @click.stop="selectOption(opt)"
        @mouseenter="focusedIndex = index"
      >
        <span class="app-select__option-label">{{ opt.label }}</span>
        <i v-if="isSelected(opt)" class="bi bi-check-lg app-select__option-check" aria-hidden="true"></i>
      </li>
    </ul>
  </div>
</template>
