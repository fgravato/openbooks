import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { createApp, h, type DefineComponent } from 'vue';
import { ZiggyVue } from 'ziggy-js';

const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue');

createInertiaApp({
  title: (title: string) => `${title} - OpenBooks`,
  resolve: (name: string) => resolvePageComponent(`./Pages/${name}.vue`, pages),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });

    app.use(plugin);
    app.use(createPinia());
    app.use(ZiggyVue);
    app.mount(el);

    return app;
  },
  progress: {
    color: '#344af5',
  },
});
