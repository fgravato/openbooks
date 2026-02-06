<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  title?: string;
}>();
</script>

<template>
  <Head :title="title ?? 'Dashboard'" />

  <div class="min-h-screen bg-gradient-to-b from-brand-50 to-white bg-hero-grid [background-size:28px_28px]">
    <header class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
      <Link href="/" class="font-display text-xl font-semibold text-brand-800">OpenBooks</Link>
      <nav class="flex items-center gap-4 text-sm text-slate-700">
        <Link href="/" class="transition hover:text-brand-600">Home</Link>
      </nav>
    </header>

    <main class="mx-auto max-w-7xl px-6 pb-12 lg:px-8">
      <slot />
    </main>
  </div>
</template>
