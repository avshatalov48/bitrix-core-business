module.exports = {
	input: './src/bitrixvue.js',
	output: './dist/bitrixvue.bundle.js',
	namespace: 'BX.Vue3',
	browserslist: true,
	concat: {
		js: [
			'./src/wrap/start.js',
			'./dist/bitrixvue.bundle.js',
			'./src/wrap/end.js',
		],
	},
};