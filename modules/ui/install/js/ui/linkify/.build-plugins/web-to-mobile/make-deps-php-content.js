/**
 * @param dependencies {Array<string>}
 * @return {string}
 */
module.exports = function makeDepsPhpContent(dependencies) {
	const renderedList = dependencies.map((moduleName) => {
		return `\n\t\t'${moduleName}',`;
	}).join('');

	return `<?php\n\nreturn [\n\t'extensions' => [${renderedList}\n\t],\n];`
};
