import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Utils } from 'im.v2.lib.utils';
import { CallManager } from 'im.v2.lib.call';
import { showKickUserConfirm, showLeaveFromChatConfirm } from 'im.v2.lib.confirm';
import { PermissionManager } from 'im.v2.lib.permission';
import { ChatActionType, EventType } from 'im.v2.const';
import { ChatService } from 'im.v2.provider.service';
import { Messenger } from 'im.public';

import { SidebarMenu } from '../sidebar-base-menu';

import type { ImModelUser } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

type MembersMenuContext = {
	dialogId: string,
	contextDialogId: string,
	contextChatId: string,
};

export class MembersMenu extends SidebarMenu
{
	context: MembersMenuContext;
	chatService: ChatService;
	callManager: CallManager;
	permissionManager: PermissionManager;

	constructor()
	{
		super();

		this.chatService = new ChatService();
		this.callManager = CallManager.getInstance();
		this.permissionManager = PermissionManager.getInstance();
	}

	getMenuItems(): MenuItem[]
	{
		return [
			this.getInsertNameItem(),
			this.getSendMessageItem(),
			this.getCallItem(),
			this.getOpenProfileItem(),
			this.getOpenUserCalendarItem(),
			this.getKickItem(),
			this.getLeaveItem(),
		];
	}

	getInsertNameItem(): MenuItem
	{
		const user: ImModelUser = this.store.getters['users/get'](this.context.dialogId, true);

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_INSERT_NAME'),
			onclick: () => {
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: user.name,
					mentionReplacement: Utils.text.getMentionBbCode(this.context.dialogId, user.name),
				});
				this.menuInstance.close();
			},
		};
	}

	getSendMessageItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_WRITE'),
			onclick: () => {
				Messenger.openChat(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getCallItem(): ?MenuItem
	{
		const chatCanBeCalled = this.callManager.chatCanBeCalled(this.context.dialogId);
		const chatIsAllowedToCall = this.permissionManager.canPerformAction(ChatActionType.call, this.context.dialogId);
		if (!chatCanBeCalled || !chatIsAllowedToCall)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_CALL_2'),
			onclick: () => {
				this.callManager.startCall(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getOpenProfileItem(): ?MenuItem
	{
		const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
		if (!isUser)
		{
			return null;
		}

		const profileUri = Utils.user.getProfileLink(this.context.dialogId);

		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE'),
			href: profileUri,
			onclick: () => {
				this.menuInstance.close();
			},
		};
	}

	getOpenUserCalendarItem(): ?MenuItem
	{
		const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
		if (!isUser)
		{
			return null;
		}
		const user = this.store.getters['users/get'](this.context.dialogId, true);
		if (user.bot)
		{
			return null;
		}

		const profileUri = Utils.user.getCalendarLink(this.context.dialogId);

		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN_CALENDAR'),
			onclick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	getKickItem(): ?MenuItem
	{
		const userIdToKick = Number.parseInt(this.context.dialogId, 10);
		const isSelfKick = userIdToKick === this.getCurrentUserId();
		const canKick = this.permissionManager.canPerformKick(this.context.contextDialogId, this.context.dialogId);
		if (isSelfKick || !canKick)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_SIDEBAR_MENU_KICK_FROM_CHAT'),
			onclick: async () => {
				this.menuInstance.close();
				const userChoice = await showKickUserConfirm();
				if (userChoice === true)
				{
					this.chatService.kickUserFromChat(this.context.contextDialogId, this.context.dialogId);
				}
			},
		};
	}

	getLeaveItem(): ?MenuItem
	{
		const userIdToKick = Number.parseInt(this.context.dialogId, 10);
		const isSelfKick = userIdToKick === this.getCurrentUserId();

		const canLeaveChat = this.permissionManager.canPerformAction(ChatActionType.leave, this.context.contextDialogId);
		if (!isSelfKick || !canLeaveChat)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_LEAVE'),
			onclick: async () => {
				this.menuInstance.close();
				const userChoice = await showLeaveFromChatConfirm();
				if (userChoice === true)
				{
					this.chatService.leaveChat(this.context.contextDialogId);
				}
			},
		};
	}
}
