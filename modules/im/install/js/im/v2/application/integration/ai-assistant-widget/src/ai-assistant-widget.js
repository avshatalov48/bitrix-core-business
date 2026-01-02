import { Core } from 'im.v2.application.core';

import { AiAssistantWidgetChatOpener } from './components/ai-assistant-widget-chat-opener';

import type { RunActionError } from 'im.v2.lib.rest';

const APP_NAME = 'AiAssistantWidgetApplication';

type MountPayload = {
	rootContainer: string | HTMLElement,
	aiAssistantBotId: number,
	onError: (RunActionError[]) => void,
};

export class AiAssistantWidgetApplication
{
	#initPromise: Promise<AiAssistantWidgetApplication>;

	constructor()
	{
		this.#initPromise = this.#init();
	}

	ready(): Promise
	{
		return this.#initPromise;
	}

	async mount(payload: MountPayload): Promise
	{
		await this.ready();

		const { rootContainer, aiAssistantBotId, onError } = payload;
		if (!rootContainer)
		{
			return Promise.reject(new Error('Provide node or selector for root container'));
		}

		const dialogId = aiAssistantBotId.toString();

		return Core.createVue(this, {
			name: APP_NAME,
			el: rootContainer,
			onError,
			components: { AiAssistantWidgetChatOpener },
			template: `<AiAssistantWidgetChatOpener dialogId="${dialogId}" />`,
		});
	}

	async #init(): Promise<AiAssistantWidgetApplication>
	{
		await Core.ready();

		return this;
	}
}
