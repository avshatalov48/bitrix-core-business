const replace = require('rollup-plugin-replace');
const path = require('path');
const fs = require('fs');
const { readFile, writeFile } = require('node:fs/promises');

function importFlowType()
{
	return {
		name: 'import-flow-type',
		async buildEnd()
		{
			const lexicalDir = __dirname;
			const lexicalPackageJson = require(path.join(lexicalDir, 'package.json'));
			const bundles = Object.keys(lexicalPackageJson.dependencies)
				.filter(dependence => {
					return dependence.startsWith('@lexical/') || dependence === 'lexical';
				})
				.map(dependence => {
					const id = dependence === 'lexical' ? 'core' : dependence.replace('@lexical/', '');

					return [id, dependence];
				})
			;

			const regex = /(?<=from )'@?lexical(?:\/(?<id>[_a-z-]+))?'/gm;
			const copyFlowFile = async (sourceFilePath, targetFilePath) => {
				const data = await readFile(sourceFilePath, 'utf8');
				const result = data.replaceAll(regex, (match, p1) => {
					return `'ui.lexical.${p1 || 'core'}'`;
				});

				await writeFile(targetFilePath, result, 'utf8');
			};

			for (const [id, nodeModule] of bundles)
			{
				const nodeModulePath = path.join(lexicalDir, 'node_modules', nodeModule);
				const extensionPath = `${path.join(lexicalDir, id)}/src`;
				if (fs.existsSync(nodeModulePath) && fs.existsSync(extensionPath))
				{
					const packageJson = require(path.join(nodeModulePath, 'package.json'));
					const sourceFilePath = path.join(nodeModulePath, `${packageJson.main}.flow`);
					const targetFilePath = path.join(extensionPath, `/lexical-${id}.js.flow`);

					void copyFlowFile(sourceFilePath, targetFilePath);
				}
			}
		},
	};
}

module.exports = {
	input: 'src/index.js',
	output: 'prod/dist/lexical.prod.bundle.min.js',
	namespace: 'BX.UI.Lexical',
	browserslist: true,
	plugins: {
		resolve: true,
		custom: [
			replace({
				'process.env.NODE_ENV': JSON.stringify('production'),
			}),
			importFlowType(),
		],
	},
	adjustConfigPhp: false,
	sourceMaps: false,
	minification: {
		mangle: false,
		enclose: false,
		keep_classnames: false,
		keep_fnames: false,
		ie8: false,
		module: false,
		nameCache: null,
		safari10: false,
		toplevel: false,
	},
};
