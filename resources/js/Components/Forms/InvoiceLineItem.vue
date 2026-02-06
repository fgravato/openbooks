<script setup lang="ts">
import Input from '@/Components/UI/Input.vue';
import CurrencyInput from '@/Components/UI/CurrencyInput.vue';
import Button from '@/Components/UI/Button.vue';
import { computed } from 'vue';

interface Line {
  id?: number;
  description: string;
  quantity: number;
  unit_price: number;
  tax_id?: number;
}

interface Props {
  line: Line;
  index: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['update', 'remove']);

const total = computed(() => {
  return (props.line.quantity * props.line.unit_price);
});

const updateLine = (field: keyof Line, value: any) => {
  emit('update', { ...props.line, [field]: value });
};
</script>

<template>
  <div class="grid grid-cols-12 gap-4 items-start py-4 border-b border-gray-100 dark:border-gray-700">
    <div class="col-span-12 md:col-span-6">
      <Input
        :model-value="line.description"
        @update:model-value="updateLine('description', $event)"
        placeholder="Description"
        required
      />
    </div>
    <div class="col-span-4 md:col-span-2">
      <Input
        :model-value="line.quantity"
        @update:model-value="updateLine('quantity', parseFloat($event as string))"
        type="number"
        placeholder="Qty"
        required
      />
    </div>
    <div class="col-span-4 md:col-span-3">
      <CurrencyInput
        :model-value="line.unit_price"
        @update:model-value="updateLine('unit_price', $event)"
        required
      />
    </div>
    <div class="col-span-4 md:col-span-1 flex items-center justify-end pt-2">
      <button @click="$emit('remove')" class="text-gray-400 hover:text-red-500 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
      </button>
    </div>
  </div>
</template>
