module.exports = {
	input: './src/main.core.minimal.js',
	output: './dist/main.core.minimal.bundle.js',
	namespace: 'BX',
	namespaceFunction: null,
	adjustConfigPhp: false,
	browserslist: true,
	transformClasses: true,
	concat: {
		js: [
			'../src/internal/wrap-start.js',
			'../../polyfill/core/dist/polyfill.bundle.js',
			'./dist/main.core.minimal.bundle.js',
			'../src/internal/wrap-end.js',
		],
	},
};
