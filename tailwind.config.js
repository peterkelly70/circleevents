import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import fs from 'node:fs';

const organizationThemeSource = fs.readFileSync('./app/Support/OrganizationThemes.php', 'utf8');
const organizationThemeSafelist = Array.from(
    new Set(
        [...organizationThemeSource.matchAll(/'([^']+)'/g)]
            .map((match) => match[1])
            .filter((value) => value.includes(' '))
            .flatMap((value) => value.split(/\s+/))
            .filter(Boolean)
    )
);

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './app/**/*.php',
        './resources/views/**/*.blade.php',
    ],
    safelist: organizationThemeSafelist,

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
