<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import ClientSelect from '@/Components/Forms/ClientSelect.vue';
import InvoiceLineItem from '@/Components/Forms/InvoiceLineItem.vue';
import DatePicker from '@/Components/UI/DatePicker.vue';
import TextArea from '@/Components/UI/TextArea.vue';
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { computed } from 'vue';

const { formatCurrency } = useCurrency();

const props = defineProps<{
  invoice: any;
}>();

const form = useForm({
  client_id: props.invoice.client_id,
  invoice_number: props.invoice.invoice_number,
  issue_date: props.invoice.issue_date,
  due_date: props.invoice.due_date,
  line_items: props.invoice.line_items.map((item: any) => ({...item})),
  notes: props.invoice.notes || '',
  terms: props.invoice.terms || '',
});

const subtotal = computed(() => {
  return form.line_items.reduce((acc, item) => acc + (item.quantity * item.unit_price), 0);
});

const addLine = () => {
  form.line_items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeLine = (index: number) => {
  form.line_items.splice(index, 1);
};

const submit = () => {
  form.put(route('invoices.update', props.invoice.id));
};
</script>

<template>
  <AppLayout title="Edit Invoice">
    <form @submit.prevent="submit" class="space-y-6 max-w-5xl mx-auto">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Invoice #{{ invoice.invoice_number }}</h1>
        <div class="space-x-2">
          <Button variant="white" type="button" @click="$window.history.back()">Cancel</Button>
          <Button type="submit" :loading="form.processing">Update Invoice</Button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card class="md:col-span-2">
          <div class="space-y-6">
            <ClientSelect v-model="form.client_id" :error="form.errors.client_id" required />
            
            <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
              <h3 class="text-lg font-medium mb-4">Line Items</h3>
              <div v-for="(line, index) in form.line_items" :key="index">
                <InvoiceLineItem
                  :line="line"
                  :index="index"
                  @update="Object.assign(form.line_items[index], $event)"
                  @remove="removeLine(index)"
                />
              </div>
              <Button variant="secondary" size="sm" class="mt-4" @click="addLine" type="button">
                Add Line Item
              </Button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6">
              <TextArea v-model="form.notes" label="Notes" />
              <TextArea v-model="form.terms" label="Terms" />
            </div>
          </div>
        </Card>

        <Card title="Summary">
          <div class="space-y-4">
            <Input v-model="form.invoice_number" label="Invoice Number" :error="form.errors.invoice_number" required />
            <DatePicker v-model="form.issue_date" label="Issue Date" :error="form.errors.issue_date" required />
            <DatePicker v-model="form.due_date" label="Due Date" :error="form.errors.due_date" />
            
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <span>{{ formatCurrency(subtotal) }}</span>
              </div>
              <div class="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>{{ formatCurrency(subtotal) }}</span>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </form>
  </AppLayout>
</template>
