import Type from '../../../type';

type SettingsEntry = {extension: string, script: string};

export default function fetchExtensionSettings(html: string): Array<SettingsEntry>
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