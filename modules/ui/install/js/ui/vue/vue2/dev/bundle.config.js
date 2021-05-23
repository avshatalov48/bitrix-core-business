module.exports = {
	input: './src/vue-dev.js',
	output: './dist/vue.bundle.js',
	namespace: 'BX',
	protected: true,
	concat: {
		js: [
			'./src/wrap/start.js',
			'./dist/vue.bundle.js',
			'./src/wrap/end.js',
		],
	},
};