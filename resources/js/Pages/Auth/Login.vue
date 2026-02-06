<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
  status?: string;
  two_factor_required?: boolean;
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
  totp_code: '',
});

const submit = (): void => {
  form.post(route('login.attempt'));
};
</script>

<template>
  <Head title="Login" />

  <div class="mx-auto mt-10 w-full max-w-lg rounded-xl2 border border-brand-100 bg-white p-8 shadow-panel">
    <h1 class="font-display text-3xl font-semibold text-slate-900">Sign in to OpenBooks</h1>
    <p class="mt-2 text-sm text-slate-600">Manage invoicing, payments, and your tenant workspace.</p>

    <p v-if="status" class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
      {{ status }}
    </p>

    <form class="mt-6 space-y-4" @submit.prevent="submit">
      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
        <input v-model="form.email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
        <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
        <input v-model="form.password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
        <p v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</p>
      </div>

      <div v-if="two_factor_required" class="rounded-lg border border-amber-200 bg-amber-50 p-3">
        <label class="mb-1 block text-sm font-medium text-amber-800">Two-factor code</label>
        <input v-model="form.totp_code" inputmode="numeric" maxlength="6" class="w-full rounded-lg border border-amber-300 px-3 py-2" />
        <p v-if="form.errors.totp_code" class="mt-1 text-xs text-rose-600">{{ form.errors.totp_code }}</p>
      </div>

      <label class="flex items-center gap-2 text-sm text-slate-700">
        <input v-model="form.remember" type="checkbox" />
        Remember me
      </label>

      <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700" :disabled="form.processing">
        Sign in
      </button>
    </form>

    <div class="mt-6 flex items-center justify-between text-sm">
      <Link :href="route('register')" class="text-brand-600 hover:text-brand-700">Create account</Link>
      <Link :href="route('password.request')" class="text-slate-600 hover:text-slate-800">Forgot password?</Link>
    </div>
  </div>
</template>
