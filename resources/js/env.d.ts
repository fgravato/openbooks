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

interface RouteFunction {
  (name: string, params?: Record<string, unknown>, absolute?: boolean): string;
  (): { current: (name?: string) => boolean };
}

declare module 'vue' {
  interface ComponentCustomProperties {
    route: RouteFunction;
    $window: Window & typeof globalThis;
  }
}

declare module '@inertiajs/vue3' {
  export function usePage<T = AppPageProps>(): { props: T };
}

export {};
