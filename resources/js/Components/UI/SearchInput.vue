<script setup lang="ts">
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

interface Props {
  modelValue: string;
  placeholder?: string;
  loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Search...',
  loading: false,
});

const emit = defineEmits(['update:modelValue']);

const innerValue = ref(props.modelValue);

const debouncedEmit = debounce((val) => {
  emit('update:modelValue', val);
}, 300);

watch(innerValue, (val) => {
  debouncedEmit(val);
});

watch(() => props.modelValue, (val) => {
  innerValue.value = val;
});
</script>

<template>
  <div class="relative rounded-md shadow-sm">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
      <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
      </svg>
    </div>
    <input
      v-model="innerValue"
      type="text"
      :placeholder="placeholder"
      class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md transition-colors duration-200"
    />
    <div v-if="loading" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
      <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    </div>
  </div>
</template>
