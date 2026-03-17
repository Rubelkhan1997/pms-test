import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { createPinia } from 'pinia';

// Import layouts
import AppLayout from './Layouts/AppLayout.vue';
import HotelLayout from './Layouts/HotelLayout.vue';

createInertiaApp({
    title: (title) => title ? `${title} - PMS` : 'PMS',

    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`] as DefineComponent;

        // Set default layout
        page.default.layout = page.default.layout || AppLayout;

        return page;
    },

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(createPinia())
            .component('Head', Head)
            .component('Link', Link)
            .mount(el);
    },

    progress: {
        color: '#3b82f6',
        showSpinner: true
    }
});
