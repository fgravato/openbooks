import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import { debounce } from 'lodash-es';

export function useFilters(initialFilters: Record<string, any>, routeName: string) {
  const filters = reactive({ ...initialFilters });

  const updateFilters = debounce(() => {
    router.get(route(routeName), filters, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }, 300);

  watch(filters, () => {
    updateFilters();
  }, { deep: true });

  const resetFilters = () => {
    Object.keys(initialFilters).forEach(key => {
      filters[key] = initialFilters[key];
    });
  };

  return { filters, resetFilters };
}
