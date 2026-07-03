/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './components/**/*.{vue,js,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './app.vue',
    './assets/css/**/*.css',
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#1F4E79',
          dark: '#163A5A',
          soft: '#DBE7F1',
        },
        ink: '#1A1F24',
        grey: {
          DEFAULT: '#697682',
          light: '#8A97A3',
        },
        line: {
          DEFAULT: '#D8DEE4',
          soft: '#E8ECF0',
        },
        status: {
          hijau: { DEFAULT: '#2E7D4F', soft: '#E8F3EC' },
          kuning: { DEFAULT: '#C98A1B', soft: '#FAF0D7' },
          merah: { DEFAULT: '#C0392B', soft: '#FCEAE8' },
        },
        'bg-soft': '#F4F6F8',
        surface: '#FFFFFF',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['"Plus Jakarta Sans"', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'monospace'],
      },
      fontSize: {
        'page-title': ['1.75rem', { lineHeight: '2.125rem', letterSpacing: '-0.02em', fontWeight: '800' }],
        'section-title': ['0.875rem', { lineHeight: '1.25rem', fontWeight: '600' }],
        'kpi-label': ['0.75rem', { lineHeight: '1rem', letterSpacing: '0.04em' }],
        'kpi-value': ['1.625rem', { lineHeight: '2rem', letterSpacing: '-0.02em', fontWeight: '800' }],
      },
      borderRadius: {
        card: '0.75rem',
        btn: '0.5rem',
      },
      spacing: {
        page: '1.75rem',
        section: '1.25rem',
        card: '1.25rem',
      },
      transitionDuration: {
        DEFAULT: '200ms',
      },
    },
  },
  plugins: [],
}
