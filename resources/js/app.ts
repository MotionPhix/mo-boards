import '../css/ckeditor.css';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import { initializeTheme } from './composables/useTheme';
import VueApexCharts from 'vue3-apexcharts';
import { Modal, ModalLink, renderApp, putConfig } from '@inertiaui/modal-vue';
import FlashToasts from './plugins/flashToasts';

// Configure default modal settings
putConfig({
    type: 'modal',
    navigate: false,
    modal: {
        closeButton: true,
        closeExplicitly: true,
        maxWidth: '2xl',
        paddingClasses: 'p-0',
        panelClasses: 'bg-background rounded-lg shadow-lg border',
        position: 'center',
    },
    slideover: {
        closeButton: true,
        closeExplicitly: false,
        maxWidth: 'md',
        paddingClasses: 'p-0',
        panelClasses: 'bg-background min-h-screen border-l',
        position: 'right',
    },
});

// Vite env typings are declared in resources/js/types/vite-env.d.ts

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
    (createApp as any)({ render: renderApp(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(VueApexCharts)
            .use(FlashToasts, { initialFlash: (props as any)?.initialPage?.props?.flash })
            .component('ModalUi', Modal)
            .component('ModalLink', ModalLink)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
