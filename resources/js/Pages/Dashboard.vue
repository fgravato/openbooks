<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
  summary: {
    open_invoices: number;
    overdue_invoices: number;
    monthly_revenue_cents: number;
    active_clients: number;
  };
  activity: Array<{ id: number; label: string; timestamp: string }>;
  context: {
    user_name?: string | null;
    organization_name?: string | null;
  };
}>();

</script>

<template>
  <AppLayout title="Dashboard">
    <Head title="Dashboard" />

    <section class="rounded-xl2 border border-brand-100 bg-white/90 p-6 shadow-panel">
      <p class="text-sm uppercase tracking-wider text-brand-600">Welcome back</p>
      <h1 class="mt-2 font-display text-3xl font-bold text-slate-900">Hello, {{ context.user_name ?? 'there' }}</h1>
      <p class="mt-1 text-sm text-slate-600">Organization: {{ context.organization_name ?? 'Not configured' }}</p>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-4">
      <article class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Open invoices</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ summary.open_invoices }}</p>
      </article>
      <article class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Overdue</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ summary.overdue_invoices }}</p>
      </article>
      <article class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Monthly revenue</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">${{ (summary.monthly_revenue_cents / 100).toFixed(2) }}</p>
      </article>
      <article class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-slate-500">Active clients</p>
        <p class="mt-2 text-2xl font-semibold text-slate-900">{{ summary.active_clients }}</p>
      </article>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-semibold text-slate-900">Recent activity</h2>
        <Link :href="route('2fa.setup')" class="text-sm text-brand-600 hover:text-brand-700">Security setup</Link>
      </div>

      <ul class="mt-4 space-y-3">
        <li v-for="item in activity" :key="item.id" class="rounded-lg bg-slate-50 px-3 py-2">
          <p class="text-sm text-slate-700">{{ item.label }}</p>
          <p class="text-xs text-slate-500">{{ item.timestamp }}</p>
        </li>
      </ul>
    </section>
  </AppLayout>
</template>
