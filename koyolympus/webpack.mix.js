const mix = require('laravel-mix');
const CompressionPlugin = require('compression-webpack-plugin');
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

mix.js('resources/js/app.js', 'public/js')
    .js("resources/js/router.js", "public/js")
    .sass('resources/sass/app.scss', 'public/css')
    .webpackConfig({
        plugins: [
            new CompressionPlugin({
                filename: "[path][base].gz",
                algorithm: 'gzip',
                test: /\.js$|\.css$|\.html$|\.svg$/,
                threshold: 10240,
                minRatio: 0.8,
            })
        ],
        output: {
            chunkFilename: 'js/chunk/[name].js',
        }
    })
    .options({
        processCssUrls: false
    }).vue();

if (mix.inProduction()) {
    mix.version();
}
