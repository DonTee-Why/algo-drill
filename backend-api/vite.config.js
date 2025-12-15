import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import flowbiteReact from "flowbite-react/plugin/vite";

export default defineConfig({
    plugins: [laravel({
        input: ['resources/css/app.css', 'resources/js/app.jsx'],
        refresh: true,
    }), react(), tailwindcss(), flowbiteReact()],
    optimizeDeps: {
        include: ['monaco-editor'],
    },
    worker: {
        format: 'es',
    },
});