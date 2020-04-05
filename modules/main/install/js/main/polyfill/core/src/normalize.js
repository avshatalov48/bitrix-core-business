const fs = require('fs');
const path = require('path');

const coreJsModules = path.resolve('node_modules', 'core-js', 'modules');
const redefinedCoreJsModules = path.resolve('redefined_node_modules', 'core-js', 'modules');

// redefine /node_modules/core-js/modules/_shared.js
{
	const sharedModulePath = path.resolve(coreJsModules, '_shared.js');
	const redefinedSharedModulePath = path.resolve(redefinedCoreJsModules, '_shared.js');

	if (
		fs.existsSync(sharedModulePath)
		&& fs.existsSync(redefinedSharedModulePath)
	)
	{
		const content = fs.readFileSync(redefinedSharedModulePath);
		fs.writeFileSync(sharedModulePath, content, {encoding: 'utf8'});
	}
}

// redefine /node_modules/core-js/modules/es6.math.hypot.js
{
	const hypotModulePath = path.resolve(coreJsModules, 'es6.math.hypot.js');
	const redefinedHypotModulePath = path.resolve(redefinedCoreJsModules, 'es6.math.hypot.js');

	if (
		fs.existsSync(hypotModulePath)
		&& fs.existsSync(redefinedHypotModulePath)
	)
	{
		const content = fs.readFileSync(redefinedHypotModulePath);
		fs.writeFileSync(hypotModulePath, content);
	}
}

// redefine /node_modules/core-js/modules/_export.js
{
	const exportModulePath = path.resolve(coreJsModules, '_export.js');
	const redefinedExportModulePath = path.resolve(redefinedCoreJsModules, '_export.js');

	if (
		fs.existsSync(exportModulePath)
		&& fs.existsSync(redefinedExportModulePath)
	)
	{
		const content = fs.readFileSync(redefinedExportModulePath);
		fs.writeFileSync(exportModulePath, content);
	}
}