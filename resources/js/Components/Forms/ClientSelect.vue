<script setup lang="ts">
import { ref, onMounted } from 'vue';
import Select from '@/Components/UI/Select.vue';
import axios from 'axios';

interface Props {
  modelValue: number | string | null;
  error?: string;
  required?: boolean;
}

defineProps<Props>();
const emit = defineEmits(['update:modelValue']);

const clients = ref<{label: string, value: number}[]>([]);
const loading = ref(false);

onMounted(async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/clients/search');
    clients.value = response.data.map((c: any) => ({
      label: c.company_name || c.name,
      value: c.id
    }));
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <Select
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    :options="clients"
    label="Client"
    placeholder="Select a client"
    :error="error"
    :required="required"
    :disabled="loading"
  />
</template>
