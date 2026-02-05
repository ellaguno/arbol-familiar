/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./plugins/**/*.blade.php",
    ],
    theme: {
        extend: {
            colors: {
                // Colores del proyecto Mi Familia (paleta azul neutra)
                'mf-primary': '#3b82f6',
                'mf-secondary': '#2563eb',
                'mf-accent': '#f59e0b',
                'mf-light': '#dbeafe',
                'mf-dark': '#1d4ed8',
            },
            fontFamily: {
                'sans': ['Montserrat', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
    ],
}
