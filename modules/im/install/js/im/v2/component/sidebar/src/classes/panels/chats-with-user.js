import { RestMethod } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';
import { Core } from 'im.v2.application.core';

import type { RestClient } from 'rest.client';
import type { Store } from 'ui.vue3.vuex';

const REQUEST_ITEMS_LIMIT = 50;

type RequestParams = {
	filter: {
		userId: number,
	},
	limit: number,
	offset?: number,
};

type RawChatItem = {
	avatar: string,
	color: string,
	description: string,
	dialogId: string,
	diskFolderId: number,
	entityData1: string,
	entityData2: string,
	entityData3: string,
	entityId: string,
	entityType: string,
	extranet: boolean,
	id: number,
	name: string,
	owner: number,
	messageType: string,
	role: string,
	type: string,
	manageUsers: string,
	manageUi: string,
	manageSettings: string,
	manageMessages: string,
	dateMessage: string,
}

type ChatItem = {
	dialogId: string,
	dateMessage: string,
}

export class ChatsWithUser
{
	hasMoreItemsToLoad: boolean = true;
	#chatsCount: number = 0;

	store: Store;
	dialogId: string;
	userManager: UserManager;
	restClient: RestClient;

	constructor({ dialogId }: {dialogId: string})
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.dialogId = dialogId;
		this.userManager = new UserManager();
	}

	loadFirstPage(): Promise<ChatItem[]>
	{
		return this.#requestPage();
	}

	loadNextPage(): Promise<ChatItem[]>
	{
		return this.#requestPage();
	}

	#getRequestParams(): RequestParams
	{
		const userId = Number.parseInt(this.dialogId, 10);

		const requestParams = {
			filter: { userId },
			limit: REQUEST_ITEMS_LIMIT,
		};

		if (this.#chatsCount > 0)
		{
			requestParams.offset = this.#chatsCount;
		}

		return requestParams;
	}

	async #requestPage(): Promise<ChatItem[]>
	{
		const requestParams = this.#getRequestParams();
		const response = await this.restClient.callMethod(RestMethod.imV2ChatListShared, requestParams);

		return this.#handleResponse(response.data());
	}

	async #handleResponse(response): Promise<ChatItem[]>
	{
		const { chats }: { chats: RawChatItem[] } = response;
		this.#chatsCount += chats.length;
		if (chats.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		await this.#updateModels(chats);

		return chats.map((chat: RawChatItem) => {
			return {
				dialogId: chat.dialogId,
				dateMessage: chat.dateMessage,
			};
		});
	}

	#updateModels(chats: RawChatItem[]): Promise
	{
		return this.#setDialoguesPromise(chats);
	}

	#setDialoguesPromise(chats: RawChatItem[]): Promise
	{
		return this.store.dispatch('chats/set', chats);
	}
}
