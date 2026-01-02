import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { EventType } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Logger } from 'im.v2.lib.logger';

import { BxLinkHandler } from './event-handlers/bx-link';
import { AuthHandler } from './event-handlers/auth';
import { StatusHandler } from './event-handlers/status';
import { CounterHandler } from './event-handlers/counter';
import { HotkeyHandler } from './event-handlers/hotkey';
import { NewTabHandler } from './event-handlers/new-tab';
import { SliderBindings } from '../helpers/slider-bindings';
import { WakeUpHandler } from './event-handlers/wake-up';
import { ReloadChecker } from './reload-checker';

/* eslint-disable no-undef */
export class DesktopChatWindow
{
	static init(): DesktopChatWindow
	{
		return new DesktopChatWindow();
	}

	constructor()
	{
		ReloadChecker.init();
		WakeUpHandler.init();
		StatusHandler.init();
		AuthHandler.init();
		BxLinkHandler.init();
		CounterHandler.init();
		HotkeyHandler.init();
		NewTabHandler.init();
		SliderBindings.init();

		this.#sendInitEvent();
		this.#subscribeOnErrorEvent();
		this.#initComplete();
	}

	#sendInitEvent()
	{
		const { currentUser } = Core.getApplicationData();
		DesktopApi.emit(EventType.desktop.onInit, [{
			userInfo: currentUser ?? {},
		}]);
	}

	#initComplete()
	{
		DesktopApi.setLogInfo = function(...params)
		{
			Logger.desktop(...params);
		};

		window.BX.debugEnable(true);

		DesktopApi.printWelcomePrompt();
	}

	#subscribeOnErrorEvent()
	{
		EventEmitter.subscribe(EventType.request.onAuthError, () => {
			return this.#handleInvalidAuthError();
		});
	}

	#handleInvalidAuthError(): Promise
	{
		return DesktopApi.login();
	}
}
