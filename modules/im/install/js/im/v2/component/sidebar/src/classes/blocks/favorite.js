import {RestMethod} from 'im.v2.const';
import {Base} from './base';

const REQUEST_ITEMS_LIMIT = 50;
export class Favorite extends Base
{
	hasMoreItemsToLoad: boolean = true;
	lastId: number = 0;

	getInitialRequest()
	{
		return {
			[RestMethod.imChatFavoriteCounterGet]: [RestMethod.imChatFavoriteCounterGet, {chat_id: this.chatId}],
			[RestMethod.imChatFavoriteGet]: [RestMethod.imChatFavoriteGet, {chat_id: this.chatId, limit: REQUEST_ITEMS_LIMIT}]
		};
	}

	getResponseHandler()
	{
		return (response) => {
			if (!response)
			{
				return Promise.reject(new Error('SidebarInfo service error: no response'));
			}

			const requestError = this.extractLoadCountersError(response);
			if (requestError)
			{
				return Promise.reject(new Error(requestError));
			}

			const favoriteCounterGetResponse = response[RestMethod.imChatFavoriteCounterGet].data();
			const setCounterResult = this.store.dispatch('sidebar/favorites/setCounter', {
				chatId: this.chatId,
				counter: favoriteCounterGetResponse.counter
			});

			const setFavoriteResult = this.handleResponse(response[RestMethod.imChatFavoriteGet]);

			return Promise.all([setCounterResult, setFavoriteResult]);
		};
	}

	extractLoadCountersError(response): ?string
	{
		const favoriteCounterGetResult = response[RestMethod.imChatFavoriteCounterGet];
		if (favoriteCounterGetResult?.error())
		{
			return `SidebarInfo service error: ${RestMethod.imChatFavoriteCounterGet}: ${favoriteCounterGetResult?.error()}`;
		}

		const favoriteGetResult = response[RestMethod.imChatFavoriteGet];
		if (favoriteGetResult?.error())
		{
			return `SidebarInfo service error: ${RestMethod.imChatFavoriteGet}: ${favoriteGetResult?.error()}`;
		}

		return null;
	}

	loadFirstPage(): Promise
	{
		const favoritesCount = this.getFavoritesCountFromModel();
		if (favoritesCount > REQUEST_ITEMS_LIMIT)
		{
			return Promise.resolve();
		}

		const queryParams = this.getQueryParams();

		return this.requestPage(queryParams);
	}

	loadNextPage(): Promise
	{
		const queryParams = this.getQueryParams();

		return this.requestPage(queryParams);
	}

	getQueryParams(): Object
	{
		const queryParams = {
			'CHAT_ID': this.chatId,
			'LIMIT': REQUEST_ITEMS_LIMIT,
		};

		if (this.lastId > 0)
		{
			queryParams.LAST_ID = this.lastId;
		}

		return queryParams;
	}

	requestPage(queryParams): Promise
	{
		return this.restClient.callMethod(RestMethod.imChatFavoriteGet, queryParams).then(response => {
			return this.handleResponse(response);
		}).catch(error => {
			console.error('SidebarInfo: Im.imChatFavoriteGet: page request error', error);
		});
	}

	handleResponse(response): Promise
	{
		const favoriteMessagesResult = response.data();

		if (favoriteMessagesResult.list.length < REQUEST_ITEMS_LIMIT)
		{
			this.hasMoreItemsToLoad = false;
		}

		const lastId = this.getLastElementId(favoriteMessagesResult.list);
		if (lastId)
		{
			this.lastId = lastId;
		}

		return this.updateModels(favoriteMessagesResult);
	}

	updateModels(resultData: {list: [], users: [], files: []}): Promise
	{
		const {list = [], users = [], files = []} = resultData;
		const addUsersPromise = this.userManager.setUsersToModel(users);

		const rawMessages = list.map(favorite => favorite.message);

		const setFilesPromise = this.store.dispatch('files/set', files);
		const storeMessagesPromise = this.store.dispatch('messages/store', rawMessages);
		const setFavoritesPromise = this.store.dispatch('sidebar/favorites/set', {
			chatId: this.chatId,
			favorites: list
		});

		return Promise.all([
			setFilesPromise, storeMessagesPromise, setFavoritesPromise, addUsersPromise
		]);
	}

	getFavoritesCountFromModel(): number
	{
		return this.store.getters['sidebar/favorites/getSize'](this.chatId);
	}
}
