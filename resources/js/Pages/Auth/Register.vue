<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  organization_name: '',
  organization_slug: '',
  terms: false,
});

const submit = (): void => {
  form.post(route('register.store'));
};

const toSlug = (value: string): string =>
  value
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '');

const syncSlug = (): void => {
  form.organization_slug = toSlug(form.organization_name);
};
</script>

<template>
  <Head title="Register" />

  <div class="mx-auto mt-10 w-full max-w-2xl rounded-xl2 border border-brand-100 bg-white p-8 shadow-panel">
    <h1 class="font-display text-3xl font-semibold text-slate-900">Create your OpenBooks workspace</h1>

    <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="submit">
      <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">Full name</label>
        <input v-model="form.name" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
        <p v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</p>
      </div>

      <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
        <input v-model="form.email" type="email" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
        <p v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</p>
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Password</label>
        <input v-model="form.password" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
      </div>

      <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label>
        <input v-model="form.password_confirmation" type="password" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
      </div>

      <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">Organization name</label>
        <input v-model="form.organization_name" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" @input="syncSlug" required />
      </div>

      <div class="md:col-span-2">
        <label class="mb-1 block text-sm font-medium text-slate-700">Organization slug</label>
        <input v-model="form.organization_slug" type="text" class="w-full rounded-lg border border-slate-300 px-3 py-2" required />
        <p class="mt-1 text-xs text-slate-500">Used for subdomain access: {{ form.organization_slug || 'your-org' }}.openbooks.test</p>
      </div>

      <label class="md:col-span-2 flex items-center gap-2 text-sm text-slate-700">
        <input v-model="form.terms" type="checkbox" required />
        I agree to the terms and privacy policy.
      </label>

      <button type="submit" class="md:col-span-2 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700" :disabled="form.processing">
        Create account
      </button>
    </form>

    <p class="mt-4 text-sm text-slate-600">
      Already registered?
      <Link :href="route('login')" class="text-brand-600 hover:text-brand-700">Sign in</Link>
    </p>
  </div>
</template>
