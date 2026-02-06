<script setup lang="ts">
interface Column {
  key: string;
  label: string;
  sortable?: boolean;
  align?: 'left' | 'center' | 'right';
}

interface Props {
  columns: Column[];
  data: any[];
  loading?: boolean;
}

withDefaults(defineProps<Props>(), {
  loading: false,
});
</script>

<template>
  <div class="flex flex-col">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
      <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
        <div class="shadow overflow-hidden border-b border-gray-200 dark:border-gray-700 sm:rounded-lg">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
              <tr>
                <th
                  v-for="column in columns"
                  :key="column.key"
                  scope="col"
                  :class="[
                    'px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                    column.align === 'center' ? 'text-center' : '',
                    column.align === 'right' ? 'text-right' : '',
                  ]"
                >
                  {{ column.label }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-if="loading" v-for="i in 3" :key="i">
                <td v-for="column in columns" :key="column.key" class="px-6 py-4 whitespace-nowrap">
                  <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse w-3/4"></div>
                </td>
              </tr>
              <tr v-else-if="data.length === 0">
                <td :colspan="columns.length" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                  No records found.
                </td>
              </tr>
              <tr v-else v-for="(item, index) in data" :key="index">
                <td
                  v-for="column in columns"
                  :key="column.key"
                  :class="[
                    'px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100',
                    column.align === 'center' ? 'text-center' : '',
                    column.align === 'right' ? 'text-right' : '',
                  ]"
                >
                  <slot :name="'cell-' + column.key" :item="item">
                    {{ item[column.key] }}
                  </slot>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
