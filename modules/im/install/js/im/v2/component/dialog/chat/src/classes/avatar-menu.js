import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';

import {Messenger} from 'im.public';
import {Core} from 'im.v2.application.core';
import {BaseMenu} from 'im.v2.lib.menu';
import {DialogType, EventType} from 'im.v2.const';
import {Utils} from 'im.v2.lib.utils';
import {ChatService} from 'im.v2.provider.service';

import type {ImModelUser, ImModelDialog} from 'im.v2.model';
import type {MenuItem} from 'im.v2.lib.menu';

type AvatarMenuContext = {
	user: ImModelUser,
	dialog: ImModelDialog
};

export class AvatarMenu extends BaseMenu
{
	context: AvatarMenuContext;

	constructor()
	{
		super();

		this.id = 'bx-im-avatar-context-menu';
	}

	getMenuOptions(): Object
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 21
		};
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getMentionItem(),
			this.getSendItem(),
			this.getProfileItem(),
			this.getKickItem()
		];
	}

	getMentionItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_MENTION'),
			onclick: () => {
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: this.context.user.name,
					mentionReplacement: Utils.user.getMentionBbCode(this.context.user.id, this.context.user.name)
				});
				this.menuInstance.close();
			}
		};
	}

	getSendItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_SEND_MESSAGE'),
			onclick: () => {
				Messenger.openChat(this.context.user.id);
				this.menuInstance.close();
			}
		};
	}

	getProfileItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_OPEN_PROFILE'),
			href: Utils.user.getProfileLink(this.context.user.id),
			onclick: () => {
				this.menuInstance.close();
			}
		};
	}

	getKickItem(): ?MenuItem
	{
		const isOwner = Core.getUserId() === this.context.dialog.owner;
		const isUser = this.context.dialog.type === DialogType.user;
		if (!isOwner || isUser)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_KICK'),
			onclick: () => {
				const chatService = new ChatService();
				chatService.kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
				this.menuInstance.close();
			}
		};
	}
}