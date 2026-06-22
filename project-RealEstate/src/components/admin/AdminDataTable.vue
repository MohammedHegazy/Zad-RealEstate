<script setup>
defineProps({
  columns: {
    type: Array,
    required: true,
    // { key, label, class? }
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
})
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
        <tr v-if="!rows.length">
          <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="admin-table__empty">
            {{ emptyMessage }}
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
