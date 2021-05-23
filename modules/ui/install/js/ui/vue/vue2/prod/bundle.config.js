module.exports = {
	input: './src/vue.js',
	output: './dist/vue.bundle.js',
	namespace: 'BX',
	protected: true,
	concat: {
		js: [
			'./../dev/src/wrap/start.js',
			'./dist/vue.bundle.js',
			'./../dev/src/wrap/end.js',
		],
	},
};