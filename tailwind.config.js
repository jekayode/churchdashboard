import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', 'Georgia', ...defaultTheme.fontFamily.serif],
                display: ['Montserrat', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Church Brand Colors - Primary Green
                church: {
                    50: '#f4f9e8',
                    100: '#e6f2c6',
                    200: '#d1e690',
                    300: '#b8d750',
                    400: '#a5cc3e',
                    500: '#9DC83B', // Primary brand color
                    600: '#8bb332',
                    700: '#759928',
                    800: '#5f7d20',
                    900: '#4d651a',
                    950: '#293609',
                },
                // Church Secondary Colors - Orange
                secondary: {
                    50: '#fef6f0',
                    100: '#fdeadd',
                    200: '#fad1bb',
                    300: '#f6b08e',
                    400: '#f18560',
                    500: '#F1592A', // Secondary brand color
                    600: '#e2421a',
                    700: '#bc3216',
                    800: '#962a18',
                    900: '#792517',
                    950: '#411009',
                },
                // Spiritual/Sacred Colors (keeping golden theme)
                sacred: {
                    50: '#fefce8',
                    100: '#fef9c3',
                    200: '#fef08a',
                    300: '#fde047',
                    400: '#facc15',
                    500: '#eab308',
                    600: '#ca8a04',
                    700: '#a16207',
                    800: '#854d0e',
                    900: '#713f12',
                    950: '#422006',
                },
                // Community/Fellowship Colors (keeping green theme but adjusted)
                community: {
                    50: '#f0fdf4',
                    100: '#dcfce7',
                    200: '#bbf7d0',
                    300: '#86efac',
                    400: '#4ade80',
                    500: '#22c55e',
                    600: '#16a34a',
                    700: '#15803d',
                    800: '#166534',
                    900: '#14532d',
                    950: '#052e16',
                },
                // Prayer/Worship Colors (keeping purple theme)
                worship: {
                    50: '#faf5ff',
                    100: '#f3e8ff',
                    200: '#e9d5ff',
                    300: '#d8b4fe',
                    400: '#c084fc',
                    500: '#a855f7',
                    600: '#9333ea',
                    700: '#7c3aed',
                    800: '#6b21a8',
                    900: '#581c87',
                    950: '#3b0764',
                },
                // Ministry/Service Colors (using secondary orange tones)
                ministry: {
                    50: '#fef6f0',
                    100: '#fdeadd',
                    200: '#fad1bb',
                    300: '#f6b08e',
                    400: '#f18560',
                    500: '#F1592A',
                    600: '#e2421a',
                    700: '#bc3216',
                    800: '#962a18',
                    900: '#792517',
                    950: '#411009',
                },
            },
            spacing: {
                '18': '4.5rem',
                '88': '22rem',
                '128': '32rem',
            },
            borderRadius: {
                '4xl': '2rem',
                '5xl': '2.5rem',
            },
            boxShadow: {
                'church': '0 10px 25px -3px rgba(157, 200, 59, 0.1), 0 4px 6px -2px rgba(157, 200, 59, 0.05)',
                'secondary': '0 10px 25px -3px rgba(241, 89, 42, 0.1), 0 4px 6px -2px rgba(241, 89, 42, 0.05)',
                'sacred': '0 10px 25px -3px rgba(234, 179, 8, 0.1), 0 4px 6px -2px rgba(234, 179, 8, 0.05)',
                'worship': '0 10px 25px -3px rgba(168, 85, 247, 0.1), 0 4px 6px -2px rgba(168, 85, 247, 0.05)',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-up': 'slideUp 0.3s ease-out',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(10px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
            },
        },
    },

    plugins: [
        forms,
        function({ addUtilities }) {
            const newUtilities = {
                '.text-shadow': {
                    textShadow: '0 2px 4px rgba(0,0,0,0.10)',
                },
                '.text-shadow-lg': {
                    textShadow: '0 4px 8px rgba(0,0,0,0.12), 0 2px 4px rgba(0,0,0,0.08)',
                },
                '.gradient-church': {
                    background: 'linear-gradient(135deg, #9DC83B 0%, #8bb332 100%)',
                },
                '.gradient-secondary': {
                    background: 'linear-gradient(135deg, #F1592A 0%, #e2421a 100%)',
                },
                '.gradient-sacred': {
                    background: 'linear-gradient(135deg, #eab308 0%, #ca8a04 100%)',
                },
                '.gradient-worship': {
                    background: 'linear-gradient(135deg, #a855f7 0%, #9333ea 100%)',
                },
                '.gradient-brand': {
                    background: 'linear-gradient(135deg, #9DC83B 0%, #F1592A 100%)',
                },
            }
            addUtilities(newUtilities)
        }
    ],
};
