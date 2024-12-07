const webToMobilePlugin = require('../.build-plugins/web-to-mobile');
const fs = require('fs');
const path = require('path');
module.exports = {
	input: 'src/formatter.js',
	output: 'dist/formatter.bundle.js',
	namespace: 'BX.UI.BBCode',
	browserslist: true,
	plugins: {
		custom: [
			webToMobilePlugin({
				targetExtension: 'bbcode/formatter',
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
						/main_core\./g,
						'',
					],
					[
						/ui_bbcode_model/g,
						'Model',
					],
					[
						/ui_bbcode_parser\./g,
						'',
					],
				],
				banner: fs.readFileSync(path.resolve(__dirname, 'build/chunks/attention.message.txt'), 'utf8'),
			}),
		],
	},
};
