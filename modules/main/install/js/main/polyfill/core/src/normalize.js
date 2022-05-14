const fs = require('fs');
const path = require('path');

const coreJsInternals = path.resolve('node_modules', 'core-js', 'internals');
const redefinedCoreJsModules = path.resolve('redefined_node_modules', 'core-js', 'internals');

// redefine /node_modules/core-js/internals/shared.js
{
	const sharedModulePath = path.resolve(coreJsInternals, 'shared.js');
	const redefinedSharedModulePath = path.resolve(redefinedCoreJsModules, 'shared.js');

	if (
		fs.existsSync(sharedModulePath)
		&& fs.existsSync(redefinedSharedModulePath)
	)
	{
		const content = fs.readFileSync(redefinedSharedModulePath);
		fs.writeFileSync(sharedModulePath, content, {encoding: 'utf8'});
	}
}