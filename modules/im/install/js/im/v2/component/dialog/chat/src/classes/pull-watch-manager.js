import { Core } from 'im.v2.application.core';
import { UserRole, RestMethod } from 'im.v2.const';
import { runAction } from 'im.v2.lib.rest';

import type { ImModelDialog } from 'im.v2.model';
import type { PULL as Pull } from 'pull.client';

const TAG_PREFIX = 'IM_PUBLIC_';

export class PullWatchManager
{
	#dialog: ImModelDialog;
	#pullClient: Pull;

	constructor(dialogId: string)
	{
		this.#dialog = Core.getStore().getters['dialogues/get'](dialogId);
		this.#pullClient = Core.getPullClient();
	}

	onChatLoad()
	{
		if (!this.#isGuest())
		{
			return;
		}

		this.#pullClient.extendWatch(`${TAG_PREFIX}${this.#dialog.chatId}`);
	}

	onChatExit()
	{
		if (!this.#isGuest())
		{
			return;
		}

		this.#pullClient.clearWatch(`${TAG_PREFIX}${this.#dialog.chatId}`);
	}

	onLoadedChatEnter()
	{
		if (!this.#isGuest())
		{
			return;
		}

		this.#requestWatchStart();
		this.#pullClient.extendWatch(`${TAG_PREFIX}${this.#dialog.chatId}`, true);
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
		return this.#dialog.role === UserRole.guest && this.#dialog.dialogId !== 'settings';
	}
}
