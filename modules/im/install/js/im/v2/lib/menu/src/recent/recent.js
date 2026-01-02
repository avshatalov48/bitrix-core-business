import { Analytics } from 'im.v2.lib.analytics';
import { Loc, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MessageBox, MessageBoxButtons } from 'ui.dialogs.messagebox';

import { Core } from 'im.v2.application.core';
import { ActionByRole, EventType, SidebarDetailBlock, ChatType, UserType, ActionByUserType } from 'im.v2.const';
import { CallManager } from 'im.v2.lib.call';
import { LegacyRecentService } from 'im.v2.provider.service.recent';
import { ChatService } from 'im.v2.provider.service.chat';
import { Utils } from 'im.v2.lib.utils';
import { PermissionManager } from 'im.v2.lib.permission';
import { showLeaveChatConfirm } from 'im.v2.lib.confirm';
import { ChannelManager } from 'im.v2.lib.channel';
import { Messenger } from 'im.public';
import { InviteManager } from 'im.v2.lib.invite';

import { BaseMenu } from '../base/base';

import type { ImModelRecentItem, ImModelUser, ImModelChat } from 'im.v2.model';
import type { MenuItemOptions, MenuSectionOptions, MenuOptions } from 'ui.system.menu';

type MenuItemContext = {
	dialogId: string,
	compactMode?: boolean,
	recentItem?: ImModelRecentItem
}

const MenuSectionCode = Object.freeze({
	main: 'main',
	invite: 'invite',
});

export class RecentMenu extends BaseMenu
{
	context: MenuItemContext;
	callManager: CallManager;
	permissionManager: PermissionManager;
	chatService: ChatService;

	constructor()
	{
		super();

		this.id = 'im-recent-context-menu';
		this.chatService = new ChatService();
		this.callManager = CallManager.getInstance();
		this.permissionManager = PermissionManager.getInstance();
	}

	getMenuOptions(): MenuOptions
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
		};
	}

	getMenuClassName(): string
	{
		return this.context.compactMode ? '' : super.getMenuClassName();
	}

	getMenuItems(): MenuItemOptions | null[]
	{
		if (this.#isInvitationActive())
		{
			const mainGroupItems = [
				this.getSendMessageItem(),
				this.getOpenProfileItem(),
			];

			return [
				...this.groupItems(mainGroupItems, MenuSectionCode.main),
				...this.groupItems(this.getInviteItems(), MenuSectionCode.invite),
			];
		}

		return [
			this.getUnreadMessageItem(),
			this.getPinMessageItem(),
			this.getMuteItem(),
			this.getOpenProfileItem(),
			this.getChatsWithUserItem(),
			this.getHideItem(),
			this.getLeaveItem(),
		];
	}

	getMenuGroups(): MenuSectionOptions[]
	{
		if (this.#isInvitationActive())
		{
			return [
				{ code: MenuSectionCode.main },
				{ code: MenuSectionCode.invite },
			];
		}

		return [];
	}

	getSendMessageItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_WRITE_V2'),
			onClick: () => {
				void Messenger.openChat(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getOpenItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_MENU_OPEN'),
			onClick: () => {
				void Messenger.openChat(this.context.dialogId);
				this.menuInstance.close();
			},
		};
	}

	getUnreadMessageItem(): ?MenuItemOptions
	{
		const { recentItem, dialogId } = this.context;
		if (!recentItem)
		{
			return null;
		}

		const dialog = this.store.getters['chats/get'](dialogId, true);
		const showReadOption = recentItem.unread || dialog.counter > 0;

		return {
			title: showReadOption ? Loc.getMessage('IM_LIB_MENU_READ') : Loc.getMessage('IM_LIB_MENU_UNREAD'),
			onClick: () => {
				if (showReadOption)
				{
					this.chatService.readDialog(dialogId);
				}
				else
				{
					this.chatService.unreadDialog(dialogId);
				}
				this.menuInstance.close();
			},
		};
	}

	getPinMessageItem(): ?MenuItemOptions
	{
		const { recentItem, dialogId } = this.context;
		if (!recentItem)
		{
			return null;
		}

		const isPinned = recentItem.pinned;

		return {
			title: isPinned ? Loc.getMessage('IM_LIB_MENU_UNPIN_MSGVER_1') : Loc.getMessage('IM_LIB_MENU_PIN_MSGVER_1'),
			onClick: () => {
				if (isPinned)
				{
					this.chatService.unpinChat(dialogId);
				}
				else
				{
					this.chatService.pinChat(dialogId);
					Analytics.getInstance().chatPins.onPin(dialogId);
				}
				this.menuInstance.close();
			},
		};
	}

	getMuteItem(): ?MenuItemOptions
	{
		const { dialogId } = this.context;
		const canMute = this.permissionManager.canPerformActionByRole(ActionByRole.mute, dialogId);
		if (!canMute)
		{
			return null;
		}

		const dialog = this.store.getters['chats/get'](dialogId, true);
		const isMuted = dialog.muteList.includes(Core.getUserId());

		return {
			title: isMuted ? Loc.getMessage('IM_LIB_MENU_UNMUTE_2') : Loc.getMessage('IM_LIB_MENU_MUTE_2'),
			onClick: () => {
				if (isMuted)
				{
					this.chatService.unmuteChat(dialogId);
				}
				else
				{
					this.chatService.muteChat(dialogId);
				}
				this.menuInstance.close();
			},
		};
	}

	getOpenProfileItem(): ?MenuItemOptions
	{
		if (!this.isUser() || this.isBot())
		{
			return null;
		}

		const profileUri = Utils.user.getProfileLink(this.context.dialogId);

		return {
			title: Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
			onClick: () => {
				BX.SidePanel.Instance.open(profileUri);
				this.menuInstance.close();
			},
		};
	}

	getHideItem(): ?MenuItemOptions
	{
		if (!this.#canHideChat())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_MENU_HIDE_MSGVER_1'),
			onClick: () => {
				LegacyRecentService.getInstance().hideChat(this.context.dialogId);

				this.menuInstance.close();
			},
		};
	}

	getLeaveItem(): ?MenuItemOptions
	{
		if (this.isCollabChat())
		{
			return this.#leaveCollab();
		}

		return this.#leaveChat();
	}

	getChatsWithUserItem(): ?MenuItemOptions
	{
		if (!this.isUser() || this.isBot() || this.isChatWithCurrentUser())
		{
			return null;
		}

		const isAnyChatOpened = this.store.getters['application/getLayout'].entityId.length > 0;

		return {
			title: Loc.getMessage('IM_LIB_MENU_FIND_SHARED_CHATS'),
			onClick: async () => {
				if (!isAnyChatOpened)
				{
					await Messenger.openChat(this.context.dialogId);
				}

				EventEmitter.emit(EventType.sidebar.open, {
					panel: SidebarDetailBlock.chatsWithUser,
					standalone: true,
					dialogId: this.context.dialogId,
				});
				this.menuInstance.close();
			},
		};
	}

	// region invitation
	getInviteItems(): MenuItemOptions[]
	{
		const { recentItem } = this.context;
		if (!recentItem)
		{
			return [];
		}

		const items = [];

		let canInvite; // TODO change to APPLICATION variable
		if (Type.isUndefined(BX.MessengerProxy))
		{
			canInvite = true;
			console.error('BX.MessengerProxy.canInvite() method not found in v2 version!');
		}
		else
		{
			canInvite = BX.MessengerProxy.canInvite();
		}

		const canManageInvite = canInvite && Core.getUserId() === recentItem.invitation.originator;
		if (canManageInvite)
		{
			items.push(
				this.getResendInviteItem(),
				this.getCancelInviteItem(),
			);
		}

		return items;
	}

	getResendInviteItem(): ?MenuItemOptions
	{
		const { recentItem, dialogId } = this.context;
		if (!recentItem || !this.#canResendInvitation())
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_INVITE_RESEND'),
			onClick: () => {
				InviteManager.resendInvite(dialogId);
				this.menuInstance.close();
			},
		};
	}

	getCancelInviteItem(): MenuItemOptions
	{
		return {
			title: Loc.getMessage('IM_LIB_INVITE_CANCEL'),
			onClick: () => {
				MessageBox.show({
					message: Loc.getMessage('IM_LIB_INVITE_CANCEL_CONFIRM'),
					modal: true,
					buttons: MessageBoxButtons.OK_CANCEL,
					onOk: (messageBox) => {
						InviteManager.cancelInvite(this.context.dialogId);
						messageBox.close();
					},
					onCancel: (messageBox) => {
						messageBox.close();
					},
				});
				this.menuInstance.close();
			},
		};
	}
	// endregion

	getChat(): ImModelChat
	{
		return this.store.getters['chats/get'](this.context.dialogId, true);
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

		return user.type === UserType.bot;
	}

	isChannel(): boolean
	{
		return ChannelManager.isChannel(this.context.dialogId);
	}

	isCommentsChat(): boolean
	{
		const { type }: ImModelChat = this.store.getters['chats/get'](this.context.dialogId, true);

		return type === ChatType.comment;
	}

	isCollabChat(): boolean
	{
		const { type }: ImModelChat = this.store.getters['chats/get'](this.context.dialogId, true);

		return type === ChatType.collab;
	}

	isChatWithCurrentUser(): boolean
	{
		return this.getCurrentUserId() === Number.parseInt(this.context.dialogId, 10);
	}

	#leaveChat(): ?MenuItemOptions
	{
		const canLeaveChat = this.permissionManager.canPerformActionByRole(ActionByRole.leave, this.context.dialogId);
		if (!canLeaveChat)
		{
			return null;
		}

		const title = this.isChannel()
			? Loc.getMessage('IM_LIB_MENU_LEAVE_CHANNEL')
			: Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1')
		;

		return {
			title,
			onClick: async () => {
				this.menuInstance.close();
				const userChoice = await showLeaveChatConfirm(this.context.dialogId);
				if (userChoice === true)
				{
					this.chatService.leaveChat(this.context.dialogId);
				}
			},
		};
	}

	#leaveCollab(): ?MenuItemOptions
	{
		const canLeaveChat = this.permissionManager.canPerformActionByRole(ActionByRole.leave, this.context.dialogId);
		const canLeaveCollab = this.permissionManager.canPerformActionByUserType(ActionByUserType.leaveCollab);
		if (!canLeaveChat || !canLeaveCollab)
		{
			return null;
		}

		return {
			title: Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1'),
			onClick: async () => {
				this.menuInstance.close();
				const userChoice = await showLeaveChatConfirm(this.context.dialogId);
				if (!userChoice)
				{
					return;
				}

				this.chatService.leaveCollab(this.context.dialogId);
			},
		};
	}

	#canHideChat(): boolean
	{
		const { recentItem, dialogId } = this.context;
		if (!recentItem)
		{
			return null;
		}

		const isInvitation = this.#isInvitationActive();
		const isFakeUser = recentItem.isFakeElement;
		const isAiAssistantBot = this.store.getters['users/bots/isAiAssistant'](dialogId);

		return !isInvitation && !isFakeUser && !isAiAssistantBot;
	}

	#isInvitationActive(): boolean
	{
		const { recentItem } = this.context;
		if (!recentItem || !recentItem.invitation)
		{
			return false;
		}

		return recentItem.invitation.isActive;
	}

	#canResendInvitation(): boolean
	{
		const { recentItem } = this.context;
		if (!recentItem || !recentItem.invitation)
		{
			return false;
		}

		return recentItem.invitation.canResend;
	}
}
