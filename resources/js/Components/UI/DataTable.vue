<script setup lang="ts">
import { ref, computed } from 'vue';
import Table from './Table.vue';
import SearchInput from './SearchInput.vue';
import Pagination from './Pagination.vue';

interface Column {
  key: string;
  label: string;
  sortable?: boolean;
  align?: 'left' | 'center' | 'right';
}

interface Props {
  columns: Column[];
  data: any[];
  pagination?: any;
  loading?: boolean;
  searchable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  loading: false,
  searchable: true,
});

const emit = defineEmits(['search', 'sort', 'page-change']);

const search = ref('');

const onSearch = (val: string) => {
  emit('search', val);
};
</script>

<template>
  <div class="space-y-4">
    <div v-if="searchable" class="flex justify-between items-center">
      <div class="w-full max-w-xs">
        <SearchInput v-model="search" @update:model-value="onSearch" />
      </div>
      <div class="flex items-center space-x-2">
        <slot name="actions" />
      </div>
    </div>

    <Table :columns="columns" :data="data" :loading="loading">
      <template v-for="(_, slotName) in $slots" v-slot:[slotName]="slotProps">
        <slot :name="slotName" v-bind="slotProps" />
      </template>
    </Table>

    <Pagination v-if="pagination" :links="pagination.links" :meta="pagination.meta" />
  </div>
</template>
