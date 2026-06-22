<script setup>
import PropertyCard from '@/components/cards/PropertyCard.vue'
import EstateFilters from '@/components/estates/EstateFilters.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import Pagination from '@/components/ui/Pagination.vue'
import AppButton from '@/components/ui/AppButton.vue'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { useEstatesList } from '@/composables/useEstatesList.js'

const narrative = PAGE_NARRATIVES.estates

const {
  loading,
  error,
  displayedEstates,
  pagination,
  filters,
  hasListingTypeFilter,
  fetchEstates,
  applyFilters,
  goToPage,
} = useEstatesList()

function resetFilters() {
  applyFilters({
    search: '',
    cities_id: '',
    places_id: '',
    type_text: '',
    kind_text: '',
    listing_type: '',
    is_furnished: '',
    min_price: '',
    max_price: '',
    min_bedrooms: '',
    sort: 'latest',
    per_page: filters.value.per_page,
  })
}
</script>

<template>
  <div class="estates-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="narrative.title"
        :description="narrative.description"
        :step="narrative.step"
        icon="bi-building"
      />

      <div class="estates-page__layout">
        <EstateFilters
          :model-value="filters"
          @apply="applyFilters"
          @reset="resetFilters"
        />

        <div class="estates-page__results">
          <div v-if="pagination" class="estates-page__toolbar">
            <p class="estates-page__count">
              <strong>{{ pagination.total }}</strong> عقار متاح
            </p>
            <AppButton to="/estates/map" variant="outline" size="sm">
              <i class="bi bi-map"></i>
              البحث على الخريطة
            </AppButton>
            <p v-if="hasListingTypeFilter" class="estates-page__hint">
              <i class="bi bi-info-circle"></i>
              تصفية البيع/الإيجار تُطبَّق على نتائج الصفحة الحالية
            </p>
          </div>

          <LoadingSpinner v-if="loading" />

          <ErrorAlert
            v-else-if="error"
            :message="error"
            @retry="fetchEstates"
          />

          <EmptyState
            v-else-if="!displayedEstates.length"
            icon="bi-building"
            title="لا توجد عقارات"
            message="جرّب تعديل معايير البحث أو مسح الفلاتر."
          >
            <AppButton variant="outline" @click="resetFilters">مسح الفلاتر</AppButton>
          </EmptyState>

          <template v-else>
            <div class="row g-4">
              <div
                v-for="estate in displayedEstates"
                :key="estate.id"
                class="col-sm-6 col-xl-4"
              >
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
      </div>

      <CtaBanner
        title="تحتاج مساعدة في قرارك؟"
        description="تواصل مع وكلاء وشركات معتمدة ليرشدوك في رحلتك العقارية."
        :primary="{ label: 'الوكلاء المعتمدون', to: '/agents' }"
        :secondary="{ label: 'الشركات العقارية', to: '/companies' }"
      />
    </div>
  </div>
</template>
