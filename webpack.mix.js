const mix = require('laravel-mix');

mix

// Forms
  // Front-end
  .js([
    'src/web/assets/fieldmapping/src/js/fieldmapping.js',
  ], 'src/web/assets/fieldmapping/dist/js/fieldmapping.min.js');