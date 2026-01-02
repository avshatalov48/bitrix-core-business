import type { ImModelChat, ImModelUser } from 'im.v2.model';
import type { RawUser, RawChat } from 'im.v2.provider.service.types';

export type ImRecentProviderItem = {
	id: string,
	entityId: string,
	entityType: string,
	title: string,
	avatar: string,
	sort: number,
	customData: {
		id: number | string,
		chat: Partial<RawChat>,
		secondSort: number,
		dateMessage?: string,
		user?: RawUser,
		byUser?: boolean,
	},
};

export type LocalSearchItem = {
	dialogId: string,
	dialog: ImModelChat,
	user?: ImModelUser,
	dateMessage: string,
}

export type SearchResultItem = {
	dialogId: string,
	dateMessage: string,
};

type EntitySelectorProviderEntity = {
	id: string,
	options: Object,
	dynamicLoad: boolean,
	dynamicSearch: boolean,
};

export type EntitySelectorRequestConfig = {
	dialog: {
		entities: EntitySelectorProviderEntity[],
		preselectedItems: [],
		clearUnavailableItems: boolean,
		context: string,
		id: string,
	}
};

export type SearchConfig = {
	chats: boolean,
	users: boolean,
};
