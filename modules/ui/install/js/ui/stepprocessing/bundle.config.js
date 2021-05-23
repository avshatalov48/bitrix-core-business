module.exports = {
	input: 'src/index.js',
	output: 'dist/process.bundle.js',
	concat: {
		css: ['src/dialog.css']
	},
	namespace: 'BX.UI.StepProcessing'
};