module.exports = {
	input: 'src/index.js',
	output: {
		js: 'script.js',
		css: 'style.css',
	},
	sourceMaps: false,
	adjustConfigPhp: false,
	cssImages: {
		output: './',
		type: 'copy',
		svgo: true,
	},
	namespace: 'BX.Landing',
};
