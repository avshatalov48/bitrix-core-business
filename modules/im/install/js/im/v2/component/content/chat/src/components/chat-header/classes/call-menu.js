import { Extension, Loc } from 'main.core';

import { Messenger } from 'im.public';
import { BaseMenu } from 'im.v2.lib.menu';
import { CallManager } from 'im.v2.lib.call';

import type { ImModelDialog } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

import type { PopupOptions } from 'main.popup';

export class CallMenu extends BaseMenu
{
	context: ImModelDialog;

	constructor()
	{
		super();

		this.id = 'bx-im-chat-header-call-menu';
	}

	getMenuOptions(): PopupOptions
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 4,
			offsetTop: 5,
		};
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.#getVideoCallItem(),
			this.#getAudioCallItem(),
			this.#getBetaCallItem(),
		];
	}

	#getVideoCallItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_VIDEOCALL_HD'),
			onclick: () => {
				Messenger.startVideoCall(this.context.dialogId);

				this.menuInstance.close();
			},
		};
	}

	#getAudioCallItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_AUDIO'),
			onclick: () => {
				Messenger.startVideoCall(this.context.dialogId, false);

				this.menuInstance.close();
			},
		};
	}

	#getBetaCallItem(): ?MenuItem
	{
		if (!this.#isCallBetaAvailable())
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_BETA'),
			onclick: () => {
				CallManager.getInstance().createBetaCallRoom(this.context.chatId);

				this.menuInstance.close();
			},
		};
	}

	#isCallBetaAvailable(): boolean
	{
		const settings = Extension.getSettings('im.v2.component.content.chat');

		return settings.get('isCallBetaAvailable');
	}
}
