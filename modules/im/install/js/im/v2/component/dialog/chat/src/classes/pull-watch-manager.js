import { Core } from 'im.v2.application.core';
import { UserRole, RestMethod, ChatType } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';
import { ChannelManager } from 'im.v2.lib.channel';

import type { ImModelChat } from 'im.v2.model';
import type { PULL as Pull } from 'pull.client';

const MESSAGES_TAG_PREFIX = 'IM_PUBLIC_';
const COMMENTS_TAG_PREFIX = 'IM_PUBLIC_COMMENT_';

export class PullWatchManager
{
	#dialog: ImModelChat;
	#pullClient: Pull;

	constructor(dialogId: string)
	{
		this.#dialog = Core.getStore().getters['chats/get'](dialogId, true);
		this.#pullClient = Core.getPullClient();
	}

	subscribe()
	{
		if (this.#isChannel())
		{
			this.#subscribeChannel();

			return;
		}

		if (this.#isCommentsChat() || !this.#isGuest())
		{
			return;
		}

		this.#subscribeOpenChat();
	}

	unsubscribe()
	{
		this.#pullClient.clearWatch(`${MESSAGES_TAG_PREFIX}${this.#dialog.chatId}`);
		this.#pullClient.clearWatch(`${COMMENTS_TAG_PREFIX}${this.#dialog.chatId}`);
	}

	#subscribeChannel()
	{
		this.#requestWatchStart();
		this.#pullClient.extendWatch(`${MESSAGES_TAG_PREFIX}${this.#dialog.chatId}`);
		this.#pullClient.extendWatch(`${COMMENTS_TAG_PREFIX}${this.#dialog.chatId}`);
	}

	#subscribeOpenChat()
	{
		this.#requestWatchStart();
		this.#pullClient.extendWatch(`${MESSAGES_TAG_PREFIX}${this.#dialog.chatId}`);
	}

	#requestWatchStart()
	{
		runAction(RestMethod.imV2ChatExtendPullWatch, {
			data: {
				dialogId: this.#dialog.dialogId,
			},
		});
	}

	#isGuest(): boolean
	{
		return this.#dialog?.role === UserRole.guest && this.#dialog?.dialogId !== 'settings';
	}

	#isChannel(): boolean
	{
		return ChannelManager.isChannel(this.#dialog?.dialogId);
	}

	#isCommentsChat(): boolean
	{
		return this.#dialog?.type === ChatType.comment;
	}
}
