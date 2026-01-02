import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { Utils } from 'im.v2.lib.utils';
import { ChatService } from 'im.v2.provider.service.chat';
import { showKickUserConfirm } from 'im.v2.lib.confirm';
import { PermissionManager } from 'im.v2.lib.permission';
import { ActionByRole, ChatType, EventType, UserType } from 'im.v2.const';

import { BaseMenu } from '../base/base';

import type { MenuItemOptions } from 'ui.system.menu';
import type { ImModelUser, ImModelChat } from 'im.v2.model';

type UserMenuContext = {
	user: ImModelUser,
	dialog: ImModelChat
};

export class UserMenu extends BaseMenu
{
	context: UserMenuContext;
	permissionManager: PermissionManager;

	constructor()
	{
		super();

		this.id = 'bx-im-user-context-menu';
		this.permissionManager = PermissionManager.getInstance();
	}

	getKickItem(): ?MenuItemOptions
	{
		const canKick = this.permissionManager.canPerformActionByRole(ActionByRole.kick, this.context.dialog.dialogId);
		if (!canKick)
		{
			return null;
		}

		return {
			title: this.#getKickItemText(),
			onClick: async () => {
				this.menuInstance.close();
				const userChoice = await showKickUserConfirm(this.context.dialog.dialogId);
				if (userChoice !== true)
				{
					return;
				}

				void this.#kickUser();
			},
		};
	}

	getMentionItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_USER_MENTION'),
			onClick: () => {
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: this.context.user.name,
					mentionReplacement: Utils.text.getMentionBbCode(this.context.user.id, this.context.user.name),
					dialogId: this.context.dialog.dialogId,
					isMentionSymbol: false,
				});
				this.menuInstance.close();
			},
		};
	}

	getSendItem(): ?MenuItemOptions
	{
		if (this.context.dialog.type === ChatType.user)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_MENU_USER_WRITE'),
			onClick: () => {
				void Messenger.openChat(this.context.user.id);
				this.menuInstance.close();
			},
		};
	}

	getProfileItem(): ?MenuItemOptions
	{
		if (this.isBot())
		{
			return null;
		}

		const profileUri = Utils.user.getProfileLink(this.context.user.id);

		return {
			title: Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
			onClick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	isCollabChat(): boolean
	{
		const { type }: ImModelChat = this.store.getters['chats/get'](this.context.dialog.dialogId, true);

		return type === ChatType.collab;
	}

	isBot(): boolean
	{
		return this.context.user.type === UserType.bot;
	}

	#getKickItemText(): string
	{
		if (this.isCollabChat())
		{
			return Loc.getMessage('IM_LIB_MENU_USER_KICK_FROM_COLLAB');
		}

		return Loc.getMessage('IM_LIB_MENU_USER_KICK_FROM_CHAT');
	}

	#kickUser(): Promise
	{
		if (this.isCollabChat())
		{
			return (new ChatService()).kickUserFromCollab(this.context.dialog.dialogId, this.context.user.id);
		}

		return (new ChatService()).kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
	}
}
