// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: true },
  css: [
    'bootstrap/dist/css/bootstrap.css',
    'bootstrap/dist/css/bootstrap.min.css',
    'bootstrap-vue/dist/bootstrap-vue.css',
    'bootstrap-icons/font/bootstrap-icons.css'
  ],
  plugins: [
    { src: '~/plugins/bootstrap-vue.js', mode: 'client' }
  ],
  build: {
    transpile: ['bootstrap-vue']
  },
  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000' 
    }
  }
})
