/**
 * @param content {string}
 * @return {Array<string>}
 */
module.exports = function fetchDependenciesContent(content) {
	if (typeof content === 'string')
	{
		const matches = [...content.matchAll(/require\(['"](.*)['"]\)/g)];

		return matches.map(([, moduleName]) => {
			return moduleName;
		});
	}

	return [];
};
