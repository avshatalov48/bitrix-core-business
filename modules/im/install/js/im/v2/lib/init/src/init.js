import {Core} from 'im.v2.application.core';
import {CallManager} from 'im.v2.lib.call';
import {SmileManager} from 'im.v2.lib.smile-manager';
import {UserManager} from 'im.v2.lib.user';
import {CounterManager} from 'im.v2.lib.counter';
import {Logger} from 'im.v2.lib.logger';
import {NotifierManager} from 'im.v2.lib.notifier';
import {ApplicationName} from 'im.v2.const';
import {MarketManager} from 'im.v2.lib.market';
import {DesktopManager} from 'im.v2.lib.desktop';

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
		this.#initChatRestrictions();
		this.#initCounters();
		this.#initMarket();
		this.#initSettings();

		CallManager.init();
		SmileManager.init();
		NotifierManager.init();
		DesktopManager.init();

		this.#started = true;
	}

	static #initCurrentUser()
	{
		const {currentUser} = Core.getApplicationData(ApplicationName.quickAccess);
		if (!currentUser)
		{
			return false;
		}

		new UserManager().setUsersToModel([currentUser]);
	}

	static #initLogger()
	{
		const {loggerConfig} = Core.getApplicationData(ApplicationName.quickAccess);
		if (!loggerConfig)
		{
			return false;
		}

		Logger.setConfig(loggerConfig);
	}

	static #initChatRestrictions()
	{
		const {chatOptions} = Core.getApplicationData(ApplicationName.quickAccess);
		if (!chatOptions)
		{
			return false;
		}

		Core.getStore().dispatch('dialogues/setChatOptions', chatOptions);
	}

	static #initCounters()
	{
		const {counters} = Core.getApplicationData(ApplicationName.quickAccess);
		if (!counters)
		{
			return false;
		}

		Logger.warn('InitManager: counters', counters);
		CounterManager.init(counters);
	}

	static #initMarket()
	{
		const {marketApps} = Core.getApplicationData(ApplicationName.quickAccess);
		if (!marketApps)
		{
			return;
		}

		Logger.warn('InitManager: marketApps', marketApps);
		MarketManager.init(marketApps);
	}

	static #initSettings()
	{
		const {settings} = Core.getApplicationData(ApplicationName.quickAccess);
		if (!settings)
		{
			return;
		}

		Logger.warn('InitManager: settings', settings);
		Core.getStore().dispatch('application/settings/set', settings);
	}
}