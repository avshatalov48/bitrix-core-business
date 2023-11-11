/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_slider,ui_dialogs_messagebox,im_v2_lib_call,im_v2_provider_service,im_v2_lib_utils,im_v2_lib_permission,im_v2_lib_confirm,im_public,main_popup,main_core_events,ui_vue3_vuex,rest_client,im_v2_application_core,im_v2_const,main_core) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Lib.Menu';
	var _prepareMenuItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareMenuItems");
	var _filterExcessDelimiters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterExcessDelimiters");
	var _filterDuplicateDelimiters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterDuplicateDelimiters");
	var _filterFinishingDelimiter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("filterFinishingDelimiter");
	var _isDelimiter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDelimiter");
	class BaseMenu extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _isDelimiter, {
	      value: _isDelimiter2
	    });
	    Object.defineProperty(this, _filterFinishingDelimiter, {
	      value: _filterFinishingDelimiter2
	    });
	    Object.defineProperty(this, _filterDuplicateDelimiters, {
	      value: _filterDuplicateDelimiters2
	    });
	    Object.defineProperty(this, _filterExcessDelimiters, {
	      value: _filterExcessDelimiters2
	    });
	    Object.defineProperty(this, _prepareMenuItems, {
	      value: _prepareMenuItems2
	    });
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

	    // EventEmitter.subscribe(EventType.dialog.closePopup, this.onClosePopupHandler);
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
	      items: babelHelpers.classPrivateFieldLooseBase(this, _prepareMenuItems)[_prepareMenuItems](),
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
	    // EventEmitter.unsubscribe(EventType.dialog.closePopup, this.onClosePopupHandler);
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
	function _prepareMenuItems2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _filterExcessDelimiters)[_filterExcessDelimiters](this.getMenuItems());
	}
	function _filterExcessDelimiters2(menuItems) {
	  const menuItemsWithoutDuplicates = babelHelpers.classPrivateFieldLooseBase(this, _filterDuplicateDelimiters)[_filterDuplicateDelimiters](menuItems);
	  return babelHelpers.classPrivateFieldLooseBase(this, _filterFinishingDelimiter)[_filterFinishingDelimiter](menuItemsWithoutDuplicates);
	}
	function _filterDuplicateDelimiters2(menuItems) {
	  let previousElement = null;
	  return menuItems.filter(element => {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDelimiter)[_isDelimiter](previousElement) && babelHelpers.classPrivateFieldLooseBase(this, _isDelimiter)[_isDelimiter](element)) {
	      return false;
	    }
	    if (element !== null) {
	      previousElement = element;
	    }
	    return true;
	  });
	}
	function _filterFinishingDelimiter2(menuItems) {
	  let previousElement = null;
	  return menuItems.reverse().filter(element => {
	    if (previousElement === null && babelHelpers.classPrivateFieldLooseBase(this, _isDelimiter)[_isDelimiter](element)) {
	      return false;
	    }
	    if (element !== null) {
	      previousElement = element;
	    }
	    return true;
	  }).reverse();
	}
	function _isDelimiter2(element) {
	  return main_core.Type.isObjectLike(element) && element.delimiter === true;
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
	    this.chatService = new im_v2_provider_service.ChatService();
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
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
	    return [this.getOpenItem(), this.getUnreadMessageItem(), this.getPinMessageItem(), this.getMuteItem(), this.getCallItem(), this.getOpenProfileItem(), this.getHideItem(), this.getLeaveItem()];
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
	  getOpenItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onclick: () => {
	        im_public.Messenger.openChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenInNewTabItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_IN_NEW_TAB'),
	      onclick: () => {
	        im_v2_lib_slider.MessengerSlider.getInstance().openNewTab(im_v2_const.PathPlaceholder.dialog.replace('#DIALOG_ID#', this.context.dialogId));
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
	    const canMute = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.mute, this.context.dialogId);
	    if (!canMute) {
	      return null;
	    }
	    const dialog = this.store.getters['dialogues/get'](this.context.dialogId, true);
	    const isMuted = dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	    return {
	      text: isMuted ? main_core.Loc.getMessage('IM_LIB_MENU_UNMUTE_2') : main_core.Loc.getMessage('IM_LIB_MENU_MUTE_2'),
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
	    const chatIsAllowedToCall = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.call, this.context.dialogId);
	    if (!chatCanBeCalled || !chatIsAllowedToCall) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_CALL_2'),
	      onclick: () => {
	        this.callManager.startCall(this.context.dialogId);
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
	    const canLeaveChat = this.permissionManager.canPerformAction(im_v2_const.ChatActionType.leave, this.context.dialogId);
	    if (!canLeaveChat) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE'),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showLeaveFromChatConfirm();
	        if (userChoice === true) {
	          this.chatService.leaveChat(this.context.dialogId);
	        }
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

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Messenger.v2.Lib,BX.UI.Dialogs,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Main,BX.Event,BX.Vue3.Vuex,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
