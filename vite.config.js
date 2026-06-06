import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/agents.js',
                'resources/js/companies.js',
                'resources/js/admin-leads-filters.js',
                'resources/js/leads-closed-chart.js',
                'resources/js/agent-notifications-poller.js',
                'resources/js/agent-web-push.js',
                'resources/js/admin-notifications-poller.js',
                'resources/js/admin-dashboard-filters.js',
                'resources/js/dashboard.js',
                'resources/js/folder-form-unsaved-guard.js',
                'resources/js/folder-numeric-inputs.js',
                'resources/js/folder-payment-show.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
