import { SearchEntityIdTypes } from 'im.v2.const';

import type { JsonObject } from 'main.core';
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

	getCustomData(): JsonObject
	{
		return this.#itemOptions.customData;
	}

	getDate(): ?string
	{
		return this.#itemOptions.customData.dateMessage;
	}

	isFoundByUser(): boolean
	{
		return Boolean(this.#itemOptions.customData?.byUser);
	}
}
