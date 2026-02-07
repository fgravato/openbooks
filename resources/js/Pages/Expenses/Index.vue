<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Button from '@/Components/UI/Button.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useDateFormat } from '@/Composables/useDateFormat';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';

const { formatCurrency } = useCurrency();
const { formatDate } = useDateFormat();

const props = defineProps<{
  expenses: any;
  filters: any;
}>();

const columns = [
  { key: 'date', label: 'Date' },
  { key: 'vendor', label: 'Vendor' },
  { key: 'category', label: 'Category' },
  { key: 'amount', label: 'Amount', align: 'right' as const },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '', align: 'right' as const },
];

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
  router.get(route('expenses.index'), { search: value }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
  <AppLayout title="Expenses">
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Expenses</h1>
        <Link :href="route('expenses.create')">
          <Button>Record Expense</Button>
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="w-64">
          <SearchInput v-model="search" placeholder="Search expenses..." />
        </div>
      </div>

      <Table :columns="columns" :data="expenses.data">
        <template #cell-date="{ item }">
          {{ formatDate(item.date) }}
        </template>
        <template #cell-category="{ item }">
          {{ item.category?.name }}
        </template>
        <template #cell-amount="{ item }">
          {{ formatCurrency(item.amount, item.currency_code) }}
        </template>
        <template #cell-status="{ item }">
          <StatusBadge :status="item.status" type="expense" />
        </template>
        <template #cell-actions="{ item }">
          <Link :href="route('expenses.edit', item.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">Edit</Link>
        </template>
      </Table>

      <Pagination :links="expenses.links" :meta="expenses.meta" />
    </div>
  </AppLayout>
</template>
