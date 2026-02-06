import { ref } from 'vue';

const show = ref(false);
const options = ref({
  title: 'Confirm Action',
  message: 'Are you sure you want to proceed?',
  confirmText: 'Confirm',
  cancelText: 'Cancel',
  variant: 'danger' as 'primary' | 'danger',
});

let resolvePromise: (value: boolean) => void;

export function useConfirm() {
  const confirm = (opts: Partial<typeof options.value> = {}) => {
    options.value = { ...options.value, ...opts };
    show.value = true;
    return new Promise<boolean>((resolve) => {
      resolvePromise = resolve;
    });
  };

  const onConfirm = () => {
    show.value = false;
    resolvePromise(true);
  };

  const onCancel = () => {
    show.value = false;
    resolvePromise(false);
  };

  return {
    show,
    options,
    confirm,
    onConfirm,
    onCancel,
  };
}
