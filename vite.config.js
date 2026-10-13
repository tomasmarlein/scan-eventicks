import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/shared/scan.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            '@': '/resources/js',
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        },
    },
    build: {
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
                scan: 'resources/js/shared/scan.js',
            },
        },
    },
});
