import {Type} from 'main.core';

import {versionFunctions} from './functions/version';
import {eventFunctions} from './functions/event';
import {windowFunctions} from './functions/window';
import {iconFunctions} from './functions/icon';
import {settingsFunctions} from './functions/settings';
import {legacyFunctions} from './functions/legacy';

export const DesktopApi = {
	isDesktop(): boolean
	{
		return Type.isObject(window.BXDesktopSystem);
	},
	isTwoWindowMode(): boolean
	{
		return !!BXDesktopSystem?.IsTwoWindowsMode();
	},
	isChatWindow(): boolean
	{
		return location.href.includes('desktop_app');
	},
	exit()
	{
		BXDesktopSystem?.Shutdown();
	},
	log(fileName: string, text: string)
	{
		BXDesktopSystem?.Log(fileName, text);
	},
	...versionFunctions,
	...eventFunctions,
	...windowFunctions,
	...iconFunctions,
	...settingsFunctions,
	...legacyFunctions
};