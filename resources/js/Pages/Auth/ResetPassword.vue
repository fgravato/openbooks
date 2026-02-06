<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
  email: string;
  token: string;
}>();

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
});

const submit = (): void => {
  form.post(route('password.update'));
};
</script>

<template>
  <Head title="Reset Password" />

  <div class="mx-auto mt-10 w-full max-w-lg rounded-xl2 border border-brand-100 bg-white p-8 shadow-panel">
    <h1 class="font-display text-3xl font-semibold text-slate-900">Create a new password</h1>

    <form class="mt-6 space-y-3" @submit.prevent="submit">
      <input v-model="form.email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2" readonly />
      <input v-model="form.password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="New password" />
      <input v-model="form.password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2" placeholder="Confirm password" />
      <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Update password</button>
    </form>
  </div>
</template>
