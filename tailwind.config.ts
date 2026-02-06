import type { Config } from 'tailwindcss';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.ts',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['"Space Grotesk"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        brand: {
          50: '#f4f7ff',
          100: '#e9efff',
          200: '#cad8ff',
          300: '#a4bcff',
          400: '#7595ff',
          500: '#4e6bff',
          600: '#344af5',
          700: '#2a3bd5',
          800: '#2634ac',
          900: '#243283',
        },
        accent: {
          DEFAULT: '#0ea5a4',
          muted: '#e6fffc',
        },
      },
      boxShadow: {
        panel: '0 20px 45px -30px rgba(52, 74, 245, 0.45)',
      },
      borderRadius: {
        xl2: '1.25rem',
      },
      backgroundImage: {
        'hero-grid': 'linear-gradient(to right, rgba(78, 107, 255, 0.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(78, 107, 255, 0.08) 1px, transparent 1px)',
      },
      backgroundSize: {
        'hero-grid': '28px 28px',
      },
    },
  },
  plugins: [forms, typography],
} satisfies Config;
