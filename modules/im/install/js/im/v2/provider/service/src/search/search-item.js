import { SearchEntityIdTypes } from 'im.v2.const';

import type { ImRecentProviderItem } from './types/recent-provider-item';

export class SearchItem
{
	#itemOptions: ImRecentProviderItem;

	constructor(itemOptions: ImRecentProviderItem)
	{
		this.#itemOptions = itemOptions;
	}

	getDialogId(): string
	{
		return this.#itemOptions.id;
	}

	getEntityId(): string
	{
		return this.#itemOptions.entityId;
	}

	getEntityType(): string
	{
		return this.#itemOptions.entityType;
	}

	getTitle(): string
	{
		return this.#itemOptions.title;
	}

	getAvatar(): string
	{
		return this.#itemOptions.avatar;
	}

	isUser(): boolean
	{
		return this.getEntityType() === SearchEntityIdTypes.imUser;
	}

	isChat(): boolean
	{
		return this.getEntityType() === SearchEntityIdTypes.chat;
	}

	getCustomData(): {[key: string]: any}
	{
		return this.#itemOptions.customData;
	}

	getDateUpdate(): string
	{
		return this.#itemOptions.customData.dateUpdate;
	}
}
