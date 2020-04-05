module.exports = {
	input: './src/core.js',
	output: './core.js',
	namespace: 'BX',
	namespaceFunction: null,
	adjustConfigPhp: false,
	protected: true,
	concat: {
		js: [
			'./src/internal/wrap-start.js',
			'../polyfill/core/dist/polyfill.bundle.js',
			'./core.js',
			'./src/old/core.js',
			'./core_promise.js',
			'./core_ajax.js',

			'../lazyload/dist/lazyload.bundle.js',
			'../parambag/dist/parambag.bundle.js',
			'../fixfontsize/dist/fixfontsize.bundle.js',
			'./src/internal/wrap-end.js',
		],
	},
};