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

 mix.styles([
     'public/css/bootstrap.min.css',
     'public/css/font-awesome.min.css',
     'public/css/fileinput.min.css',
     'public/css/selectize-bootstrap4.css',
     'public/css/jquery.fancybox.css',
     'public/css/nosh-timeline.css'
 ], 'public/css/builds/base.css');

 mix.scripts([
     'public/js/jquery-3.4.1.min.js',
     'public/js/bootstrap.bundle.min.js',
     'public/js/bootstrap3-typeahead.min.js',
     'public/js/fileinput.min.js',
     'public/js/theme.min.js',
     'public/js/moment.min.js',
     'public/js/selectize.min.js',
     'public/js/list.min.js',
     'public/js/numeric.min.js',
     'public/js/jquery.abacus.min.js',
     'public/js/jquery.fancybox.js',
     'public/js/toastr.min.js'
 ], 'public/js/builds/base.js');

 mix.version();
