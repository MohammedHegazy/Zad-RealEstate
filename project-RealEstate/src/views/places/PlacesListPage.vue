<script setup>
import { useRoute } from 'vue-router'

import CtaBanner from '@/components/ui/CtaBanner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { placesService } from '@/api/places.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const route = useRoute()
const narrative = PAGE_NARRATIVES.places

const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => placesService.list(params),
  {
    extraParams: (r) => {
      const params = {}
      if (r.query.cities_id) params.cities_id = r.query.cities_id
      if (r.query.search) params.search = r.query.search
      return params
    },
  },
)
</script>

<template>
  <div class="directory-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="route.query.cities_id ? 'مناطق المدينة' : narrative.title"
        :description="narrative.description"
        :step="narrative.step"
      />

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

      <EmptyState
        v-else-if="!items.length"
        icon="bi-pin-map"
        title="لا توجد مناطق"
        message="جرّب مدينة أخرى أو تصفح العقارات مباشرة."
      />

      <template v-else>
        <div class="directory-place-list">
          <RouterLink
            v-for="place in items"
            :key="place.id"
            :to="`/places/${place.id}`"
            class="directory-place-list__item"
          >
            <div>
              <h3>{{ place.name }}</h3>
              <p>{{ place.city?.name }}</p>
            </div>
            <span v-if="place.active_estates_count" class="directory-place-list__count">
              {{ place.active_estates_count }} عقار
            </span>
            <i class="bi bi-arrow-left"></i>
          </RouterLink>
        </div>

        <Pagination
          v-if="pagination"
          :current-page="pagination.current_page"
          :last-page="pagination.last_page"
          :total="pagination.total"
          @page-change="goToPage"
        />
      </template>

      <CtaBanner v-bind="narrative.cta" />
    </div>
  </div>
</template>
