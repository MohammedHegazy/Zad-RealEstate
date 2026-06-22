<script setup>
import PropertyCard from '@/components/cards/PropertyCard.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { favoritesService } from '@/api/favorites.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const narrative = PAGE_NARRATIVES.favorites

const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => favoritesService.list({ ...params, active_only: true }),
)

const estates = () => items.value.map((fav) => fav.estate).filter(Boolean)
</script>

<template>
  <div class="directory-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="narrative.title"
        :description="narrative.description"
        :step="narrative.step"
        icon="bi-heart"
      />

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

      <EmptyState
        v-else-if="!estates().length"
        icon="bi-heart"
        title="لا توجد مفضلات بعد"
        message="تصفح العقارات وأضف ما يعجبك إلى قائمة المفضلة."
      >
        <AppButton to="/estates" variant="primary">تصفح العقارات</AppButton>
      </EmptyState>

      <template v-else>
        <div class="row g-4">
          <div v-for="estate in estates()" :key="estate.id" class="col-sm-6 col-lg-4 col-xl-3">
            <PropertyCard :estate="estate" />
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

      <CtaBanner
        title="واصل استكشاف العقارات"
        description="كلما تصفحت أكثر، زادت فرصك في إيجاد العقار المثالي."
        :primary="{ label: 'تصفح العقارات', to: '/estates' }"
        :secondary="{ label: 'التوصيات', to: '/recommendations' }"
      />
    </div>
  </div>
</template>
