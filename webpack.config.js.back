const path = require('path');

const PATHS = {
  ROOT: path.resolve(),
  SRC: path.resolve('client/src'),
  DIST: path.resolve('client/dist'),
};

const ENV = process.env.NODE_ENV;


const ExtractTextPlugin = require("extract-text-webpack-plugin");
const extractSASS = new ExtractTextPlugin({ filename: 'styles/bulkTools.css' });


const config = [
  {
    name: 'js',
    entry: [
      `${PATHS.SRC}/js/manager.js`,
      `${PATHS.SRC}/js/managerBulkEditingForm.js`,
      `${PATHS.SRC}/js/uploader.js`
    ],
    output: {
      path: PATHS.DIST,
      filename: 'js/bulkTools.js'
    },
    devtool: (ENV !== 'production') ? 'source-map' : ''
  },{
    name: 'scss',
    entry: [
      `${PATHS.SRC}/styles/manager.scss`,
      `${PATHS.SRC}/styles/managerBulkEditingForm.scss`,
      `${PATHS.SRC}/styles/uploader.scss`
    ],
    output: {
      path: PATHS.DIST,
      filename: 'styles/bundle.css'
    },
    devtool: (ENV !== 'production') ? 'source-map' : '',
    module: {
      rules: [{
        test: /\.scss$/,
        use: extractSASS.extract({
          fallback: 'style-loader',
          use: [ 'css-loader', 'sass-loader' ]
        })
      }]
    },
    plugins: [
        extractSASS
    ]
  }
];

module.exports = config;