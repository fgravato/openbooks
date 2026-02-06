<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import Card from '@/Components/UI/Card.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import { useForm, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;

const form = useForm({
  name: user?.name || '',
  email: user?.email || '',
});

const submit = () => {
  form.put(route('profile.update'));
};
</script>

<template>
  <AppLayout title="Profile Settings">
    <div class="max-w-3xl mx-auto space-y-6">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profile Settings</h1>
      
      <Card title="Personal Information" subtitle="Update your account's profile information and email address.">
        <form @submit.prevent="submit" class="space-y-6">
          <Input v-model="form.name" label="Name" :error="form.errors.name" required />
          <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" required />
          
          <div class="flex justify-end">
            <Button type="submit" :loading="form.processing">Save Changes</Button>
          </div>
        </form>
      </Card>
      
      <Card title="Update Password" subtitle="Ensure your account is using a long, random password to stay secure.">
        <!-- Password change form would go here -->
        <p class="text-sm text-gray-500">Form implementation pending...</p>
      </Card>
    </div>
  </AppLayout>
</template>
