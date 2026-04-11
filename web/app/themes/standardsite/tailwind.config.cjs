// https://tailwindcss.com/docs/configuration
const defaultTheme = require('tailwindcss/defaultTheme');
module.exports = {
  content: ['./index.php', './app/**/*.php', './resources/**/*.{php,vue,js}'],
  theme: {
    screens: {
      'xs': '414px',
      ...defaultTheme.screens
    },
    extend: {
      colors: {}, // Extend Tailwind's default colors
    },
    container: {
      center: true,
      padding: '1rem',
    },
  },
  plugins: [],
};
