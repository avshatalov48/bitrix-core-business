import { Core } from 'im.v2.application.core';
import { runAction } from 'im.v2.lib.rest';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod } from 'im.v2.const';

export class CopilotAiModelService
{
	updateAIModel({ dialogId, aiModelCode }: { dialogId: string, aiModelCode: string }): Promise
	{
		Logger.warn('CopilotService: update ai model', dialogId, aiModelCode);
		void Core.getStore().dispatch('copilot/chats/updateModel', { dialogId, aiModel: aiModelCode });

		return this.#sendRequest({ dialogId, engineCode: aiModelCode });
	}

	#sendRequest({ dialogId, engineCode }: {dialogId: string, engineCode: string}): Promise
	{
		const requestParams = { data: { dialogId, engineCode } };

		return runAction(RestMethod.imV2ChatCopilotUpdateAiModel, requestParams);
	}
}
