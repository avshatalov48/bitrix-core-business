import {Loc, Type} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';

import {Core} from 'im.v2.application.core';
import {DialogType, EventType, OpenTarget} from 'im.v2.const';
import {CallManager} from 'im.v2.lib.call';
import {ChatService, RecentService} from 'im.v2.provider.service';
import {Utils} from 'im.v2.lib.utils';
import {Messenger} from 'im.public';

import {BaseMenu} from '../base/base';
import {InviteManager} from './invite-manager';

import type {MenuItem} from 'im.v2.lib.menu';

export class RecentMenu extends BaseMenu
{
	callManager: CallManager;
	chatService: ChatService;

	constructor()
	{
		super();

		this.id = 'im-recent-context-menu';
		this.callManager = CallManager.getInstance();
		this.chatService = new ChatService();
	}

	getMenuOptions(): Object
	{
		return {
			...super.getMenuOptions(),
			className: this.getMenuClassName(),
			angle: true,
			offsetLeft: 32
		};
	}

	getMenuClassName(): string
	{
		return this.context.compactMode ? '' : super.getMenuClassName();
	}

	getMenuItems(): MenuItem[]
	{
		if (this.context.invitation.isActive)
		{
			return this.getInviteItems();
		}

		return [
			this.getSendMessageItem(),
			this.getUnreadMessageItem(),
			this.getPinMessageItem(),
			this.getMuteItem(),
			this.getCallItem(),
			// this.getHistoryItem(),
			this.getOpenProfileItem(),
			this.getHideItem(),
			this.getLeaveItem()
		];
	}

	getSendMessageItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_WRITE'),
			onclick: () => {
				Messenger.openChat(this.context.dialogId);
				this.menuInstance.close();
			}
		};
	}

	getUnreadMessageItem(): MenuItem
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
		const showReadOption = this.context.unread || dialog.counter > 0;

		return {
			text: showReadOption ? Loc.getMessage('IM_LIB_MENU_READ') : Loc.getMessage('IM_LIB_MENU_UNREAD'),
			onclick: () => {
				if (showReadOption)
				{
					this.chatService.readDialog(this.context.dialogId);
				}
				else
				{
					this.chatService.unreadDialog(this.context.dialogId);
				}
				this.menuInstance.close();
			}
		};
	}

	getPinMessageItem(): MenuItem
	{
		const isPinned = this.context.pinned;

		return {
			text: isPinned ? Loc.getMessage('IM_LIB_MENU_UNPIN') : Loc.getMessage('IM_LIB_MENU_PIN'),
			onclick: () => {
				if (isPinned)
				{
					this.chatService.unpinChat(this.context.dialogId);
				}
				else
				{
					this.chatService.pinChat(this.context.dialogId);
				}
				this.menuInstance.close();
			}
		};
	}

	getMuteItem(): ?MenuItem
	{
		const canMute = this.store.getters['dialogues/canMute'](this.context.dialogId);
		if (!canMute)
		{
			return null;
		}

		const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
		const isMuted = dialog.muteList.includes(Core.getUserId());
		return {
			text: isMuted? Loc.getMessage('IM_LIB_MENU_UNMUTE') : Loc.getMessage('IM_LIB_MENU_MUTE'),
			onclick: () => {
				if (isMuted)
				{
					this.chatService.unmuteChat(this.context.dialogId);
				}
				else
				{
					this.chatService.muteChat(this.context.dialogId);
				}
				this.menuInstance.close();
			}
		};
	}

	getCallItem(): ?MenuItem
	{
		const chatCanBeCalled = this.callManager.chatCanBeCalled(this.context.dialogId);
		if (!chatCanBeCalled)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_CALL'),
			onclick: () => {
				this.callManager.startCall(this.context.dialogId);
				this.menuInstance.close();
			}
		};
	}

	getHistoryItem(): ?MenuItem
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
		const isUser = dialog.type === DialogType.user;
		if (isUser)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_OPEN_HISTORY'),
			onclick: () => {
				const target = this.context.target === OpenTarget.current? OpenTarget.current: OpenTarget.auto;

				EventEmitter.emit(EventType.dialog.openHistory, {
					...this.context,
					chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
					user: this.store.getters['users/get'](this.context.dialogId, true),
					target
				});
				this.menuInstance.close();
			}
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
			}
		};
	}

	getHideItem(): ?MenuItem
	{
		if (this.context.invitation?.isActive || this.context.options?.default_user_record)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_HIDE'),
			onclick: () => {
				RecentService.getInstance().hideChat(this.context.dialogId);

				this.menuInstance.close();
			}
		};
	}

	getLeaveItem(): ?MenuItem
	{
		const canLeaveChat = this.store.getters['dialogues/canLeave'](this.context.dialogId);
		if (!canLeaveChat)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_LIB_MENU_LEAVE'),
			onclick: () => {
				this.chatService.leaveChat(this.context.dialogId);
				this.menuInstance.close();
			}
		};
	}

	// invitation
	getInviteItems(): Array
	{
		const items = [
			this.getSendMessageItem(),
			this.getOpenProfileItem()
		];

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

		const canManageInvite = canInvite && Core.getUserId() === this.context.invitation.originator;
		if (canManageInvite)
		{
			items.push(
				this.getDelimiter(),
				this.context.invitation.canResend? this.getResendInviteItem(): null,
				this.getCancelInviteItem()
			);
		}

		return items;
	}

	getResendInviteItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_INVITE_RESEND'),
			onclick: () => {
				InviteManager.resendInvite(this.context.dialogId);
				this.menuInstance.close();
			}
		};
	}

	getCancelInviteItem(): MenuItem
	{
		return {
			text: Loc.getMessage('IM_LIB_MENU_INVITE_CANCEL'),
			onclick: () => {
				MessageBox.show({
					message: Loc.getMessage('IM_LIB_MENU_INVITE_CANCEL_CONFIRM'),
					modal: true,
					buttons: MessageBoxButtons.OK_CANCEL,
					onOk: (messageBox) => {
						InviteManager.cancelInvite(this.context.dialogId);
						messageBox.close();
					},
					onCancel: (messageBox) => {
						messageBox.close();
					}
				});
				this.menuInstance.close();
			}
		};
	}
	// invitation end

	getDelimiter(): Object
	{
		return {delimiter: true};
	}
}