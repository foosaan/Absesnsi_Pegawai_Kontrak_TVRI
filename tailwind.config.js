import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                inter: ['Inter', 'sans-serif'],
            },
            colors: {
                // Admin One compatible color extensions if needed
            },
            transitionProperty: {
                'position': 'right, left, top, bottom, margin, padding',
            },
        },
    },
    plugins: [forms],
};
