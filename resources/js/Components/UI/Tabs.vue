<script setup lang="ts">
interface Tab {
  key: string;
  label: string;
}

interface Props {
  tabs: Tab[];
  modelValue: string;
}

defineProps<Props>();
defineEmits(['update:modelValue']);
</script>

<template>
  <div>
    <div class="sm:hidden">
      <label for="tabs" class="sr-only">Select a tab</label>
      <select
        id="tabs"
        name="tabs"
        class="block w-full focus:ring-indigo-500 focus:border-indigo-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
        @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
        :value="modelValue"
      >
        <option v-for="tab in tabs" :key="tab.key" :value="tab.key">{{ tab.label }}</option>
      </select>
    </div>
    <div class="hidden sm:block">
      <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            @click="$emit('update:modelValue', tab.key)"
            :class="[
              tab.key === modelValue
                ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>
