export default defineNuxtConfig({
  vite: {
    optimizeDeps: {
      include: ['pusher-js', 'laravel-echo'] // ✅ Corrected syntax
    }
  },

  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },

  css: [
    'bootstrap/dist/css/bootstrap.min.css', // ✅ Removed duplicate imports
    'bootstrap-icons/font/bootstrap-icons.css'
  ],

  plugins: [
    { src: '~/plugins/bootstrap-vue.js', mode: 'client' },
    { src: '~/plugins/echo.client.js', mode: 'client' } // ✅ Explicitly marked as client
  ],

  build: {
    transpile: ['bootstrap-vue']
  },

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000',
      VITE_REVERB_APP_KEY: process.env.VITE_REVERB_APP_KEY,
      VITE_REVERB_HOST: process.env.VITE_REVERB_HOST,
      VITE_REVERB_PORT: process.env.VITE_REVERB_PORT,
      VITE_REVERB_SCHEME: process.env.VITE_REVERB_SCHEME,
    }
  },

  modules: ['@pinia/nuxt'],
});
