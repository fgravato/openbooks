<script setup lang="ts">
import { computed } from 'vue';
import Input from './Input.vue';

interface Props {
  modelValue: number | null;
  label?: string;
  error?: string;
  currency?: string;
  required?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  currency: 'USD',
});

const emit = defineEmits(['update:modelValue']);

const displayValue = computed({
  get: () => {
    if (props.modelValue === null) return '';
    return (props.modelValue / 100).toFixed(2);
  },
  set: (val) => {
    const num = parseFloat(val);
    emit('update:modelValue', isNaN(num) ? 0 : Math.round(num * 100));
  }
});
</script>

<template>
  <div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none mt-6" v-if="label">
      <span class="text-gray-500 sm:text-sm">$</span>
    </div>
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none" v-else>
      <span class="text-gray-500 sm:text-sm">$</span>
    </div>
    <Input
      v-model="displayValue"
      :label="label"
      :error="error"
      :required="required"
      type="number"
      step="0.01"
      class="pl-7"
    />
  </div>
</template>
