import { Core } from 'im.v2.application.core';
import { EventType } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import { BxLinkHandler } from './event-handlers/bx-link';
import { AuthHandler } from './event-handlers/auth';
import { StatusHandler } from './event-handlers/status';
import { CounterHandler } from './event-handlers/counter';
import { HotkeyHandler } from './event-handlers/hotkey';

export class Desktop
{
	static init(): Desktop
	{
		return new Desktop();
	}

	constructor()
	{
		StatusHandler.init();
		AuthHandler.init();
		BxLinkHandler.init();
		CounterHandler.init();
		HotkeyHandler.init();

		this.#sendInitEvent();
	}

	#sendInitEvent()
	{
		const { currentUser } = Core.getApplicationData();
		DesktopApi.emit(EventType.desktop.onInit, [{
			userInfo: currentUser ?? {},
		}]);
	}
}
