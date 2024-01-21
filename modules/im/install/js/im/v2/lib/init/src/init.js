import { Core } from 'im.v2.application.core';
import { CallManager } from 'im.v2.lib.call';
import { PhoneManager } from 'im.v2.lib.phone';
import { SmileManager } from 'im.v2.lib.smile-manager';
import { UserManager } from 'im.v2.lib.user';
import { CounterManager } from 'im.v2.lib.counter';
import { Logger } from 'im.v2.lib.logger';
import { NotifierManager } from 'im.v2.lib.notifier';
import { MarketManager } from 'im.v2.lib.market';
import { DesktopManager } from 'im.v2.lib.desktop';
import { PromoManager } from 'im.v2.lib.promo';
import { PermissionManager } from 'im.v2.lib.permission';
import { UpdateStateManager } from 'im.v2.lib.update-state.manager';

export class InitManager
{
	static #started: boolean = false;

	static start()
	{
		if (this.#started)
		{
			return;
		}

		this.#initLogger();
		Logger.warn('InitManager: start');
		this.#initCurrentUser();
		this.#initSettings();

		CounterManager.init();
		PermissionManager.init();
		PromoManager.init();
		MarketManager.init();
		PhoneManager.init();
		CallManager.init();
		SmileManager.init();
		NotifierManager.init();
		DesktopManager.init();
		UpdateStateManager.init();

		this.#started = true;
	}

	static #initCurrentUser()
	{
		const { currentUser } = Core.getApplicationData();
		if (!currentUser)
		{
			return;
		}

		new UserManager().setUsersToModel([currentUser]);
	}

	static #initLogger()
	{
		const { loggerConfig } = Core.getApplicationData();
		if (!loggerConfig)
		{
			return;
		}

		Logger.setConfig(loggerConfig);
	}

	static #initSettings()
	{
		const { settings } = Core.getApplicationData();
		if (!settings)
		{
			return;
		}

		Logger.warn('InitManager: settings', settings);
		Core.getStore().dispatch('application/settings/set', settings);
	}
}
