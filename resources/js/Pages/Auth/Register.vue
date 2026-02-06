<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  organization_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>

<template>
  <AuthLayout title="Create your account">
    <form @submit.prevent="submit" class="space-y-6">
      <Input
        v-model="form.name"
        label="Full Name"
        :error="form.errors.name"
        required
        autofocus
      />
      
      <Input
        v-model="form.organization_name"
        label="Company Name"
        :error="form.errors.organization_name"
        required
      />

      <Input
        v-model="form.email"
        type="email"
        label="Email address"
        :error="form.errors.email"
        required
      />

      <Input
        v-model="form.password"
        type="password"
        label="Password"
        :error="form.errors.password"
        required
      />

      <Input
        v-model="form.password_confirmation"
        type="password"
        label="Confirm Password"
        :error="form.errors.password_confirmation"
        required
      />

      <div class="flex items-center">
        <input
          id="terms"
          v-model="form.terms"
          type="checkbox"
          class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          required
        />
        <label for="terms" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
          I agree to the <a href="#" class="text-indigo-600 hover:text-indigo-500">Terms of Service</a> and <a href="#" class="text-indigo-600 hover:text-indigo-500">Privacy Policy</a>
        </label>
      </div>

      <Button type="submit" class="w-full" :loading="form.processing">
        Create Account
      </Button>

      <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
        Already have an account?
        <Link :href="route('login')" class="font-medium text-indigo-600 hover:text-indigo-500">
          Sign in
        </Link>
      </p>
    </form>
  </AuthLayout>
</template>
