import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType, UserRole, MessageStatus, FakeMessagePrefix } from 'im.v2.const';

import type { ImModelMessage } from 'im.v2.model';
import type {
	RecentRestResult,
	RawUser,
	RawChat,
	RawRecentItem,
	RawMessage,
	RawCopilot,
} from '../../types/rest';

type RecentFile = {
	id: number,
	name: string,
	type: string,
};

type ExtractionResult = {
	users: RawUser[],
	chats: RawChat[],
	messages: RawMessage[],
	files: RecentFile[],
	recentItems: RawRecentItem[],
	copilot?: RawCopilot,
};

export class RecentDataExtractor
{
	#restResult: RecentRestResult;
	#withBirthdays: boolean;

	#users: { [id: string]: RawUser } = {};
	#chats: { [id: string]: RawChat } = {};
	#messages: { [id: string]: RawMessage } = {};
	#files: { [id: string]: RecentFile } = {};
	#recentItems: { [id: string]: RawRecentItem } = {};

	constructor(params: { rawData: RecentRestResult, withBirthdays?: boolean })
	{
		const { rawData, withBirthdays = true } = params;
		this.#withBirthdays = withBirthdays;
		this.#restResult = rawData;
	}

	getItems(): ExtractionResult
	{
		const { items = [], copilot } = this.#restResult;
		items.forEach((item: RawRecentItem) => {
			this.#extractUser(item);
			this.#extractChat(item);
			this.#extractMessage(item);
			this.#extractRecentItem(item);
		});

		this.#extractBirthdayItems();

		return {
			users: Object.values(this.#users),
			chats: Object.values(this.#chats),
			messages: Object.values(this.#messages),
			files: Object.values(this.#files),
			recentItems: Object.values(this.#recentItems),
			copilot,
		};
	}

	#extractUser(item: RawRecentItem)
	{
		if (item.user?.id && !this.#users[item.user.id])
		{
			this.#users[item.user.id] = item.user;
		}
	}

	#extractChat(item: RawRecentItem)
	{
		if (item.chat)
		{
			this.#chats[item.id] = this.#prepareGroupChat(item);

			if (item.user.id && !this.#chats[item.user.id])
			{
				this.#chats[item.user.id] = this.#prepareChatForAdditionalUser(item.user);
			}
		}
		else if (item.user.id)
		{
			const existingRecentItem = Core.getStore().getters['recent/get'](item.user.id);
			// we should not update real chat with "default" chat data
			if (!existingRecentItem || !item.options.default_user_record)
			{
				this.#chats[item.user.id] = this.#prepareChatForUser(item);
			}
		}
	}

	#extractMessage(item: RawRecentItem): void
	{
		const message = item.message;
		if (!message)
		{
			return;
		}

		if (message.id === 0)
		{
			message.id = `${FakeMessagePrefix}-${item.id}`;
		}

		let viewedByOthers = false;
		if (message.status === MessageStatus.delivered)
		{
			viewedByOthers = true;
		}

		const existingMessage: ImModelMessage = Core.getStore().getters['messages/getById'](message.id);
		// recent has shortened attach format, we should not rewrite attach if model has it
		if (Type.isArrayFilled(existingMessage?.attach))
		{
			delete message.attach;
		}

		if (Type.isPlainObject(message.file))
		{
			const file: RecentFile = message.file;
			if (existingMessage)
			{
				// recent doesn't know about several files in one message,
				// we should not rewrite message files, so we merge it.
				message.files = this.#mergeFileIds(existingMessage, file.id);
			}
			else
			{
				message.files = [file.id];
			}

			const existingFile = Core.getStore().getters['files/get'](file.id);
			// recent has shortened file format, we should not rewrite file if model has it
			if (!existingFile)
			{
				this.#files[file.id] = file;
			}
		}

		this.#messages[message.id] = { ...message, viewedByOthers };
	}

	#extractRecentItem(item: RawRecentItem): void
	{
		const messageId = item.message?.id ?? 0;
		this.#recentItems[item.id] = { ...item, messageId };
	}

	#extractBirthdayItems()
	{
		if (!this.#withBirthdays)
		{
			return;
		}

		const { birthdayList = [] } = this.#restResult;
		birthdayList.forEach((item) => {
			if (!this.#users[item.id])
			{
				this.#users[item.id] = item;
			}

			if (!this.#chats[item.id])
			{
				this.#chats[item.id] = this.#prepareChatForAdditionalUser(item);
			}

			if (!this.#recentItems[item.id])
			{
				const messageId = `${FakeMessagePrefix}-${item.id}`;
				this.#recentItems[item.id] = { ...this.#getBirthdayPlaceholder(item), messageId };
				this.#messages[messageId] = { id: messageId };
			}
		});
	}

	#prepareGroupChat(item: RawRecentItem): RawChat
	{
		return {
			...item.chat,
			counter: item.counter,
			dialogId: item.id,
			copilot: item.copilot,
		};
	}

	#prepareChatForUser(item: RawRecentItem): RawChat
	{
		return {
			chatId: item.chat_id,
			avatar: item.user.avatar,
			color: item.user.color,
			dialogId: item.id,
			name: item.user.name,
			type: ChatType.user,
			counter: item.counter,
			role: UserRole.member,
		};
	}

	#prepareChatForAdditionalUser(user: RawUser): RawChat
	{
		return {
			dialogId: user.id,
			avatar: user.avatar,
			color: user.color,
			name: user.name,
			type: ChatType.user,
			role: UserRole.member,
		};
	}

	#getBirthdayPlaceholder(item: RawRecentItem): { id: string, isBirthdayPlaceholder: true }
	{
		return {
			id: item.id,
			isBirthdayPlaceholder: true,
		};
	}

	#mergeFileIds(existingMessage: ImModelMessage, fileId: number): number[]
	{
		const existingMessageFilesIds = existingMessage.files.map((id) => {
			return Number.parseInt(id, 10);
		});
		const setOfFileIds = new Set([...existingMessageFilesIds, fileId]);

		return [...setOfFileIds];
	}
}
