module.exports = {
	input: 'src/stepbystep.js',
	output: 'dist/stepbystep.bundle.js',
	concat: {
		css: ['src/style.css']
	},
	namespace: 'BX.UI'
};