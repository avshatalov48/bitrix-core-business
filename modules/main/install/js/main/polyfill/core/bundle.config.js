module.exports = {
	input: './src/polyfill.js',
	output: './dist/polyfill.bundle.js',
	concat: {
		js: [
			'./lib/babel-external-helpers.js',
			'./lib/babel-regenerator-runtime.js',
			'./dist/polyfill.bundle.js',
			'../matches/js/matches.js',
			'../closest/js/closest.js',
			'../domrect/dist/domrect.bundle.js',
		],
	},
	plugins: {
		resolve: true,
	},
	namespaceFunction: null,
	protected: true,
	adjustConfigPhp: false,
};