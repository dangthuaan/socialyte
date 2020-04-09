const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/sass/style.scss', 'public/css')
   .js('resources/js/app.js', 'public/js')
   .js('resources/js/load_more.js', 'public/js')
   .js('resources/js/control_block.js', 'public/js')
   .js('resources/js/functions.js', 'public/js')
   .copyDirectory('resources/views/theme', 'public/theme');
