module.exports = {
	input: 'src/index.js',
	output: 'dist/debugger.bundle.js',
	namespace: 'BX.Bizproc',
	browserslist: true,
	concat: {
		css: ['src/style.css']
	},
	//transformClasses: true
};