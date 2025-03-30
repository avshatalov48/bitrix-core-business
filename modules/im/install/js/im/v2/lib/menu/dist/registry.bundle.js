/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_dialogs_messagebox,im_v2_lib_call,im_v2_lib_channel,call_lib_analytics,main_popup,ui_vue3_vuex,rest_client,main_core,main_core_events,im_public,im_v2_lib_utils,im_v2_application_core,im_v2_provider_service,im_v2_lib_confirm,im_v2_lib_permission,im_v2_const) {
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
	      this.showNotification(main_core.Loc.getMessage('IM_LIB_INVITE_RESEND_DONE'), 2000);
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
	      this.showNotification(main_core.Loc.getMessage('IM_LIB_INVITE_CANCEL_DONE'), 2000);
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

	var _leaveChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("leaveChat");
	var _leaveCollab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("leaveCollab");
	class RecentMenu extends BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _leaveCollab, {
	      value: _leaveCollab2
	    });
	    Object.defineProperty(this, _leaveChat, {
	      value: _leaveChat2
	    });
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
	    return [this.getUnreadMessageItem(), this.getPinMessageItem(), this.getMuteItem(), this.getOpenProfileItem(), this.getChatsWithUserItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getSendMessageItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_WRITE_V2'),
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
	  getUnreadMessageItem() {
	    const dialog = this.store.getters['chats/get'](this.context.dialogId, true);
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
	      text: isPinned ? main_core.Loc.getMessage('IM_LIB_MENU_UNPIN_MSGVER_1') : main_core.Loc.getMessage('IM_LIB_MENU_PIN_MSGVER_1'),
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
	    const canMute = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.mute, this.context.dialogId);
	    if (!canMute) {
	      return null;
	    }
	    const dialog = this.store.getters['chats/get'](this.context.dialogId, true);
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
	    const chatIsAllowedToCall = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.call, this.context.dialogId);
	    if (!chatCanBeCalled || !chatIsAllowedToCall) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_CALL_2'),
	      onclick: () => {
	        call_lib_analytics.Analytics.getInstance().onRecentStartCallClick({
	          isGroupChat: this.context.dialogId.includes('chat'),
	          chatId: this.context.chatId
	        });
	        this.callManager.startCall(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenProfileItem() {
	    if (!this.isUser() || this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getProfileLink(this.context.dialogId);
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
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
	      text: main_core.Loc.getMessage('IM_LIB_MENU_HIDE_MSGVER_1'),
	      onclick: () => {
	        im_v2_provider_service.RecentService.getInstance().hideChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getLeaveItem() {
	    if (this.isCollabChat()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _leaveCollab)[_leaveCollab]();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _leaveChat)[_leaveChat]();
	  }
	  getChatsWithUserItem() {
	    if (!this.isUser() || this.isBot()) {
	      return null;
	    }
	    const isAnyChatOpened = this.store.getters['application/getLayout'].entityId.length > 0;
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_FIND_CHATS_WITH_USER_MSGVER_1'),
	      onclick: async () => {
	        if (!isAnyChatOpened) {
	          await im_public.Messenger.openChat(this.context.dialogId);
	        }
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.sidebar.open, {
	          panel: im_v2_const.SidebarDetailBlock.chatsWithUser,
	          standalone: true,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }

	  // region invitation
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
	      text: main_core.Loc.getMessage('IM_LIB_INVITE_RESEND'),
	      onclick: () => {
	        InviteManager.resendInvite(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCancelInviteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_INVITE_CANCEL'),
	      onclick: () => {
	        ui_dialogs_messagebox.MessageBox.show({
	          message: main_core.Loc.getMessage('IM_LIB_INVITE_CANCEL_CONFIRM'),
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
	  // endregion

	  getDelimiter() {
	    return {
	      delimiter: true
	    };
	  }
	  getChat() {
	    return this.store.getters['chats/get'](this.context.dialogId, true);
	  }
	  isUser() {
	    return this.store.getters['chats/isUser'](this.context.dialogId);
	  }
	  isBot() {
	    if (!this.isUser()) {
	      return false;
	    }
	    const user = this.store.getters['users/get'](this.context.dialogId);
	    return user.type === im_v2_const.UserType.bot;
	  }
	  isChannel() {
	    return im_v2_lib_channel.ChannelManager.isChannel(this.context.dialogId);
	  }
	  isCommentsChat() {
	    const {
	      type
	    } = this.store.getters['chats/get'](this.context.dialogId, true);
	    return type === im_v2_const.ChatType.comment;
	  }
	  isCollabChat() {
	    const {
	      type
	    } = this.store.getters['chats/get'](this.context.dialogId, true);
	    return type === im_v2_const.ChatType.collab;
	  }
	}
	function _leaveChat2() {
	  const canLeaveChat = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.leave, this.context.dialogId);
	  if (!canLeaveChat) {
	    return null;
	  }
	  const text = this.isChannel() ? main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_CHANNEL') : main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1');
	  return {
	    text,
	    onclick: async () => {
	      this.menuInstance.close();
	      const userChoice = await im_v2_lib_confirm.showLeaveChatConfirm(this.context.dialogId);
	      if (userChoice === true) {
	        this.chatService.leaveChat(this.context.dialogId);
	      }
	    }
	  };
	}
	function _leaveCollab2() {
	  const canLeaveChat = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.leave, this.context.dialogId);
	  const canLeaveCollab = this.permissionManager.canPerformActionByUserType(im_v2_const.ActionByUserType.leaveCollab);
	  if (!canLeaveChat || !canLeaveCollab) {
	    return null;
	  }
	  return {
	    text: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1'),
	    onclick: async () => {
	      this.menuInstance.close();
	      const userChoice = await im_v2_lib_confirm.showLeaveChatConfirm(this.context.dialogId);
	      if (!userChoice) {
	        return;
	      }
	      this.chatService.leaveCollab(this.context.dialogId);
	    }
	  };
	}

	var _getKickItemText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getKickItemText");
	var _kickUser = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("kickUser");
	class UserMenu extends BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _kickUser, {
	      value: _kickUser2
	    });
	    Object.defineProperty(this, _getKickItemText, {
	      value: _getKickItemText2
	    });
	    this.id = 'bx-im-user-context-menu';
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getKickItem() {
	    const canKick = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.kick, this.context.dialog.dialogId);
	    if (!canKick) {
	      return null;
	    }
	    return {
	      text: babelHelpers.classPrivateFieldLooseBase(this, _getKickItemText)[_getKickItemText](),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showKickUserConfirm(this.context.dialog.dialogId);
	        if (userChoice !== true) {
	          return;
	        }
	        void babelHelpers.classPrivateFieldLooseBase(this, _kickUser)[_kickUser]();
	      }
	    };
	  }
	  getMentionItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_USER_MENTION'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: this.context.user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.context.user.id, this.context.user.name),
	          dialogId: this.context.dialog.dialogId,
	          isMentionSymbol: false
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getSendItem() {
	    if (this.context.dialog.type === im_v2_const.ChatType.user) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_LIB_MENU_USER_WRITE'),
	      onclick: () => {
	        void im_public.Messenger.openChat(this.context.user.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getProfileItem() {
	    if (this.isBot()) {
	      return null;
	    }
	    const profileUri = im_v2_lib_utils.Utils.user.getProfileLink(this.context.user.id);
	    const isCurrentUser = this.context.user.id === im_v2_application_core.Core.getUserId();
	    const phraseCode = isCurrentUser ? 'IM_LIB_MENU_OPEN_OWN_PROFILE' : 'IM_LIB_MENU_OPEN_PROFILE_V2';
	    return {
	      text: main_core.Loc.getMessage(phraseCode),
	      href: profileUri,
	      onclick: () => {
	        this.menuInstance.close();
	      }
	    };
	  }
	  isCollabChat() {
	    const {
	      type
	    } = this.store.getters['chats/get'](this.context.dialog.dialogId, true);
	    return type === im_v2_const.ChatType.collab;
	  }
	  isBot() {
	    return this.context.user.type === im_v2_const.UserType.bot;
	  }
	}
	function _getKickItemText2() {
	  if (this.isCollabChat()) {
	    return main_core.Loc.getMessage('IM_LIB_MENU_USER_KICK_FROM_COLLAB');
	  }
	  return main_core.Loc.getMessage('IM_LIB_MENU_USER_KICK_FROM_CHAT');
	}
	function _kickUser2() {
	  if (this.isCollabChat()) {
	    return new im_v2_provider_service.ChatService().kickUserFromCollab(this.context.dialog.dialogId, this.context.user.id);
	  }
	  return new im_v2_provider_service.ChatService().kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
	}

	exports.RecentMenu = RecentMenu;
	exports.BaseMenu = BaseMenu;
	exports.UserMenu = UserMenu;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.UI.Dialogs,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Call.Lib,BX.Main,BX.Vue3.Vuex,BX,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=registry.bundle.js.map
