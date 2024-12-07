const webToMobilePlugin = require('../.build-plugins/web-to-mobile');
const fs = require('fs');
const path = require('path');
module.exports = {
	input: 'src/encoder.js',
	output: 'dist/encoder.bundle.js',
	namespace: 'BX.UI.BBCode',
	browserslist: true,
	plugins: {
		custom: [
			webToMobilePlugin({
				targetExtension: 'bbcode/encoder',
				replacements: [
					[
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/header.web.txt'), 'utf8'),
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/header.mobile.txt'), 'utf8'),
					],
					[
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/footer.web.txt'), 'utf8'),
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/footer.mobile.txt'), 'utf8'),
					],
				],
				banner: fs.readFileSync(path.resolve(__dirname, 'build/chunks/attention.message.txt'), 'utf8'),
			}),
		],
	},
	tests: {
		localization: {
			autoLoad: false,
		},
	},
};
