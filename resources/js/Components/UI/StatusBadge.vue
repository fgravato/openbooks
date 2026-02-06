<script setup lang="ts">
import Badge from './Badge.vue';
import { computed } from 'vue';

interface Props {
  status: string;
  type: 'invoice' | 'payment' | 'expense';
}

const props = defineProps<Props>();

const config = computed(() => {
  const status = props.status.toLowerCase();
  
  if (props.type === 'invoice') {
    switch (status) {
      case 'draft': return { variant: 'default' as const, label: 'Draft' };
      case 'sent': return { variant: 'info' as const, label: 'Sent' };
      case 'viewed': return { variant: 'info' as const, label: 'Viewed' };
      case 'partial': return { variant: 'warning' as const, label: 'Partial' };
      case 'paid': return { variant: 'success' as const, label: 'Paid' };
      case 'overdue': return { variant: 'danger' as const, label: 'Overdue' };
      case 'cancelled': return { variant: 'default' as const, label: 'Cancelled' };
      default: return { variant: 'default' as const, label: status };
    }
  }
  
  if (props.type === 'payment') {
    switch (status) {
      case 'completed': return { variant: 'success' as const, label: 'Completed' };
      case 'pending': return { variant: 'warning' as const, label: 'Pending' };
      case 'failed': return { variant: 'danger' as const, label: 'Failed' };
      default: return { variant: 'default' as const, label: status };
    }
  }

  if (props.type === 'expense') {
    switch (status) {
      case 'pending': return { variant: 'warning' as const, label: 'Pending' };
      case 'approved': return { variant: 'success' as const, label: 'Approved' };
      case 'rejected': return { variant: 'danger' as const, label: 'Rejected' };
      case 'reimbursed': return { variant: 'info' as const, label: 'Reimbursed' };
      default: return { variant: 'default' as const, label: status };
    }
  }

  return { variant: 'default' as const, label: status };
});
</script>

<template>
  <Badge :variant="config.variant">
    {{ config.label }}
  </Badge>
</template>
