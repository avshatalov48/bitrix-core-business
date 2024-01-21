import { Core } from 'im.v2.application.core';
import { ChatType, UserRole } from 'im.v2.const';

import type { JsonObject } from 'main.core';
import type { RecentRestResult, RawUser, RawChat, RawRecentItem } from '../types/rest';

export class RecentDataExtractor
{
	#restResult: RecentRestResult;
	#withBirthdays: boolean;

	#users: RawUser[] = {};
	#chats: RawChat[] = {};
	#recentItems: RawRecentItem[] = {};

	constructor(params: { rawData: RecentRestResult, withBirthdays?: boolean  })
	{
		const { rawData, withBirthdays = true } = params;
		this.#withBirthdays = withBirthdays;
		this.#restResult = rawData;
	}

	getItems(): { users: RawUser[], chats: RawChat[], recentItems: RawRecentItem[] }
	{
		const { items = [] } = this.#restResult;
		items.forEach((item) => {
			this.#extractUser(item);
			this.#extractChat(item);
			this.#recentItems[item.id] = item;
		});

		this.#extractBirthdayItems();

		return {
			users: Object.values(this.#users),
			chats: Object.values(this.#chats),
			recentItems: Object.values(this.#recentItems),
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
				this.#recentItems[item.id] = this.#getBirthdayPlaceholder(item);
			}
		});
	}

	#prepareGroupChat(item: RawRecentItem): RawChat
	{
		return {
			...item.chat,
			counter: item.counter,
			dialogId: item.id
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

	#getBirthdayPlaceholder(item: RawRecentItem): { id: string, options: { birthdayPlaceholder: true } }
	{
		return {
			id: item.id,
			options: {
				birthdayPlaceholder: true,
			},
		};
	}
}
