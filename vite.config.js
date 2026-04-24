import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [tailwindcss()],
  build: {
    manifest: true,
    outDir: 'www/assets',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        front: 'resources/js/front.js',
        admin: 'resources/js/admin.js'
      }
    }
  },
  server: {
    cors: true,
    strictPort: false
  }
})
