module.exports = {
	input: './src/portal.js',
	output: './dist/portal.bundle.js',
	namespace: 'BX.Vue',
	concat: {
		js: [
			'./src/wrap/start.js',
			'./dist/portal.bundle.js',
			'./src/wrap/end.js',
		],
	},
};
