import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import { getChatId } from './helpers/get-chat-id';
import { getLastElementId } from './helpers/get-last-element-id';

import type { Store } from 'ui.vue3.vuex';
import type { RestClient } from 'rest.client';

type RestResponse = {
	additionalMessages: [],
	files: [],
	messages: [],
	reactions: [],
	reminders: [],
	users: [],
	usersShort: []
};

const REQUEST_ITEMS_LIMIT = 50;

export class MessageSearch
{
	store: Store;
	dialogId: string;
	chatId: number;
	userManager: UserManager;
	restClient: RestClient;

	// eslint-disable-next-line no-unused-private-class-members
	hasMoreItemsToLoad: boolean = true;
	#lastMessageId: number = 0;
	#query: string = '';

	constructor({ dialogId }: {dialogId: string})
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.dialogId = dialogId;
		this.chatId = getChatId(dialogId);
		this.userManager = new UserManager();
	}

	searchOnServer(query: string): Promise<string[]>
	{
		if (this.#query !== query)
		{
			this.#query = query;
			this.hasMoreItemsToLoad = true;
			this.#lastMessageId = 0;
		}

		return this.#request();
	}

	loadNextPage(): Promise<string[]>
	{
		return this.#request();
	}

	#request(): Promise<string[]>
	{
		const config = {
			SEARCH_MESSAGE: this.#query,
			CHAT_ID: this.chatId,
		};

		if (this.#lastMessageId > 0)
		{
			config.LAST_ID = this.#lastMessageId;
		}

		return new Promise((resolve, reject) => {
			this.restClient.callMethod(RestMethod.imDialogMessagesSearch, config).then((response) => {
				const responseData: RestResponse = response.data();
				resolve(this.#processSearchResponse(responseData));
			}).catch((error) => reject(error));
		});
	}

	loadFirstPage(): Promise
	{
		return Promise.resolve();
	}

	resetSearchState()
	{
		this.#lastMessageId = 0;
		this.#query = '';
		this.hasMoreItemsToLoad = true;
	}

	#processSearchResponse(response: RestResponse): Promise<string[]>
	{
		this.#lastMessageId = getLastElementId(response.messages);
		if (response.messages.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		return this.#updateModels(response).then(() => {
			return response.messages.map((message) => message.id);
		});
	}

	#updateModels(rawData: RestResponse): Promise
	{
		const {
			files,
			users,
			usersShort,
			reactions,
			additionalMessages,
			messages,
		} = rawData;

		const usersPromise = Promise.all([
			this.userManager.setUsersToModel(users),
			this.userManager.addUsersToModel(usersShort),
		]);
		const filesPromise = this.store.dispatch('files/set', files);
		const reactionsPromise = this.store.dispatch('messages/reactions/set', reactions);
		const additionalMessagesPromise = this.store.dispatch('messages/store', additionalMessages);
		const messagesPromise = this.store.dispatch('messages/store', messages);

		return Promise.all([
			filesPromise, usersPromise, reactionsPromise, additionalMessagesPromise, messagesPromise,
		]);
	}
}
