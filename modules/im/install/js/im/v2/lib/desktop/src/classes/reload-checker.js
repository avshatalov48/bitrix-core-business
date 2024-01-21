import { CallManager } from 'im.v2.lib.call';
import { Logger } from 'im.v2.lib.logger';
import { Utils } from 'im.v2.lib.utils';
import { DesktopApi } from 'im.v2.lib.desktop-api';

import { CheckUtils } from './check-utils';

const ONE_HOUR = 60 * 60 * 1000;

export class ReloadChecker
{
	#initDate: Date;
	#sidePanelManager: BX.SidePanel.Manager = BX.SidePanel.Instance;

	static init(): ReloadChecker
	{
		return new ReloadChecker();
	}

	constructor()
	{
		this.#initDate = new Date();
		this.#startReloadCheck();
	}

	#startReloadCheck(): void
	{
		setInterval(async () => {
			const isReloadNeeded = await this.#isReloadNeeded();
			if (isReloadNeeded)
			{
				this.#reloadWindow();
			}
		}, ONE_HOUR);
	}

	async #isReloadNeeded(): Promise<boolean>
	{
		if (Utils.date.isSameDay(new Date(), this.#initDate))
		{
			return false;
		}

		if (this.#sidePanelManager.opened)
		{
			Logger.desktop('Checker: checkDayForReload, slider is open - delay reload');

			return false;
		}

		if (CallManager.getInstance().hasCurrentCall())
		{
			Logger.desktop('Checker: checkDayForReload, call is active - delay reload');

			return false;
		}

		return CheckUtils.testInternetConnection();
	}

	#reloadWindow(): void
	{
		Logger.desktop('Checker: checkDayForReload, new day - reload window');
		DesktopApi.reloadWindow();
	}
}
