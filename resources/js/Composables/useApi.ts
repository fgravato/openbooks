import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

export function useApi() {
  const loading = ref(false);

  const request = async (method: 'get' | 'post' | 'put' | 'patch' | 'delete', url: string, data: any = {}, options: any = {}) => {
    loading.value = true;
    return new Promise((resolve, reject) => {
      (router as any)[method](url, data, {
        ...options,
        onSuccess: (page: any) => {
          loading.value = false;
          if (options.onSuccess) options.onSuccess(page);
          resolve(page);
        },
        onError: (errors: any) => {
          loading.value = false;
          if (options.onError) options.onError(errors);
          reject(errors);
        },
        onFinish: () => {
          loading.value = false;
          if (options.onFinish) options.onFinish();
        }
      });
    });
  };

  return {
    loading,
    get: (url: string, params: any = {}, options: any = {}) => request('get', url, params, options),
    post: (url: string, data: any = {}, options: any = {}) => request('post', url, data, options),
    put: (url: string, data: any = {}, options: any = {}) => request('put', url, data, options),
    patch: (url: string, data: any = {}, options: any = {}) => request('patch', url, data, options),
    destroy: (url: string, options: any = {}) => request('delete', url, {}, options),
  };
}
