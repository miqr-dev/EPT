import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import tailwindcss from '@tailwindcss/vite';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        {
            // Redirect legacy per-option SVG references for LPS-B column 3
            // to the new single-SVG-per-row assets so Vite doesn't try to
            // load removed files during CSS analysis or template transforms.
            name: 'lps-column3-inline-alias',
            enforce: 'pre',
            resolveId(source) {
                const match = source.match(/lps-b\/column3\/row-(0\d|\d{2})\/option-\d+\.svg$/);
                if (!match) return null;

                const rowNumber = match[1];
                return path.resolve(__dirname, `resources/images/lps-b/column3/row-${rowNumber}.svg`);
            },
        },
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
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
});
