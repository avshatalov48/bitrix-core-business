const fs = require('fs');
const StyleDictionary = require('style-dictionary');
const { fileHeader, sortByReference, createPropertyFormatter } = StyleDictionary.formatHelpers;

const Color = require('tinycolor2');

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
		'rgb-color-value': {
			name: 'rgb-color-value',
			type: 'value',
			matcher: (prop) => {
				return prop.attributes.category === 'color';
			},
			transformer: (token) => {
				if (token.value === 'none' || token.value === 'transparent')
				{
					return token.value;
				}

				const color = Color(token.value);
				if (color.getAlpha() === 1)
				{
					return color.toHexString();
				}
				else
				{
					return color.toRgbString();
				}
			},
		},
	},
	transformGroup: {
		'ui-design-tokens': [
			'attribute/cti',
			'name/cti/kebab',
			'time/seconds',
			'content/icon',
			'rgb-color-value'
		]
	},
	format: {
		myFormat: ({dictionary, file, options={}}) => {
			const selector = options.selector ? options.selector : `:root`;
			const { outputReferences } = options;
			const formatProperty = createPropertyFormatter({
				outputReferences,
				dictionary,
				format: 'css',
				/*formatting: {
					prefix: '--',
					commentStyle: 'long',
					indentation: '\t',
					separator: ':',
					suffix: ';',
				}*/
			});

			const formatColor = (token) => {
				const prop = formatProperty(token);
				if (token.value === 'transparent' || token.value === 'none')
				{
					return `${prop}\n`;
				}

				let propRgb = prop
					.replace(/(--[^:]+):/, '$1-rgb:')
					.replace(/:(\s*var\([^)]+)/, ':$1-rgb')
				;

				if (!/var\(/.test(prop))
				{
					const color = Color(token.value);
					if (color.isValid() && color.getAlpha() === 1)
					{
						propRgb = propRgb.replace(token.value, `${color._r}, ${color._g}, ${color._b}`);
					}
					else
					{
						propRgb = '';
					}
				}

				return `${prop}\n${propRgb === '' ? '' : propRgb + '\n' }`;
			};

			// Variables
			let result = `${fileHeader({file})}${selector} {\n`;
			dictionary.allTokens.sort(sortByReference(dictionary)).forEach(token => {
				if (token.attributes.category === 'color')
				{
					result += formatColor(token);
				}
				else
				{
					const prop = formatProperty(token);
					result += `${prop}\n`;
				}
			});
			result +=`}\n\n`;

			// Typography
			const typography = dictionary.tokens.typography;
			Object.keys(typography).forEach(category => {
				Object.keys(typography[category]).forEach((item) => {
					const props = typography[category][item];
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
	platforms: {
		css: {
			transformGroup: 'ui-design-tokens',
			prefix: 'ui',
			buildPath: '../dist/',
			outputReferences: true,
			files: [
				{
					destination: 'ui.design-tokens.css',
					format: 'myFormat',
					options: {
						outputReferences: true,
					},
				}
			],
		},
	},
};
