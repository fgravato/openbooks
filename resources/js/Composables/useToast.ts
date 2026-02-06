import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
// Assuming vue-toastification is used as per requirement
import { useToast as useVueToast } from 'vue-toastification';

export function useToast() {
  const toast = useVueToast();
  const page = usePage();

  const success = (message: string) => toast.success(message);
  const error = (message: string) => toast.error(message);
  const warning = (message: string) => toast.warning(message);
  const info = (message: string) => toast.info(message);

  // Watch for flash messages from Inertia
  watch(() => (page.props as any).flash, (flash) => {
    if (flash?.success) success(flash.success);
    if (flash?.error) error(flash.error);
  }, { deep: true, immediate: true });

  return { success, error, warning, info };
}
