module.exports = {
	input: 'src/popup.js',
	output: 'dist/popupcomponentsmader.bundle.js',
	concat: {
		css: ['src/style.css']
	},
	namespace: 'BX.UI'
};