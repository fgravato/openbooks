<script setup lang="ts">
import Card from './Card.vue';

interface Props {
  title: string;
  value: string | number;
  change?: string | number;
  changeType?: 'increase' | 'decrease' | 'neutral';
  icon?: any;
}

defineProps<Props>();
</script>

<template>
  <Card no-padding>
    <div class="p-5">
      <div class="flex items-center">
        <div v-if="icon" class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
          <component :is="icon" class="h-6 w-6 text-white" aria-hidden="true" />
        </div>
        <div :class="[icon ? 'ml-5' : '', 'w-0 flex-1']">
          <dl>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
              {{ title }}
            </dt>
            <dd>
              <div class="text-lg font-medium text-gray-900 dark:text-white">
                {{ value }}
              </div>
            </dd>
          </dl>
        </div>
      </div>
    </div>
    <div v-if="change !== undefined" class="bg-gray-50 dark:bg-gray-800/50 px-5 py-3">
      <div class="text-sm">
        <span :class="[
          changeType === 'increase' ? 'text-green-600' : '',
          changeType === 'decrease' ? 'text-red-600' : '',
          'font-medium'
        ]">
          {{ changeType === 'increase' ? '↑' : changeType === 'decrease' ? '↓' : '' }} {{ change }}
        </span>
        <span class="text-gray-500 dark:text-gray-400 ml-1">from last month</span>
      </div>
    </div>
  </Card>
</template>
