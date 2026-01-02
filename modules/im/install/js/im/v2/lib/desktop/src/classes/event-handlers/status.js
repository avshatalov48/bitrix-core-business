import { Browser, Event } from 'main.core';

import { Core } from 'im.v2.application.core';
import { EventType, RestMethod, Settings } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import { DesktopManager } from '../../desktop-manager';

export class StatusHandler
{
	static init(): StatusHandler
	{
		return new StatusHandler();
	}

	constructor()
	{
		this.#subscribeToAwayEvent();
		this.#subscribeToFocusEvent();
		this.#subscribeToBlurEvent();
		this.#subscribeToIconClickEvent();

		this.#setInitialStatus();
		this.#subscribeToStatusChange();
	}

	// region icon click
	#subscribeToIconClickEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onIconClick, this.#onIconClick.bind(this));
	}

	#onIconClick()
	{
		DesktopManager.getInstance().toggleConference();
	}

	// endregion icon click

	// region away
	#subscribeToAwayEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onUserAway, this.#onUserAway.bind(this));
	}

	#onUserAway(away: boolean)
	{
		const method = away ? RestMethod.imUserStatusIdleStart : RestMethod.imUserStatusIdleEnd;
		Core.getRestClient().callMethod(method)
			.catch((error) => {
				console.error(`Desktop: error in ${method}  - ${error}`);
			})
		;
	}
	// endregion away

	// region focus/blur events
	#subscribeToFocusEvent()
	{
		Event.bind(window, 'focus', this.#removeNativeNotifications.bind(this));
	}

	#subscribeToBlurEvent()
	{
		// TODO remove this after refactor notification balloons
		Event.bind(window, 'blur', this.#removeNativeNotifications.bind(this));
	}

	#removeNativeNotifications()
	{
		if (!Browser.isWin() || !DesktopApi.isChatWindow())
		{
			return;
		}

		DesktopApi.removeNativeNotifications();
	}
	// endregion focus/blur events

	// region user status
	#setInitialStatus()
	{
		const status = Core.getStore().getters['application/settings/get'](Settings.user.status);
		DesktopApi.setIconStatus(status);
	}

	#subscribeToStatusChange()
	{
		const statusWatcher = (state, getters) => {
			return getters['application/settings/get'](Settings.user.status);
		};
		Core.getStore().watch(statusWatcher, (newStatus: string) => {
			DesktopApi.setIconStatus(newStatus);
		});
	}
	// endregion user status
}
