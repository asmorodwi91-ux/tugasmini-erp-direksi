export default defineNuxtConfig({
  compatibilityDate: '2025-01-01',
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss', '@pinia/nuxt'],
  css: ['~/assets/css/main.css'],
  tailwindcss: {
    configPath: 'tailwind.config',
  },
  runtimeConfig: {
    public: {
      apiBase: 'https://api.devsise.my.id/api',
    },
  },
  app: {
    head: {
      title: 'Mini ERP — Modul Direksi',
      meta: [{ name: 'viewport', content: 'width=device-width, initial-scale=1' }],
    },
  },
})
