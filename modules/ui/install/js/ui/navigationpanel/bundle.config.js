module.exports = {
	input: 'src/index.js',
	output: {
		js: 'dist/navigationpanel.bundle.js',
		css: 'dist/navigationpanel.bundle.css',
	},
	namespace: 'BX.UI',
	browserslist: true,
	transformClasses: true,
};
