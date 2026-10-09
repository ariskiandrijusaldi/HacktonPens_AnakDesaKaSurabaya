import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/campuscare.css',
                'resources/js/app.js',
                'resources/js/pages/landing.js',
                'resources/js/pages/lapor.js',
                'resources/js/pages/tiket.js',
                'resources/js/pages/bantuan.js',
                'resources/js/pages/masuk.js',
                'resources/js/pages/dasbor.js',
                'resources/js/pages/laporan.js',
                'resources/js/pages/statistik.js',
                'resources/js/pages/pola.js',
                'resources/js/pages/audit.js',
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
