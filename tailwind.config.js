import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                tema1: '#195b81',   // Color primario (puede usarse en botones, encabezados, etc.)
                tema2: '#5b8080',   // Color secundario (ideal para textos o acentos)
                tema3: '#157b80',   // Color terciario (para detalles, hover, etc.)
                footer: '#185b81',   // Color específico para el fondo del footer
            },
            fontFamily: {
                sans: ['Montserrat', ...defaultTheme.fontFamily.sans],
            },
            backgroundImage: {
                'text-gradient': 'linear-gradient(84deg, #195b81 24.87%, #5b8080 61.64%)',
              },
        },
    },
    plugins: [forms, typography],
};
