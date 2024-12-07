import { Extension, Loc } from 'main.core';

import { BaseMenu } from 'im.v2.lib.menu';
import { CallManager } from 'im.v2.lib.call';

import { CallTypes } from '../call-button/call-types';

import type { ImModelChat } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

import type { PopupOptions } from 'main.popup';

export class CallMenu extends BaseMenu
{
	context: ImModelChat;

	static events = {
		onMenuItemClick: 'onMenuItemClick',
	};

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
		// TODO temporary disable active option
		// const isAvailable = CallManager.getInstance().chatCanBeCalled(this.context.dialogId);
		const isAvailable = true;

		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_VIDEOCALL'),
			onclick: () => {
				if (!isAvailable)
				{
					return;
				}
				CallTypes.video.start(this.context.dialogId);
				this.emit(CallMenu.events.onMenuItemClick, CallTypes.video);
				this.menuInstance.close();
			},
			disabled: !isAvailable,
		};
	}

	#getAudioCallItem(): MenuItem
	{
		// TODO temporary disable active option
		// const isAvailable = CallManager.getInstance().chatCanBeCalled(this.context.dialogId);
		const isAvailable = true;

		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_AUDIO'),
			onclick: () => {
				if (!isAvailable)
				{
					return;
				}
				CallTypes.audio.start(this.context.dialogId);

				this.emit(CallMenu.events.onMenuItemClick, CallTypes.audio);
				this.menuInstance.close();
			},
			disabled: !isAvailable,
		};
	}

	#getBetaCallItem(): ?MenuItem
	{
		if (!this.#isCallBetaAvailable())
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_CONTENT_CHAT_HEADER_CALL_MENU_BETA_2'),
			onclick: () => {
				if (!this.#isCallBetaAvailable())
				{
					return;
				}
				CallTypes.beta.start(this.context.dialogId);

				this.emit(CallMenu.events.onMenuItemClick, CallTypes.beta);
				this.menuInstance.close();
			},
		};
	}

	#isCallBetaAvailable(): boolean
	{
		// TODO remove this after release call beta
		// const settings = Extension.getSettings('im.v2.component.content.chat');
		// return settings.get('isCallBetaAvailable');

		return false;
	}
}
