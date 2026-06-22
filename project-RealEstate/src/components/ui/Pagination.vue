<script setup>
import { computed } from 'vue'

defineOptions({ name: 'UiPagination' })
const props = defineProps({
  currentPage: {
    type: Number,
    required: true,
  },
  lastPage: {
    type: Number,
    required: true,
  },
  total: {
    type: Number,
    default: 0,
  },
})

const emit = defineEmits(['page-change'])

const pages = computed(() => {
  const range = []
  const start = Math.max(1, props.currentPage - 2)
  const end = Math.min(props.lastPage, props.currentPage + 2)

  for (let i = start; i <= end; i += 1) {
    range.push(i)
  }

  return range
})

function changePage(page) {
  if (page < 1 || page > props.lastPage || page === props.currentPage) return
  emit('page-change', page)
}
</script>

<template>
  <nav v-if="lastPage > 1" class="pagination-nav" aria-label="ترقيم الصفحات">
    <p v-if="total" class="pagination-nav__info">
      إجمالي {{ total }} نتيجة — الصفحة {{ currentPage }} من {{ lastPage }}
    </p>

    <ul class="pagination pagination-nav__list">
      <li class="page-item" :class="{ disabled: currentPage <= 1 }">
        <button class="page-link" type="button" @click="changePage(currentPage - 1)">
          <i class="bi bi-chevron-right"></i>
        </button>
      </li>

      <li
        v-for="page in pages"
        :key="page"
        class="page-item"
        :class="{ active: page === currentPage }"
      >
        <button class="page-link" type="button" @click="changePage(page)">
          {{ page }}
        </button>
      </li>

      <li class="page-item" :class="{ disabled: currentPage >= lastPage }">
        <button class="page-link" type="button" @click="changePage(currentPage + 1)">
          <i class="bi bi-chevron-left"></i>
        </button>
      </li>
    </ul>
  </nav>
</template>
