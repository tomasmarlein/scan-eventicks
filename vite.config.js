import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import fs from 'fs';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
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
            manifest: true,
            outDir: 'public/build',
            input: {
                app: 'resources/js/app.js',
                css: 'resources/css/app.css',
                scan: 'resources/js/shared/scan.js',
            },
        },
    },
});
