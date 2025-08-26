import type { Config } from 'tailwindcss'
export default {
  content: ['./index.html', './src/**/*.{ts,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          600: '#424dd9',
          700: '#333bb0'
        }
      },
      boxShadow: {
        soft: '0 4px 20px rgba(0,0,0,0.06)'
      }
    }
  },
  plugins: []
} satisfies Config
