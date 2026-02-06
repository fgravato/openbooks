<script setup lang="ts">
import Input from '@/Components/UI/Input.vue';
import Select from '@/Components/UI/Select.vue';

interface Address {
  address: string;
  city: string;
  state: string;
  postal_code: string;
  country: string;
}

interface Props {
  modelValue: Address;
  error?: Record<string, string>;
}

const props = defineProps<Props>();
const emit = defineEmits(['update:modelValue']);

const update = (field: keyof Address, value: string) => {
  emit('update:modelValue', { ...props.modelValue, [field]: value });
};
</script>

<template>
  <div class="space-y-4">
    <Input
      :model-value="modelValue.address"
      @update:model-value="update('address', $event as string)"
      label="Street Address"
      :error="error?.address"
    />
    <div class="grid grid-cols-2 gap-4">
      <Input
        :model-value="modelValue.city"
        @update:model-value="update('city', $event as string)"
        label="City"
        :error="error?.city"
      />
      <Input
        :model-value="modelValue.state"
        @update:model-value="update('state', $event as string)"
        label="State / Province"
        :error="error?.state"
      />
    </div>
    <div class="grid grid-cols-2 gap-4">
      <Input
        :model-value="modelValue.postal_code"
        @update:model-value="update('postal_code', $event as string)"
        label="Postal Code"
        :error="error?.postal_code"
      />
      <Input
        :model-value="modelValue.country"
        @update:model-value="update('country', $event as string)"
        label="Country"
        :error="error?.country"
      />
    </div>
  </div>
</template>
