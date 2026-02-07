<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Table from '@/Components/UI/Table.vue';
import Pagination from '@/Components/UI/Pagination.vue';
import Button from '@/Components/UI/Button.vue';
import SearchInput from '@/Components/UI/SearchInput.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';

const { formatCurrency } = useCurrency();

const props = defineProps<{
  clients: any;
  filters: any;
}>();

const columns = [
  { key: 'name', label: 'Client Name' },
  { key: 'email', label: 'Email' },
  { key: 'balance', label: 'Balance', align: 'right' as const },
  { key: 'actions', label: '', align: 'right' as const },
];

const search = ref(props.filters.search || '');

watch(search, debounce((value) => {
  router.get(route('clients.index'), { search: value }, { preserveState: true, replace: true });
}, 300));
</script>

<template>
  <AppLayout title="Clients">
    <div class="space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Clients</h1>
        <Link :href="route('clients.create')">
          <Button>New Client</Button>
        </Link>
      </div>

      <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="w-64">
          <SearchInput v-model="search" placeholder="Search clients..." />
        </div>
      </div>

      <Table :columns="columns" :data="clients.data">
        <template #cell-balance="{ item }">
          {{ formatCurrency(item.balance, item.currency_code) }}
        </template>
        <template #cell-actions="{ item }">
          <Link :href="route('clients.show', item.id)" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</Link>
        </template>
      </Table>

      <Pagination :links="clients.links" :meta="clients.meta" />
    </div>
  </AppLayout>
</template>
