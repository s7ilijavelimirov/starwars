const path = require('path');
const glob = require('glob-all');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const CopyPlugin = require('copy-webpack-plugin');
const webpack = require('webpack');
const purgecss = require('@fullhuman/postcss-purgecss');

// Definišemo mode na osnovu procesa
const isProduction = process.argv.includes('--mode=production');

// Inicijalizujemo entries objekat
let entries = {
  frontend: [],
  admin: []
};

// Zadržavamo istu logiku za JS fajlove
const assets = glob.sync('./assets/js/*.js');
assets.forEach(function (el) {
  let elObj = path.parse(el);
  if (!entries[elObj.name]) {
    entries[elObj.name] = [];
  }
  entries[elObj.name].push(el);
});

// Dodamo SCSS fajlove
entries.frontend.push('./assets/sass/app.scss');
entries.admin.push('./assets/sass/dashboard.scss');

module.exports = {
  entry: entries,
  output: {
    path: path.resolve(__dirname, 'dist'),
    filename: 'js/[name]-build.js',
    clean: true
  },
  resolve: {
    alias: {
      '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
      '~swiper': path.resolve(__dirname, 'node_modules/swiper'),
    }
  },
  module: {
    rules: [
      // Babel konfiguracija za JS
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: "babel-loader",
          options: {
            presets: ['@babel/preset-env']
          }
        }
      },
      // SASS/SCSS kompilacija
      {
        test: /\.(sass|scss)$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              sourceMap: false, // UKLONI SOURCE MAPS u produkciji
              url: false
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  require('autoprefixer'),
                  // PurgeCSS uvek u produkciji
                  ...(isProduction ? [
                    purgecss({
                      content: [
                        './templates/**/*.php',
                        './inc/**/*.php',
                        './header.php',
                        './footer.php',
                        './page.php',
                        './single.php',
                        './404.php',
                        './index.php',
                        './functions.php',
                        './assets/js/**/*.js',
                        './template-parts/**/*.php',
                        './woocommerce/**/*.php' // DODAJ WooCommerce template-e
                      ],
                      defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || [],
                      safelist: {
                        standard: [
                          /^wp-block/,
                          /^wp-has-aspect-ratio/,
                          /^has-/,
                          /^align/,
                          /^search/,
                          /^screen-reader/,
                          /^navbar/,
                          /^nav-/,
                          /^offcanvas/,
                          /^sticky/,
                          /^container/,
                          /^row/,
                          /^col/,
                          /^d-/,
                          /^m-/,
                          /^p-/,
                          /^bg-/,
                          /^text-/,
                          /^carousel/,
                          /^dropdown/,
                          /^btn/,
                          /^fw-/,
                          /^fs-/,
                          /^woocommerce/,
                          /^sw-/,
                          /^form-/,
                          /^input-/,
                          /^table/,
                          /^border/,
                          /^rounded/,
                          'fade',
                          'show',
                          'collapse',
                          'collapsing',
                          'open',
                          'active',
                          'current-menu-item',
                          'current_page_item',
                          'scrolled',
                          'header-scrolled',
                          /^swiper/,
                          'blog-card',
                          'product-card',
                          'product-image',
                          'product-title',
                          'product-price',
                          'blog-image',
                          'blog-details',
                          'read-more',
                        ],
                        deep: [
                          /carousel$/,
                          /dropdown$/,
                          /modal$/,
                          /show$/,
                          /active$/,
                          /control$/,
                          /wrapper$/,
                          /container$/,
                          /item$/,
                          /slide$/,
                          /pagination$/,
                          /button$/,
                          /lazy$/,
                        ],
                        greedy: []
                      }
                    })
                  ] : [])
                ],
              },
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: false,
              sassOptions: {
                includePaths: ['node_modules'],
                quietDeps: true,
                outputStyle: isProduction ? 'compressed' : 'expanded', // KLJUČNO
                silenceDeprecations: ['legacy-js-api', 'import', 'global-builtin'],
                // Dodatne optimizacije
                precision: 6,
                sourceComments: false
              }
            }
          }
        ]
      }
    ]
  },
  plugins: [
    // CSS ekstrakcija
    new MiniCssExtractPlugin({
      filename: 'css/[name].min.css'
    }),

    // Kopiraj potrebne fajlove
    new CopyPlugin({
      patterns: [
        // Bootstrap bundle
        {
          from: 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
          to: 'js/bootstrap.bundle.min.js'
        },
        // Swiper bundle
        {
          from: 'node_modules/swiper/swiper-bundle.min.js',
          to: 'js/swiper-bundle.min.js'
        },
        {
          from: 'node_modules/swiper/swiper-bundle.min.css',
          to: 'css/swiper-bundle.min.css'
        },
        // Slike - DODAJ KOMPRESIJU
        {
          from: 'assets/images',
          to: 'images'
        },
        // Fontovi
        {
          from: 'assets/fonts',
          to: 'fonts'
        }
      ],
    }),

    // UKLONI ProvidePlugin ako ne koristiš globalno
    ...(isProduction ? [] : [
      new webpack.ProvidePlugin({
        bootstrap: ['bootstrap', 'default'],
        Swiper: ['swiper', 'default']
      })
    ])
  ],
  optimization: {
    minimizer: [
      // Agresivniji Terser
      new TerserPlugin({
        parallel: true,
        terserOptions: {
          parse: {
            ecma: 8,
          },
          compress: {
            ecma: 5,
            warnings: false,
            comparisons: false,
            inline: 2,
            drop_console: isProduction,
            drop_debugger: isProduction,
            pure_funcs: isProduction ? ['console.log', 'console.info', 'console.debug', 'console.warn'] : []
          },
          mangle: {
            safari10: true,
          },
          output: {
            ecma: 5,
            comments: false,
            ascii_only: true,
          },
        },
        extractComments: false,
      }),
      // ISPRAVLJEN CSS minifier
      new CssMinimizerPlugin({
        test: /\.css$/i,
        parallel: true,
        minimizerOptions: {
          preset: [
            'default',
            {
              discardComments: { removeAll: true },
              discardDuplicates: true,
              discardOverridden: true,
              normalizeWhitespace: true, // UVEK true za minifikaciju
              mergeLonghand: true,
              mergeRules: true,
              colormin: true,
              convertValues: true,
              calc: true,
              // Zadržimo samo bezbedne optimizacije
              discardUnused: false,
              mergeIdents: false,
              reduceIdents: false,
              zindex: false
            },
          ],
        },
      }),
    ],
    // Ukloni splitChunks u produkciji - pravi dodatne fajlove
    splitChunks: false,
    // Dodaj sideEffects optimizaciju
    sideEffects: false,
    usedExports: true
  },
  mode: isProduction ? 'production' : 'development',
  devtool: false, // UKLONI SVE SOURCE MAPS
  performance: {
    hints: isProduction ? 'error' : false, // Promeni na 'error'
    maxAssetSize: 300000, // Smanji na 300kb
    maxEntrypointSize: 300000
  },
  stats: {
    modules: false,
    children: false,
    entrypoints: false,
    warnings: true,
    colors: true
  }
};