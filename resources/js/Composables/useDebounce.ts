import { ref, watch, Ref } from 'vue';
import { debounce } from 'lodash-es';

export function useDebounce<T>(value: Ref<T>, delay: number = 300) {
  const debouncedValue = ref(value.value) as Ref<T>;

  const update = debounce((newValue: T) => {
    debouncedValue.value = newValue;
  }, delay);

  watch(value, (newValue) => {
    update(newValue);
  });

  return debouncedValue;
}
