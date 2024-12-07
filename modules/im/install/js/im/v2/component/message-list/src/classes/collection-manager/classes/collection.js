import { BlockManager } from './block-manager';

import type { ImModelMessage } from 'im.v2.model';
import type { DateGroupItem, DateGroupItemType } from '../collection-manager';

export class Collection
{
	#blockManager: BlockManager;

	#collection: DateGroupItem[] = [];
	#currentDateTitles: Set<string> = new Set();
	#markedIndicatorInserted: boolean = false;

	#lastDateItems: DateGroupItemType = [];
	#lastAuthorId: number | null = null;
	#lastAuthorItems: ImModelMessage[] = [];

	constructor()
	{
		this.#blockManager = new BlockManager();
	}

	get(): DateGroupItem[]
	{
		return this.#collection;
	}

	hasDateTitle(dateTitle: string): boolean
	{
		return this.#currentDateTitles.has(dateTitle);
	}

	addDateGroup(dateTitle: string): void
	{
		this.#currentDateTitles.add(dateTitle);
		this.#lastDateItems = [];
		this.#collection.push({
			dateTitle,
			items: this.#lastDateItems,
		});
		this.#clearLastAuthor();
	}

	addAuthorGroup(message: ImModelMessage): void
	{
		this.#lastAuthorId = message.authorId;
		this.#lastAuthorItems = [];

		this.#lastDateItems.push({
			...this.#blockManager.getAuthorBlock(message),
			messages: this.#lastAuthorItems,
		});
	}

	addMessage(message: ImModelMessage)
	{
		this.#lastAuthorItems.push(message);
	}

	addMarkedIndicator(): void
	{
		this.#lastDateItems.push(this.#blockManager.getMarkedBlock());
		this.#markedIndicatorInserted = true;
		this.#clearLastAuthor();
	}

	addNewMessagesIndicator(): void
	{
		if (this.#markedIndicatorInserted)
		{
			return;
		}

		this.#lastDateItems.push(this.#blockManager.getNewMessagesBlock());
		this.#clearLastAuthor();
	}

	getLastAuthorId(): number | null
	{
		return this.#lastAuthorId;
	}

	#clearLastAuthor(): void
	{
		this.#lastAuthorId = null;
	}
}
