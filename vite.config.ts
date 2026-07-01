import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { defineConfig, loadEnv } from 'vite';

function resolveBase(mode: string): string {
    const env = loadEnv(mode, process.cwd(), '');
    const appUrl = env.APP_URL ?? env.VITE_APP_URL ?? '';

    if (!appUrl) {
        return '/build/';
    }

    try {
        const pathname = new URL(appUrl).pathname;
        if (!pathname || pathname === '/') {
            return '/build/';
        }

        const basePath = pathname.endsWith('/') ? pathname : `${pathname}/`;

        return `${basePath}build/`;
    } catch {
        return '/build/';
    }
}

export default defineConfig(({ mode }) => ({
    base: resolveBase(mode),
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    css: {
        postcss: {
            plugins: [tailwindcss, autoprefixer],
        },
    },
}));
