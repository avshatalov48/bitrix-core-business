import {RestClient} from 'rest.client';
import {Store} from 'ui.vue3.vuex';

import {Core} from 'im.v2.application.core';
import {Logger} from 'im.v2.lib.logger';
import {RestMethod} from 'im.v2.const';

type ChatConfig = {
	title: string,
	members: number[],
	ownerId: number,
	manageType: string,
	isAvailableInSearch: boolean,
	description: string
};

const PRIVATE_CHAT = 'CHAT';
const OPEN_CHAT = 'OPEN';

export class CreateService
{
	#restClient: RestClient;
	#store: Store;

	constructor()
	{
		this.#restClient = Core.getRestClient();
		this.#store = Core.getStore();
	}

	createChat(chatConfig: ChatConfig): Promise
	{
		Logger.warn('ChatService: createChat', chatConfig);
		return this.#restClient.callMethod(RestMethod.imV2ChatAdd, {
			fields: {
				title: chatConfig.title,
				description: chatConfig.description,
				users: chatConfig.members,
				ownerId: chatConfig.ownerId,
				searchable: chatConfig.isAvailableInSearch ? 'Y' : 'N',
			}
		}).then(result => {
			const {chatId: newChatId} = result.data();
			Logger.warn('ChatService: createChat result', newChatId);
			const newDialogId = `chat${newChatId}`;
			this.#addChatToModel(newDialogId, chatConfig);

			return newDialogId;
		}).catch(error => {
			console.error('ChatService: createChat error:', error);
			throw new Error(error);
		});
	}

	#addChatToModel(newDialogId: string, chatConfig: ChatConfig)
	{
		const chatType = chatConfig.isAvailableInSearch ? OPEN_CHAT : PRIVATE_CHAT;
		this.#store.dispatch('dialogues/set', {
			dialogId: newDialogId,
			type: chatType.toLowerCase(),
			name: chatConfig.title,
			userCounter: chatConfig.members.length
		});
	}
}