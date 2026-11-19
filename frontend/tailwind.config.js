/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        office: {
          dark: '#0F172A',
          card: '#1E293B',
          border: '#334155',
          accent: '#3B82F6',
          highlight: '#38BDF8',
        }
      }
    },
  },
  plugins: [],
}

