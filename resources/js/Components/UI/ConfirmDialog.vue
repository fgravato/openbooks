<script setup lang="ts">
import Modal from './Modal.vue';
import Button from './Button.vue';
import { useConfirm } from '@/Composables/useConfirm';

const { show, options, onConfirm, onCancel } = useConfirm();
</script>

<template>
  <Modal :show="show" @close="onCancel" :title="options.title" size="sm">
    <div class="sm:flex sm:items-start">
      <div :class="['mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10', options.variant === 'danger' ? 'bg-red-100' : 'bg-indigo-100']">
        <svg v-if="options.variant === 'danger'" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <svg v-else class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ options.message }}
        </p>
      </div>
    </div>

    <template #footer>
      <Button :variant="options.variant" @click="onConfirm" class="w-full sm:ml-3 sm:w-auto">
        {{ options.confirmText }}
      </Button>
      <Button variant="white" @click="onCancel" class="mt-3 w-full sm:mt-0 sm:w-auto">
        {{ options.cancelText }}
      </Button>
    </template>
  </Modal>
</template>
