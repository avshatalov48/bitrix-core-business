import { Store } from 'ui.vue3.vuex';

import { runAction } from 'im.v2.lib.rest';
import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';

import type { JsonObject } from 'main.core';

export class CopilotService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	updateRole({ dialogId, role }: {dialogId: string, role: JsonObject}): Promise
	{
		Logger.warn('CopilotService: update role', dialogId);
		void this.#store.dispatch('copilot/chats/add', { dialogId, role: role.code });
		void this.#store.dispatch('copilot/roles/add', [role]);

		return this.#sendRequest({ dialogId, role: role.code });
	}

	#sendRequest({ dialogId, role }: {dialogId: string, role: string}): Promise
	{
		const requestParams = { data: { dialogId, role } };

		return runAction(RestMethod.imV2ChatCopilotUpdateRole, requestParams);
	}
}
