<script setup>
import CityCard from '@/components/cards/CityCard.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { citiesService } from '@/api/cities.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const narrative = PAGE_NARRATIVES.cities
const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => citiesService.list(params),
)
</script>

<template>
  <div class="directory-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="narrative.title"
        :description="narrative.description"
        :step="narrative.step"
      />

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

      <EmptyState
        v-else-if="!items.length"
        icon="bi-geo-alt"
        title="لا توجد مدن"
        message="لم يتم العثور على مدن في المنصة بعد."
      />

      <template v-else>
        <div class="row g-4">
          <div v-for="city in items" :key="city.id" class="col-sm-6 col-lg-4 col-xl-3">
            <CityCard :city="city" />
          </div>
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
