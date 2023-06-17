import {DesktopFeature} from 'im.v2.const';
import {Type} from 'main.core';

export const DesktopApi = {

	getApiVersion()
	{
		if (!this.isApiAvailable())
		{
			return null;
		}

		const [,,, version] = window.BXDesktopSystem.GetProperty('versionParts');
		return version;
	},
	isApiAvailable()
	{
		return Type.isObject(window.BXDesktopSystem);
	},
	isFeatureEnabled(code: $Values<typeof DesktopFeature>): boolean
	{
		return !!window.BXDesktopSystem?.FeatureEnabled(code);
	},
};