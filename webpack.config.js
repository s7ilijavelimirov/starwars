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
              sourceMap: !isProduction,
              url: false
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              postcssOptions: {
                plugins: [
                  require('autoprefixer'),
                  // Dodajemo PurgeCSS samo u produkciji
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
                        './template-parts/**/*.php'
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
                          /^fw-/,  // Font-weight utility klase
                          /^fs-/,  // Font-size utility klase
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
                          // Swiper klase
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
              sourceMap: !isProduction,
              sassOptions: {
                includePaths: ['node_modules'],
                quietDeps: true,
                outputStyle: isProduction ? 'compressed' : 'expanded',
                // Uklanjamo upozorenja za zastarelu sintaksu
                quietDeps: true,
                logger: {
                  warn: function (message) {
                    return message.includes('@import') ? null : console.warn(message);
                  }
                }
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
        // Slike
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

    // Globalno dostupan bootstrap i swiper objekti
    new webpack.ProvidePlugin({
      bootstrap: ['bootstrap', 'default'],
      Swiper: ['swiper', 'default']
    }),

    // Dodaj banner sa informacijama o verziji teme
    new webpack.BannerPlugin({
      banner: `Theme: StarWars | Version: ${require('./package.json').version} | Build date: ${new Date().toISOString()}`
    }),
  ],
  optimization: {
    minimizer: [
      // Terser za JS minifikaciju
      new TerserPlugin({
        parallel: true,
        terserOptions: {
          format: {
            comments: false,
          },
          compress: {
            drop_console: isProduction,
            drop_debugger: isProduction,
          },
        },
        extractComments: false,
      }),
      // CSS minifikacija
      new CssMinimizerPlugin({
        minimizerOptions: {
          preset: [
            'default',
            {
              discardComments: { removeAll: true },
              discardDuplicates: true,
              discardOverridden: true,
              normalizeWhitespace: isProduction,
            },
          ],
        },
      }),
    ],
    // Optimizacija chunk-ova
    splitChunks: isProduction ? {
      chunks: 'all',
      cacheGroups: {
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: 'vendors',
          chunks: 'all'
        }
      }
    } : false
  },
  mode: isProduction ? 'production' : 'development',
  devtool: isProduction ? false : 'source-map',
  performance: {
    hints: isProduction ? 'warning' : false,
    maxAssetSize: 500000, // 500kb
    maxEntrypointSize: 500000
  },
  stats: {
    modules: false,
    children: false,
    entrypoints: false,
    warnings: true,
    colors: true
  }
};