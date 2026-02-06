<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
  title?: string;
}>();
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <Head :title="title" />
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <Link href="/" class="flex justify-center">
        <span class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400">OpenBooks</span>
      </Link>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
        {{ title }}
      </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10 border border-gray-200 dark:border-gray-700">
        <slot />
      </div>
    </div>
  </div>
</template>
