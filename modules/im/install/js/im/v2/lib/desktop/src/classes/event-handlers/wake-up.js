import { Core } from 'im.v2.application.core';
import { CallManager } from 'im.v2.lib.call';
import { DesktopManager } from 'im.v2.lib.desktop';
import { DesktopApi } from 'im.v2.lib.desktop-api';
import { EventType } from 'im.v2.const';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';

import { DesktopDataUpdater } from '../../helpers/data-updater';
import { CheckUtils } from '../check-utils';

export class WakeUpHandler
{
	#initDate: Date;
	#wakeUpTimer = null;
	sidePanelManager: Object = BX.SidePanel.Instance;

	static init(): WakeUpHandler
	{
		return new WakeUpHandler();
	}

	constructor()
	{
		this.#initDate = new Date();

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

			if (CallManager.getInstance().hasCurrentCall())
			{
				clearTimeout(this.#wakeUpTimer);
				this.#wakeUpTimer = setTimeout(this.#onWakeUp.bind(this), 60 * 1000);

				Logger.desktop('StatusHandler: onWakeUp event, call is active, delay 60 sec');

				return;
			}

			Logger.desktop('StatusHandler: onWakeUp event, reload window');

			if (!DesktopManager.getInstance().canReloadWindow())
			{
				await DesktopDataUpdater.reloadChatInfo();

				return;
			}

			DesktopApi.reloadWindow();
		}
	}
}
