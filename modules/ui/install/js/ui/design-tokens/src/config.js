const fs = require('fs');

module.exports = {
	source: ['*.json'],
	transform: {
		shadow: {
			name: 'shadow/css',
			type: 'value',
			matcher: (prop) => {
				return prop.attributes.category === 'shadow';
			},
			transformer: (prop) => {
				const { x, y, blur, spread, color, alpha } = prop.original.value;
				//const shadowColor = tinycolor(color);
				//shadowColor.setAlpha(alpha);
				//shadowColor.toRgbString();

				return `${x}px ${y}px ${blur}px ${spread}px ${shadowColor}`
			},
		},
	},
	transformGroup: {
		'ui-design-tokens': [
			'attribute/cti',
			'name/cti/kebab',
			'time/seconds',
			'content/icon',
			'color/css',
		]
	},
	format: {
		typography: ({dictionary, platform}) => {

			const tokens = dictionary.tokens;
			//const tokens = dictionary.tokens.filter(token => token.attributes.category === 'typography');

			let result = '';
			Object.keys(tokens.typography).forEach(category => {
				Object.keys(tokens.typography[category]).forEach((item) => {
					const props = tokens.typography[category][item];
					result += `.ui-typography-${category}-${item} {\n`;
					Object.keys(props).forEach(prop => {
						const propData = props[prop];
						result += `\t${prop}: var(--${propData.name});\n`;
					});
					result += `}\n\n`;
				});


			});

			return result;
		},
	},
	action: {
		makeBundle: {
			do: (dictionary, config) => {
				const bundleDir = __dirname + '/../dist';
				if (!fs.existsSync(bundleDir))
				{
					fs.mkdirSync(bundleDir, { recursive: true });
				}

				const bundleFile = bundleDir + '/ui.design-tokens.bundle.css';
				if (fs.existsSync(bundleFile))
				{
					fs.unlinkSync(bundleFile);
				}

				const buildDir = __dirname + '/build';
				//const files = fs.readdirSync(buildDir);
				const files = config.files.map(file => file.destination);

				files.forEach((file) => {
					const filePath = buildDir + '/' + file;
					if (fs.lstatSync(filePath).isFile())
					{
						fs.appendFileSync(bundleFile, fs.readFileSync(filePath).toString());
					}
				});

			},
			undo: () => {

			},
		}
	},
	platforms: {
		css: {
			transformGroup: 'ui-design-tokens',
			prefix: 'ui',
			buildPath: 'build/',
			outputReferences: true,
			files: [
				{
					destination: 'variables.css',
					format: 'css/variables',
					options: {
						"outputReferences": true
					}
				},
				{
					destination: 'typography.css',
					format: 'typography',
					filter: (token) => {
						return token.attributes.category === 'typography';
					},
				}
			],
			actions: ['makeBundle'],
		},
	},
};
