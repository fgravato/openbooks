<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  title?: string;
}>();
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <Head :title="title" />
    
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center">
        <Link href="/" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">OpenBooks</Link>
      </div>
    </header>

    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
      <slot />
    </main>

    <footer class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center text-gray-500 dark:text-gray-400 text-sm">
      Powered by OpenBooks
    </footer>
  </div>
</template>
