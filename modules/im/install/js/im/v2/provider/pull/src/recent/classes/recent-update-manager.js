import { Core } from 'im.v2.application.core';
import { UserManager } from 'im.v2.lib.user';

import type { RecentUpdateParams } from '../../types/recent';

export class RecentUpdateManager
{
	#params: RecentUpdateParams;

	constructor(params: RecentUpdateParams)
	{
		this.#params = params;
	}

	setLastMessageInfo(): void
	{
		this.#setMessageChat();
		this.#setUsers();
		this.#setFiles();
		this.#setMessage();
	}

	getDialogId(): string
	{
		return this.#params.chat.dialogId;
	}

	getLastMessageId(): number
	{
		const [lastMessage] = this.#params.messages;

		return lastMessage.id;
	}

	#setUsers(): void
	{
		const userManager = new UserManager();
		userManager.setUsersToModel(this.#params.users);
	}

	#setFiles(): void
	{
		Core.getStore().dispatch('files/set', this.#params.files);
	}

	#setMessageChat(): void
	{
		const chat = { ...this.#params.chat, counter: this.#params.counter, dialogId: this.getDialogId() };
		Core.getStore().dispatch('chats/set', chat);
	}

	#setMessage(): void
	{
		const [lastChannelPost] = this.#params.messages;
		Core.getStore().dispatch('messages/store', lastChannelPost);
	}
}
