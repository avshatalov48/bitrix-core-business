module.exports = {
	input: './src/polyfill.js',
	output: './dist/polyfill.bundle.js',
	concat: {
		js: [
			'./lib/babel-external-helpers.js',
			'./lib/babel-regenerator-runtime.js',
			'./dist/polyfill.bundle.js',
		],
	},
	namespaceFunction: null,
	protected: true,
};