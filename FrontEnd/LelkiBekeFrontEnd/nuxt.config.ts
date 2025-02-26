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
    langDir: 'locales',
    defaultLocale: 'en',
    locales: [
      { code: 'en', file: 'en.json'},
      { code: 'hu', file: 'hu.json' },
      { code: 'de', file: 'de.json' },
      { code: 'fr', file: 'fr.json' },
      { code: 'es', file: 'es.json' }
      // Add more languages...
    ],
    strategy: 'prefix_except_default', // URLs like /en/about or /hu/about
    lazy: true, // Load translations lazily
    detectBrowserLanguage: {
      useCookie: true,
      cookieKey: 'i18n_redirected',
      redirectOn: 'root', // Redirect users to their preferred language
    },
    customRoutes: 'config',
    pages: {
      auth: {
        en: '/auth',
        hu: '/identifikacio',
        de: '/identifikation',
        fr: '/identification',
        es: '/identificacion'
      },
      index: {
        en: '/',
        hu: '/fooldal',
        de: '/startseite',
        fr: '/accueil',
        es: '/inicio'
      },
      kitchen: {
        en: '/kitchen',
        hu: '/konyha',
        de: '/kueche',
        fr: '/cuisine',
        es: '/cocina'
      },
      profile: {
        en: '/profile',
        hu: '/profil',
        de: '/profil',
        fr: '/profil',
        es: '/perfil'
      },
      menu: {
        en: '/menu',
        hu: '/menu',
        de: '/speisekarte',
        fr: '/carte',
        es: '/menu'
      },
      qrscannerpage: {
        en: '/qrscannerpage',
        hu: '/qrscanneroldal',
        de: '/qrscannerseite',
        fr: '/pageqrscanner',
        es: '/qrscannerpagina'
      },
      thankyou: {
        en: '/thankyou',
        hu: '/koszonjuk',
        de: '/dankeschon',
        fr: '/merci',
        es: '/gracias'
      },
      waiter: {
        en: '/waiter',
        hu: '/pincer',
        de: '/kellner',
        fr: '/serveur',
        es: '/camarero'
      }
    }
    
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
    baseURL: '/innerpeace/'
  }
});
