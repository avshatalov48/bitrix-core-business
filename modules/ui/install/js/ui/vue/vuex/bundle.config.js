module.exports = {
	input: './src/vuex.js',
	output: './dist/vuex.bundle.js',
	namespace: 'BX',
	concat: {
		js: [
			'./src/wrap/start.js',
			'./dist/vuex.bundle.js',
			'./src/wrap/end.js',
		],
	},
};