import {MenuManager} from 'main.popup';
import {Loc} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import {ChatTypes, EventType, OpenTarget} from 'im.v2.const';

import {InviteManager} from './invite-manager';
import {PinManager} from './pin-manager';
import {MuteManager} from './mute-manager';
import {CallManager} from './call-manager';

export class ContextMenuManager
{
	menuInstance: Object = null;
	store: Object = null;
	restClient: Object = null;
	pinManager: Object = null;

	constructor($Bitrix)
	{
		this.$Bitrix = $Bitrix;
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();
		this.pinManager = new PinManager($Bitrix);
		this.muteManager = new MuteManager($Bitrix);
	}

	openMenu(context: Object, target: HTMLElement)
	{
		if (this.menuInstance)
		{
			this.menuInstance.destroy();
			this.menuInstance = null;
		}
		this.menuInstance = this.getMenuInstance(context, target);
		this.menuInstance.show();
	}

	getMenuInstance(context: Object, target: HTMLElement)
	{
		return MenuManager.create({
			id: 'im-recent-context-menu',
			bindOptions: {forceBindPosition: true, position: 'bottom'},
			targetContainer: document.body,
			bindElement: target,
			cacheable: false,
			darkMode: this.isDarkMode(),
			items: this.getMenuItems(context)
		});
	}

	getMenuItems(context: Object)
	{
		if (context.invitation.isActive)
		{
			return this.getInviteItems(context);
		}

		return [
			this.getSendMessageItem(context),
			this.getPinMessageItem(context),
			this.getMuteItem(context),
			this.getCallItem(context),
			this.getHistoryItem(context),
			this.getOpenProfileItem(context),
			this.getHideItem(context),
			this.getLeaveItem(context)
		];
	}

	getSendMessageItem(context: Object): Object
	{
		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_WRITE'),
			onclick: function() {
				const target = context.target === OpenTarget.current? OpenTarget.current: OpenTarget.auto;

				EventEmitter.emit(EventType.dialog.open, {
					...context,
					chat: this.store.getters['dialogues/get'](context.dialogId, true),
					user: this.store.getters['users/get'](context.dialogId, true),
					target
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getPinMessageItem(context: Object): Object
	{
		const isPinned = context.pinned;

		return {
			text: isPinned ? Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNPIN') : Loc.getMessage('IM_RECENT_CONTEXT_MENU_PIN'),
			onclick: function() {
				if (isPinned)
				{
					this.pinManager.unpinDialog(context.dialogId);
				}
				else
				{
					this.pinManager.pinDialog(context.dialogId);
				}
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getMuteItem(context: Object): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](context.dialogId);
		const isUser = dialog.type === ChatTypes.user;
		const isAnnouncement = dialog.type === ChatTypes.announcement;
		if (!dialog || isUser || !dialog.restrictions.mute || isAnnouncement)
		{
			return null;
		}

		const isMuted = dialog.muteList.includes(this.getCurrentUserId());
		return {
			text: isMuted? Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNMUTE') : Loc.getMessage('IM_RECENT_CONTEXT_MENU_MUTE'),
			onclick: function() {
				if (isMuted)
				{
					this.muteManager.unmuteDialog(context.dialogId);
				}
				else
				{
					this.muteManager.muteDialog(context.dialogId);
				}
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getCallItem(context: Object): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](context.dialogId);
		if (!dialog)
		{
			return null;
		}
		if (dialog.type !== ChatTypes.user && !dialog.restrictions.call)
		{
			return null;
		}
		const callManager = CallManager.getInstance(this.$Bitrix);
		const callSupport = callManager.checkCallSupport(context.dialogId);
		const isAnnouncement = dialog.type === ChatTypes.announcement;
		const isExternalTelephonyCall = dialog.type === ChatTypes.call;
		const hasActiveCall = callManager.hasActiveCall();
		if (!callSupport || isAnnouncement || isExternalTelephonyCall || hasActiveCall)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_CALL'),
			onclick: function() {
				EventEmitter.emit(EventType.dialog.call, context);
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getHistoryItem(context: Object): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](context.dialogId, true);
		const isUser = dialog.type === ChatTypes.user;
		if (isUser)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_HISTORY'),
			onclick: function() {
				const target = context.target === OpenTarget.current? OpenTarget.current: OpenTarget.auto;

				EventEmitter.emit(EventType.dialog.openHistory, {
					...context,
					chat: this.store.getters['dialogues/get'](context.dialogId, true),
					user: this.store.getters['users/get'](context.dialogId, true),
					target
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getOpenProfileItem(context: Object): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](context.dialogId, true);
		const isUser = dialog.type === ChatTypes.user;
		if (!isUser)
		{
			return null;
		}

		const profileUri = `/company/personal/user/${context.dialogId}/`;

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_PROFILE'),
			href: profileUri,
			onclick: function() {
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getHideItem(context: Object): ?Object
	{
		if (context.invitation.isActive || context.options.default_user_record)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_HIDE'),
			onclick: function() {
				EventEmitter.emit(EventType.dialog.hide, {
					...context,
					chat: this.store.getters['dialogues/get'](context.dialogId, true),
					user: this.store.getters['users/get'](context.dialogId, true)
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getLeaveItem(context: Object): ?Object
	{
		const dialog = this.store.getters['dialogues/get'](context.dialogId);
		if (!dialog)
		{
			return null;
		}

		const isUser = dialog.type === ChatTypes.user;
		if (isUser)
		{
			return null;
		}

		const isExternalTelephonyCall = dialog.type === ChatTypes.call;
		let canLeave = dialog.restrictions.leave;
		if (dialog.owner === this.getCurrentUserId())
		{
			canLeave = dialog.restrictions.leaveOwner;
		}
		if (isExternalTelephonyCall || !canLeave)
		{
			return null;
		}

		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_LEAVE'),
			onclick: function() {
				EventEmitter.emit(EventType.dialog.leave, {
					...context,
					chat: this.store.getters['dialogues/get'](context.dialogId, true),
					user: this.store.getters['users/get'](context.dialogId, true)
				});
				this.menuInstance.close();
			}.bind(this)
		};
	}

	// invitation
	getInviteItems(context: Object): Array
	{
		const items = [
			this.getSendMessageItem(context),
			this.getOpenProfileItem(context)
		];

		const canManageInvite = BX.MessengerProxy.canInvite() && this.getCurrentUserId() === context.invitation.originator;
		if (canManageInvite)
		{
			items.push(
				this.getDelimiter(),
				context.invitation.canResend? this.getResendInviteItem(context): null,
				this.getCancelInviteItem(context)
			);
		}

		return items;
	}

	getResendInviteItem(context: Object): Object
	{
		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_RESEND'),
			onclick: function() {
				InviteManager.resendInvite(context.dialogId);
				this.menuInstance.close();
			}.bind(this)
		};
	}

	getCancelInviteItem(context: Object): Object
	{
		return {
			text: Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL'),
			onclick: function() {
				MessageBox.show({
					message: Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL_CONFIRM'),
					modal: true,
					buttons: MessageBoxButtons.OK_CANCEL,
					onOk: (messageBox) => {
						InviteManager.cancelInvite(context.dialogId);
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

	isDarkMode(): boolean
	{
		return this.store.state.application.options.darkTheme;
	}

	destroy()
	{
		if (!this.menuInstance)
		{
			return null;
		}

		this.menuInstance.destroy();
		this.menuInstance = null;
	}
}