const fs = require('fs');
const StyleDictionary = require('style-dictionary');
const { fileHeader, sortByReference, createPropertyFormatter } = StyleDictionary.formatHelpers;

const Color = require('tinycolor2');

const fontWeights = {
	100: 'Thin',
	200: 'Extra Light',
	300: 'Light',
	400: 'Regular',
	500: 'Medium',
	600: 'Semi Bold',
	700: 'Bold',
	800: 'Extra Bold',
	900: 'Black',
	950: 'Extra Black',
};

module.exports = {
	source: ['*.json'],
	transform: {
		'css/shadow': {
			type: 'value',
			matcher: (prop) => {
				return prop.attributes.category === 'shadow';
			},
			transformer: (prop) => {
				const { x, y, blur, spread, color, alpha } = prop.original.value;
				//const shadowColor = tinycolor(color);
				//shadowColor.setAlpha(alpha);
				//shadowColor.toRgbString();

				return `${x}px ${y}px ${blur}px ${spread}px ${color}`
			},
		},
		'rgb-color-value': {
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
		'figma/font-weight': {
			type: 'value',
			matcher: (prop) => {
				return prop.attributes.category === 'font' && prop.attributes.type === 'weight';
			},
			transformer: (token) => {
				return fontWeights[token.value];
			},
		},
		'figma/letter-spacing': {
			type: 'value',
			matcher: (prop) => {
				return prop.attributes.category === 'text' && prop.attributes.type === 'letter-spacing';
			},
			transformer: (token) => {
				const value = parseFloat(token.value);
				if (isNaN(value))
				{
					return token.value;
				}

				return (value * 100) + '%';
			},
		},
		'figma/line-height': {
			type: 'value',
			matcher: (prop) => {
				return prop.attributes.category === 'font' && prop.attributes.type === 'line-height';
			},
			transformer: (token) => {
				const value = parseFloat(token.value);
				if (isNaN(value))
				{
					return token.value;
				}

				return (value * 100) + '%';
			},
		},

	},
	transformGroup: {
		'css-design-tokens': [
			'attribute/cti',
			'name/cti/kebab',
			'time/seconds',
			'content/icon',
			'css/shadow',
			'rgb-color-value',
		],
		'figma-design-tokens': [
			'attribute/cti',
			'name/cti/kebab',
			'rgb-color-value',
			'figma/line-height',
			'figma/font-weight',
			'figma/letter-spacing',
		],
	},
	format: {
		cssFormat: ({dictionary, file, options={}}) => {
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
		figmaFormat: ({dictionary}) => {
			const minifyDictionary = (obj) => {
				if (typeof obj !== 'object' || Array.isArray(obj))
				{
					return obj;
				}

				const result = {};
				if (obj.hasOwnProperty('ignoreFigma'))
				{
					return null;
				}
				else if (obj.hasOwnProperty('value'))
				{
					if (obj.figmaValue)
					{
						return { value: obj.figmaValue };
					}
					else
					{
						const value = /\{.+\}/.test(obj.original.value) ? obj.original.value : obj.value;

						return { value };
					}
				}
				else
				{
					for (const name in obj)
					{
						if (obj.hasOwnProperty(name))
						{
							const value = minifyDictionary(obj[name]);
							if (value !== null)
							{
								result[name] = minifyDictionary(obj[name]);
							}
						}
					}
				}

				return result;
			}
			const toCamelCase = (str) => {
				const regex = /[-_\s]+(.)?/g;
				if (!regex.test(str))
				{
					return str.match(/^[A-Z]+$/) ? str.toLowerCase() : str[0].toLowerCase() + str.slice(1);
				}

				str = str.toLowerCase();
				str = str.replace(regex, (match, letter) => {
					return letter ? letter.toUpperCase() : '';
				});

				return str[0].toLowerCase() + str.substr(1);
			}

			const typo = { type: 'typography' };
			const typography = dictionary.tokens.typography;
			Object.keys(typography).forEach(category => {
				typo[category] = {};
				Object.keys(typography[category]).forEach((item) => {;
					const complexValue = {};
					const props = typography[category][item];
					Object.keys(props).forEach(prop => {
						const propData = props[prop];
						complexValue[toCamelCase(prop)] = propData.original.value;
					});

					typo[category][item] = { value: complexValue };
				});
			});

			const result = minifyDictionary(dictionary.tokens);
			result.typography = typo;

			return JSON.stringify({ Bitrix24: result }, null, '\t');
		},
	},
	platforms: {
		css: {
			transformGroup: 'css-design-tokens',
			prefix: 'ui',
			buildPath: '../dist/',
			outputReferences: true,
			files: [
				{
					destination: 'ui.design-tokens.css',
					format: 'cssFormat',
					options: {
						outputReferences: true,
					},
				}
			],
		},
		figma: {
			transformGroup: 'figma-design-tokens',
			buildPath: '../dist/',
			outputReferences: true,
			files: [
				{
					destination: 'figma-tokens.json',
					format: 'figmaFormat',
					/*filter: (token) => {
						if (
							token.attributes.category === 'text'
							&& token.attributes.type === 'decoration'
							&& token.attributes.item === 'style'
						)
						{
							return false;
						}

						return true;
					}*/
				}
			],
		},
	},
};
