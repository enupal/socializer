const mix = require('laravel-mix');

mix

// Forms
  // Front-end
  .js([
    'src/web/assets/provider/src/js/provider.js',
  ], 'src/web/assets/provider/dist/js/provider.min.js');