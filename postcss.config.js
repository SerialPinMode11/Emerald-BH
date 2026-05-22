import autoprefixer from 'autoprefixer';
import tailwindcss from 'tailwindcss';

/** PostCSS for Tailwind CSS v3 (not v4 @tailwindcss/postcss) */
export default {
    plugins: [tailwindcss, autoprefixer],
};
