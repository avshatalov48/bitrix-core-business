import { Core } from 'im.v2.application.core';
import { DesktopManager } from '../../desktop-manager';
import { Utils } from 'im.v2.lib.utils';
import { EventType, RestMethod } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Browser, Event } from 'main.core';

import { Checker } from '../checker';

import type { ImModelUser } from 'im.v2.model';

export class StatusHandler
{
	#initDate: Date;

	static init(): StatusHandler
	{
		return new StatusHandler();
	}

	constructor()
	{
		this.#initDate = new Date();
		this.#subscribeToWakeUpEvent();
		this.#subscribeToAwayEvent();
		this.#subscribeToFocusEvent();
		this.#subscribeToBlurEvent();
		this.#subscribeToIconClickEvent();

		this.#setInitialStatus();
		this.#subscribeToStatusChange();
	}

	// region wake up
	#subscribeToWakeUpEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onWakeUp, this.#onWakeUp.bind(this));
	}

	async #onWakeUp()
	{
		const hasConnection = await Checker.testInternetConnection();
		if (!hasConnection)
		{
			console.error('NO INTERNET CONNECTION!');

			return;
		}

		if (Utils.date.isSameDay(new Date(), this.#initDate))
		{
			Core.getPullClient().restart();
		}
		else
		{
			DesktopApi.reloadWindow();
		}
	}
	// endregion wake up

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
		const userId = Core.getUserId();
		const user: ImModelUser = Core.getStore().getters['users/get'](userId);
		if (!user)
		{
			return;
		}
		DesktopApi.setIconStatus(user.status);
	}

	#subscribeToStatusChange()
	{
		const statusWatcher = (state, getters) => {
			const userId = Core.getUserId();
			const user: ImModelUser = getters['users/get'](userId);

			return user?.status;
		};
		Core.getStore().watch(statusWatcher, (newStatus: string) => {
			DesktopApi.setIconStatus(newStatus);
		});
	}
	// endregion user status
}
