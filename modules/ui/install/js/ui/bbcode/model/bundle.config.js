const webToMobilePlugin = require('../.build-plugins/web-to-mobile');

const fs = require('fs');
const path = require('path');

module.exports = {
	input: 'src/model.js',
	output: 'dist/model.bundle.js',
	namespace: 'BX.UI.BBCode',
	browserslist: true,
	plugins: {
		custom: [
			webToMobilePlugin({
				targetExtension: 'bbcode/model',
				replacements: [
					[
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/header.web.txt'), 'utf8'),
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/header.mobile.txt'), 'utf8'),
					],
					[
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/footer.web.txt'), 'utf8'),
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/footer.mobile.txt'), 'utf8'),
					],
					[
						/main_core\.Type/g,
						'Type',
					],
					[
						/ui_bbcode_encoder\./g,
						'',
					],
				],
				banner: fs.readFileSync(path.resolve(__dirname, 'build/chunks/attention.message.txt'), 'ascii'),
			}),
		],
	},
};
