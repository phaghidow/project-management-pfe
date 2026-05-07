import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Light UI backgrounds
                'bg-page': '#F8F9FC',
                'bg-card': '#FFFFFF',
                'border-ui': '#E2E8F0',

                // Primary (AT blue)
                primary: {
                    50: '#eef2ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#1E216D',
                    600: '#1A1D5A',
                    700: '#151B48',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },
                // Success
                success: {
                    500: '#397B44',
                },
                // Warning
                warning: {
                    500: '#F59E0B',
                },
                // Secondary alias (AT blue)
                secondary: {
                    DEFAULT: '#2E3192',
                    50: '#eef2ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#1E216D',
                    600: '#1A1D5A',
                    700: '#151B48',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                },
                // Secondary/Alert
                'at-orange': {
                    50: '#fff7ed',
                    100: '#ffedd5',
                    200: '#fed7aa',
                    300: '#fdba74',
                    400: '#fb923c',
                    500: '#f37021',
                    600: '#ea580c',
                    700: '#c2410c',
                    800: '#9a3412',
                    900: '#7c2d12',
                    950: '#431407',
                },
                'at-red': {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#ed1c24',
                    600: '#dc2626',
                    700: '#b91c1c',
                    800: '#991b1b',
                    900: '#7f1d1d',
                    950: '#450a0a',
                },
            },
        },
    },

    plugins: [forms],
};