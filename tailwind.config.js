/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/**/*.php'],
  theme: {
    fontFamily: {
      sans: ['Inter', 'system-ui', 'sans-serif'],
      mono: ['"JetBrains Mono"', 'monospace'],
    },
    extend: {
      colors: { zinc: { 925: '#111113', 950: '#09090b' } },
    },
  },
};
