<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  modelValue: {
    type: [File, Array],
    default: null,
  },
  accept: {
    type: String,
    default: 'image/*',
  },
  multiple: {
    type: Boolean,
    default: false,
  },
  maxSize: {
    type: Number,
    default: 5 * 1024 * 1024,
  },
  hint: {
    type: String,
    default: '',
  },
  hasError: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['update:modelValue', 'error'])

const isDragging = ref(false)
const dropZoneRef = ref(null)

const dragStateClass = computed(() => ({
  'app-file-upload--dragging': isDragging.value,
  'app-file-upload--error': props.hasError,
  'app-file-upload--has-value': props.multiple ? (props.modelValue?.length > 0) : !!props.modelValue,
}))

function onDragOver(event) {
  event.preventDefault()
  event.stopPropagation()
  isDragging.value = true
}

function onDragLeave(event) {
  event.preventDefault()
  event.stopPropagation()
  isDragging.value = false
}

function onDrop(event) {
  event.preventDefault()
  event.stopPropagation()
  isDragging.value = false
  handleFiles(event.dataTransfer.files)
}

function onFileInput(event) {
  handleFiles(event.target.files)
  event.target.value = ''
}

function handleFiles(fileList) {
  const files = Array.from(fileList)
  if (!files.length) return

  const validFiles = files.filter((f) => {
    if (props.maxSize && f.size > props.maxSize) {
      emit('error', `الملف "${f.name}" حجمه كبير جداً. الحد الأقصى ${(props.maxSize / 1024 / 1024).toFixed(0)}MB`)
      return false
    }
    return true
  })

  if (!validFiles.length) return

  if (props.multiple) {
    const existing = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    emit('update:modelValue', [...existing, ...validFiles])
  } else {
    emit('update:modelValue', validFiles[0])
  }
}

function removeFile(index) {
  if (props.multiple && Array.isArray(props.modelValue)) {
    const updated = props.modelValue.filter((_, i) => i !== index)
    emit('update:modelValue', updated)
  } else {
    emit('update:modelValue', null)
  }
}

function formatSize(bytes) {
  if (!bytes) return ''
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}
</script>

<template>
  <div
    ref="dropZoneRef"
    class="app-file-upload"
    :class="dragStateClass"
    @dragover="onDragOver"
    @dragleave="onDragLeave"
    @drop="onDrop"
  >
    <input
      type="file"
      :accept="accept"
      :multiple="multiple"
      class="app-file-upload__input"
      @change="onFileInput"
    />

    <div v-if="!multiple || !modelValue?.length" class="app-file-upload__placeholder">
      <i class="bi bi-cloud-arrow-up app-file-upload__icon"></i>
      <p class="app-file-upload__text">
        <span class="app-file-upload__link">اضغط لاختيار ملف</span>
        أو اسحب وأفلت
      </p>
      <p v-if="hint" class="app-file-upload__hint">{{ hint }}</p>
    </div>

    <div v-if="modelValue && !multiple" class="app-file-upload__file">
      <img
        v-if="accept.includes('image') && typeof modelValue === 'object'"
        :src="URL.createObjectURL(modelValue)"
        alt="Preview"
        class="app-file-upload__thumb"
      />
      <div class="app-file-upload__file-info">
        <span class="app-file-upload__file-name">{{ modelValue.name }}</span>
        <span class="app-file-upload__file-size">{{ formatSize(modelValue.size) }}</span>
      </div>
      <button type="button" class="app-file-upload__remove" @click="removeFile(0)" aria-label="إزالة">
        <i class="bi bi-x"></i>
      </button>
    </div>

    <div v-if="multiple && modelValue?.length" class="app-file-upload__files">
      <div v-for="(file, idx) in modelValue" :key="idx" class="app-file-upload__file">
        <img
          v-if="accept.includes('image')"
          :src="URL.createObjectURL(file)"
          alt="Preview"
          class="app-file-upload__thumb"
        />
        <div class="app-file-upload__file-info">
          <span class="app-file-upload__file-name">{{ file.name }}</span>
          <span class="app-file-upload__file-size">{{ formatSize(file.size) }}</span>
        </div>
        <button type="button" class="app-file-upload__remove" @click="removeFile(idx)" aria-label="إزالة">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.app-file-upload {
  position: relative;
  border: 2px dashed var(--color-border);
  border-radius: 0.75rem;
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s, background-color 0.2s;
  background-color: var(--color-surface);
}
.app-file-upload:hover {
  border-color: var(--color-primary);
  background-color: var(--color-surface-hover);
}
.app-file-upload--dragging {
  border-color: var(--color-primary);
  background-color: color-mix(in srgb, var(--color-primary) 8%, transparent);
}
.app-file-upload--error {
  border-color: var(--color-error);
}
.app-file-upload--has-value {
  border-style: solid;
}
.app-file-upload__input {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
}
.app-file-upload__placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.375rem;
}
.app-file-upload__icon {
  font-size: 2rem;
  color: var(--color-text-muted);
}
.app-file-upload__text {
  margin: 0;
  color: var(--color-text-secondary);
  font-size: var(--text-sm);
}
.app-file-upload__link {
  color: var(--color-primary);
  text-decoration: underline;
  text-underline-offset: 2px;
}
.app-file-upload__hint {
  margin: 0;
  color: var(--color-text-muted);
  font-size: var(--text-xs);
}
.app-file-upload__files {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 0.75rem;
}
.app-file-upload__file {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background-color: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 0.5rem;
  text-align: start;
  width: 100%;
}
.app-file-upload__thumb {
  width: 3rem;
  height: 3rem;
  object-fit: cover;
  border-radius: 0.375rem;
  flex-shrink: 0;
}
.app-file-upload__file-info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  min-width: 0;
  flex: 1;
}
.app-file-upload__file-name {
  font-size: var(--text-sm);
  font-weight: 500;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.app-file-upload__file-size {
  font-size: var(--text-xs);
  color: var(--color-text-muted);
}
.app-file-upload__remove {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.5rem;
  height: 1.5rem;
  border: none;
  border-radius: 50%;
  background-color: var(--color-error);
  color: var(--white);
  cursor: pointer;
  flex-shrink: 0;
  transition: opacity 0.15s;
}
.app-file-upload__remove:hover {
  opacity: 0.85;
}
</style>
