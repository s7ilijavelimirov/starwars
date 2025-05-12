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
                        './assets/js/**/*.js'
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
                        ],
                        deep: [
                          /carousel$/,
                          /dropdown$/,
                          /modal$/,
                          /show$/,
                          /active$/,
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
                  warn: function(message) {
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

    // Kopiraj samo Bootstrap bundle
    new CopyPlugin({
      patterns: [
        // Bootstrap bundle
        {
          from: 'node_modules/bootstrap/dist/js/bootstrap.bundle.min.js',
          to: 'js/bootstrap.bundle.min.js'
        },
        // Fontovi
        {
          from: 'assets/fonts',
          to: 'fonts'
        }
      ],
    }),

    // Globalno dostupan bootstrap objekat
    new webpack.ProvidePlugin({
      bootstrap: ['bootstrap', 'default']
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