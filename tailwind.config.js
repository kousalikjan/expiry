/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      screens: {
        'xs': '380px',
      },
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: false,
  },
}
