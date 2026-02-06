<script setup lang="ts">
interface Props {
  modelValue: string | null;
  label?: string;
  error?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  rows?: number;
  helper?: string;
}

withDefaults(defineProps<Props>(), {
  rows: 3,
});

defineEmits(['update:modelValue']);
</script>

<template>
  <div class="w-full">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <textarea
      :value="modelValue"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
      :rows="rows"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :class="[
        'block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200',
        error ? 'border-red-300 text-red-900' : '',
        disabled ? 'bg-gray-100 cursor-not-allowed opacity-75' : ''
      ]"
    ></textarea>
    <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    <p v-else-if="helper" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ helper }}</p>
  </div>
</template>
