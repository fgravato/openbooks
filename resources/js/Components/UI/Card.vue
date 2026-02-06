<script setup lang="ts">
interface Props {
  title?: string;
  subtitle?: string;
  noPadding?: boolean;
}

defineProps<Props>();
</script>

<template>
  <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg border border-gray-200 dark:border-gray-700">
    <div v-if="title || $slots.header" class="px-4 py-5 sm:px-6 border-b border-gray-200 dark:border-gray-700">
      <slot name="header">
        <h3 v-if="title" class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
          {{ title }}
        </h3>
        <p v-if="subtitle" class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
          {{ subtitle }}
        </p>
      </slot>
    </div>
    <div :class="[noPadding ? '' : 'px-4 py-5 sm:p-6']">
      <slot />
    </div>
    <div v-if="$slots.footer" class="px-4 py-4 sm:px-6 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
      <slot name="footer" />
    </div>
  </div>
</template>
