/** Build de produccion (reemplaza el CDN de Tailwind). Correr: npm run build:css */
module.exports = {
  content: [
    './src/*.html',
    './*.php',
    './admin/*.php',
    './partials/*.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Inter', 'sans-serif'],
        spartan: ['League Spartan', 'sans-serif'],
      },
      colors: {
        primary: '#0b142c',
        secondary: '#f3580e',
        aux: '#d8d7f5',
        aux2: '#b0aab3',
        canvas: '#f5f6fa',
        ink: '#374151',
      },
    },
  },
  plugins: [],
};
