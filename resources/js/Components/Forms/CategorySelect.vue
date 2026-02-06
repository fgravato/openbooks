<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Select from '@/Components/UI/Select.vue';
import axios from 'axios';

interface Props {
  modelValue: number | string | null;
  type?: string;
  error?: string;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue']);

const categories = ref<{label: string, value: number}[]>([]);

onMounted(async () => {
  try {
    const response = await axios.get('/api/categories', { params: { type: props.type } });
    categories.value = response.data.map((c: any) => ({
      label: c.name,
      value: c.id
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
    :options="categories"
    label="Category"
    placeholder="Select a category"
    :error="error"
  />
</template>
