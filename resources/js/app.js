import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import ToastContainer from '@/Components/ToastContainer.vue';
import { toast } from '@/composables/useToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Mount ToastContainer globally
        const toastEl = document.createElement('div');
        toastEl.id = 'toast-container';
        document.body.appendChild(toastEl);
        createApp(ToastContainer).mount(toastEl);

        return app.mount(el);
    },
    progress: {
        color: '#f97316', // Construction Orange
    },
});

// Listen for Inertia flash messages and show as toasts
router.on('finish', (event) => {
    const flash = event.detail.page?.props?.flash;
    if (flash?.success) {
        toast.success(flash.success);
    }
    if (flash?.error) {
        toast.error(flash.error);
    }
    if (flash?.warning) {
        toast.warning(flash.warning);
    }
    if (flash?.info) {
        toast.info(flash.info);
    }
    if (flash?.message) {
        toast.info(flash.message);
    }
});
