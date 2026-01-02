import { Core } from 'im.v2.application.core';
import { CopilotManager } from 'im.v2.lib.copilot';
import { Logger } from 'im.v2.lib.logger';

import type { Store } from 'ui.vue3.vuex';
import type { ImModelChat } from 'im.v2.model';
import type { EngineUpdateParams, FileTranscriptionParams, CopilotRoleParams } from '../../types/ai';

export class AiPullHandler
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	handleChangeEngine(params: EngineUpdateParams)
	{
		Logger.warn('AiPullHandler: handleChangeEngine', params);
		const { chatId, engineCode } = params;
		const dialog: ImModelChat = this.#store.getters['chats/getByChatId'](chatId);

		if (!dialog)
		{
			return;
		}

		this.#store.dispatch('copilot/chats/updateModel', { dialogId: dialog.dialogId, aiModel: engineCode });
	}

	handleFileTranscription(params: FileTranscriptionParams)
	{
		Logger.warn('AiPullHandler: handleFileTranscription', params);

		this.#store.dispatch('files/setTranscription', params);
	}

	handleChatCopilotRoleUpdate(params: CopilotRoleParams)
	{
		if (!params.copilotRole)
		{
			return;
		}

		const copilotManager = new CopilotManager();
		void copilotManager.handleRoleUpdate(params.copilotRole);
	}
}
