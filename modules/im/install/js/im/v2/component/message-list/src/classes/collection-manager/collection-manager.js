import { Core } from 'im.v2.application.core';
import { DialogBlockType as BlockType, MessageType } from 'im.v2.const';

import { Collection } from './classes/collection';
import { DateManager } from './classes/date-manager';
import { BlockManager } from './classes/block-manager';

import type { ImModelMessage, ImModelChat } from 'im.v2.model';

export type DateGroupItem = {
	dateTitle: string,
	items: DateGroupItemType,
};

export type DateGroupItemType = Array<AuthorGroupItem | NewMessagesItem | MarkedMessageItem>

export type AuthorGroupItem = {
	type: BlockType.authorGroup,
	userId: number,
	avatar: {
		isNeeded: boolean,
		avatarId: string,
	},
	messageType: MessageType.opponent | MessageType.self | MessageType.system,
	messages: ImModelMessage[]
};

export type NewMessagesItem = {
	type: BlockType.newMessages
};

export type MarkedMessageItem = {
	type: BlockType.markedMessages
};

export class CollectionManager
{
	dialogId: number;
	dateManager: DateManager;

	firstIteration: boolean = true;
	lastReadMessageId: number | null;
	markedMessageId: number;

	constructor(dialogId)
	{
		this.dialogId = dialogId;
		this.dateManager = new DateManager();
	}

	#setInitialValues(): void
	{
		if (!this.firstIteration)
		{
			return;
		}

		const { markedId } = this.#getDialog();
		this.lastReadMessageId = this.#getLastReadMessageId();
		this.markedMessageId = markedId;
	}

	#handleMarkedMessageId(): void
	{
		const { markedId } = this.#getDialog();
		if (markedId === this.markedMessageId || markedId === 0)
		{
			return;
		}

		// if mark was set after chat load - remember marked message and remove "new messages" block
		this.markedMessageId = markedId;
		this.lastReadMessageId = null;
	}

	formatMessageCollection(messageCollection: ImModelMessage[]): DateGroupItem[]
	{
		/*
		Collection
		├── Date Group
		│   ├── Marked Message Indicator
		│   ├── Author Group
		│   │   └── Messages
		│   └── New Messages Indicator
		*/

		const collection = new Collection();

		this.#setInitialValues();
		this.#handleMarkedMessageId();

		messageCollection.forEach((message: ImModelMessage, index) => {
			const dateTitle = this.dateManager.getDateTitle(message.date);
			if (!collection.hasDateTitle(dateTitle))
			{
				collection.addDateGroup(dateTitle);
			}

			if (message.id === this.markedMessageId)
			{
				collection.addMarkedIndicator();
			}

			if (message.authorId !== collection.getLastAuthorId())
			{
				collection.addAuthorGroup(message);
			}

			collection.addMessage(message);

			const isLastMessage = index === messageCollection.length - 1;
			if (!isLastMessage && message.id === this.lastReadMessageId)
			{
				collection.addNewMessagesIndicator();
			}
		});

		const { inited } = this.#getDialog();
		if (inited)
		{
			this.firstIteration = false;
		}

		return collection.get();
	}

	formatAuthorGroup(message: ImModelMessage): AuthorGroupItem
	{
		const blockManager = new BlockManager();

		return {
			...blockManager.getAuthorBlock(message),
			messages: [message],
		};
	}

	#getLastReadMessageId(): number
	{
		const { lastMessageId }: ImModelChat = this.#getDialog();
		const lastReadId = Core.getStore().getters['chats/getLastReadId'](this.dialogId);
		if (lastReadId === lastMessageId)
		{
			return 0;
		}

		return lastReadId;
	}

	#getDialog(): ImModelChat
	{
		return Core.getStore().getters['chats/get'](this.dialogId);
	}
}
