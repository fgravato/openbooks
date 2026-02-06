<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Button from '@/Components/UI/Button.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const { formatCurrency } = useCurrency();

const props = defineProps<{
  invoices: any;
  filters: any;
}>();

const columns = [
  { key: 'invoice_number', label: 'Number' },
  { key: 'client', label: 'Client' },
  { key: 'issue_date', label: 'Date' },
  { key: 'total', label: 'Total', align: 'right' as const },
  { key: 'status', label: 'Status' },
  { key: 'actions', label: '', align: 'right' as const },
];

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
  router.get(route('invoices.index'), { search: value }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
  <AppLayout title="Invoices">
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoices</h1>
        <Link :href="route('invoices.create')">
          <Button>New Invoice</Button>
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 flex justify-between">
        <div class="w-64">
          <SearchInput v-model="search" placeholder="Search invoices..." />
        </div>
        <!-- Other filters here -->
      </div>

      <Table :columns="columns" :data="invoices.data">
        <template #cell-client="{ item }">
          {{ item.client?.name }}
        </template>
        <template #cell-total="{ item }">
          {{ formatCurrency(item.total, item.currency_code) }}
        </template>
        <template #cell-status="{ item }">
          <StatusBadge :status="item.status" type="invoice" />
        </template>
        <template #cell-actions="{ item }">
          <Link :href="route('invoices.show', item.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</Link>
        </template>
      </Table>

      <Pagination :links="invoices.links" :meta="invoices.meta" />
    </div>
  </AppLayout>
</template>
