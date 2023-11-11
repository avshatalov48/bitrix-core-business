import { Core } from 'im.v2.application.core';
import { DialogBlockType as BlockType, MessageType, Settings, DialogAlignment } from 'im.v2.const';
import { DateFormatter, DateTemplate } from 'im.v2.lib.date-formatter';

import type { ImModelMessage, ImModelDialog } from 'im.v2.model';

export type FormattedCollectionItem = {
	type: BlockType.dateGroup,
	date: {
		id: string,
		title: string
	},
	items: Array<AuthorGroupItem | NewMessagesItem>
};

type AuthorGroupItem = {
	type: BlockType.authorGroup,
	userId: number,
	messageType: MessageType.opponent | MessageType.self | MessageType.system,
	avatar: {
		isNeeded: boolean,
		avatarId: string
	},
	items: ImModelMessage[]
};

type NewMessagesItem = {
	type: BlockType.newMessages
};

export class CollectionManager
{
	store: Object;
	dialogId: number;
	firstIteration: boolean = true;
	initialLastReadMessage: boolean;
	initialMarkedId: boolean;
	cachedDateGroups: {
		id: number,
		title: string
	} = {};

	constructor(dialogId)
	{
		this.store = Core.getStore();
		this.dialogId = dialogId;
	}

	formatMessageCollection(messageCollection: ImModelMessage[]): FormattedCollectionItem[]
	{
		const dateGroups = {};
		const collection = [];
		let lastDateItems = null;
		let lastAuthorId = null;
		let lastAuthorItems = null;

		const dialog: ImModelDialog = this.store.getters['dialogues/get'](this.dialogId);
		const { markedId, inited } = dialog;
		let markInserted = false;
		const lastReadId = this.store.getters['dialogues/getLastReadId'](this.dialogId);

		if (this.firstIteration)
		{
			this.initialLastReadMessage = lastReadId;
			this.initialMarkedId = markedId;
		}

		if (markedId !== this.initialMarkedId && markedId !== 0)
		{
			this.initialMarkedId = markedId;
			this.initialLastReadMessage = null;
		}

		messageCollection.forEach((message: ImModelMessage, index) => {
			const dateGroup = this.getDateGroup(message.date);
			// new date = new date group + new author group
			if (!dateGroups[dateGroup.title])
			{
				dateGroups[dateGroup.title] = dateGroup.id;
				lastDateItems = [];
				collection.push({
					type: BlockType.dateGroup,
					date: dateGroup,
					items: lastDateItems
				});
				lastAuthorId = null;
			}

			// marked messages
			if (message.id === this.initialMarkedId)
			{
				lastDateItems.push({
					type: BlockType.markedMessages
				});
				lastAuthorId = null;
				markInserted = true;
			}

			// new author = new author group
			if (message.authorId !== lastAuthorId)
			{
				lastAuthorId = message.authorId;
				lastAuthorItems = [];

				lastDateItems.push({
					type: BlockType.authorGroup,
					userId: message.authorId,
					avatar: this.getAvatarConfig(message),
					messageType: this.getMessageType(message),
					items: lastAuthorItems
				});
			}

			// add current message to last active author group
			lastAuthorItems.push(message);

			// new messages block
			const isLastMessage = index === messageCollection.length - 1;
			if (!markInserted && !isLastMessage && message.id === this.initialLastReadMessage)
			{
				lastDateItems.push({
					type: BlockType.newMessages
				});
				lastAuthorId = null;
			}
		});

		if (inited)
		{
			this.firstIteration = false;
		}


		return collection;
	}

	getDateGroup(date: Date): {id: number, title: string}
	{
		const INDEX_BETWEEN_DATE_AND_TIME = 10;
		// 2022-10-25T14:58:44.000Z => 2022-10-25
		const shortDate = date.toJSON().slice(0, INDEX_BETWEEN_DATE_AND_TIME);
		if (this.cachedDateGroups[shortDate])
		{
			return this.cachedDateGroups[shortDate];
		}

		this.cachedDateGroups[shortDate] = {
			id: shortDate,
			title: DateFormatter.formatByTemplate(date, DateTemplate.dateGroup)
		};

		return this.cachedDateGroups[shortDate];
	}

	getAvatarConfig(message: ImModelMessage): {isNeeded: boolean, avatarId: string}
	{
		const messageType = this.getMessageType(message);
		const isSystem = messageType === MessageType.system;
		const isSelf = messageType === MessageType.self;

		const alignment = this.store.getters['application/settings/get'](Settings.appearance.alignment);
		let isNeeded = true;
		if (alignment === DialogAlignment.left)
		{
			isNeeded = !isSystem;
		}
		else if (alignment === DialogAlignment.center)
		{
			isNeeded = !isSelf && !isSystem;
		}

		return {
			isNeeded,
			avatarId: message.authorId.toString(),
		};
	}

	getMessageType(message: ImModelMessage): string
	{
		if (!message.authorId)
		{
			return MessageType.system;
		}

		if (message.authorId === Core.getUserId())
		{
			return MessageType.self;
		}

		return MessageType.opponent;
	}
}
