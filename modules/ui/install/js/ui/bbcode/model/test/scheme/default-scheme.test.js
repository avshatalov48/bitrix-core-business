import { DefaultBBCodeScheme } from '../../src/scheme/default-bbcode-scheme';
import fs from 'fs';
import path from 'path';

xdescribe('Default scheme rules', () => {
	let scheme;
	beforeEach(() => {
		scheme = new DefaultBBCodeScheme();
	});

	it('rules generator', () => {
		const scheme = new DefaultBBCodeScheme();

		const map = scheme.getParentChildMap();
		const mapEntries = [...map.entries()];
		const mapKeys = [...map.keys()];

		const resolveName = (name) => {
			return mapEntries.reduce((acc, [key, entry]) => {
				if (entry.aliases.has(name))
				{
					acc.push(key);
				}

				return acc;
			}, [name]);
		};

		let allowedAsserts = '';
		let notAllowedAsserts = '';

		mapEntries
			.filter(([name]) => {
				return !['#tab', '#text', '#linebreak', '#void', 'disk', '#fragment', 'code'].includes(name);
			})
			.forEach(([name, entry]) => {
				const allAllowedChildren = [
					...new Set(
						(() => {
							if (entry.allowedChildren.size === 0)
							{
								return [...map.keys()];
							}

							return [...entry.allowedChildren].flatMap((childName) => {
								if (childName.startsWith('#'))
								{
									return resolveName(childName);
								}

								return childName;
							});
						})()
					),
				]
				.filter((childName) => {
					const allowedIn = [...map.get(childName).allowedIn].flatMap((parentName) => {
						return resolveName(parentName);
					});

					if (allowedIn.length === 0)
					{
						return true;
					}

					return allowedIn.includes(name);
				});

				const notAllowedChildren = mapKeys.filter((key) => {
					return !allAllowedChildren.includes(key);
				});

				allAllowedChildren.forEach((allowedChild) => {
					allowedAsserts += `
	it('${name} allowed ${allowedChild}', () => {
		assert.ok(scheme.isChildAllowed('${name}', '${allowedChild}'));
	});
					`;
				});

				notAllowedChildren.forEach((notAllowedChild) => {
					notAllowedAsserts += `
	it('${name} not allowed ${notAllowedChild}', () => {
		assert.ok(scheme.isChildAllowed('${name}', '${notAllowedChild}') === false);
	});
					`;
				});
			});

		let testContent = `import { DefaultBBCodeScheme } from '../../src/scheme/default-bbcode-scheme';

describe('Parent <-> child rules (auto-generated)', () => {
	let scheme;
	beforeEach(() => {
		scheme = new DefaultBBCodeScheme();
	});
	
	${allowedAsserts}
	
	${notAllowedAsserts}
});
		`;

		fs.writeFileSync(path.join(__dirname, 'default-scheme-rules.test.js'), testContent, 'utf-8');
	});
});
