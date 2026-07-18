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
                // LifePointe brand: Body/UI → Quicksand · Headings → Amaranth · Script accent → Pacifico
                sans: ['Quicksand', 'Trebuchet MS', ...defaultTheme.fontFamily.sans],
                serif: ['Playfair Display', 'Georgia', ...defaultTheme.fontFamily.serif],
                display: ['Amaranth', 'Quicksand', ...defaultTheme.fontFamily.sans],
                script: ['Pacifico', 'Playball', 'cursive'],
            },
            colors: {
                // LifePointe Brand Colors - Primary Burnt Orange (#DD5D20)
                church: {
                    50: '#fdf3ec',
                    100: '#fae3d3',
                    200: '#f5c5a6',
                    300: '#efa274',
                    400: '#e77e45',
                    500: '#DD5D20', // Burnt Orange — primary / action
                    600: '#C24E16', // hover / pressed
                    700: '#a03f12',
                    800: '#7A3B12', // Support brown
                    900: '#62300f',
                    950: '#351806',
                },
                // LifePointe Secondary - Amber Yellow (#F79000)
                secondary: {
                    50: '#fff8eb',
                    100: '#feeecc',
                    200: '#fddb99',
                    300: '#fbc35c',
                    400: '#f9a928',
                    500: '#F79000', // Amber Yellow — highlight
                    600: '#d97a00',
                    700: '#b36200',
                    800: '#8c4d00',
                    900: '#733f02',
                    950: '#422301',
                },
                // LifePointe Neutrals
                cream: {
                    DEFAULT: '#FBF4EA', // default page background
                    deep: '#EFE3CF',    // borders, dividers, muted fills
                },
                ink: '#241813',        // Warm Ink — body text & headings
                lemon: '#B6DF19',      // decorative accent ONLY, never text
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
                // Prayer/Worship Colors - LifePointe Spiritual Purple (#6B3FA0)
                worship: {
                    50: '#f4effa',
                    100: '#e8dff4',
                    200: '#cdb9e7',
                    300: '#ac8bd4',
                    400: '#8a5fbe',
                    500: '#6B3FA0', // Spiritual accent
                    600: '#5a3488',
                    700: '#4a2b70',
                    800: '#3a2158',
                    900: '#2c1943',
                    950: '#180d26',
                },
                // Ministry/Service Colors (LifePointe burnt orange tones)
                ministry: {
                    50: '#fdf3ec',
                    100: '#fae3d3',
                    200: '#f5c5a6',
                    300: '#efa274',
                    400: '#e77e45',
                    500: '#DD5D20',
                    600: '#C24E16',
                    700: '#a03f12',
                    800: '#7A3B12',
                    900: '#62300f',
                    950: '#351806',
                },
                // Alert / energy (LifePointe red)
                danger: {
                    500: '#D5341A',
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
                'church': '0 10px 25px -3px rgba(221, 93, 32, 0.1), 0 4px 6px -2px rgba(221, 93, 32, 0.05)',
                'secondary': '0 10px 25px -3px rgba(247, 144, 0, 0.1), 0 4px 6px -2px rgba(247, 144, 0, 0.05)',
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
                    background: 'linear-gradient(135deg, #DD5D20 0%, #C24E16 100%)',
                },
                '.gradient-secondary': {
                    background: 'linear-gradient(135deg, #F79000 0%, #d97a00 100%)',
                },
                '.gradient-sacred': {
                    background: 'linear-gradient(135deg, #F79000 0%, #d97a00 100%)',
                },
                '.gradient-worship': {
                    background: 'linear-gradient(135deg, #6B3FA0 0%, #5a3488 100%)',
                },
                '.gradient-brand': {
                    background: 'linear-gradient(135deg, #DD5D20 0%, #F79000 100%)',
                },
            }
            addUtilities(newUtilities)
        }
    ],
};
