module.exports = {
	input: './src/vue.js',
	output: './dist/vue.bundle.js',
	namespace: 'BX.Vue3',
	protected: true,
	sourceMaps: false,
	concat: {
		js: [
			'./../dev/src/wrap/start.js',
			'./dist/vue.bundle.js',
			'./../dev/src/wrap/end.js',
		],
	},
	plugins: {
		babel: false,
	},
	browserslist: true,
};