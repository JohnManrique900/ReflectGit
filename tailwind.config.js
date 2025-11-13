/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./public/**/*.php",
    "./src/views/**/*.php"
  ],
  theme: {
    extend: {
      // Aquí definimos nuestra paleta de colores "ReflectGit"
      colors: {
        'brand-bg': '#0D1117',        // Nuestro fondo principal (Casi negro)
        'brand-surface': '#161B22',   // El fondo de nuestras "tarjetas" (Gris oscuro)
        'brand-border': '#30363D',    // Nuestros bordes
        'brand-text': '#C9D1D9',     // Texto principal (Gris claro)
        'brand-text-dim': '#8B949E', // Texto secundario (Más atenuado)
        'brand-primary': '#58A6FF',   // Azul brillante (Acentos, botones)
        'brand-accent': '#A371F7'    // Púrpura (Acentos, degradados)
      }
    },
  },
  plugins: [],
}