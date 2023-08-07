import { Store } from 'ui.vue3.vuex';

import { Core } from 'im.v2.application.core';
import { RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { MessageService } from 'im.v2.provider.service';
import { UserManager } from 'im.v2.lib.user';
import { Utils } from 'im.v2.lib.utils';

import { ChatDataExtractor } from '../chat-data-extractor';

import type { ChatLoadRestResult } from '../../types/rest';

export class LoadService
{
	#store: Store;

	constructor()
	{
		this.#store = Core.getStore();
	}

	loadChat(dialogId: string): Promise
	{
		const params = { dialogId };

		return this.#requestChat(RestMethod.imV2ChatShallowLoad, params);
	}

	loadChatWithMessages(dialogId: string): Promise
	{
		const params = {
			dialogId,
			messageLimit: MessageService.getMessageRequestLimit(),
		};

		return this.#requestChat(RestMethod.imV2ChatLoad, params);
	}

	loadChatWithContext(dialogId: string, messageId: number): Promise
	{
		const params = {
			dialogId,
			messageId,
			messageLimit: MessageService.getMessageRequestLimit(),
		};

		return this.#requestChat(RestMethod.imV2ChatLoadInContext, params);
	}

	prepareDialogId(dialogId: string): Promise<string>
	{
		if (!Utils.dialog.isExternalId(dialogId))
		{
			return Promise.resolve(dialogId);
		}

		return runAction(RestMethod.imV2ChatGetDialogId, {
			data: { externalId: dialogId },
		})
			.then((result: {dialogId: string}) => {
				return result.dialogId;
			})
			.catch((error) => {
				console.error('ChatService: Load: error preparing external id', error);
			});
	}

	#requestChat(actionName: string, params: Object<string, any>): Promise
	{
		const { dialogId } = params;
		this.#markDialogAsLoading(dialogId);

		return runAction(actionName, { data: params })
			.then((result: ChatLoadRestResult) => {
				return this.#updateModels(result);
			})
			.then(() => {
				this.#markDialogAsLoaded(dialogId);
			})
			.catch((error) => {
				console.error('ChatService: Load: error loading chat', error);
				throw error;
			});
	}

	#markDialogAsLoading(dialogId: string)
	{
		this.#store.dispatch('dialogues/update', {
			dialogId,
			fields: {
				loading: true,
			},
		});
	}

	#markDialogAsLoaded(dialogId: string)
	{
		return this.#store.dispatch('dialogues/update', {
			dialogId,
			fields: {
				inited: true,
				loading: false,
			},
		});
	}

	#updateModels(restResult: ChatLoadRestResult): Promise
	{
		const extractor = new ChatDataExtractor(restResult);
		if (extractor.isOpenlinesChat())
		{
			return Promise.reject(new Error('OL chats are not supported'));
		}

		const dialoguesPromise = this.#store.dispatch('dialogues/set', extractor.getChats());
		const filesPromise = this.#store.dispatch('files/set', extractor.getFiles());

		const userManager = new UserManager();
		const usersPromise = Promise.all([
			this.#store.dispatch('users/set', extractor.getUsers()),
			userManager.addUsersToModel(extractor.getAdditionalUsers()),
		]);

		const messagesPromise = Promise.all([
			this.#store.dispatch('messages/setChatCollection', {
				messages: extractor.getMessages(),
				clearCollection: true,
			}),
			this.#store.dispatch('messages/store', extractor.getMessagesToStore()),
			this.#store.dispatch('messages/pin/setPinned', {
				chatId: extractor.getChatId(),
				pinnedMessages: extractor.getPinnedMessageIds(),
			}),
			this.#store.dispatch('messages/reactions/set', extractor.getReactions()),
		]);

		return Promise.all([
			dialoguesPromise,
			filesPromise,
			usersPromise,
			messagesPromise,
		]);
	}
}
