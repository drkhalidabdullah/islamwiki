import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [react()],
  base: '/', // Use absolute paths for production
  build: {
    outDir: 'public',
    assetsDir: 'assets',
    sourcemap: false,
    rollupOptions: {
      input: 'resources/index.html',
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
    port: 80, // Use port 80 as preferred
    host: true
  }
}) 