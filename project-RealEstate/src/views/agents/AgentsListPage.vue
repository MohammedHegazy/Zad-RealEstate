<script setup>
import { computed } from 'vue'

import AgentCard from '@/components/cards/AgentCard.vue'
import CtaBanner from '@/components/ui/CtaBanner.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import DirectoryToolbar from '@/components/ui/DirectoryToolbar.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import ErrorAlert from '@/components/ui/ErrorAlert.vue'
import LoadingSpinner from '@/components/ui/LoadingSpinner.vue'
import PageIntro from '@/components/ui/PageIntro.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { AGENT_SORT_OPTIONS } from '@/config/agents.js'
import { PAGE_NARRATIVES } from '@/config/journey.js'
import { useAgentsList } from '@/composables/useAgentsList.js'

const narrative = PAGE_NARRATIVES.agents

const {
  loading,
  error,
  items,
  pagination,
  fetchItems,
  goToPage,
  companies,
  companiesLoading,
  search,
  companiesId,
  sort,
  hasActiveFilters,
  clearFilters,
} = useAgentsList()

const companyOptions = computed(() => [
  { value: '', label: 'كل الشركات' },
  ...companies.value.map((company) => ({
    value: String(company.id),
    label: company.company_name,
  })),
])
</script>

<template>
  <div class="directory-page agents-page">
    <div class="container">
      <PageIntro
        :overline="narrative.overline"
        :title="narrative.title"
        :description="narrative.description"
        :step="narrative.step"
        icon="bi-people"
      />

      <DirectoryToolbar
        v-model:search="search"
        search-placeholder="ابحث باسم الوسيط أو الشركة..."
        :show-clear="hasActiveFilters"
        @clear="clearFilters"
      >
        <template #filters>
          <div class="filter-wrap">
            <i class="bi bi-buildings filter-wrap__icon"></i>
            <AppSelect
              v-model="companiesId"
              size="sm"
              :options="companyOptions"
              :disabled="companiesLoading"
            />
          </div>
          <div class="filter-wrap">
            <i class="bi bi-sort-down filter-wrap__icon"></i>
            <AppSelect v-model="sort" size="sm" :options="AGENT_SORT_OPTIONS" />
          </div>
        </template>
      </DirectoryToolbar>

      <div v-if="pagination?.total" class="agents-page__meta">
        <span>{{ pagination.total }} وسيط عقاري</span>
      </div>

      <LoadingSpinner v-if="loading" />
      <ErrorAlert v-else-if="error" :message="error" @retry="fetchItems" />

      <EmptyState
        v-else-if="!items.length"
        icon="bi-person-badge"
        title="لا يوجد وسطاء"
        :message="
          hasActiveFilters
            ? 'جرّب تعديل معايير البحث أو مسح الفلاتر.'
            : 'لم يتم تسجيل وسطاء عقاريين بعد.'
        "
      />

      <template v-else>
        <div class="row g-4">
          <div v-for="agent in items" :key="agent.id" class="col-sm-6 col-lg-4 col-xl-3">
            <AgentCard :agent="agent" />
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
