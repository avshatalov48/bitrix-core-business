import { Type } from 'main.core';
import { RestClient } from 'rest.client';
import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { Logger } from 'im.v2.lib.logger';
import { RestMethod, UserRole } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import type { ChatConfig, RestChatConfig } from '../../types/chat';

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

	async createChat(chatConfig: ChatConfig): Promise<string>
	{
		Logger.warn('ChatService: createChat', chatConfig);

		const preparedFields = await this.#prepareFields(chatConfig);

		const createResult: RestResult = await this.#restClient.callMethod(RestMethod.imV2ChatAdd, {
			fields: preparedFields,
		}).catch((error) => {
			// eslint-disable-next-line no-console
			console.error('ChatService: createChat error:', error);
			throw new Error(error);
		});

		const { chatId: newChatId } = createResult.data();

		Logger.warn('ChatService: createChat result', newChatId);
		const newDialogId = `chat${newChatId}`;
		this.#addChatToModel(newDialogId, preparedFields);

		return newDialogId;
	}

	async #prepareFields(chatConfig: ChatConfig): RestChatConfig
	{
		const preparedConfig = { ...chatConfig };
		if (preparedConfig.avatar)
		{
			preparedConfig.avatar = await Utils.file.getBase64(chatConfig.avatar);
		}

		preparedConfig.managers = preparedConfig.managers ?? [];
		const allMembers = [...preparedConfig.members, ...preparedConfig.managers, preparedConfig.ownerId];
		preparedConfig.members = [...new Set(allMembers)];

		return {
			entityType: preparedConfig.type?.toUpperCase() ?? null,
			title: preparedConfig.title,
			avatar: preparedConfig.avatar,
			description: preparedConfig.description,
			users: preparedConfig.members,
			managers: preparedConfig.managers,
			ownerId: preparedConfig.ownerId,
			searchable: preparedConfig.isAvailableInSearch ? 'Y' : 'N',
			manageUsers: preparedConfig.manageUsers,
			manageUi: preparedConfig.manageUi,
			manageSettings: preparedConfig.manageSettings,
			canPost: preparedConfig.canPost,
			conferencePassword: preparedConfig.conferencePassword ?? null,
		};
	}

	#addChatToModel(newDialogId: string, chatConfig: RestChatConfig)
	{
		let chatType = chatConfig.searchable === 'Y' ? OPEN_CHAT : PRIVATE_CHAT;
		if (Type.isStringFilled(chatConfig.entityType))
		{
			chatType = chatConfig.entityType.toLowerCase();
		}

		this.#store.dispatch('dialogues/set', {
			dialogId: newDialogId,
			type: chatType.toLowerCase(),
			name: chatConfig.title,
			userCounter: chatConfig.users.length,
			role: UserRole.owner,
			canPost: chatConfig.canPost,
		});
	}
}
