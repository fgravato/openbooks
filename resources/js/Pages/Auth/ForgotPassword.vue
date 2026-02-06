<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ status?: string }>();

const form = useForm({
  email: '',
});

const submit = (): void => {
  form.post(route('password.email'));
};
</script>

<template>
  <Head title="Forgot Password" />

  <div class="mx-auto mt-10 w-full max-w-lg rounded-xl2 border border-brand-100 bg-white p-8 shadow-panel">
    <h1 class="font-display text-3xl font-semibold text-slate-900">Reset your password</h1>
    <p class="mt-2 text-sm text-slate-600">We will send a reset link to your email address.</p>

    <p v-if="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
      {{ status }}
    </p>

    <form class="mt-6 space-y-3" @submit.prevent="submit">
      <input v-model="form.email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="you@company.com" />
      <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Send reset link</button>
    </form>

    <Link :href="route('login')" class="mt-4 inline-block text-sm text-brand-600 hover:text-brand-700">Back to login</Link>
  </div>
</template>
