module.exports = {
	input: './src/index.js',
	output: './script.js',
	namespace: 'BX.Main',
	adjustConfigPhp: false,
	namespaceFunction: null,
	protected: true,
	concat: {
		js: [
			'./src/internal/wrap-start.js',
			'./script.js',
			'./script-old.js',
			'./src/internal/wrap-end.js',
		],
	}
};