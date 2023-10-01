const fs = require('fs');
const path = require('path');

/**
 * @param {{
 * 		targetExtension: string,
 * 		replacements: Array<Array<pattern, replacement>>,
 * 		banner?: string,
 * }} options
 */
module.exports = function webToMobilePlugin(options) {
	this.name = 'web-to-mobile-plugin';
	const modulesDirectory = path.join(__dirname.split('modules')[0], 'modules');
	const extensionsDirectory = path.join(modulesDirectory, 'mobile', 'install', 'mobileapp', 'mobile', 'extensions', 'bitrix');
	const targetExtensionFilePath = path.join(extensionsDirectory, ...options.targetExtension.split('/'), 'extension.js');

	return {
		name: 'web-to-mobile-plugin',

		generateBundle(bundle) {
			if (fs.existsSync(targetExtensionFilePath))
			{
				fs.rmSync(targetExtensionFilePath);
			}

			fs.writeFileSync(targetExtensionFilePath, '');

			let bundleContent = fs.readFileSync(bundle.file, 'ascii');
			if (Array.isArray(options.replacements) && options.replacements.length > 0)
			{
				options.replacements.forEach((entry) => {
					if (Array.isArray(entry) && entry.length > 1)
					{
						bundleContent = bundleContent.replace(...entry);
					}
				});
			}

			if (typeof options.banner === 'string' && options.banner.length > 0)
			{
				bundleContent = `${options.banner}\n${bundleContent}`;
			}

			fs.writeFileSync(targetExtensionFilePath, bundleContent);

		}
	}
}