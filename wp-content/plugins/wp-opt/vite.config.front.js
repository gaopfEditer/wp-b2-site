import {defineConfig} from 'vite'
export default defineConfig({
    plugins: [
    ],
    build: {
        emptyOutDir: false,
        rollupOptions: {
            input: {
                'front': './vue/entry/front.js'
            },
            output: {
                dir: './static/js',
                format: 'iife',
                entryFileNames: '[name].min.js',
            }
        },
    }
})