import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Logger } from 'im.v2.lib.logger';

import { NewTabHandler } from './event-handlers/new-tab';
import { HotkeyHandler } from './event-handlers/hotkey';
import { WakeUpHandler } from './event-handlers/wake-up';
import { ReloadChecker } from './reload-checker';
import { SliderBindings } from '../helpers/slider-bindings';

/* eslint-disable no-undef */
export class DesktopBrowserWindow
{
	static init(): DesktopBrowserWindow
	{
		return new DesktopBrowserWindow();
	}

	constructor()
	{
		if (DesktopApi.isAirDesignEnabledInDesktop())
		{
			ReloadChecker.init();
			WakeUpHandler.init();
			HotkeyHandler.init();
			SliderBindings.init();
		}

		NewTabHandler.init();

		this.#initComplete();
	}

	#initComplete()
	{
		DesktopApi.setLogInfo = function(...params)
		{
			Logger.desktop(...params);
		};
	}
}
