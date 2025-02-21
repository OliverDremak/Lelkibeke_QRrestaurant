export default defineNuxtConfig({
  devtools: { enabled: true },

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

  modules: ['@pinia/nuxt'],
});
