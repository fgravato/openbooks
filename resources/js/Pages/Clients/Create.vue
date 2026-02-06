<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Button from '@/Components/UI/Button.vue';
import Input from '@/Components/UI/Input.vue';
import AddressForm from '@/Components/Forms/AddressForm.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  company_name: '',
  email: '',
  phone: '',
  website: '',
  address: '',
  city: '',
  state: '',
  postal_code: '',
  country: '',
});

const submit = () => {
  form.post(route('clients.store'));
};
</script>

<template>
  <AppLayout title="Create Client">
    <form @submit.prevent="submit" class="max-w-4xl mx-auto space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Client</h1>
        <div class="space-x-2">
          <Button variant="white" type="button" @click="$window.history.back()">Cancel</Button>
          <Button type="submit" :loading="form.processing">Save Client</Button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card title="Primary Contact">
          <div class="space-y-4">
            <Input v-model="form.name" label="Full Name" :error="form.errors.name" required />
            <Input v-model="form.company_name" label="Company Name" :error="form.errors.company_name" />
            <Input v-model="form.email" type="email" label="Email Address" :error="form.errors.email" required />
            <Input v-model="form.phone" label="Phone Number" :error="form.errors.phone" />
          </div>
        </Card>

        <Card title="Address & Location">
          <AddressForm v-model="form" :error="form.errors" />
        </Card>
      </div>
    </form>
  </AppLayout>
</template>
