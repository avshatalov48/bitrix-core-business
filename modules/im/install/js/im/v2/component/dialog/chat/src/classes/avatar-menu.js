import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { BaseMenu } from 'im.v2.lib.menu';
import { PermissionManager } from 'im.v2.lib.permission';
import { EventType, ChatActionType } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';
import { showKickUserConfirm } from 'im.v2.lib.confirm';
import { ChatService } from 'im.v2.provider.service';

import type { ImModelUser, ImModelDialog } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

type AvatarMenuContext = {
	user: ImModelUser,
	dialog: ImModelDialog
};

export class AvatarMenu extends BaseMenu
{
	context: AvatarMenuContext;
	permissionManager: PermissionManager;

	constructor()
	{
		super();

		this.id = 'bx-im-avatar-context-menu';
		this.permissionManager = PermissionManager.getInstance();
	}

	getMenuOptions(): Object
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 21,
		};
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getMentionItem(),
			this.getSendItem(),
			this.getProfileItem(),
			this.getKickItem(),
		];
	}

	getMentionItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_MENTION_2'),
			onclick: () => {
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: this.context.user.name,
					mentionReplacement: Utils.text.getMentionBbCode(this.context.user.id, this.context.user.name),
				});
				this.menuInstance.close();
			},
		};
	}

	getSendItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_SEND_MESSAGE'),
			onclick: () => {
				Messenger.openChat(this.context.user.id);
				this.menuInstance.close();
			},
		};
	}

	getProfileItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_OPEN_PROFILE'),
			href: Utils.user.getProfileLink(this.context.user.id),
			onclick: () => {
				this.menuInstance.close();
			},
		};
	}

	getKickItem(): ?MenuItem
	{
		const canKick = this.permissionManager.canPerformKick(this.context.dialog.dialogId, this.context.user.id);
		if (!canKick)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_DIALOG_AVATAR_MENU_KICK'),
			onclick: async () => {
				this.menuInstance.close();
				const userChoice = await showKickUserConfirm();
				if (userChoice === true)
				{
					const chatService = new ChatService();
					chatService.kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
				}
			},
		};
	}
}
