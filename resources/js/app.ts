import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { createPinia } from 'pinia';

// Import layouts
import { AppLayout, HotelLayout, MobileLayout } from '@/Layouts';

// Register global components (optional - for frequently used components)
// import { AppButton, AppInput, AppModal } from '@/Components';

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
        const app = createApp({ render: () => h(App, props) });

        // Use plugins
        app.use(plugin);
        app.use(createPinia());

        // Register global components
        app.component('Head', Head);
        app.component('Link', Link);
        // app.component('AppButton', AppButton);
        // app.component('AppInput', AppInput);
        // app.component('AppModal', AppModal);

        app.mount(el);
    },

    progress: {
        color: '#3b82f6',
        showSpinner: true
    }
});
