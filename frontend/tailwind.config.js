/** @type {import('tailwindcss').Config} */
export default {
    content: ["./index.html", "./src/**/*.{vue,js,ts,jsx,tsx}"],
    theme: {
        extend: {
            colors: {
                // Wild Alaskan Brand Colors
                'alaskan': {
                    50: '#f0fdfa',   // Very light teal background
                    100: '#ccfbf1',  // Light teal background
                    200: '#99f6e4',  // Light teal accent
                    300: '#5eead4',  // Medium light teal
                    400: '#2dd4bf',  // Medium teal
                    500: '#14b8a6',  // Primary brand teal (closest to logo)
                    600: '#0d9488',  // Darker teal for hover states
                    700: '#0f766e',  // Dark teal for text/borders
                    800: '#115e59',  // Very dark teal
                    900: '#134e4a',  // Darkest teal
                },
                'salmon': {
                    50: '#fef7f0',   // Very light salmon
                    100: '#feeee0',  // Light salmon background
                    200: '#fdd5b8',  // Light salmon accent  
                    300: '#fbb885',  // Medium light salmon
                    400: '#f89650',  // Medium salmon
                    500: '#f97316',  // Primary salmon (complementary to teal)
                    600: '#ea580c',  // Darker salmon
                    700: '#c2410c',  // Dark salmon
                    800: '#9a3412',  // Very dark salmon
                    900: '#7c2d12',  // Darkest salmon
                },
                'ocean': {
                    50: '#f0f9ff',   // Very light ocean blue
                    100: '#e0f2fe',  // Light ocean background
                    200: '#bae6fd',  // Light ocean accent
                    300: '#7dd3fc',  // Medium light ocean
                    400: '#38bdf8',  // Medium ocean blue
                    500: '#0ea5e9',  // Primary ocean blue
                    600: '#0284c7',  // Darker ocean
                    700: '#0369a1',  // Dark ocean
                    800: '#075985',  // Very dark ocean
                    900: '#0c4a6e',  // Darkest ocean
                },
                'driftwood': {
                    50: '#fafaf9',   // Very light neutral
                    100: '#f5f5f4',  // Light neutral background
                    200: '#e7e5e4',  // Light neutral accent
                    300: '#d6d3d1',  // Medium light neutral
                    400: '#a8a29e',  // Medium neutral
                    500: '#78716c',  // Primary driftwood neutral
                    600: '#57534e',  // Darker neutral
                    700: '#44403c',  // Dark neutral
                    800: '#292524',  // Very dark neutral
                    900: '#1c1917',  // Darkest neutral
                },
                'golden': {
                    DEFAULT: '#ffb600',
                    50: '#fffaf0',
                    100: '#fff5e6',
                    200: '#ffe4b3',
                    300: '#ffd280',
                    400: '#ffc14d',
                    500: '#ffb600',
                    600: '#e6a300',
                    700: '#cc9100',
                    800: '#997000',
                    900: '#664a00',
                }
            }
        },
    },
    plugins: [],
};
