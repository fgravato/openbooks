<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useDateFormat } from '@/Composables/useDateFormat';
import { Link } from '@inertiajs/vue3';

const { formatCurrency } = useCurrency();
const { formatDate } = useDateFormat();

const props = defineProps<{
  invoice: any;
}>();
</script>

<template>
  <AppLayout title="Invoice Detail">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoice #{{ invoice.invoice_number }}</h1>
          <StatusBadge :status="invoice.status" type="invoice" />
        </div>
        <div class="flex space-x-2">
          <Button variant="white">Download PDF</Button>
          <Link :href="route('invoices.edit', invoice.id)">
            <Button variant="secondary">Edit</Button>
          </Link>
          <Button variant="primary">Send Invoice</Button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <Card no-padding>
            <div class="p-8">
              <div class="flex justify-between mb-8">
                <div>
                  <h2 class="text-xl font-bold">OpenBooks</h2>
                  <p class="text-gray-500">123 Business St, City, Country</p>
                </div>
                <div class="text-right">
                  <h3 class="text-lg font-bold">Invoice</h3>
                  <p class="text-gray-500">#{{ invoice.invoice_number }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-8 mb-8">
                <div>
                  <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bill To</h4>
                  <p class="font-bold">{{ invoice.client?.name }}</p>
                  <p class="text-gray-500">{{ invoice.client?.address }}</p>
                  <p class="text-gray-500">{{ invoice.client?.city }}, {{ invoice.client?.country }}</p>
                </div>
                <div class="text-right">
                  <div class="mb-2">
                    <span class="text-gray-500 mr-2">Issue Date:</span>
                    <span>{{ formatDate(invoice.issue_date) }}</span>
                  </div>
                  <div>
                    <span class="text-gray-500 mr-2">Due Date:</span>
                    <span>{{ formatDate(invoice.due_date) }}</span>
                  </div>
                </div>
              </div>

              <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 mb-8">
                <thead>
                  <tr>
                    <th class="py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Description</th>
                    <th class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Qty</th>
                    <th class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Price</th>
                    <th class="py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Total</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                  <tr v-for="item in invoice.line_items" :key="item.id">
                    <td class="py-4 text-sm">{{ item.description }}</td>
                    <td class="py-4 text-sm text-right">{{ item.quantity }}</td>
                    <td class="py-4 text-sm text-right">{{ formatCurrency(item.unit_price, invoice.currency_code) }}</td>
                    <td class="py-4 text-sm text-right font-medium">{{ formatCurrency(item.total, invoice.currency_code) }}</td>
                  </tr>
                </tbody>
              </table>

              <div class="flex justify-end">
                <div class="w-64 space-y-3">
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Subtotal</span>
                    <span>{{ formatCurrency(invoice.subtotal, invoice.currency_code) }}</span>
                  </div>
                  <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Tax</span>
                    <span>{{ formatCurrency(invoice.tax_total, invoice.currency_code) }}</span>
                  </div>
                  <div class="flex justify-between text-lg font-bold border-t border-gray-100 dark:border-gray-700 pt-3">
                    <span>Total</span>
                    <span>{{ formatCurrency(invoice.total, invoice.currency_code) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </div>

        <div class="space-y-6">
          <Card title="Payments">
            <div v-if="invoice.payments.length === 0" class="text-sm text-gray-500 text-center py-4">
              No payments recorded yet.
            </div>
            <div v-else class="space-y-4">
              <div v-for="payment in invoice.payments" :key="payment.id" class="flex justify-between items-center text-sm">
                <div>
                  <p class="font-medium">{{ formatCurrency(payment.amount, invoice.currency_code) }}</p>
                  <p class="text-xs text-gray-400">{{ formatDate(payment.payment_date) }}</p>
                </div>
                <Badge variant="success">Paid</Badge>
              </div>
            </div>
            <template #footer>
              <Button variant="secondary" size="sm" class="w-full">Record Payment</Button>
            </template>
          </Card>

          <Card title="Activity">
            <!-- Timeline here -->
            <p class="text-sm text-gray-500">No recent activity.</p>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
