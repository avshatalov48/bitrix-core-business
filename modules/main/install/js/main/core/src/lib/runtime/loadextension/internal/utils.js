import Type from '../../../type';

export function fetchInlineScripts(acc: Array<string>, item: { JS: string, isInternal: boolean })
{
	if (item.isInternal)
	{
		acc.push(item.JS);
	}

	return acc;
}

export function fetchExternalScripts(acc: Array<string>, item: { JS: string, isInternal: boolean })
{
	if (!item.isInternal)
	{
		acc.push(item.JS);
	}

	return acc;
}

export function fetchExternalStyles(acc: Array<string>, item: ?string)
{
	if (Type.isString(item) && item !== '')
	{
		acc.push(item);
	}

	return acc;
}

type SettingsEntry = { extension: string, script: string };

export function fetchExtensionSettings(html: string): Array<SettingsEntry>
{
	if (Type.isStringFilled(html))
	{
		const scripts = html.match(/<script type="extension\/settings" \b[^>]*>([\s\S]*?)<\/script>/g);
		if (Type.isArrayFilled(scripts))
		{
			return scripts.map((script) => {
				const [, extension] = script.match(/data-extension="(.[a-z0-9_.-]+)"/);

				return {
					extension,
					script,
				};
			});
		}
	}

	return [];
}

export function loadAll(items: Array<string>): Promise<void>
{
	const itemsList = Type.isArray(items) ? items : [items];

	if (!itemsList.length)
	{
		return Promise.resolve();
	}

	return new Promise((resolve) => {
		// eslint-disable-next-line
		BX.load(itemsList, resolve);
	});
}
