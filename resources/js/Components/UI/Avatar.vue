<script setup lang="ts">
import { computed } from 'vue';

interface Props {
  src?: string | null;
  name: string;
  size?: 'sm' | 'md' | 'lg' | 'xl';
  shape?: 'circle' | 'square';
}

const props = withDefaults(defineProps<Props>(), {
  src: null,
  size: 'md',
  shape: 'circle',
});

const initials = computed(() => {
  return props.name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
});

const sizeClasses = {
  sm: 'h-8 w-8 text-xs',
  md: 'h-10 w-10 text-sm',
  lg: 'h-12 w-12 text-base',
  xl: 'h-16 w-16 text-lg',
};

const shapeClasses = {
  circle: 'rounded-full',
  square: 'rounded-md',
};

const colors = [
  'bg-red-500', 'bg-blue-500', 'bg-green-500', 'bg-yellow-500', 
  'bg-purple-500', 'bg-pink-500', 'bg-indigo-500', 'bg-teal-500'
];

const bgColor = computed(() => {
  const index = props.name.length % colors.length;
  return colors[index];
});
</script>

<template>
  <div :class="['inline-flex items-center justify-center overflow-hidden', sizeClasses[size], shapeClasses[shape], src ? '' : bgColor]">
    <img v-if="src" :src="src" :alt="name" class="h-full w-full object-cover" />
    <span v-else class="font-medium text-white">{{ initials }}</span>
  </div>
</template>
