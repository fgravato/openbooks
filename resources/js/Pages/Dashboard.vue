<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import Card from '@/Components/UI/Card.vue';
import Table from '@/Components/UI/Table.vue';
import Badge from '@/Components/UI/Badge.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import { useCurrency } from '@/Composables/useCurrency';

const { formatCurrency } = useCurrency();

const props = defineProps<{
  stats: {
    revenue: number;
    outstanding: number;
    overdue: number;
    expenses: number;
  };
  recentInvoices: any[];
}>();

const columns = [
  { key: 'invoice_number', label: 'Number' },
  { key: 'client', label: 'Client' },
  { key: 'total', label: 'Total', align: 'right' as const },
  { key: 'status', label: 'Status' },
];
</script>

<template>
  <AppLayout title="Dashboard">
    <div class="space-y-6">
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <StatCard title="Revenue (Monthly)" :value="formatCurrency(stats.revenue)" change="12%" changeType="increase" />
        <StatCard title="Outstanding" :value="formatCurrency(stats.outstanding)" />
        <StatCard title="Overdue" :value="formatCurrency(stats.overdue)" changeType="decrease" />
        <StatCard title="Expenses (Monthly)" :value="formatCurrency(stats.expenses)" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <Card title="Recent Invoices">
          <Table :columns="columns" :data="recentInvoices">
            <template #cell-client="{ item }">
              {{ item.client?.name }}
            </template>
            <template #cell-total="{ item }">
              {{ formatCurrency(item.total, item.currency_code) }}
            </template>
            <template #cell-status="{ item }">
              <StatusBadge :status="item.status" type="invoice" />
            </template>
          </Table>
        </Card>

        <Card title="Revenue Overview">
          <div class="h-64 flex items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-lg">
            <p class="text-gray-500">Chart will be here</p>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
