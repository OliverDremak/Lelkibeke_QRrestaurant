export default defineNuxtConfig({
  devtools: { enabled: true },

  runtimeConfig: {
      public: {
        reverbKey: process.env.REVERB_KEY, // Exposes REVERB_KEY to client code
      },
    },

  css: [
    'bootstrap/dist/css/bootstrap.min.css', 
    'bootstrap-icons/font/bootstrap-icons.css'
  ],

  plugins: [
    { src: '~/plugins/bootstrap-vue.js', mode: 'client' },
    { src: '~/plugins/echo.client.js', mode: 'client' } 
  ],

  build: {
    transpile: ['bootstrap-vue']
  },

  modules: [
    '@pinia/nuxt'
  ],
  compatibilityDate: '2025-02-23',

  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0'
        }
      ]
    }
  }
});