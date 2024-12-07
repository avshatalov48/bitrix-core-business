import { Core } from 'im.v2.application.core';
import { DialogAlignment, DialogBlockType as BlockType, MessageType, Settings } from 'im.v2.const';

import type { ImModelMessage } from 'im.v2.model';
import type { AuthorGroupItem, NewMessagesItem, MarkedMessageItem } from '../collection-manager';

export class BlockManager
{
	getAuthorBlock(message: ImModelMessage): AuthorGroupItem
	{
		return {
			type: BlockType.authorGroup,
			userId: message.authorId,
			avatar: this.#getAvatarConfig(message),
			messageType: this.#getMessageType(message),
		};
	}

	getMarkedBlock(): MarkedMessageItem
	{
		return { type: BlockType.markedMessages };
	}

	getNewMessagesBlock(): NewMessagesItem
	{
		return { type: BlockType.newMessages };
	}

	#getAvatarConfig(message: ImModelMessage): {isNeeded: boolean, avatarId: string}
	{
		return {
			isNeeded: this.#checkIfAvatarIsNeeded(message),
			avatarId: message.authorId.toString(),
		};
	}

	#getMessageType(message: ImModelMessage): string
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

	#checkIfAvatarIsNeeded(message: ImModelMessage): boolean
	{
		const messageType = this.#getMessageType(message);

		const isSystem = messageType === MessageType.system;
		if (isSystem)
		{
			return false;
		}

		const isSelf = messageType === MessageType.self;
		const alignment = Core.getStore().getters['application/settings/get'](Settings.appearance.alignment);
		if (alignment === DialogAlignment.center)
		{
			return !isSelf;
		}

		return true;
	}
}
