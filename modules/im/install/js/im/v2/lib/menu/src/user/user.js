import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Messenger } from 'im.public';
import { Utils } from 'im.v2.lib.utils';
import { Core } from 'im.v2.application.core';
import { ChatService } from 'im.v2.provider.service';
import { showKickUserConfirm } from 'im.v2.lib.confirm';
import { PermissionManager } from 'im.v2.lib.permission';
import { ActionByRole, ChatType, EventType, UserType } from 'im.v2.const';

import { BaseMenu } from '../base/base';

import type { MenuItem } from '../type/menu';

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

	getKickItem(): ?MenuItem
	{
		const canKick = this.permissionManager.canPerformActionByRole(ActionByRole.kick, this.context.dialog.dialogId);
		if (!canKick)
		{
			return null;
		}

		return {
			text: this.#getKickItemText(),
			onclick: async () => {
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

	getMentionItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_USER_MENTION'),
			onclick: () => {
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

	getSendItem(): ?MenuItem
	{
		if (this.context.dialog.type === ChatType.user)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_USER_WRITE'),
			onclick: () => {
				void Messenger.openChat(this.context.user.id);
				this.menuInstance.close();
			},
		};
	}

	getProfileItem(): ?MenuItem
	{
		if (this.isBot())
		{
			return null;
		}

		const profileUri = Utils.user.getProfileLink(this.context.user.id);
		const isCurrentUser = this.context.user.id === Core.getUserId();
		const phraseCode = isCurrentUser ? 'IM_LIB_MENU_OPEN_OWN_PROFILE' : 'IM_LIB_MENU_OPEN_PROFILE_V2';

		return {
			text: Loc.getMessage(phraseCode),
			href: profileUri,
			onclick: () => {
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
