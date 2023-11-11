import { RestMethod } from 'im.v2.const';

import { Base } from './base';

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

export class MessageSearch extends Base
{
	hasMoreItemsToLoad: boolean = true;
	#lastMessageId: number = 0;
	#query: string = '';

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

	getInitialRequest(): {}
	{
		return {};
	}

	getResponseHandler(): Function
	{
		return () => {};
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
		this.#lastMessageId = this.#getLastMessageId(response);
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

	#getLastMessageId(response: RestResponse): number
	{
		if (response.messages.length === 0)
		{
			return 0;
		}

		const [oldestMessage] = response.messages;

		return oldestMessage.id;
	}
}
