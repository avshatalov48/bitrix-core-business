this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_dialogs_messagebox,im_v2_lib_call,im_v2_provider_service,im_v2_lib_utils,im_public,main_popup,main_core_events,ui_vue3_vuex,rest_client,im_v2_application_core,im_v2_const,main_core) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Lib.Menu';
	class BaseMenu extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.id = 'im-base-context-menu';
	    this.setEventNamespace(EVENT_NAMESPACE);
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	    this.onClosePopupHandler = this.onClosePopup.bind(this);
	  }

	  // public
	  openMenu(context, target) {
	    if (this.menuInstance) {
	      this.close();
	    }
	    this.context = context;
	    this.target = target;
	    this.menuInstance = this.getMenuInstance();
	    this.menuInstance.show();
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.closePopup, this.onClosePopupHandler);
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
	      items: this.getMenuItems(),
	      events: {
	        onClose: () => {
	          this.emit(BaseMenu.events.onCloseMenu);
	          this.close();
	        }
	      }
	    };
	  }
	  getMenuItems() {
	    return [];
	  }
	  getMenuClassName() {
	    return '';
	  }
	  onClosePopup() {
	    this.close();
	  }
	  close() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.closePopup, this.onClosePopupHandler);
	    if (!this.menuInstance) {
	      return;
	    }
	    this.menuInstance.destroy();
	    this.menuInstance = null;
	  }
	  destroy() {
	    this.close();
	  }
	  getCurrentUserId() {
	    return im_v2_application_core.Core.getUserId();
	  }
	}
	BaseMenu.events = {
	  onCloseMenu: 'onCloseMenu'
	};

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
	      this.showNotification(main_core.Loc.getMessage('IM_LIB_MENU_INVITE_RESEND_DONE'), 2000);
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
	      this.showNotification(main_core.Loc.getMessage('IM_LIB_MENU_INVITE_CANCEL_DONE'), 2000);
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
	    this.showNotification(main_core.Loc.getMessage('IM_LIST_RECENT_CONNECT_ERROR'));
	  }
	};

	class RecentMenu extends BaseMenu {
	  constructor() {
	    super();
	    this.id = 'im-recent-context-menu';
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
	    this.chatService = new im_v2_provider_service.ChatService();
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
	    return [this.getSendMessageItem(), this.getUnreadMessageItem(), this.getPinMessageItem(), this.getMuteItem(), this.getCallItem(),
	    // this.getHistoryItem(),
	    this.getOpenProfileItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getSendMessageItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_WRITE'),
	      onclick: () => {
	        im_public.Messenger.openChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getUnreadMessageItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const showReadOption = this.context.unread || dialog.counter > 0;
	    return {
	      text: showReadOption ? main_core.Loc.getMessage('IM_LIB_MENU_READ') : main_core.Loc.getMessage('IM_LIB_MENU_UNREAD'),
	      onclick: () => {
	        if (showReadOption) {
	          this.chatService.readDialog(this.context.dialogId);
	        } else {
	          this.chatService.unreadDialog(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getPinMessageItem() {
	    const isPinned = this.context.pinned;
	    return {
	      text: isPinned ? main_core.Loc.getMessage('IM_LIB_MENU_UNPIN') : main_core.Loc.getMessage('IM_LIB_MENU_PIN'),
	      onclick: () => {
	        if (isPinned) {
	          this.chatService.unpinChat(this.context.dialogId);
	        } else {
	          this.chatService.pinChat(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getMuteItem() {
	    const canMute = this.store.getters['dialogues/canMute'](this.context.dialogId);
	    if (!canMute) {
	      return null;
	    }
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const isMuted = dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	    return {
	      text: isMuted ? main_core.Loc.getMessage('IM_LIB_MENU_UNMUTE') : main_core.Loc.getMessage('IM_LIB_MENU_MUTE'),
	      onclick: () => {
	        if (isMuted) {
	          this.chatService.unmuteChat(this.context.dialogId);
	        } else {
	          this.chatService.muteChat(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCallItem() {
	    const chatCanBeCalled = this.callManager.chatCanBeCalled(this.context.dialogId);
	    if (!chatCanBeCalled) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_CALL'),
	      onclick: () => {
	        this.callManager.startCall(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getHistoryItem() {
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const isUser = dialog.type === im_v2_const.DialogType.user;
	    if (isUser) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_HISTORY'),
	      onclick: () => {
	        const target = this.context.target === im_v2_const.OpenTarget.current ? im_v2_const.OpenTarget.current : im_v2_const.OpenTarget.auto;
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.openHistory, {
	          ...this.context,
	          chat: this.store.getters['dialogues/get'](this.context.dialogId, true),
	          user: this.store.getters['users/get'](this.context.dialogId, true),
	          target
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenProfileItem() {
	    const isUser = this.store.getters['dialogues/isUser'](this.context.dialogId);
	    if (!isUser) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getProfileLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE'),
	      href: profileUri,
	      onclick: () => {
	        this.menuInstance.close();
	      }
	    };
	  }
	  getHideItem() {
	    var _this$context$invitat, _this$context$options;
	    if ((_this$context$invitat = this.context.invitation) != null && _this$context$invitat.isActive || (_this$context$options = this.context.options) != null && _this$context$options.default_user_record) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_HIDE'),
	      onclick: () => {
	        im_v2_provider_service.RecentService.getInstance().hideChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getLeaveItem() {
	    const canLeaveChat = this.store.getters['dialogues/canLeave'](this.context.dialogId);
	    if (!canLeaveChat) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE'),
	      onclick: () => {
	        this.chatService.leaveChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }

	  // invitation
	  getInviteItems() {
	    const items = [this.getSendMessageItem(), this.getOpenProfileItem()];
	    let canInvite; // TODO change to APPLICATION variable
	    if (main_core.Type.isUndefined(BX.MessengerProxy)) {
	      canInvite = true;
	      console.error('BX.MessengerProxy.canInvite() method not found in v2 version!');
	    } else {
	      canInvite = BX.MessengerProxy.canInvite();
	    }
	    const canManageInvite = canInvite && im_v2_application_core.Core.getUserId() === this.context.invitation.originator;
	    if (canManageInvite) {
	      items.push(this.getDelimiter(), this.context.invitation.canResend ? this.getResendInviteItem() : null, this.getCancelInviteItem());
	    }
	    return items;
	  }
	  getResendInviteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_INVITE_RESEND'),
	      onclick: () => {
	        InviteManager.resendInvite(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCancelInviteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_INVITE_CANCEL'),
	      onclick: () => {
	        ui_dialogs_messagebox.MessageBox.show({
	          message: main_core.Loc.getMessage('IM_LIB_MENU_INVITE_CANCEL_CONFIRM'),
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
	      }
	    };
	  }
	  // invitation end

	  getDelimiter() {
	    return {
	      delimiter: true
	    };
	  }
	}

	exports.RecentMenu = RecentMenu;
	exports.BaseMenu = BaseMenu;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.UI.Dialogs,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Main,BX.Event,BX.Vue3.Vuex,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
