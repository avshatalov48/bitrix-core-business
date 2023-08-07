import {Core} from 'im.v2.application.core';
import {Utils} from 'im.v2.lib.utils';
import {EventType, RestMethod} from 'im.v2.const';
import {DesktopApi} from 'im.v2.lib.desktop-api';

import {Checker} from '../checker';

import type {ImModelUser} from 'im.v2.model';

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

		this.#setInitialStatus();
		this.#subscribeToStatusChange();
	}

	// wake up
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
	// end wake up

	// away
	#subscribeToAwayEvent()
	{
		DesktopApi.subscribe(EventType.desktop.onUserAway, this.#onUserAway.bind(this));
	}

	#onUserAway(away: boolean)
	{
		const method = away ? RestMethod.imUserStatusIdleStart : RestMethod.imUserStatusIdleEnd;
		Core.getRestClient().callMethod(method)
			.catch(error => {
				console.error(`Desktop: error in ${method}  - ${error}`);
			});
	}
	// end away

	// user status
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
	// user status
}