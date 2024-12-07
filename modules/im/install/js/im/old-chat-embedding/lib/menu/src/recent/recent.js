import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';

import {DialogType, EventType, OpenTarget, ChatOption} from 'im.old-chat-embedding.const';

import {BaseMenu} from '../base/base';
import {PinManager} from './pin-manager';
import {UnreadManager} from './unread-manager';
import {MuteManager} from './mute-manager';
import {InviteManager} from './invite-manager';
import {CallHelper} from './call-helper';

export class RecentMenu extends BaseMenu
{
	pinManager: Object = null;
	unreadManager: Object = null;
	muteManager: Object = null;
	callHelper: Object = null;

	constructor($Bitrix)
	{
		super($Bitrix);

		this.id = 'im-recent-context-menu';
		this.pinManager = new PinManager($Bitrix);
		this.unreadManager = new UnreadManager($Bitrix);
		this.muteManager = new MuteManager($Bitrix);
		this.callHelper = new CallHelper($Bitrix);
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

	getMenuClassName(): String
	{
		return this.context.compactMode ? '' : super.getMenuClassName();
	}

	getMenuItems(): Array
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
			this.getHistoryItem(),
			this.getOpenProfileItem(),
			this.getHideItem(),
			this.getLeaveItem()
		];
	}

	getSendMessageItem(): Object
	{
		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_WRITE'),
			onclick: function() {
				const target = this.context.target === OpenTarget.current? OpenTarget.current: OpenTarget.auto;

				EventEmitter.emit(EventType.dialog.open, {
					...this.context,
					chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
					user: this.store.getters['users/get'](this.context.dialogId, true),
					target
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getUnreadMessageItem(): Object
	{
		let isUnreaded = this.context.unread;
		if (!isUnreaded)
		{
			const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
			isUnreaded = dialog.counter > 0;
		}

		return {
			text: isUnreaded ? Loc.getMessage('IM_RECENT_CONTEXT_MENU_READ') : Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNREAD'),
			onclick: function() {
				if (isUnreaded)
				{
					this.unreadManager.readDialog(this.context.dialogId);
				}
				else
				{
					this.unreadManager.unreadDialog(this.context.dialogId);
				}
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getPinMessageItem(): Object
	{
		const isPinned = this.context.pinned;

		return {
			text: isPinned ? Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNPIN') : Loc.getMessage('IM_RECENT_CONTEXT_MENU_PIN'),
			onclick: function() {
				if (isPinned)
				{
					this.pinManager.unpinDialog(this.context.dialogId);
				}
				else
				{
					this.pinManager.pinDialog(this.context.dialogId);
				}
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getMuteItem(): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
		const isUser = dialog.type === DialogType.user;
		const isAnnouncement = dialog.type === DialogType.announcement;
		if (!dialog || isUser || isAnnouncement)
		{
			return null;
		}

		const muteAllowed = this.store.getters['dialogues/getChatOption'](dialog.type, ChatOption.mute);
		if (!muteAllowed)
		{
			return null;
		}

		const isMuted = dialog.muteList.includes(this.getCurrentUserId());
		return {
			text: isMuted? Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNMUTE') : Loc.getMessage('IM_RECENT_CONTEXT_MENU_MUTE'),
			onclick: function() {
				if (isMuted)
				{
					this.muteManager.unmuteDialog(this.context.dialogId);
				}
				else
				{
					this.muteManager.muteDialog(this.context.dialogId);
				}
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getCallItem(): ?Object
	{
		return null;

		const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
		if (!dialog)
		{
			return null;
		}

		const isChat = dialog.type !== DialogType.user;
		const callAllowed = this.store.getters['dialogues/getChatOption'](dialog.type, ChatOption.call);
		if (isChat && !callAllowed)
		{
			return null;
		}

		const callSupport = this.callHelper.checkCallSupport(this.context.dialogId);
		const isAnnouncement = dialog.type === DialogType.announcement;
		const isExternalTelephonyCall = dialog.type === DialogType.call;
		const hasActiveCall = this.callHelper.hasActiveCall();
		if (!callSupport || isAnnouncement || isExternalTelephonyCall || hasActiveCall)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_CALL'),
			onclick: function() {
				EventEmitter.emit(EventType.dialog.call, this.context);
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getHistoryItem(): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
		const isUser = dialog.type === DialogType.user;
		if (isUser)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_HISTORY'),
			onclick: function() {
				const target = this.context.target === OpenTarget.current? OpenTarget.current: OpenTarget.auto;

				EventEmitter.emit(EventType.dialog.openHistory, {
					...this.context,
					chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
					user: this.store.getters['users/get'](this.context.dialogId, true),
					target
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getOpenProfileItem(): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
		const isUser = dialog.type === DialogType.user;
		if (!isUser)
		{
			return null;
		}

		const profileUri = `/company/personal/user/${this.context.dialogId}/`;

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_PROFILE'),
			href: profileUri,
			onclick: function() {
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getHideItem(): ?Object
	{
		if (this.context.invitation.isActive || this.context.options.default_user_record)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_HIDE'),
			onclick: function() {
				EventEmitter.emit(EventType.dialog.hide, {
					...this.context,
					chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
					user: this.store.getters['users/get'](this.context.dialogId, true)
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getLeaveItem(): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
		if (!dialog)
		{
			return null;
		}

		const isUser = dialog.type === DialogType.user;
		if (isUser)
		{
			return null;
		}

		let optionToCheck = ChatOption.leave;
		if (dialog.owner === this.getCurrentUserId())
		{
			optionToCheck = ChatOption.leaveOwner;
		}
		const leaveAllowed = this.store.getters['dialogues/getChatOption'](dialog.type, optionToCheck);

		const isExternalTelephonyCall = dialog.type === DialogType.call;
		if (isExternalTelephonyCall || !leaveAllowed)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_LEAVE'),
			onclick: function() {
				EventEmitter.emit(EventType.dialog.leave, {
					...this.context,
					chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
					user: this.store.getters['users/get'](this.context.dialogId, true)
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	// invitation
	getInviteItems(): Array
	{
		const items = [
			this.getSendMessageItem(),
			this.getOpenProfileItem()
		];

		const canManageInvite = BX.MessengerProxy.canInvite() && this.getCurrentUserId() === this.context.invitation.originator;
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

	getResendInviteItem(): Object
	{
		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_RESEND'),
			onclick: function() {
				InviteManager.resendInvite(this.context.dialogId);
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getCancelInviteItem(): Object
	{
		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL'),
			onclick: function() {
				MessageBox.show({
					message: Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL_CONFIRM'),
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
			}.bind(this)
		};
	}
	// invitation end

	getDelimiter(): Object
	{
		return {delimiter: true};
	}

	getCurrentUserId(): number
	{
		return this.store.state.application.common.userId;
	}
}