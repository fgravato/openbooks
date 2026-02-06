<script setup lang="ts">
import { onMounted, ref } from 'vue';

interface Props {
  modelValue: string | number | null;
  label?: string;
  error?: string;
  type?: string;
  placeholder?: string;
  required?: boolean;
  disabled?: boolean;
  helper?: string;
  autofocus?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  autofocus: false,
});

defineEmits(['update:modelValue']);

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
  if (props.autofocus && input.value) {
    input.value.focus();
  }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
  <div class="w-full">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }} <span v-if="required" class="text-red-500">*</span>
    </label>
    <div class="relative rounded-md shadow-sm">
      <input
        ref="input"
        :type="type"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
          'block w-full sm:text-sm rounded-md transition-colors duration-200',
          error
            ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500'
            : 'border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-indigo-500 focus:border-indigo-500',
          disabled ? 'bg-gray-100 cursor-not-allowed opacity-75' : ''
        ]"
      />
    </div>
    <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
    <p v-else-if="helper" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ helper }}</p>
  </div>
</template>
