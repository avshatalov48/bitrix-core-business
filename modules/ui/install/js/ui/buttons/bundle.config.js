module.exports = {
	input: 'src/index.js',
	output: {
		js: 'dist/ui.buttons.bundle.js',
		css: 'dist/ui.buttons.bundle.css',
	},
	namespace: 'BX.UI',
	adjustConfigPhp: false,
	cssImages: {
		type: 'inline',
	},
	browserslist: true,
	transformClasses: true,
};
