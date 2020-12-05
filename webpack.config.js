const HTMLWebpackPlugin = require('html-webpack-plugin');
const path = require('path');
const { VueLoaderPlugin } = require('vue-loader');
const { HotModuleReplacementPlugin } = require('webpack');

module.exports = {
  // mode: 'development',
  entry: ['./resources/js/main.js', './resources/css/main.scss'],
  output: {
    filename: 'js/app.js',
    path: path.resolve(__dirname, './public'),
    // publicPath: '/public/js/',
  },
  // devServer: {
  //   contentBase: path.join(__dirname, './'),
  //   host: 'localhost',
  //   port: 8080,
  //   hot: true,
  //   // open: true,
  //   // historyApiFallback: true
  // },
  module: {
    rules: [
      {
        test: /\.js$/,
        loader: 'babel-loader',
        options: {
          presets: ['@babel/preset-env']
        },
        // exclude: /node_modules/
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.scss$/,
        use: [
          {
            loader: 'file-loader',
            options: {
              name: 'css/[name].css',
            }
          }, {
            loader: 'extract-loader'
          }, {
            loader: 'css-loader?-url'
          }, {
            loader: 'postcss-loader'
          }, {
            loader: 'sass-loader'
          }
        ],
      }, {
        test: /\.css$/,
        loaders: ["style-loader", "css-loader"]
      }
    ]
  },
  plugins: [
    new HotModuleReplacementPlugin(),
    new VueLoaderPlugin(),
    // new HTMLWebpackPlugin({
    //   showErrors: true,
    //   cache: true,
    //   title: 'Vue with Webpack',
    //   template: join(__dirname, 'index.php')
    // })
  ]
}

// if (process.env.NODE_ENV === 'production') {
//   module.exports.devtool = '#source-map'
//   // http://vue-loader.vuejs.org/en/workflow/production.html
//   module.exports.plugins = (module.exports.plugins || []).concat([
//     new webpack.DefinePlugin({
//       'process.env': {
//         NODE_ENV: '"production"'
//       }
//     }),
//     new webpack.optimize.UglifyJsPlugin({
//       sourceMap: true,
//       compress: {
//         warnings: false
//       }
//     }),
//     new webpack.LoaderOptionsPlugin({
//       minimize: true
//     })
//   ])
// }
