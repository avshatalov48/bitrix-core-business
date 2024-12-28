import { ChatType } from 'im.v2.const';
import { UserManager } from 'im.v2.lib.user';

import type { RawSession } from 'imopenlines.v2.provider.service';
import type {
	ChatLoadRestResult,
	RawChat,
	RawFile,
	RawUser,
	RawShortUser,
	RawMessage,
	RawCommentInfo,
	RawCollabInfo,
	RawPin,
	RawReaction,
	RawCopilot,
} from '../../types/rest';

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
		return this.#restResult.chat.type === ChatType.lines;
	}

	isCopilotChat(): boolean
	{
		return this.#restResult.chat.type === ChatType.copilot;
	}

	getChats(): RawChat[]
	{
		const mainChat = {
			...this.#restResult.chat,
			hasPrevPage: this.#restResult.hasPrevPage,
			hasNextPage: this.#restResult.hasNextPage,
			tariffRestrictions: this.#restResult.tariffRestrictions,
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

	getCommentInfo(): RawCommentInfo[]
	{
		return this.#restResult.commentInfo ?? [];
	}

	getCollabInfo(): ?RawCollabInfo
	{
		return this.#restResult.collabInfo ?? null;
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

	getCopilot(): RawCopilot
	{
		return this.#restResult.copilot;
	}

	getSession(): RawSession
	{
		return this.#restResult.session;
	}
}
