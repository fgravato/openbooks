<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';
import Button from '@/Components/UI/Button.vue';
import AddressForm from '@/Components/Forms/AddressForm.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const tenant = (usePage().props as any).tenant;

const form = useForm({
  name: tenant?.name || '',
  currency_code: tenant?.currency_code || 'USD',
  timezone: tenant?.timezone || 'UTC',
  address: tenant?.address || '',
  city: tenant?.city || '',
  state: tenant?.state || '',
  postal_code: tenant?.postal_code || '',
  country: tenant?.country || '',
});

const submit = () => {
  form.put(route('organization.update'));
};
</script>

<template>
  <AppLayout title="Organization Settings">
    <div class="max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Organization Settings</h1>
      
      <Card title="Organization Details" subtitle="Information about your company used for invoices.">
        <form @submit.prevent="submit" class="space-y-6">
          <Input v-model="form.name" label="Organization Name" :error="form.errors.name" required />
          
          <div class="grid grid-cols-2 gap-4">
            <Select
              v-model="form.currency_code"
              label="Default Currency"
              :options="[{label: 'USD - US Dollar', value: 'USD'}, {label: 'EUR - Euro', value: 'EUR'}, {label: 'GBP - British Pound', value: 'GBP'}]"
            />
            <Input v-model="form.timezone" label="Timezone" />
          </div>

          <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-4">Business Address</h4>
            <AddressForm v-model="form" />
          </div>
          
          <div class="flex justify-end pt-4">
            <Button type="submit" :loading="form.processing">Save Changes</Button>
          </div>
        </form>
      </Card>
    </div>
  </AppLayout>
</template>
