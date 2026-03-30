import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { toast, confirm } from '@/Plugins';

// Import default layout statically (used as fallback)
import AppLayout from '@/Layouts/AppLayout.vue';

// Import global components (truly universal components used everywhere)
// TODO: Uncomment when components are implemented
// import { AppButton, AppInput, AppModal } from '@/Components';

createInertiaApp({
    title: (title) => title ? `${title} - PMS` : 'PMS',

    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        const pageModule = pages[`./Pages/${name}.vue`];

        if (!pageModule) {
            throw new Error(`Page "${name}" not found`);
        }

        const page = pageModule.default || pageModule;

        // Set default layout if not defined on the page
        if (!page.layout) {
            page.layout = AppLayout;
        }

        return page;
    },

    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Register global components (available in all templates without import)
        // Inertia.js global components
        app.component('Head', Head);
        app.component('Link', Link);

        // TODO: Uncomment when components are implemented
        // app.component('AppButton', AppButton);
        // app.component('AppInput', AppInput);
        // app.component('AppModal', AppModal);

        app.use(plugin);
        app.use(createPinia());
        app.use(toast);
        app.use(confirm);

        app.mount(el);
    },

    progress: {
        color: '#3b82f6',
        showSpinner: true
    }
});
