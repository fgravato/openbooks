<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import CurrencyInput from '@/Components/UI/CurrencyInput.vue';
import DatePicker from '@/Components/UI/DatePicker.vue';
import CategorySelect from '@/Components/Forms/CategorySelect.vue';
import ClientSelect from '@/Components/Forms/ClientSelect.vue';
import FileUpload from '@/Components/UI/FileUpload.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  vendor: '',
  amount: 0,
  tax_amount: 0,
  date: new Date().toISOString().split('T')[0],
  category_id: null as number | null,
  client_id: null as number | null,
  description: '',
  receipt: null as File | null,
  is_billable: false,
});

const submit = () => {
  form.post(route('expenses.store'));
};
</script>

<template>
  <AppLayout title="Record Expense">
    <form @submit.prevent="submit" class="max-w-4xl mx-auto space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Record Expense</h1>
        <div class="space-x-2">
          <Button variant="white" type="button" @click="$window.history.back()">Cancel</Button>
          <Button type="submit" :loading="form.processing">Save Expense</Button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card title="Expense Details">
          <div class="space-y-4">
            <Input v-model="form.vendor" label="Vendor" :error="form.errors.vendor" required />
            <div class="grid grid-cols-2 gap-4">
              <CurrencyInput v-model="form.amount" label="Amount" :error="form.errors.amount" required />
              <CurrencyInput v-model="form.tax_amount" label="Tax Amount" :error="form.errors.tax_amount" />
            </div>
            <DatePicker v-model="form.date" label="Date" :error="form.errors.date" required />
            <CategorySelect v-model="form.category_id" type="expense" :error="form.errors.category_id" required />
          </div>
        </Card>

        <Card title="Receipt & Billing">
          <div class="space-y-4">
            <FileUpload label="Receipt" @change="form.receipt = $event" :error="form.errors.receipt" />
            <ClientSelect v-model="form.client_id" :error="form.errors.client_id" />
            <div class="flex items-center">
              <input type="checkbox" v-model="form.is_billable" id="is_billable" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
              <label for="is_billable" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Billable to client</label>
            </div>
          </div>
        </Card>
      </div>
    </form>
  </AppLayout>
</template>
