import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  publicDir: false,
  base: './', // Use relative paths
  build: {
    outDir: 'public',
    assetsDir: 'assets',
    sourcemap: false,
    rollupOptions: {
      input: 'resources/js/main.tsx',
      output: {
        manualChunks: {
          vendor: ['react', 'react-dom'],
          router: ['react-router-dom'],
          ui: ['zustand']
        }
      }
    }
  },
  server: {
    port: 3000,
    host: true
  }
}) 