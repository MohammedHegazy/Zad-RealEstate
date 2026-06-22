<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
  },
  rows: {
    type: Array,
    default: () => [],
  },
  rowKey: {
    type: String,
    default: 'id',
  },
  emptyMessage: {
    type: String,
    default: 'لا توجد بيانات.',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  skeletonRows: {
    type: Number,
    default: 5,
  },
})

const SKELETON_COLS = ['50', '70', '40', '55', '45', '60']
</script>

<template>
  <div class="admin-table-wrap">
    <table class="table admin-table">
      <thead>
        <tr>
          <th v-for="col in columns" :key="col.key" :class="col.class">
            {{ col.label }}
          </th>
          <th v-if="$slots.actions" class="admin-table__actions-col">إجراءات</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td
            v-for="(w, i) in SKELETON_COLS.slice(0, columns.length + ($slots.actions ? 1 : 0))"
            :key="i"
            class="admin-table__skeleton"
          >
            <div class="admin-table__skeleton-bar" :style="{ width: w + '%' }" />
          </td>
        </tr>
        <tr v-else-if="!rows.length">
          <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="admin-table__empty">
            <div class="admin-table__empty-icon">
              <i class="bi bi-inbox"></i>
            </div>
            <p>{{ emptyMessage }}</p>
          </td>
        </tr>
        <tr v-for="row in rows" :key="row[rowKey]">
          <td v-for="col in columns" :key="col.key" :class="col.class">
            <slot :name="`cell-${col.key}`" :row="row">
              {{ row[col.key] ?? '—' }}
            </slot>
          </td>
          <td v-if="$slots.actions" class="admin-table__actions">
            <slot name="actions" :row="row" />
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
