import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react()],
    build: {
        outDir: '../public/biometric',
        emptyOutDir: true,
        rollupOptions: {
            input: './index.html',
            output: {
                entryFileNames: 'biometric-login.js',
                chunkFileNames: 'chunks/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]'
            }
        }
    }
});
