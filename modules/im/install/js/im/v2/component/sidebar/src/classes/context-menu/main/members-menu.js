import { Loc } from 'main.core';
import { EventEmitter } from 'main.core.events';

import { Core } from 'im.v2.application.core';
import { Messenger } from 'im.public';
import { Utils } from 'im.v2.lib.utils';
import { CallManager } from 'im.v2.lib.call';
import { ChatService } from 'im.v2.provider.service';
import { ChatActionType, EventType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { showKickUserConfirm, showLeaveFromChatConfirm } from 'im.v2.lib.confirm';

import { SidebarMenu } from '../sidebar-base-menu';

import type { ImModelUser, ImModelChat } from 'im.v2.model';
import type { MenuItem } from 'im.v2.lib.menu';

type MembersMenuContext = {
	dialogId: string,
	contextDialogId: string,
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
		const targetUserId = Number.parseInt(this.context.dialogId, 10);
		if (targetUserId === Core.getUserId())
		{
			return [
				this.getOpenProfileItem(),
				this.getOpenUserCalendarItem(),
				this.getLeaveItem(),
			];
		}

		return [
			this.getInsertNameItem(),
			this.getSendMessageItem(),
			this.getManagerItem(),
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
			text: Loc.getMessage('IM_SIDEBAR_MENU_INSERT_NAME_V2'),
			onclick: () => {
				EventEmitter.emit(EventType.textarea.insertMention, {
					mentionText: user.name,
					mentionReplacement: Utils.text.getMentionBbCode(this.context.dialogId, user.name),
					dialogId: this.context.contextDialogId,
					isMentionSymbol: false,
				});
				this.menuInstance.close();
			},
		};
	}

	getSendMessageItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_WRITE_V2'),
			onclick: () => {
				Messenger.openChat(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getManagerItem(): ?MenuItem
	{
		const userId = Number.parseInt(this.context.dialogId, 10);
		const chat: ImModelChat = this.store.getters['chats/get'](this.context.contextDialogId);
		const isOwner = userId === chat.ownerId;
		const canChangeManagers = PermissionManager.getInstance().canPerformAction(
			ChatActionType.changeManagers,
			this.context.contextDialogId,
		);

		if (isOwner || !canChangeManagers)
		{
			return null;
		}

		const isManager = chat.managerList.includes(userId);

		return {
			text: isManager ? Loc.getMessage('IM_SIDEBAR_MENU_MANAGER_REMOVE') : Loc.getMessage('IM_SIDEBAR_MENU_MANAGER_ADD'),
			onclick: () => {
				if (isManager)
				{
					this.chatService.removeManager(this.context.contextDialogId, userId);
				}
				else
				{
					this.chatService.addManager(this.context.contextDialogId, userId);
				}
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
		if (!this.isUser() || this.isBot())
		{
			return null;
		}

		const targetUserId = Number.parseInt(this.context.dialogId, 10);

		const profileUri = Utils.user.getProfileLink(this.context.dialogId);
		const isCurrentUser = targetUserId === Core.getUserId();
		const phraseCode = isCurrentUser ? 'IM_LIB_MENU_OPEN_OWN_PROFILE' : 'IM_LIB_MENU_OPEN_PROFILE_V2';

		return {
			text: Loc.getMessage(phraseCode),
			href: profileUri,
			onclick: () => {
				this.menuInstance.close();
			},
		};
	}

	getOpenUserCalendarItem(): ?MenuItem
	{
		if (!this.isUser() || this.isBot())
		{
			return null;
		}

		const targetUserId = Number.parseInt(this.context.dialogId, 10);

		const profileUri = Utils.user.getCalendarLink(this.context.dialogId);
		const isCurrentUser = targetUserId === Core.getUserId();
		const phraseCode = isCurrentUser ? 'IM_LIB_MENU_OPEN_OWN_CALENDAR' : 'IM_LIB_MENU_OPEN_CALENDAR_V2';

		return {
			text: Loc.getMessage(phraseCode),
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
		const canKick = this.permissionManager.canPerformAction(ChatActionType.kick, this.context.contextDialogId);
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
			text: Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1'),
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

	isUser(): boolean
	{
		return this.store.getters['chats/isUser'](this.context.dialogId);
	}

	isBot(): boolean
	{
		if (!this.isUser())
		{
			return false;
		}

		const user: ImModelUser = this.store.getters['users/get'](this.context.dialogId);

		return user.bot === true;
	}
}
