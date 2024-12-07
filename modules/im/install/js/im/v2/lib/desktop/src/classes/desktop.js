import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { EventType } from 'im.v2.const';
import { DesktopApi, DesktopSettingsKey } from 'im.v2.lib.desktop-api';
import { Logger } from 'im.v2.lib.logger';
import { ReloadChecker } from './reload-checker';

import { BxLinkHandler } from './event-handlers/bx-link';
import { AuthHandler } from './event-handlers/auth';
import { StatusHandler } from './event-handlers/status';
import { CounterHandler } from './event-handlers/counter';
import { HotkeyHandler } from './event-handlers/hotkey';
import { NewTabHandler } from './event-handlers/new-tab';

/* eslint-disable no-undef */
export class Desktop
{
	static init(): Desktop
	{
		return new Desktop();
	}

	constructor()
	{
		ReloadChecker.init();

		StatusHandler.init();
		AuthHandler.init();
		BxLinkHandler.init();
		CounterHandler.init();
		HotkeyHandler.init();
		NewTabHandler.init();

		this.#sendInitEvent();
		this.#subscribeOnErrorEvent();

		this.#initSliderBindings();
		this.#initComplete();
	}

	#sendInitEvent()
	{
		const { currentUser } = Core.getApplicationData();
		DesktopApi.emit(EventType.desktop.onInit, [{
			userInfo: currentUser ?? {},
		}]);
	}

	#initSliderBindings()
	{
		const sliderBindingStatus = this.#getSliderBindingsStatus();
		if (sliderBindingStatus)
		{
			BX.SidePanel.Instance.enableAnchorBinding();

			return;
		}

		BX.SidePanel.Instance.disableAnchorBinding();
	}

	#getSliderBindingsStatus(): boolean
	{
		const result = DesktopApi.getCustomSetting(DesktopSettingsKey.sliderBindingsStatus, '1');

		return result === '1';
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
