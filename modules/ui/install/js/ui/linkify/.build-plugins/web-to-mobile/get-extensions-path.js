const path = require('path');

module.exports = function getExtensionsPath() {
	const modulesDirectory = path.join(__dirname.split('modules')[0], 'modules');
	return path.join(modulesDirectory, 'mobile', 'install', 'mobileapp', 'mobile', 'extensions', 'bitrix');
};
