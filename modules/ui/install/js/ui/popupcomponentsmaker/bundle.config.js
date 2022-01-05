module.exports = {
	input: 'src/popup.js',
	output: 'dist/popupcomponentsmaker.bundle.js',
	concat: {
		css: ['src/style.css']
	},
	namespace: 'BX.UI'
};