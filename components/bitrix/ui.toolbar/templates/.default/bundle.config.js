module.exports = {
	input: 'src/index.js',
	output: {
		// js: './script.js',
		js: './dist/ui.toolbar.bundle.js',
		css: './dist/ui.toolbar.bundle.css',
	},
	namespace: 'BX.UI',
	adjustConfigPhp: false,
	browserslist: true,
};
