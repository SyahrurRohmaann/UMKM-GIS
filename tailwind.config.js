/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        tinta: '#16232E',
        kertas: '#EDEEE4',
        'cyan-cetak': '#2F6E8E',
        'ambar-sinyal': '#E2A63B',
        lumut: '#5B6E4E',
        karat: '#A85A3D',
      },
      fontFamily: {
        display: ['"Space Grotesk"', 'sans-serif'],
        body: ['"IBM Plex Sans"', 'sans-serif'],
        mono: ['"IBM Plex Mono"', 'monospace'],
      },
      letterSpacing: {
        data: '0.01em',
      }
    },
  },
  plugins: [],
}

