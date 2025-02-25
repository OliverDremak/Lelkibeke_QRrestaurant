import Thankyou from "./pages/thankyou.vue";

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
    '@pinia/nuxt',
    '@nuxtjs/i18n'
  ],

  i18n: {
    locales: [
      { code: 'en', iso: 'en-US', file: 'en.json', name: 'English' },
      { code: 'hu', iso: 'hu-HU', file: 'hu.json', name: 'Magyar' },
      // Add more languages...
    ],
    defaultLocale: 'en', // Default language
    strategy: 'prefix_except_default', // URLs like /en/about or /hu/about
    lazy: true, // Load translations lazily
    langDir: 'locales/', // Directory for translation files
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      redirectOn: 'root', // Redirect users to their preferred language
    },
    customRoutes: 'config',
    pages: {
      'auth': {
        en: '/auth',
        hu: '/identifikacio',
      },
      'index':{
        en: '/',
        hu: '/fooldal'
      },
      'kitchen':{
        en: '/kitchen',
        hu: '/konyha'
      },
      'profile':{
        en: '/profile',
        hu: '/profil'
      },
      'menu':{
        en: '/menu',
        hu: '/menu'
      },
      'qrscannerpage':{
        en: '/qrscannerpage',
        hu: '/qrscanneroldal'
      },
      'thankyou':{
        en: '/thankyou',
        hu: '/koszonjuk'
      },
      'waiter':{
        en: '/waiter',
        hu: '/pincer'
      },
    },
  },

  compatibilityDate: '2025-02-23',

  app: {
    head: {
      link: [
        {
          rel: 'stylesheet',
          href: 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0'
        }
      ]
    },
    baseURL:'/innerpeace/'
  }
});
