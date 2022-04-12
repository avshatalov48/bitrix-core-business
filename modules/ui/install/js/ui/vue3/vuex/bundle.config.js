module.exports = {
	input: './src/vuex.js',
	output: './dist/vuex.bundle.js',
	namespace: 'BX.Vue3.Vuex',
	concat: {
		js: [
			'./src/wrap/start.js',
			'./dist/vuex.bundle.js',
			'./src/wrap/end.js',
		],
	},
	browserslist: true,
};