module.exports = {
	input: './src/vue-dev.js',
	output: './dist/vue.bundle.js',
	namespace: 'BX.Vue3',
	protected: true,
	concat: {
		js: [
			'./src/wrap/start.js',
			'./dist/vue.bundle.js',
			'./src/wrap/end.js',
		],
	},
	browserslist: true,
};