import { router } from '@inertiajs/vue3';

export function usePagination() {
  const changePage = (url: string | null) => {
    if (url) {
      router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
  };

  const changePerPage = (perPage: number, baseUrl: string) => {
    router.get(baseUrl, { per_page: perPage }, { preserveState: true, preserveScroll: true });
  };

  return { changePage, changePerPage };
}
