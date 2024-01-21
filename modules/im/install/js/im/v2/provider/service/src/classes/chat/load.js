import { Type } from 'main.core';
import { Store } from 'ui.vue3.vuex';

import { Messenger } from 'im.public';
import { Core } from 'im.v2.application.core';
import { RestMethod, Layout } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { MessageService } from 'im.v2.provider.service';
import { UserManager } from 'im.v2.lib.user';
import { LayoutManager } from 'im.v2.lib.layout';
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
				// eslint-disable-next-line no-console
				console.error('ChatService: Load: error preparing external id', error);
			});
	}

	async #requestChat(actionName: string, params: Object<string, any>): Promise
	{
		const { dialogId } = params;
		this.#markDialogAsLoading(dialogId);

		const actionResult = await runAction(actionName, { data: params })
			.catch((error) => {
				// eslint-disable-next-line no-console
				console.error('ChatService: Load: error loading chat', error);
				this.#markDialogAsNotLoaded(dialogId);
				throw error;
			});
		const updateModelResult = await this.#updateModels(actionResult);
		if (Type.isStringFilled(updateModelResult?.linesDialogId))
		{
			LayoutManager.getInstance().setLastOpenedElement(Layout.chat.name, '');

			return Messenger.openLines(updateModelResult.linesDialogId);
		}

		if (this.#isDialogLoadedMarkNeeded(actionName))
		{
			return this.#markDialogAsLoaded(dialogId);
		}

		return true;
	}

	#markDialogAsLoading(dialogId: string)
	{
		this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				loading: true,
			},
		});
	}

	#markDialogAsLoaded(dialogId: string): Promise
	{
		return this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				inited: true,
				loading: false,
			},
		});
	}

	#markDialogAsNotLoaded(dialogId: string): Promise
	{
		return this.#store.dispatch('chats/update', {
			dialogId,
			fields: {
				loading: false,
			},
		});
	}

	#isDialogLoadedMarkNeeded(actionName: string): boolean
	{
		return actionName !== RestMethod.imV2ChatShallowLoad;
	}

	#updateModels(restResult: ChatLoadRestResult): Promise
	{
		const extractor = new ChatDataExtractor(restResult);
		if (extractor.isOpenlinesChat())
		{
			return Promise.resolve({ linesDialogId: extractor.getDialogId() });
		}

		const chatsPromise = this.#store.dispatch('chats/set', extractor.getChats());
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
			chatsPromise,
			filesPromise,
			usersPromise,
			messagesPromise,
		]);
	}
}
