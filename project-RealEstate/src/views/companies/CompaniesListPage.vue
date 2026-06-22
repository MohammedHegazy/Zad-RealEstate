<script setup>
import CompanyCard from '@/components/cards/CompanyCard.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { companiesService } from '@/api/companies.js'
import { usePaginatedList } from '@/composables/usePaginatedList.js'

const narrative = PAGE_NARRATIVES.companies
const { loading, error, items, pagination, fetchItems, goToPage } = usePaginatedList(
  (params) => companiesService.list(params),
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
        icon="bi-briefcase"
        title="لا توجد شركات"
        message="لم يتم تسجيل شركات معتمدة بعد."
      />

      <template v-else>
        <div class="row g-4">
          <div v-for="company in items" :key="company.id" class="col-md-6 col-lg-4">
            <CompanyCard :company="company" />
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
