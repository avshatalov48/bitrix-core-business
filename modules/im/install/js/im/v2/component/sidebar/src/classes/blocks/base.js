import {Type} from 'main.core';

import {Core} from 'im.v2.application.core';
import {UserManager} from 'im.v2.lib.user';

const NOT_IMPLEMENTED_ERROR = 'Not implemented';

export class Base
{
	store: Object = null;
	dialogId: string = '';
	chatId: number = 0;
	userManager: UserManager = null;

	constructor(chatId: number, dialogId: string)
	{
		this.store = Core.getStore();
		this.restClient = Core.getRestClient();
		this.chatId = chatId;
		this.dialogId = dialogId;
		this.userManager = new UserManager();
	}

	loadFirstPage(): Promise
	{
		throw new Error(NOT_IMPLEMENTED_ERROR);
	}

	loadNextPage(): Promise
	{
		throw new Error(NOT_IMPLEMENTED_ERROR);
	}

	getInitialRequest()
	{
		throw new Error(NOT_IMPLEMENTED_ERROR);
	}

	getResponseHandler()
	{
		throw new Error(NOT_IMPLEMENTED_ERROR);
	}

	getCurrentUserId(): number
	{
		return Core.getUserId();
	}

	getLastElementId(collection): ?number
	{
		const lastId = collection[collection.length - 1]?.id;

		if (Type.isNumber(lastId))
		{
			return lastId;
		}

		return null;
	}
}
