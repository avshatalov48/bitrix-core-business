import {Extension} from 'main.core';

export const legacyFunctions = {
	changeTab(tabId: string)
	{
		const settings = Extension.getSettings('im.v2.lib.desktop-api');
		const v2 = settings.get('v2');
		if (v2)
		{
			return;
		}

		BX.desktop.changeTab(tabId);
	}
};