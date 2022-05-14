if (Array.isArray(process.argv) ? process.argv.includes('build') : false)
{
	const compat = require('core-js-compat');
	const fs = require('fs');
	const path = require('path');

	const getBrowserslist = (currentDir = __dirname) => {
		const filePath = path.resolve(currentDir, '.browserslistrc');
		if (fs.existsSync(filePath))
		{
			const content = fs.readFileSync(filePath, 'utf-8');
			if (typeof content === 'string')
			{
				return content.split('\n').map((rule) => {
					return rule.trim();
				});
			}
		}
		else if (filePath !== path.sep)
		{
			return getBrowserslist(path.dirname(currentDir));
		}

		return [
			'last 3 versions',
			'not IE <= 11',
			'not dead',
		]
	};

	// Gets required modules by 'browserslist' query
	// https://browserslist.dev/?q=bGFzdCA0IHZlcnNpb25zLCBub3QgSUUgPD0gMTEsIG5vdCBkZWFk
	const result = compat({
		targets: getBrowserslist(),
		filter: /^(es|web)\./,
		version: '3.19',
	});

	const blacklist = [
		'es.symbol.description',
		'es.symbol.match-all',
		'es.array.unscopables.flat',
		'es.array.unscopables.flat-map',
		'es.global-this',
		'es.json.stringify',
		'es.string.replace',
		'es.string.trim',
		'es.string.trim-end',
		'es.string.at-alternative',
		'es.reflect.to-string-tag',
		'web.url-search-params',
		'web.url.to-json',
		'web.url',
	];

	// Creates a source file with the import of the required modules
	const content = result.list
		.filter((moduleName) => {
			return !blacklist.includes(moduleName);
		})
		.reduce((acc, moduleName) => {
			return `${acc}import 'core-js/modules/${moduleName}';\n`;
		}, `// File generated automatically. Don't modify it.\n`);

	const sourceFilePath = path.resolve(
		path.join(__dirname, 'src', 'polyfill.js'),
	);

	fs.writeFileSync(sourceFilePath, content);
}

module.exports = {
	input: 'src/polyfill.js',
	output: 'dist/polyfill.bundle.js',
	concat: {
		js: [
			'./lib/babel-external-helpers.js',
			'./lib/babel-regenerator-runtime.js',
			'./dist/polyfill.bundle.js',
			'./lib/alert-message.js',
		],
	},
	plugins: {
		resolve: true,
	},
	namespaceFunction: null,
	protected: true,
	adjustConfigPhp: false,
	browserslist: true,
};