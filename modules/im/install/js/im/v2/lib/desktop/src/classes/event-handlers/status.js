import { Core } from 'im.v2.application.core';
import { CallManager } from 'im.v2.lib.call';
import { Logger } from 'im.v2.lib.logger';
import { DesktopManager } from '../../desktop-manager';
import { Utils } from 'im.v2.lib.utils';
import { EventType, RestMethod, Settings } from 'im.v2.const';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { Browser, Event } from 'main.core';
import { CheckUtils } from '../check-utils';

export class StatusHandler
{
	#initDate: Date;
	#wakeUpTimer = null;
	sidePanelManager: Object = BX.SidePanel.Instance;

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
		const hasConnection = await CheckUtils.testInternetConnection();
		if (!hasConnection)
		{
			Logger.desktop('StatusHandler: onWakeUp event, no internet connection, delay 60 sec');

			clearTimeout(this.#wakeUpTimer);
			this.#wakeUpTimer = setTimeout(this.#onWakeUp.bind(this), 60 * 1000);

			return;
		}

		if (Utils.date.isSameHour(new Date(), this.#initDate))
		{
			Logger.desktop('StatusHandler: onWakeUp event, same hour - restart pull client');
			Core.getPullClient().restart();
		}
		else
		{
			if (this.sidePanelManager.opened)
			{
				clearTimeout(this.#wakeUpTimer);
				this.#wakeUpTimer = setTimeout(this.#onWakeUp.bind(this), 60 * 1000);

				Logger.desktop('StatusHandler: onWakeUp event, slider is open, delay 60 sec');

				return;
			}

			Logger.desktop('StatusHandler: onWakeUp event, reload window');
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
