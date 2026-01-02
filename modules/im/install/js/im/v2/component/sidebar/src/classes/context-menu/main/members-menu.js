import { Loc } from 'main.core';

import { UserMenu } from 'im.v2.lib.menu';
import { Core } from 'im.v2.application.core';
import { Utils } from 'im.v2.lib.utils';
import { CallManager } from 'im.v2.lib.call';
import { ChatService } from 'im.v2.provider.service.chat';
import { ActionByRole, ActionByUserType, ChatType, UserType } from 'im.v2.const';
import { PermissionManager } from 'im.v2.lib.permission';
import { showLeaveChatConfirm } from 'im.v2.lib.confirm';

import type { ImModelUser, ImModelChat } from 'im.v2.model';
import type { MenuItemOptions } from 'ui.system.menu';

type MembersMenuContext = {
	dialog: ImModelChat,
	user: ImModelUser,
};

export class MembersMenu extends UserMenu
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

	getMenuItems(): MenuItemOptions | null[]
	{
		if (this.context.user.id === Core.getUserId())
		{
			return [
				this.getProfileItem(),
				this.getOpenUserCalendarItem(),
				this.getLeaveItem(),
			];
		}

		return [
			this.getMentionItem(),
			this.getSendItem(),
			this.getManagerItem(),
			this.getCallItem(),
			this.getProfileItem(),
			this.getOpenUserCalendarItem(),
			this.getKickItem(),
		];
	}

	getManagerItem(): ?MenuItemOptions
	{
		const isOwner = this.context.user.id === this.context.dialog.ownerId;
		const canChangeManagers = PermissionManager.getInstance().canPerformActionByRole(
			ActionByRole.changeManagers,
			this.context.dialog.dialogId,
		);
		const isCollabType = this.context.dialog.type === ChatType.collab;

		if (isOwner || !canChangeManagers || isCollabType)
		{
			return null;
		}

		const isManager = this.context.dialog.managerList.includes(this.context.user.id);

		return {
			title: isManager ? Loc.getMessage('IM_SIDEBAR_MENU_MANAGER_REMOVE') : Loc.getMessage('IM_SIDEBAR_MENU_MANAGER_ADD'),
			onClick: () => {
				if (isManager)
				{
					this.chatService.removeManager(this.context.dialog.dialogId, this.context.user.id);
				}
				else
				{
					this.chatService.addManager(this.context.dialog.dialogId, this.context.user.id);
				}
				this.menuInstance.close();
			},
		};
	}

	getCallItem(): ?MenuItemOptions
	{
		const userDialogId = this.context.user.id.toString();

		const chatCanBeCalled = this.callManager.chatCanBeCalled(userDialogId);
		const chatIsAllowedToCall = this.permissionManager.canPerformActionByRole(ActionByRole.call, userDialogId);
		if (!chatCanBeCalled || !chatIsAllowedToCall)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_MENU_CALL_2'),
			onClick: () => {
				this.callManager.startCall(userDialogId);
				this.menuInstance.close();
			},
		};
	}

	getOpenUserCalendarItem(): ?MenuItemOptions
	{
		if (this.isBot())
		{
			return null;
		}

		const profileUri = Utils.user.getCalendarLink(this.context.user.id);
		const isCurrentUser = this.context.user.id === Core.getUserId();
		const phraseCode = isCurrentUser ? 'IM_LIB_MENU_OPEN_OWN_CALENDAR' : 'IM_LIB_MENU_OPEN_CALENDAR_V2';

		return {
			title: Loc.getMessage(phraseCode),
			onClick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	getLeaveItem(): ?MenuItemOptions
	{
		if (this.isCollabChat() && !this.canLeaveCollab())
		{
			return null;
		}

		const canLeaveChat = this.permissionManager.canPerformActionByRole(
			ActionByRole.leave,
			this.context.dialog.dialogId,
		);
		if (!canLeaveChat)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1'),
			onClick: async () => {
				this.menuInstance.close();
				const userChoice = await showLeaveChatConfirm(this.context.dialog.dialogId);
				if (!userChoice)
				{
					return;
				}

				if (this.isCollabChat())
				{
					this.chatService.leaveCollab(this.context.dialog.dialogId);
				}
				else
				{
					this.chatService.leaveChat(this.context.dialog.dialogId);
				}
			},
		};
	}

	isBot(): boolean
	{
		return this.context.user.type === UserType.bot;
	}

	canLeaveCollab(): boolean
	{
		return this.permissionManager.canPerformActionByUserType(ActionByUserType.leaveCollab);
	}
}
