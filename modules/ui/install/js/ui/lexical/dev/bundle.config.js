const replace = require('rollup-plugin-replace');

module.exports = {
	input: '../src/index.js',
	output: 'dist/lexical.dev.bundle.js',
	namespace: 'BX.UI.Lexical',
	browserslist: true,
	plugins: {
		resolve: true,
		custom: [
			replace({
				'process.env.NODE_ENV': JSON.stringify('development'),
			}),
		],
	},
	adjustConfigPhp: false,
};
