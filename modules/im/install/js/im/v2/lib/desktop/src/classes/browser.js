import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Logger } from 'im.v2.lib.logger';
import { NewTabHandler } from './event-handlers/new-tab';

/* eslint-disable no-undef */
export class Browser
{
	static init(): Browser
	{
		return new Browser();
	}

	constructor()
	{
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
