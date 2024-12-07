const webToMobilePlugin = require('../.build-plugins/web-to-mobile');

const fs = require('fs');
const path = require('path');

module.exports = {
	input: 'src/ast-processor.js',
	output: 'dist/ast-processor.bundle.js',
	namespace: 'BX.UI.BBCode',
	browserslist: true,
	plugins: {
		custom: [
			webToMobilePlugin({
				targetExtension: 'bbcode/ast-processor',
				replacements: [
					[
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/header.web.txt'), 'ascii'),
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/header.mobile.txt'), 'ascii'),
					],
					[
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/footer.web.txt'), 'ascii'),
						fs.readFileSync(path.resolve(__dirname, 'build/chunks/footer.mobile.txt'), 'ascii'),
					],
					[
						/main_core\.Type/g,
						'Type',
					],
				],
				banner: fs.readFileSync(path.resolve(__dirname, 'build/chunks/attention.message.txt'), 'ascii'),
			}),
		],
	},
};
