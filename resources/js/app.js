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

// Analytics tracking
import mixpanel from 'mixpanel-browser';

const mixpanelToken = import.meta.env.VITE_MIXPANEL_TOKEN;

// Function to initialize Mixpanel securely only if consented
const initMixpanel = () => {
    if (mixpanelToken && localStorage.getItem('buildflow_cookie_consent') === 'accepted') {
        mixpanel.init(mixpanelToken, { debug: import.meta.env.DEV, track_pageview: true, persistence: 'localStorage' });
    }
}

// Check on load
initMixpanel();

// Listen for the custom event emitted by CookieConsentBanner.vue
window.addEventListener('cookie_consent_accepted', () => {
    initMixpanel();
});

// Track Inertia page navigations
router.on('navigate', (event) => {
    if (mixpanelToken && localStorage.getItem('buildflow_cookie_consent') === 'accepted') {
        mixpanel.track_pageview({
            "url": window.location.href,
            "path": window.location.pathname
        });
    }
});
