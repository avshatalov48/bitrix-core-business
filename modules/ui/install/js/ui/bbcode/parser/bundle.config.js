const fs = require('fs');
const path = require('path');
const webToMobilePlugin = require('../.build-plugins/web-to-mobile');

module.exports = {
	input: 'src/parser.js',
	output: 'dist/parser.bundle.js',
	namespace: 'BX.UI.BBCode',
	browserslist: true,
	plugins: {
		custom: [
			webToMobilePlugin({
				targetExtension: 'bbcode/parser',
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
						/ui_bbcode_model/g,
						'Model',
					],
					[
						/ui_bbcode_encoder\./g,
						'',
					],
					[
						/ui_bbcode_astProcessor\./g,
						'',
					],
					[
						/ui_linkify\./g,
						'',
					],
				],
				banner: fs.readFileSync(path.resolve(__dirname, 'build/chunks/attention.message.txt'), 'ascii'),
			}),
		],
	},
};
