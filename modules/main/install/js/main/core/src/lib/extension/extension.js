import Type from '../type';
import SettingsCollection from './internal/settings-collection';
import deepFreeze from './internal/deep-freeze';

const settingsStorage = new Map();

export default class Extension
{
	static getSettings(extensionName: string)
	{
		if (Type.isStringFilled(extensionName))
		{
			if (settingsStorage.has(extensionName))
			{
				return settingsStorage.get(extensionName);
			}

			const settingsScriptNode = document.querySelector(
				`script[data-extension="${extensionName}"]`,
			);
			if (Type.isDomNode(settingsScriptNode))
			{
				const decodedSettings = (() => {
					try
					{
						return new SettingsCollection(
							JSON.parse(settingsScriptNode.innerHTML),
						);
					}
					catch (error)
					{
						return new SettingsCollection();
					}
				})();

				const frozenSettings = deepFreeze(decodedSettings);
				settingsStorage.set(extensionName, frozenSettings);

				return frozenSettings;
			}
		}

		return deepFreeze(new SettingsCollection());
	}
}
