<script setup lang="ts">
import { ref } from 'vue';

interface Props {
  accept?: string;
  multiple?: boolean;
  label?: string;
  helper?: string;
  error?: string;
}

const props = withDefaults(defineProps<Props>(), {
  accept: '*',
  multiple: false,
});

const emit = defineEmits(['change']);

const fileInput = ref<HTMLInputElement | null>(null);
const dragging = ref(false);

const onFileChange = (e: Event) => {
  const files = (e.target as HTMLInputElement).files;
  if (files) {
    emit('change', props.multiple ? Array.from(files) : files[0]);
  }
};

const onDrop = (e: DragEvent) => {
  dragging.value = false;
  const files = e.dataTransfer?.files;
  if (files) {
    emit('change', props.multiple ? Array.from(files) : files[0]);
  }
};
</script>

<template>
  <div class="w-full">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
      {{ label }}
    </label>
    <div
      @dragover.prevent="dragging = true"
      @dragleave.prevent="dragging = false"
      @drop.prevent="onDrop"
      :class="[
        'mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md transition-colors duration-200',
        dragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/10' : 'border-gray-300 dark:border-gray-600',
        error ? 'border-red-300' : ''
      ]"
    >
      <div class="space-y-1 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <div class="flex text-sm text-gray-600 dark:text-gray-400">
          <label for="file-upload" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
            <span>Upload a file</span>
            <input id="file-upload" name="file-upload" type="file" class="sr-only" :accept="accept" :multiple="multiple" @change="onFileChange" ref="fileInput" />
          </label>
          <p class="pl-1">or drag and drop</p>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">
          {{ helper || 'PNG, JPG, GIF up to 10MB' }}
        </p>
      </div>
    </div>
    <p v-if="error" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ error }}</p>
  </div>
</template>
