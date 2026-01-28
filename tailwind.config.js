import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/livewire/flux/stubs/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['DM Sans', ...defaultTheme.fontFamily.sans],
                display: ['DM Serif Display', 'Georgia', 'serif'],
            },
            colors: {
                // Primary forest green palette
                forest: {
                    50: '#f3f6f0',
                    100: '#e4eadd',
                    200: '#c9d6bb',
                    300: '#a6bb8f',
                    400: '#86a068',
                    500: '#6a864d',
                    600: '#536b3c',
                    700: '#425432',
                    800: '#37452b',
                    900: '#2d3a24',
                    950: '#161e11',
                },
                // Warm sage accent
                sage: {
                    50: '#f6f7f4',
                    100: '#e9ece4',
                    200: '#d4d9cb',
                    300: '#b6c0a9',
                    400: '#96a384',
                    500: '#7a8968',
                    600: '#5f6c51',
                    700: '#4b5641',
                    800: '#3e4637',
                    900: '#353c30',
                    950: '#1b1f18',
                },
                // Warm cream backgrounds
                cream: {
                    50: '#fdfcfa',
                    100: '#faf7f2',
                    200: '#f5efe4',
                    300: '#ece2d0',
                    400: '#deccac',
                    500: '#d1b78e',
                    600: '#c19c6e',
                    700: '#a67f58',
                    800: '#87684b',
                    900: '#6f563f',
                    950: '#3b2c20',
                },
                // Terracotta for danger/delete
                terracotta: {
                    50: '#fdf5f3',
                    100: '#fce8e4',
                    200: '#fad5cd',
                    300: '#f5b7a9',
                    400: '#ed8d78',
                    500: '#e0684e',
                    600: '#cc4d31',
                    700: '#ab3d26',
                    800: '#8d3523',
                    900: '#763123',
                    950: '#40160e',
                },
                // Warm brown text
                bark: {
                    50: '#f9f7f5',
                    100: '#f0ebe5',
                    200: '#e0d5ca',
                    300: '#ccbaa8',
                    400: '#b59a83',
                    500: '#a5836a',
                    600: '#98725d',
                    700: '#7e5d4e',
                    800: '#684d44',
                    900: '#574139',
                    950: '#2e211d',
                },
            },
            borderRadius: {
                'xl': '1rem',
                '2xl': '1.25rem',
                '3xl': '1.5rem',
            },
            boxShadow: {
                'soft': '0 2px 15px -3px rgba(45, 58, 36, 0.07), 0 10px 20px -2px rgba(45, 58, 36, 0.04)',
                'soft-lg': '0 10px 40px -3px rgba(45, 58, 36, 0.1), 0 4px 25px -2px rgba(45, 58, 36, 0.05)',
                'inner-soft': 'inset 0 2px 4px 0 rgba(45, 58, 36, 0.05)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-out',
                'slide-up': 'slideUp 0.4s ease-out',
                'scale-in': 'scaleIn 0.3s ease-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
                scaleIn: {
                    '0%': { opacity: '0', transform: 'scale(0.95)' },
                    '100%': { opacity: '1', transform: 'scale(1)' },
                },
            },
        },
    },

    plugins: [forms],
};
