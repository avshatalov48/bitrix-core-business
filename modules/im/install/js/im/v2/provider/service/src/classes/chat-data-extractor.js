import { DialogType } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

import type {
	ChatLoadRestResult,
	RawChat,
	RawFile,
	RawUser,
	RawShortUser,
	RawMessage,
	RawPin,
	RawReaction,
} from '../types/rest';

export class ChatDataExtractor
{
	#restResult: ChatLoadRestResult;

	constructor(restResult: ChatLoadRestResult)
	{
		this.#restResult = restResult;
	}

	getChatId(): number
	{
		return this.#restResult.chat.id;
	}

	getDialogId(): string
	{
		return this.#restResult.chat.dialogId;
	}

	isOpenlinesChat(): boolean
	{
		return this.#restResult.chat.type === DialogType.lines;
	}

	getChats(): RawChat[]
	{
		const mainChat = {
			...this.#restResult.chat,
			hasPrevPage: this.#restResult.hasPrevPage,
			hasNextPage: this.#restResult.hasNextPage,
		};
		const chats = {
			[this.#restResult.chat.dialogId]: mainChat,
		};
		this.#restResult.users.forEach((user) => {
			if (chats[user.id])
			{
				chats[user.id] = { ...chats[user.id], ...UserManager.getDialogForUser(user) };
			}
			else
			{
				chats[user.id] = UserManager.getDialogForUser(user);
			}
		});

		return Object.values(chats);
	}

	getFiles(): RawFile[]
	{
		return this.#restResult.files ?? [];
	}

	getUsers(): RawUser[]
	{
		return this.#restResult.users ?? [];
	}

	getAdditionalUsers(): RawShortUser[]
	{
		return this.#restResult.usersShort ?? [];
	}

	getMessages(): RawMessage[]
	{
		return this.#restResult.messages ?? [];
	}

	getMessagesToStore(): RawMessage[]
	{
		return this.#restResult.additionalMessages ?? [];
	}

	getPinnedMessageIds(): number[]
	{
		const pinnedMessageIds = [];
		const pins: RawPin[] = this.#restResult.pins ?? [];
		pins.forEach((pin) => {
			pinnedMessageIds.push(pin.messageId);
		});

		return pinnedMessageIds;
	}

	getReactions(): RawReaction[]
	{
		return this.#restResult.reactions ?? [];
	}
}
