const fs = require('fs');
const path = require('path');
const fetchDependencies = require('./fetch-dependencies');
const makeDepsPhpContent = require('./make-deps-php-content');
const getExtensionsPath = require('./get-extensions-path');

/**
 * @param {{
 * 		targetExtension: string,
 * 		replacements: Array<Array<pattern, replacement>>,
 * 		banner?: string,
 * }} options
 */
module.exports = function webToMobilePlugin(options) {
	const extensionDirectoryPath = path.join(
		getExtensionsPath(),
		...options.targetExtension.split('/'),
	);

	const extensionJsPath = path.join(
		extensionDirectoryPath,
		'extension.js',
	);

	return {
		name: 'web-to-mobile-plugin',
		generateBundle(bundle) {
			if (!fs.existsSync(extensionJsPath))
			{
				fs.mkdirSync(extensionDirectoryPath, { recursive: true });
			}

			let bundleContent = fs.readFileSync(bundle.file, 'utf8');
			if (Array.isArray(options.replacements))
			{
				options.replacements.forEach(([pattern, replacement]) => {
					bundleContent = bundleContent.replace(pattern, replacement);
				});
			}

			const dependencies = fetchDependencies(bundleContent);
			if (dependencies.length > 0)
			{
				const depsPhpContent = makeDepsPhpContent(dependencies);
				const depsPhpPath = path.join(extensionDirectoryPath, 'deps.php');
				fs.writeFileSync(depsPhpPath, depsPhpContent, 'utf8');
			}

			if (typeof options.banner === 'string')
			{
				bundleContent = `${options.banner}\n${bundleContent}`;
			}

			fs.writeFileSync(extensionJsPath, bundleContent, 'utf8');
		}
	}
}
