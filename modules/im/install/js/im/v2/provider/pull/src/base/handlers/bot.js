import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';
import { Logger } from 'im.v2.lib.logger';

import type { Store } from 'ui.vue3.vuex';
import type { BotAddParams, BotUpdateParams } from '../../types/bot';

export class BotPullHandler
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	handleBotAdd(params: BotAddParams)
	{
		Logger.warn('BotPullHandler: handleBotAdd', params);
		const { user } = params;

		void (new UserManager()).addUsersToModel(user);
	}

	handleBotUpdate(params: BotUpdateParams)
	{
		const { user } = params;

		this.#store.dispatch('users/update', {
			id: user.id,
			fields: user,
		});
	}
}
