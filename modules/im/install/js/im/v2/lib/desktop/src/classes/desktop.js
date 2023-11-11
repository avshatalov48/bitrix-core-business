import { Core } from 'im.v2.application.core';
import { EventType } from 'im.v2.const';
import { DesktopApi, DesktopSettingsKey } from 'im.v2.lib.desktop-api';
import { Logger } from 'im.v2.lib.logger';

import { BxLinkHandler } from './event-handlers/bx-link';
import { AuthHandler } from './event-handlers/auth';
import { StatusHandler } from './event-handlers/status';
import { CounterHandler } from './event-handlers/counter';
import { HotkeyHandler } from './event-handlers/hotkey';
import { UpdateStateManager } from './update-state';

export class Desktop
{
	#minWidth: number = 1280;
	#minHeight: number = 720;
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

		UpdateStateManager.init();

		this.#setDefaults();
		this.#sendInitEvent();
		this.#initSliderBindings();
		this.#initComplete();
	}

	#setDefaults()
	{
		// DesktopApi.setMinimumWindowSize(this.#minWidth, this.#minHeight);
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
		BXDesktopSystem.LogInfo = function(...params)
		{
			Logger.desktop(...params);
		};

		DesktopApi.printWelcomePrompt();

		window.BX.debugEnable(true);
	}
}
