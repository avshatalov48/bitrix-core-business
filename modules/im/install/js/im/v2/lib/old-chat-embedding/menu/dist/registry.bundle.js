this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_popup,main_core_events,ui_dialogs_messagebox,im_v2_const,main_core) {
	'use strict';

	class BaseMenu {
	  constructor($Bitrix) {
	    this.menuInstance = null;
	    this.context = null;
	    this.target = null;
	    this.store = null;
	    this.restClient = null;
	    this.id = 'im-base-context-menu';
	    this.$Bitrix = $Bitrix;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	    this.onClosePopupHandler = this.onClosePopup.bind(this);
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.closePopup, this.onClosePopupHandler);
	  }

	  // public
	  openMenu(context, target) {
	    if (this.menuInstance) {
	      this.menuInstance.destroy();
	      this.menuInstance = null;
	    }
	    this.context = context;
	    this.target = target;
	    this.menuInstance = this.getMenuInstance();
	    this.menuInstance.show();
	  }
	  getMenuInstance() {
	    return main_popup.MenuManager.create(this.getMenuOptions());
	  }
	  getMenuOptions() {
	    return {
	      id: this.id,
	      bindOptions: {
	        forceBindPosition: true,
	        position: 'bottom'
	      },
	      targetContainer: document.body,
	      bindElement: this.target,
	      cacheable: false,
	      className: this.getMenuClassName(),
	      items: this.getMenuItems()
	    };
	  }
	  getMenuItems() {
	    return [];
	  }
	  getMenuClassName() {
	    return this.isDarkMode() ? 'im-context-menu-dark' : '';
	  }
	  isDarkMode() {
	    return this.store.state.application.options.darkTheme;
	  }
	  onClosePopup() {
	    this.destroy();
	  }
	  close() {
	    if (!this.menuInstance) {
	      return;
	    }
	    this.menuInstance.destroy();
	    this.menuInstance = null;
	  }
	  destroy() {
	    this.close();
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.closePopup, this.onClosePopupHandler);
	  }
	}

	class PinManager {
	  constructor($Bitrix) {
	    this.store = null;
	    this.restClient = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	  }
	  pinDialog(dialogId) {
	    this.store.dispatch('recent/pin', {
	      id: dialogId,
	      action: true
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'Y'
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imRecentPin, queryParams).catch(error => {
	      console.error('Im.RecentList: error pinning chat', error);
	      this.store.dispatch('recent/pin', {
	        id: dialogId,
	        action: false
	      });
	    });
	  }
	  unpinDialog(dialogId) {
	    this.store.dispatch('recent/pin', {
	      id: dialogId,
	      action: false
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'N'
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imRecentPin, queryParams).catch(error => {
	      console.error('Im.RecentList: error unpinning chat', error);
	      this.store.dispatch('recent/pin', {
	        id: dialogId,
	        action: true
	      });
	    });
	  }
	}

	class UnreadManager {
	  constructor($Bitrix) {
	    this.store = null;
	    this.restClient = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	  }
	  readDialog(dialogId) {
	    let queryParams;
	    const dialog = this.store.getters['dialogues/get'](dialogId, true);
	    if (dialog.counter > 0) {
	      queryParams = {
	        'DIALOG_ID': dialogId
	      };
	      this.restClient.callMethod(im_v2_const.RestMethod.imDialogRead, queryParams).catch(error => {
	        console.error('Im.RecentList: error reading chat', error);
	      });
	      return;
	    }
	    this.store.dispatch('recent/unread', {
	      id: dialogId,
	      action: false
	    });
	    queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'N'
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imRecentUnread, queryParams).catch(error => {
	      console.error('Im.RecentList: error reading chat', error);
	      this.store.dispatch('recent/unread', {
	        id: dialogId,
	        action: true
	      });
	    });
	  }
	  unreadDialog(dialogId) {
	    this.store.dispatch('recent/unread', {
	      id: dialogId,
	      action: true
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'Y'
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imRecentUnread, queryParams).catch(error => {
	      console.error('Im.RecentList: error unreading chat', error);
	      this.store.dispatch('recent/unread', {
	        id: dialogId,
	        action: false
	      });
	    });
	  }
	}

	class MuteManager {
	  constructor($Bitrix) {
	    this.store = null;
	    this.restClient = null;
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	  }
	  muteDialog(dialogId) {
	    this.store.dispatch('dialogues/mute', {
	      dialogId
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'Y'
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imChatMute, queryParams).catch(error => {
	      console.error('Im.RecentList: error muting chat', error);
	      this.store.dispatch('dialogues/unmute', {
	        dialogId
	      });
	    });
	  }
	  unmuteDialog(dialogId) {
	    this.store.dispatch('dialogues/unmute', {
	      dialogId
	    });
	    const queryParams = {
	      'DIALOG_ID': dialogId,
	      'ACTION': 'N'
	    };
	    this.restClient.callMethod(im_v2_const.RestMethod.imChatMute, queryParams).catch(error => {
	      console.error('Im.RecentList: error unmuting chat', error);
	      this.store.dispatch('dialogues/mute', {
	        dialogId
	      });
	    });
	  }
	}

	const resendAction = 'intranet.controller.invite.reinvite';
	const cancelAction = 'intranet.controller.invite.deleteinvitation';
	const InviteManager = {
	  resendInvite(userId) {
	    const data = {
	      params: {
	        userId
	      }
	    };
	    main_core.ajax.runAction(resendAction, {
	      data
	    }).then(() => {
	      this.showNotification(main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_RESEND_DONE'), 2000);
	    }, error => {
	      this.handleActionError(error);
	    });
	  },
	  cancelInvite(userId) {
	    const data = {
	      params: {
	        userId
	      }
	    };
	    main_core.ajax.runAction(cancelAction, {
	      data
	    }).then(() => {
	      this.showNotification(main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL_DONE'), 2000);
	    }, error => {
	      this.handleActionError(error);
	    });
	  },
	  showNotification(text, autoHideDelay = 4000) {
	    BX.UI.Notification.Center.notify({
	      content: text,
	      autoHideDelay
	    });
	  },
	  handleActionError(error) {
	    if (error.status === 'error' && error.errors.length > 0) {
	      const errorContent = error.errors.map(element => {
	        return element.message;
	      }).join('. ');
	      this.showNotification(errorContent);
	      return true;
	    }
	    this.showNotification(main_core.Loc.getMessage('IM_RECENT_CONNECT_ERROR'));
	  }
	};

	class CallHelper {
	  constructor($Bitrix) {
	    this.store = null;
	    this.store = $Bitrix.Data.get('controller').store;
	  }
	  checkCallSupport(dialogId) {
	    if (!BX.MessengerProxy.getPushServerStatus() || !BX.Call.Util.isWebRTCSupported()) {
	      return false;
	    }
	    const userId = Number.parseInt(dialogId, 10);
	    return userId > 0 ? this.checkUserCallSupport(userId) : this.checkChatCallSupport(dialogId);
	  }
	  checkUserCallSupport(userId) {
	    const user = this.store.getters['users/get'](userId);
	    return user && user.status !== 'guest' && !user.bot && !user.network && user.id !== this.getCurrentUserId() && !!user.lastActivityDate;
	  }
	  checkChatCallSupport(dialogId) {
	    const dialog = this.store.getters['dialogues/get'](dialogId);
	    if (!dialog) {
	      return false;
	    }
	    const {
	      userCounter
	    } = dialog;
	    return userCounter > 1 && userCounter <= BX.Call.Util.getUserLimit();
	  }
	  hasActiveCall() {
	    return BX.MessengerProxy.getCallController().hasActiveCall();
	  }
	  getCurrentUserId() {
	    return this.store.state.application.common.userId;
	  }
	}

	class RecentMenu extends BaseMenu {
	  constructor($Bitrix) {
	    super($Bitrix);
	    this.pinManager = null;
	    this.unreadManager = null;
	    this.muteManager = null;
	    this.callHelper = null;
	    this.id = 'im-recent-context-menu';
	    this.pinManager = new PinManager($Bitrix);
	    this.unreadManager = new UnreadManager($Bitrix);
	    this.muteManager = new MuteManager($Bitrix);
	    this.callHelper = new CallHelper($Bitrix);
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 32
	    };
	  }
	  getMenuClassName() {
	    return this.context.compactMode ? '' : super.getMenuClassName();
	  }
	  getMenuItems() {
	    if (this.context.invitation.isActive) {
	      return this.getInviteItems();
	    }
	    return [this.getSendMessageItem(), this.getUnreadMessageItem(), this.getPinMessageItem(), this.getMuteItem(), this.getCallItem(), this.getHistoryItem(), this.getOpenProfileItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getSendMessageItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_WRITE'),
	      onclick: function () {
	        const target = this.context.target === im_v2_const.OpenTarget.current ? im_v2_const.OpenTarget.current : im_v2_const.OpenTarget.auto;
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.open, {
	          ...this.context,
	          chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
	          user: this.store.getters['users/get'](this.context.dialogId, true),
	          target
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getUnreadMessageItem() {
	    let isUnreaded = this.context.unread;
	    if (!isUnreaded) {
	      const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	      isUnreaded = dialog.counter > 0;
	    }
	    return {
	      text: isUnreaded ? main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_READ') : main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNREAD'),
	      onclick: function () {
	        if (isUnreaded) {
	          this.unreadManager.readDialog(this.context.dialogId);
	        } else {
	          this.unreadManager.unreadDialog(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getPinMessageItem() {
	    const isPinned = this.context.pinned;
	    return {
	      text: isPinned ? main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNPIN') : main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_PIN'),
	      onclick: function () {
	        if (isPinned) {
	          this.pinManager.unpinDialog(this.context.dialogId);
	        } else {
	          this.pinManager.pinDialog(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getMuteItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
	    const isUser = dialog.type === im_v2_const.DialogType.user;
	    const isAnnouncement = dialog.type === im_v2_const.DialogType.announcement;
	    if (!dialog || isUser || isAnnouncement) {
	      return null;
	    }
	    const muteAllowed = this.store.getters['dialogues/getChatOption'](dialog.type, im_v2_const.ChatOption.mute);
	    if (!muteAllowed) {
	      return null;
	    }
	    const isMuted = dialog.muteList.includes(this.getCurrentUserId());
	    return {
	      text: isMuted ? main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_UNMUTE') : main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_MUTE'),
	      onclick: function () {
	        if (isMuted) {
	          this.muteManager.unmuteDialog(this.context.dialogId);
	        } else {
	          this.muteManager.muteDialog(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getCallItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
	    if (!dialog) {
	      return null;
	    }
	    const isChat = dialog.type !== im_v2_const.DialogType.user;
	    const callAllowed = this.store.getters['dialogues/getChatOption'](dialog.type, im_v2_const.ChatOption.call);
	    if (isChat && !callAllowed) {
	      return null;
	    }
	    const callSupport = this.callHelper.checkCallSupport(this.context.dialogId);
	    const isAnnouncement = dialog.type === im_v2_const.DialogType.announcement;
	    const isExternalTelephonyCall = dialog.type === im_v2_const.DialogType.call;
	    const hasActiveCall = this.callHelper.hasActiveCall();
	    if (!callSupport || isAnnouncement || isExternalTelephonyCall || hasActiveCall) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_CALL'),
	      onclick: function () {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.call, this.context);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getHistoryItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const isUser = dialog.type === im_v2_const.DialogType.user;
	    if (isUser) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_HISTORY'),
	      onclick: function () {
	        const target = this.context.target === im_v2_const.OpenTarget.current ? im_v2_const.OpenTarget.current : im_v2_const.OpenTarget.auto;
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.openHistory, {
	          ...this.context,
	          chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
	          user: this.store.getters['users/get'](this.context.dialogId, true),
	          target
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getOpenProfileItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const isUser = dialog.type === im_v2_const.DialogType.user;
	    if (!isUser) {
	      return null;
	    }
	    const profileUri = `/company/personal/user/${this.context.dialogId}/`;
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_PROFILE'),
	      href: profileUri,
	      onclick: function () {
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getHideItem() {
	    if (this.context.invitation.isActive || this.context.options.default_user_record) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_HIDE'),
	      onclick: function () {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.hide, {
	          ...this.context,
	          chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
	          user: this.store.getters['users/get'](this.context.dialogId, true)
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getLeaveItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId);
	    if (!dialog) {
	      return null;
	    }
	    const isUser = dialog.type === im_v2_const.DialogType.user;
	    if (isUser) {
	      return null;
	    }
	    let optionToCheck = im_v2_const.ChatOption.leave;
	    if (dialog.owner === this.getCurrentUserId()) {
	      optionToCheck = im_v2_const.ChatOption.leaveOwner;
	    }
	    const leaveAllowed = this.store.getters['dialogues/getChatOption'](dialog.type, optionToCheck);
	    const isExternalTelephonyCall = dialog.type === im_v2_const.DialogType.call;
	    if (isExternalTelephonyCall || !leaveAllowed) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_LEAVE'),
	      onclick: function () {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.leave, {
	          ...this.context,
	          chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
	          user: this.store.getters['users/get'](this.context.dialogId, true)
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }

	  // invitation
	  getInviteItems() {
	    const items = [this.getSendMessageItem(), this.getOpenProfileItem()];
	    const canManageInvite = BX.MessengerProxy.canInvite() && this.getCurrentUserId() === this.context.invitation.originator;
	    if (canManageInvite) {
	      items.push(this.getDelimiter(), this.context.invitation.canResend ? this.getResendInviteItem() : null, this.getCancelInviteItem());
	    }
	    return items;
	  }
	  getResendInviteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_RESEND'),
	      onclick: function () {
	        InviteManager.resendInvite(this.context.dialogId);
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getCancelInviteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL'),
	      onclick: function () {
	        ui_dialogs_messagebox.MessageBox.show({
	          message: main_core.Loc.getMessage('IM_RECENT_CONTEXT_MENU_INVITE_CANCEL_CONFIRM'),
	          modal: true,
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          onOk: messageBox => {
	            InviteManager.cancelInvite(this.context.dialogId);
	            messageBox.close();
	          },
	          onCancel: messageBox => {
	            messageBox.close();
	          }
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  // invitation end

	  getDelimiter() {
	    return {
	      delimiter: true
	    };
	  }
	  getCurrentUserId() {
	    return this.store.state.application.common.userId;
	  }
	}

	exports.BaseMenu = BaseMenu;
	exports.RecentMenu = RecentMenu;

}((this.BX.Messenger.v2.LibLegacy = this.BX.Messenger.v2.LibLegacy || {}),BX.Main,BX.Event,BX.UI.Dialogs,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
