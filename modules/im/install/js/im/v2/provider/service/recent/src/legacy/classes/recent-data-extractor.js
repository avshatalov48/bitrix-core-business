import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType, UserRole, MessageStatus, FakeMessagePrefix } from 'im.v2.const';

import type { ImModelMessage } from 'im.v2.model';
import type {
	RawUser,
	RawChat,
	RawLegacyRecentItem,
	RawMessage,
	RawCopilot,
	RawMessagesAutoDeleteConfig,
} from 'im.v2.provider.service.types';
import type { RecentRestResult } from '../types/recent';

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
	recentItems: RawLegacyRecentItem[],
	copilot?: RawCopilot,
	messagesAutoDeleteConfigs: RawMessagesAutoDeleteConfig[],
};

export class RecentDataExtractor
{
	#restResult: RecentRestResult;
	#withBirthdays: boolean;

	#users: { [id: string]: RawUser } = {};
	#chats: { [id: string]: RawChat } = {};
	#messages: { [id: string]: RawMessage } = {};
	#files: { [id: string]: RecentFile } = {};
	#recentItems: { [id: string]: RawLegacyRecentItem } = {};

	constructor(params: { rawData: RecentRestResult, withBirthdays?: boolean })
	{
		const { rawData, withBirthdays = true } = params;
		this.#withBirthdays = withBirthdays;
		this.#restResult = rawData;
	}

	getItems(): ExtractionResult
	{
		const { items = [], copilot, messagesAutoDeleteConfigs } = this.#restResult;
		items.forEach((item: RawLegacyRecentItem) => {
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
			messagesAutoDeleteConfigs,
		};
	}

	#extractUser(item: RawLegacyRecentItem)
	{
		if (item.user?.id && !this.#users[item.user.id])
		{
			this.#users[item.user.id] = item.user;
		}
	}

	#extractChat(item: RawLegacyRecentItem)
	{
		if (item.type === ChatType.chat)
		{
			this.#chats[item.id] = this.#prepareGroupChat(item);

			if (item.user.id && !this.#chats[item.user.id])
			{
				this.#chats[item.user.id] = this.#prepareChatForAdditionalUser(item.user);
			}
		}
		else if (item.type === ChatType.user)
		{
			const existingRecentItem = Core.getStore().getters['recent/get'](item.user.id);
			// we should not update real chat with "default" chat data
			if (!existingRecentItem || !item.options.default_user_record)
			{
				this.#chats[item.user.id] = this.#prepareChatForUser(item);
			}
		}
	}

	#extractMessage(item: RawLegacyRecentItem): void
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

	#extractRecentItem(item: RawLegacyRecentItem): void
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
			if (Core.getUserId() === item.id)
			{
				return;
			}

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

	#prepareGroupChat(item: RawLegacyRecentItem): RawChat
	{
		return {
			...item.chat,
			counter: item.counter,
			dialogId: item.id,
		};
	}

	#prepareChatForUser(item: RawLegacyRecentItem): RawChat
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
			backgroundId: item.chat.background_id,
			textFieldEnabled: item.chat.text_field_enabled,
			muteList: item.chat.mute_list,
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

	#getBirthdayPlaceholder(item: RawLegacyRecentItem): { id: string, isBirthdayPlaceholder: true }
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
