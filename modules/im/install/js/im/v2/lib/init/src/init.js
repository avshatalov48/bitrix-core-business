import { Core } from 'im.v2.application.core';
import { CallManager } from 'im.v2.lib.call';
import { PhoneManager } from 'im.v2.lib.phone';
import { SmileManager } from 'im.v2.lib.smile-manager';
import { UserManager } from 'im.v2.lib.user';
import { CounterManager } from 'im.v2.lib.counter';
import { Logger } from 'im.v2.lib.logger';
import { MessageNotifierManager } from 'im.v2.lib.message-notifier';
import { MarketManager } from 'im.v2.lib.market';
import { DesktopManager } from 'im.v2.lib.desktop';
import { PromoManager } from 'im.v2.lib.promo';
import { PermissionManager } from 'im.v2.lib.permission';
import { UpdateStateManager } from 'im.v2.lib.update-state.manager';
import { Router } from 'im.v2.lib.router';

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
		this.#initTariffRestrictions();
		this.#initAnchors();
		this.#initCallManager();
		this.#initAvailableAIModelsList();

		CounterManager.init();
		PermissionManager.init();
		PromoManager.init();
		MarketManager.init();
		PhoneManager.init();
		SmileManager.init();
		MessageNotifierManager.init();
		DesktopManager.init();
		UpdateStateManager.init();
		Router.init();

		this.#started = true;
	}

	static #initCurrentUser()
	{
		const { currentUser } = Core.getApplicationData();
		if (!currentUser)
		{
			return;
		}

		void new UserManager().setUsersToModel([currentUser]);
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
		void Core.getStore().dispatch('application/settings/set', settings);
	}

	static #initTariffRestrictions()
	{
		const { tariffRestrictions } = Core.getApplicationData();
		if (!tariffRestrictions)
		{
			return;
		}

		Logger.warn('InitManager: tariffRestrictions', tariffRestrictions);
		void Core.getStore().dispatch('application/tariffRestrictions/set', tariffRestrictions);
	}

	static #initCallManager()
	{
		const { activeCalls } = Core.getApplicationData();
		CallManager.getInstance().updateRecentCallsList(activeCalls);
	}

	static #initAnchors()
	{
		const { anchors } = Core.getApplicationData();
		if (!anchors)
		{
			return;
		}

		void Core.getStore().dispatch('messages/anchors/setAnchors', { anchors });
	}

	static #initAvailableAIModelsList(): void
	{
		const { copilot } = Core.getApplicationData();

		if (!copilot.availableEngines)
		{
			return;
		}

		void Core.getStore().dispatch('copilot/setAvailableAIModels', copilot.availableEngines);
	}
}
