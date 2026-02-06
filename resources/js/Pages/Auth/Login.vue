<script setup lang="ts">
import AuthLayout from '@/Layouts/AuthLayout.vue';
import Input from '@/Components/UI/Input.vue';
import Button from '@/Components/UI/Button.vue';
import { useForm, Link } from '@inertiajs/vue3';

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <AuthLayout title="Log in to your account">
    <form @submit.prevent="submit" class="space-y-6">
      <Input
        v-model="form.email"
        type="email"
        label="Email address"
        :error="form.errors.email"
        required
        autofocus
      />

      <Input
        v-model="form.password"
        type="password"
        label="Password"
        :error="form.errors.password"
        required
      />

      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <input
            id="remember_me"
            v-model="form.remember"
            type="checkbox"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
          />
          <label for="remember_me" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">
            Remember me
          </label>
        </div>

        <div class="text-sm">
          <Link :href="route('password.request')" class="font-medium text-indigo-600 hover:text-indigo-500">
            Forgot your password?
          </Link>
        </div>
      </div>

      <Button type="submit" class="w-full" :loading="form.processing">
        Sign in
      </Button>
      
      <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
        Don't have an account?
        <Link :href="route('register')" class="font-medium text-indigo-600 hover:text-indigo-500">
          Register now
        </Link>
      </p>
    </form>
  </AuthLayout>
</template>
