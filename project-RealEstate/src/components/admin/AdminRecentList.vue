<script setup>
import StatusBadge from '@/components/admin/StatusBadge.vue'
import { ESTATE_STATUS_LABELS, USER_STATUS_LABELS, USER_TYPE_LABELS } from '@/config/admin.js'
import { formatPrice } from '@/composables/useFormatters.js'

defineProps({
  type: {
    type: String,
    required: true,
    validator: (v) => ['users', 'estates'].includes(v),
  },
  items: {
    type: Array,
    default: () => [],
  },
})

function userName(item) {
  return `${item.fname ?? ''} ${item.lname ?? ''}`.trim() || item.username
}

function ownerName(estate) {
  const user = estate.user
  if (!user) return '—'
  return `${user.fname ?? ''} ${user.lname ?? ''}`.trim() || user.username
}
</script>

<template>
  <div class="admin-recent-list">
    <p v-if="!items.length" class="admin-recent-list__empty">لا توجد عناصر حديثة.</p>

    <ul v-else class="admin-recent-list__items">
      <li v-for="item in items" :key="item.id" class="admin-recent-list__item">
        <template v-if="type === 'users'">
          <div>
            <strong>{{ userName(item) }}</strong>
            <p class="admin-recent-list__meta">{{ item.email }}</p>
          </div>
          <div class="admin-recent-list__badges">
            <StatusBadge :status="item.type" :labels="USER_TYPE_LABELS" />
            <StatusBadge :status="item.status" :labels="USER_STATUS_LABELS" />
          </div>
        </template>

        <template v-else>
          <div>
            <strong>{{ item.name }}</strong>
            <p class="admin-recent-list__meta">
              {{ ownerName(item) }}
              <span v-if="item.place"> · {{ item.place.name }}</span>
            </p>
          </div>
          <div class="admin-recent-list__badges">
            <StatusBadge :status="item.status" :labels="ESTATE_STATUS_LABELS" />
            <span v-if="item.price" class="admin-recent-list__price">
              {{ formatPrice(item.price) }}
            </span>
          </div>
        </template>
      </li>
    </ul>
  </div>
</template>
