/* eslint-disable */
const fs = require('fs');
const path = require('path');

// List of special figma tokens that are in hexa format (with transparency)
const specialHexTokens = [
	"backgrounds/state/bg-state-hover-default",
	"backgrounds/state/bg-state-click-default",
	"backgrounds/state/bg-state-hover-alt",
	"backgrounds/state/bg-state-click-alt",
	"backgrounds/state/bg-state-hover-default-overlay",
	"backgrounds/state/bg-state-click-default-overlay",
	"backgrounds/state/bg-state-hover-alt-overlay",
	"backgrounds/state/bg-state-click-alt-overlay"
];

/**
 * Applies custom rules for token values.
 * Custom rules can override the default behavior.
 * For example, if the token name contains "opacity" and its type is "number",
 * then the value is divided by 100 and no "px" suffix is added.
 * @param {Object} token - The token object.
 * @param {string} defaultValue - The default CSS value computed.
 * @returns {string} The CSS value after applying custom rules.
 */
function applyCustomRules(token, defaultValue)
{
	// Custom rule: for number tokens with "opacity" in their name,
	// divide the value by 100 and do not append "px"
	if (token.type === 'number' && token.name.toLowerCase().includes('opacity'))
	{
		return (token.value / 100).toString();
	}

	return defaultValue;
}

/**
 * Checks if the token should be ignored based on excluded sections.
 * Splits the token name by '/' and returns true if any section is in the excludedSections list.
 * @param {string} tokenName - The full token name (e.g., "base/ignore/base 0")
 * @param {Array<string>} excludedSections - Array of section names to ignore
 * @returns {boolean} True if the token should be ignored, false otherwise
 */
function shouldIgnoreToken(tokenName, excludedSections)
{
	const parts = tokenName.split('/');
	for (const part of parts)
	{
		if (excludedSections.includes(part))
		{
			return true;
		}
	}

	return false;
}

/**
 * Transforms a token name into a CSS variable name.
 * Rules:
 * - Only the last part after '/' is used
 * - Replaces spaces with '-' and converts to lowercase
 * - If type === "color", the prefix "color-" is added (this can be extended in the future)
 * - In any case, the prefix "ui-" is added
 * Example: token "base/base 0", type "color" becomes "ui-color-base-0"
 * @param {string} originalName - Original token name (e.g., "base/base 0")
 * @param {string} type - Token type (e.g., "color")
 * @returns {string} Transformed CSS variable name (e.g., "ui-color-base-0")
 */
function transformTokenName(originalName, type)
{
	const parts = originalName.split('/');
	let lastPart = parts[parts.length - 1];
	lastPart = lastPart.replace(/\s+/g, '-').toLowerCase();
	const typePrefix = type === 'color' ? 'color-' : '';

	return `ui-${typePrefix}${lastPart}`;
}

/**
 * Builds a lookup table for CSS variable names by iterating over all collections and modes.
 * The lookup table is structured as: { [collectionName]: { [tokenOriginalName]: cssVariableName } }
 * Collections not in the provided collections list are skipped.
 * Tokens that should be ignored are skipped.
 * @param {Array} collectionsData - Array of collections from the JSON
 * @param {Array<string>} excludedSections - Array of section names to ignore
 * @param {Array<string>} collectionsFilter - Array of collection names to include (if empty, include all)
 * @returns {Object} Lookup table for tokens
 */
function buildTokenLookup(collectionsData, excludedSections, collectionsFilter)
{
	const lookup = {};

	collectionsData.forEach(collection =>
	{
		if (collectionsFilter.length > 0 && !collectionsFilter.includes(collection.name))
		{
			return;
		}
		const colName = collection.name;
		if (!lookup[colName])
		{
			lookup[colName] = {};
		}
		collection.modes.forEach(mode =>
		{
			if (Array.isArray(mode.variables))
			{
				mode.variables.forEach(token =>
				{
					if (shouldIgnoreToken(token.name, excludedSections))
					{
						return;
					}
					const cssVarName = transformTokenName(token.name, token.type);
					lookup[colName][token.name] = cssVarName;
				});
			}
		});
	});


	return lookup;
}

/**
 * Generates CSS variables for a given array of tokens.
 * For alias tokens (isAlias === true), the value is substituted as a reference to another CSS variable.
 * For tokens of type "number", the "px" suffix is added to the value by default,
 * but custom rules (e.g., for "opacity") can override this behavior.
 * Tokens that should be ignored are skipped.
 * Special hex tokens (with transparency) are split into two variables:
 *   - One with a "-hex" suffix holding the pure hex color (without alpha)
 *   - One with an "-opacity" suffix holding the opacity in percentage.
 * @param {Array} tokens - Array of tokens
 * @param {Object} lookup - Lookup table for token CSS variable names
 * @param {Array<string>} excludedSections - Array of section names to ignore
 * @returns {string} CSS variables declarations
 */
function generateCssVariables(tokens, lookup, excludedSections)
{
	let cssContent = '';

	tokens.forEach(token =>
	{
		if (shouldIgnoreToken(token.name, excludedSections))
		{
			return;
		}

		const cssVarName = transformTokenName(token.name, token.type);

		// Special processing for hex tokens with transparency
		if (specialHexTokens.includes(token.name))
		{
			const hexValue = token.value;

			if (hexValue && hexValue[0] === '#' && hexValue.length === 9)
			{
				const pureHex = hexValue.slice(0, 7); // "#RRGGBB"
				const alphaHex = hexValue.slice(7);   // "AA"
				const opacity = Math.round((parseInt(alphaHex, 16) / 255) * 100);

				cssContent += `  --${cssVarName}-hex: ${pureHex};\n`;
				cssContent += `  --${cssVarName}-opacity: ${opacity}%;\n`;
			}
			else
			{
				cssContent += `  --${cssVarName}: ${token.value};\n`;
			}

			cssContent += `  --${cssVarName}: ${token.value};\n`;

			return;
		}

		let cssValue = '';

		if (token.isAlias && typeof token.value === 'object')
		{
			const targetCollection = token.value.collection;
			const targetName = token.value.name;

			if (lookup[targetCollection] && lookup[targetCollection][targetName])
			{
				const targetCssVarName = lookup[targetCollection][targetName];
				cssValue = `var(--${targetCssVarName})`;
			}
			else
			{
				console.error(`Alias target token not found: collection "${targetCollection}", name "${targetName}"`);
				cssValue = 'undefined';
			}
		}
		else
		{
			if (token.type === 'number')
			{
				const defaultValue = `${token.value}px`;
				cssValue = applyCustomRules(token, defaultValue);
			}
			else
			{
				cssValue = token.value;
			}
		}


		// Custom blur token formatting
		if (token.name.toLowerCase().includes('blur')) {
		    if (token.isAlias && typeof token.value === 'object') {
		        // alias: leave cssValue as reference
		    } else if (token.type === 'number') {
		        if (token.value === 0) {
		            cssValue = 'none';
		        } else {
		            const defaultValue = `${token.value}px`;
		            const pxValue = applyCustomRules(token, defaultValue);
		            cssValue = `blur(${pxValue})`;
		        }
		    } else {
		        cssValue = `blur(${cssValue})`;
		    }
		}
		cssContent += `  --${cssVarName}: ${cssValue};\n`;
	});


	return cssContent;
}

/**
 * Generates the full CSS content based on JSON data.
 * - If a collection has only one mode, the variables are written to :root.
 * - If there are multiple modes:
 *   - The first mode in the list is treated as the default mode.
 *   - For the default mode, a combined selector with :root is used, e.g.:
 *         :root, .--ui-context-content-light {
 *             ...variables
 *         }
 *   - The other modes are written in their own CSS classes.
 * The class name is derived from the mode name: lowercased, with spaces replaced by '-', prefixed with "--ui-context-".
 * A header with generation information is prepended.
 * Collections not in the provided collections list are skipped.
 * @param {Object} jsonData - Parsed JSON data containing collections and modes
 * @param {Array<string>} excludedSections - Array of section names to ignore
 * @param {Array<string>} collectionsFilter - Array of collection names to include (if empty, include all)
 * @returns {string} Generated CSS content
 */
function generateCss(jsonData, excludedSections, collectionsFilter)
{
	const header = `/**\n * Do not edit directly\n * Generated on ${new Date().toUTCString()}\n */\n\n`;
	const { collections } = jsonData;
	// Collect bg-blur variable names for reset class
	const bgBlurVars = new Set();
	collections.forEach(collection => {
	    if (collectionsFilter.length > 0 && !collectionsFilter.includes(collection.name)) return;
	    collection.modes.forEach(mode => {
	        mode.variables.forEach(token => {
	            if (!shouldIgnoreToken(token.name, excludedSections) &&
	                token.name.toLowerCase().includes('bg-blur')) {
	                const varName = transformTokenName(token.name, token.type);
	                bgBlurVars.add(varName);
	            }
	        });
	    });
	});
	const tokenLookup = buildTokenLookup(collections, excludedSections, collectionsFilter);
	let cssOutput = header;

	collections.forEach(collection =>
	{
		if (collectionsFilter.length > 0 && !collectionsFilter.includes(collection.name))
		{
			return;
		}

		if (collection.modes.length === 1)
		{
			const mode = collection.modes[0];
			cssOutput += `:root {\n`;
			cssOutput += generateCssVariables(mode.variables, tokenLookup, excludedSections);
			cssOutput += `}\n\n`;
		}
		else
		{
			// Determine the default mode: use the first mode in the modes array.
			const defaultMode = collection.modes[0];

			const defaultClassName = `--ui-context-${defaultMode.name.toLowerCase().replace(/\s+/g, '-')}`;
			cssOutput += `:root, .${defaultClassName} {\n`;
			cssOutput += generateCssVariables(defaultMode.variables, tokenLookup, excludedSections);
			cssOutput += `}\n\n`;

			// Write separate classes for the other modes
			collection.modes.forEach(mode =>
			{
				if (mode.name.toLowerCase() === defaultMode.name.toLowerCase())
				{
					return;
				}
				const modeClassName = `--ui-context-${mode.name.toLowerCase().replace(/\s+/g, '-')}`;
				cssOutput += `.${modeClassName} {\n`;
				cssOutput += generateCssVariables(mode.variables, tokenLookup, excludedSections);
				cssOutput += `}\n\n`;
			});
		}
	});

	// Generate .reset-bg-blur class setting all bg-blur vars to none
	if (bgBlurVars.size > 0) {
		cssOutput += `.--ui-reset-bg-blur {\n`;
		bgBlurVars.forEach(varName => {
			cssOutput += `  --${varName}: none;\n`;
		});
		cssOutput += `}\n\n`;
	}
	return cssOutput;
}

/**
 * Parses named command line arguments.
 * Supported arguments:
 * --input=<path>        - Path to input JSON file (default: "./src/figma-tokens.json")
 * --output=<path>       - Path to output CSS file (default: "./dist/air-design-tokens.css")
 * --exclude=<list>      - Comma separated list of sections to ignore (default: empty)
 * --collections=<list>  - Comma separated list of collection names to include (default: include all)
 * @returns {Object} Parsed arguments with keys: input, output, exclude, collections
 */
function parseArgs()
{
	const defaultInputPath = './src/figma-tokens.json';
	const defaultOutputPath = './dist/air-design-tokens.css';
	const args = process.argv.slice(2);
	let inputFile = defaultInputPath;
	let outputFile = defaultOutputPath;
	let excludeList = ['legacy', 'chat', 'stage', 'space', 'opacity', 'size', 'radius', 'component', 'font size', 'line height'];
	let collectionsFilter = ['design', 'fixed'];

	args.forEach(arg =>
	{
		if (arg.startsWith('--input='))
		{
			inputFile = arg.split('=')[1];
		}
		else if (arg.startsWith('--output='))
		{
			outputFile = arg.split('=')[1];
		}
		else if (arg.startsWith('--exclude='))
		{
			excludeList = arg.split('=')[1].split(',').map(s => s.trim());
		}
		else if (arg.startsWith('--collections='))
		{
			collectionsFilter = arg.split('=')[1].split(',').map(s => s.trim());
		}
	});


	return {
		input: path.resolve(inputFile),
		output: path.resolve(outputFile),
		exclude: excludeList,
		collections: collectionsFilter
	};
}

/**
 * Main function that processes the JSON file and generates the CSS file.
 * Named command line arguments:
 * --input=<path>        - Path to input JSON file
 * --output=<path>       - Path to output CSS file
 * --exclude=<list>      - Comma separated list of sections to ignore
 * --collections=<list>  - Comma separated list of collection names to include
 */
function main()
{
	const args = parseArgs();
	let jsonData;
	try
	{
		const fileContent = fs.readFileSync(args.input, 'utf8');
		jsonData = JSON.parse(fileContent);
	}
	catch (err)
	{
		console.error('Error reading or parsing JSON file:', err);
		process.exit(1);
	}

	let hoverableCss = '';
	try {
		const hoverablePath = path.join(__dirname, 'src', 'hoverable.css');
		hoverableCss = fs.readFileSync(hoverablePath, 'utf8');
	} catch (err) {
		console.error('Error reading hoverable.css:', err);
		process.exit(1);
	}

	const cssContent = generateCss(jsonData, args.exclude, args.collections);

	const finalCss = `${cssContent}${hoverableCss}`;

	try {
		fs.writeFileSync(args.output, finalCss, 'utf8');
		console.log(`CSS file successfully generated: ${args.output}`);
	} catch (err) {
		console.error('Error writing CSS file:', err);
		process.exit(1);
	}
}

main();
