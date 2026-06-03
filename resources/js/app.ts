import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';

createInertiaApp({
  title: (title) => (title ? `${title} - ChatFlow AI` : 'ChatFlow AI'),
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      .mount(el);
  },
  progress: {
    color: '#7C3AED',
  },
});
