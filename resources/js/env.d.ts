/// <reference types="vite/client" />

import type { AppPageProps } from './types';

declare module '*.vue' {
  import type { DefineComponent } from 'vue';

  const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>;
  export default component;
}

declare global {
  function route(name: string, params?: Record<string, unknown>, absolute?: boolean): string;
}

declare module '@inertiajs/vue3' {
  export function usePage<T = AppPageProps>(): { props: T };
}

export {};
