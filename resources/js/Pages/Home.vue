<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
  appName: string;
}>();
</script>

<template>
  <AppLayout title="Welcome">
    <Head title="Welcome" />

    <section class="rounded-xl2 border border-brand-100 bg-white/90 p-8 shadow-panel backdrop-blur-sm">
      <p class="text-sm font-semibold uppercase tracking-widest text-brand-600">Laravel 12 + Vue 3 + Inertia</p>
      <h1 class="mt-3 font-display text-4xl font-bold text-slate-900">OpenBooks Platform Scaffolded</h1>
      <p class="mt-4 max-w-2xl text-base text-slate-600">
        Domain-driven project structure, multi-tenant-ready foundations, and modern frontend tooling are in place.
      </p>
    </section>
  </AppLayout>
</template>
