<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Select from '@/Components/UI/Select.vue';
import axios from 'axios';

interface Props {
  modelValue: number | string | null;
  error?: string;
}

defineProps<Props>();
const emit = defineEmits(['update:modelValue']);

const taxes = ref<{label: string, value: number}[]>([]);

onMounted(async () => {
  try {
    const response = await axios.get('/api/taxes');
    taxes.value = response.data.map((t: any) => ({
      label: `${t.name} (${t.rate}%)`,
      value: t.id
    }));
  } catch (e) {
    console.error(e);
  }
});
</script>

<template>
  <Select
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :options="taxes"
    label="Tax Rate"
    placeholder="No Tax"
    :error="error"
  />
</template>
