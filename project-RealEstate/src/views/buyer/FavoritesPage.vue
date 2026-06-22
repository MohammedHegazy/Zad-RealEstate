<script setup>
import PropertyCard from '@/components/cards/PropertyCard.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import AppButton from '@/components/ui/AppButton.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { favoritesService } from '@/api/favorites.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => favoritesService.list({ ...params, active_only: true }),
)

const estates = () => items.value.map((fav) => fav.estate).filter(Boolean)
</script>

<template>
  <div class="admin-page">
    <AdminPageHeader
      title="المفضلة"
      description="العقارات التي أضفتها إلى مفضلتك."
    >
      <template #actions>
        <AppButton to="/estates" variant="outline" size="sm">
          <i class="bi bi-search"></i>
          تصفح العقارات
        </AppButton>
      </template>
    </AdminPageHeader>

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
      <p class="admin-page__meta">{{ pagination?.total }} عقار</p>
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
  </div>
</template>
