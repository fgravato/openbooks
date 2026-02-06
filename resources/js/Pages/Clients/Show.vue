<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Tabs from '@/Components/UI/Tabs.vue';
import Table from '@/Components/UI/Table.vue';
import Button from '@/Components/UI/Button.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

const { formatCurrency } = useCurrency();

const props = defineProps<{
  client: any;
  invoices: any[];
}>();

const currentTab = ref('overview');
const tabs = [
  { key: 'overview', label: 'Overview' },
  { key: 'invoices', label: 'Invoices' },
  { key: 'contacts', label: 'Contacts' },
];

const invoiceColumns = [
  { key: 'invoice_number', label: 'Number' },
  { key: 'issue_date', label: 'Date' },
  { key: 'total', label: 'Total', align: 'right' as const },
  { key: 'status', label: 'Status' },
];
</script>

<template>
  <AppLayout :title="client.name">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ client.company_name || client.name }}</h1>
        <div class="space-x-2">
          <Link :href="route('clients.edit', client.id)">
            <Button variant="secondary">Edit Client</Button>
          </Link>
          <Link :href="route('invoices.create', { client_id: client.id })">
            <Button>Create Invoice</Button>
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <StatCard title="Total Invoiced" :value="formatCurrency(client.total_invoiced)" />
        <StatCard title="Outstanding Balance" :value="formatCurrency(client.balance)" changeType="danger" />
        <StatCard title="Invoices Paid" :value="client.paid_invoices_count" />
      </div>

      <Tabs v-model="currentTab" :tabs="tabs" />

      <div v-if="currentTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card title="Client Information">
          <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500">Email</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ client.email }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Phone</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ client.phone || 'N/A' }}</dd>
            </div>
            <div class="sm:col-span-2">
              <dt class="text-sm font-medium text-gray-500">Address</dt>
              <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                {{ client.address }}<br>
                {{ client.city }}, {{ client.state }} {{ client.postal_code }}<br>
                {{ client.country }}
              </dd>
            </div>
          </dl>
        </Card>
      </div>

      <div v-if="currentTab === 'invoices'">
        <Card no-padding>
          <Table :columns="invoiceColumns" :data="invoices">
            <template #cell-total="{ item }">
              {{ formatCurrency(item.total, item.currency_code) }}
            </template>
          </Table>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
