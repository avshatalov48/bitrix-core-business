/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,ui_vue3_vuex,rest_client,ui_dialogs_messagebox,im_v2_lib_call,im_v2_provider_service_recent,im_v2_lib_invite,im_public,im_v2_provider_service_chat,im_v2_lib_chat,ui_system_menu,im_v2_lib_promo,im_v2_lib_parser,im_v2_lib_entityCreator,im_v2_provider_service_message,im_v2_provider_service_disk,im_v2_lib_market,im_v2_lib_utils,im_v2_lib_permission,im_v2_lib_confirm,im_v2_lib_notifier,main_core_events,im_v2_const,im_v2_lib_channel,im_v2_lib_analytics,im_v2_lib_copilot,main_core,ui_iconSet_api_core,im_v2_application_core,im_v2_lib_feedback) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Lib.Menu';
	var _prepareItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareItems");
	var _bindBlurEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("bindBlurEvent");
	class BaseMenu extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _bindBlurEvent, {
	      value: _bindBlurEvent2
	    });
	    Object.defineProperty(this, _prepareItems, {
	      value: _prepareItems2
	    });
	    this.id = 'im-base-context-menu';
	    this.setEventNamespace(EVENT_NAMESPACE);
	    this.store = im_v2_application_core.Core.getStore();
	    this.restClient = im_v2_application_core.Core.getRestClient();
	  }

	  // public
	  openMenu(context, target) {
	    if (this.menuInstance) {
	      this.close();
	    }
	    this.context = context;
	    this.target = target;
	    this.menuInstance = new ui_system_menu.Menu(this.getMenuOptions());
	    this.menuInstance.show(this.target);
	    babelHelpers.classPrivateFieldLooseBase(this, _bindBlurEvent)[_bindBlurEvent]();
	  }
	  getMenuOptions() {
	    return {
	      id: this.id,
	      bindOptions: {
	        forceBindPosition: true,
	        position: 'bottom'
	      },
	      targetContainer: document.body,
	      cacheable: false,
	      closeByEsc: true,
	      className: this.getMenuClassName(),
	      items: babelHelpers.classPrivateFieldLooseBase(this, _prepareItems)[_prepareItems](),
	      sections: this.getMenuGroups()
	    };
	  }
	  getMenuItems() {
	    return [];
	  }
	  getMenuGroups() {
	    return [];
	  }
	  groupItems(menuItems, group) {
	    return menuItems.filter(item => item !== null).map(item => {
	      return {
	        ...item,
	        sectionCode: group
	      };
	    });
	  }
	  getMenuClassName() {
	    return '';
	  }
	  onClosePopup() {
	    this.close();
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
	  }
	  getCurrentUserId() {
	    return im_v2_application_core.Core.getUserId();
	  }
	}
	function _prepareItems2() {
	  return this.getMenuItems().filter(item => item !== null);
	}
	function _bindBlurEvent2() {
	  main_core.Event.bindOnce(window, 'blur', () => {
	    this.destroy();
	  });
	}

	const MenuSectionCode = Object.freeze({
	  main: 'main',
	  invite: 'invite'
	});
	var _leaveChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("leaveChat");
	var _leaveCollab = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("leaveCollab");
	var _canHideChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canHideChat");
	var _isInvitationActive = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isInvitationActive");
	var _canResendInvitation = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("canResendInvitation");
	class RecentMenu extends BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _canResendInvitation, {
	      value: _canResendInvitation2
	    });
	    Object.defineProperty(this, _isInvitationActive, {
	      value: _isInvitationActive2
	    });
	    Object.defineProperty(this, _canHideChat, {
	      value: _canHideChat2
	    });
	    Object.defineProperty(this, _leaveCollab, {
	      value: _leaveCollab2
	    });
	    Object.defineProperty(this, _leaveChat, {
	      value: _leaveChat2
	    });
	    this.id = 'im-recent-context-menu';
	    this.chatService = new im_v2_provider_service_chat.ChatService();
	    this.callManager = im_v2_lib_call.CallManager.getInstance();
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName()
	    };
	  }
	  getMenuClassName() {
	    return this.context.compactMode ? '' : super.getMenuClassName();
	  }
	  getMenuItems() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isInvitationActive)[_isInvitationActive]()) {
	      const mainGroupItems = [this.getSendMessageItem(), this.getOpenProfileItem()];
	      return [...this.groupItems(mainGroupItems, MenuSectionCode.main), ...this.groupItems(this.getInviteItems(), MenuSectionCode.invite)];
	    }
	    return [this.getUnreadMessageItem(), this.getPinMessageItem(), this.getMuteItem(), this.getOpenProfileItem(), this.getChatsWithUserItem(), this.getHideItem(), this.getLeaveItem()];
	  }
	  getMenuGroups() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isInvitationActive)[_isInvitationActive]()) {
	      return [{
	        code: MenuSectionCode.main
	      }, {
	        code: MenuSectionCode.invite
	      }];
	    }
	    return [];
	  }
	  getSendMessageItem() {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_WRITE_V2'),
	      onClick: () => {
	        void im_public.Messenger.openChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getOpenItem() {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_OPEN'),
	      onClick: () => {
	        void im_public.Messenger.openChat(this.context.dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getUnreadMessageItem() {
	    const {
	      recentItem,
	      dialogId
	    } = this.context;
	    if (!recentItem) {
	      return null;
	    }
	    const dialog = this.store.getters['chats/get'](dialogId, true);
	    const showReadOption = recentItem.unread || dialog.counter > 0;
	    return {
	      title: showReadOption ? main_core.Loc.getMessage('IM_LIB_MENU_READ') : main_core.Loc.getMessage('IM_LIB_MENU_UNREAD'),
	      onClick: () => {
	        if (showReadOption) {
	          this.chatService.readDialog(dialogId);
	        } else {
	          this.chatService.unreadDialog(dialogId);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getPinMessageItem() {
	    const {
	      recentItem,
	      dialogId
	    } = this.context;
	    if (!recentItem) {
	      return null;
	    }
	    const isPinned = recentItem.pinned;
	    return {
	      title: isPinned ? main_core.Loc.getMessage('IM_LIB_MENU_UNPIN_MSGVER_1') : main_core.Loc.getMessage('IM_LIB_MENU_PIN_MSGVER_1'),
	      onClick: () => {
	        if (isPinned) {
	          this.chatService.unpinChat(dialogId);
	        } else {
	          this.chatService.pinChat(dialogId);
	          im_v2_lib_analytics.Analytics.getInstance().chatPins.onPin(dialogId);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getMuteItem() {
	    const {
	      dialogId
	    } = this.context;
	    const canMute = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.mute, dialogId);
	    if (!canMute) {
	      return null;
	    }
	    const dialog = this.store.getters['chats/get'](dialogId, true);
	    const isMuted = dialog.muteList.includes(im_v2_application_core.Core.getUserId());
	    return {
	      title: isMuted ? main_core.Loc.getMessage('IM_LIB_MENU_UNMUTE_2') : main_core.Loc.getMessage('IM_LIB_MENU_MUTE_2'),
	      onClick: () => {
	        if (isMuted) {
	          this.chatService.unmuteChat(dialogId);
	        } else {
	          this.chatService.muteChat(dialogId);
	        }
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
	      title: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
	      onClick: () => {
	        BX.SidePanel.Instance.open(profileUri);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getHideItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _canHideChat)[_canHideChat]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_HIDE_MSGVER_1'),
	      onClick: () => {
	        im_v2_provider_service_recent.LegacyRecentService.getInstance().hideChat(this.context.dialogId);
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
	    if (!this.isUser() || this.isBot() || this.isChatWithCurrentUser()) {
	      return null;
	    }
	    const isAnyChatOpened = this.store.getters['application/getLayout'].entityId.length > 0;
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_FIND_SHARED_CHATS'),
	      onClick: async () => {
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
	    const {
	      recentItem
	    } = this.context;
	    if (!recentItem) {
	      return [];
	    }
	    const items = [];
	    let canInvite; // TODO change to APPLICATION variable
	    if (main_core.Type.isUndefined(BX.MessengerProxy)) {
	      canInvite = true;
	      console.error('BX.MessengerProxy.canInvite() method not found in v2 version!');
	    } else {
	      canInvite = BX.MessengerProxy.canInvite();
	    }
	    const canManageInvite = canInvite && im_v2_application_core.Core.getUserId() === recentItem.invitation.originator;
	    if (canManageInvite) {
	      items.push(this.getResendInviteItem(), this.getCancelInviteItem());
	    }
	    return items;
	  }
	  getResendInviteItem() {
	    const {
	      recentItem,
	      dialogId
	    } = this.context;
	    if (!recentItem || !babelHelpers.classPrivateFieldLooseBase(this, _canResendInvitation)[_canResendInvitation]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_INVITE_RESEND'),
	      onClick: () => {
	        im_v2_lib_invite.InviteManager.resendInvite(dialogId);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCancelInviteItem() {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_INVITE_CANCEL'),
	      onClick: () => {
	        ui_dialogs_messagebox.MessageBox.show({
	          message: main_core.Loc.getMessage('IM_LIB_INVITE_CANCEL_CONFIRM'),
	          modal: true,
	          buttons: ui_dialogs_messagebox.MessageBoxButtons.OK_CANCEL,
	          onOk: messageBox => {
	            im_v2_lib_invite.InviteManager.cancelInvite(this.context.dialogId);
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
	  isChatWithCurrentUser() {
	    return this.getCurrentUserId() === Number.parseInt(this.context.dialogId, 10);
	  }
	}
	function _leaveChat2() {
	  const canLeaveChat = this.permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.leave, this.context.dialogId);
	  if (!canLeaveChat) {
	    return null;
	  }
	  const title = this.isChannel() ? main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_CHANNEL') : main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1');
	  return {
	    title,
	    onClick: async () => {
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
	    title: main_core.Loc.getMessage('IM_LIB_MENU_LEAVE_MSGVER_1'),
	    onClick: async () => {
	      this.menuInstance.close();
	      const userChoice = await im_v2_lib_confirm.showLeaveChatConfirm(this.context.dialogId);
	      if (!userChoice) {
	        return;
	      }
	      this.chatService.leaveCollab(this.context.dialogId);
	    }
	  };
	}
	function _canHideChat2() {
	  const {
	    recentItem,
	    dialogId
	  } = this.context;
	  if (!recentItem) {
	    return null;
	  }
	  const isInvitation = babelHelpers.classPrivateFieldLooseBase(this, _isInvitationActive)[_isInvitationActive]();
	  const isFakeUser = recentItem.isFakeElement;
	  const isAiAssistantBot = this.store.getters['users/bots/isAiAssistant'](dialogId);
	  return !isInvitation && !isFakeUser && !isAiAssistantBot;
	}
	function _isInvitationActive2() {
	  const {
	    recentItem
	  } = this.context;
	  if (!recentItem || !recentItem.invitation) {
	    return false;
	  }
	  return recentItem.invitation.isActive;
	}
	function _canResendInvitation2() {
	  const {
	    recentItem
	  } = this.context;
	  if (!recentItem || !recentItem.invitation) {
	    return false;
	  }
	  return recentItem.invitation.canResend;
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
	      title: babelHelpers.classPrivateFieldLooseBase(this, _getKickItemText)[_getKickItemText](),
	      onClick: async () => {
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
	      title: main_core.Loc.getMessage('IM_LIB_MENU_USER_MENTION'),
	      onClick: () => {
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
	      title: main_core.Loc.getMessage('IM_LIB_MENU_USER_WRITE'),
	      onClick: () => {
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
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_OPEN_PROFILE_V2'),
	      onClick: () => {
	        BX.SidePanel.Instance.open(profileUri);
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
	    return new im_v2_provider_service_chat.ChatService().kickUserFromCollab(this.context.dialog.dialogId, this.context.user.id);
	  }
	  return new im_v2_provider_service_chat.ChatService().kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
	}

	const MenuSectionCode$1 = Object.freeze({
	  main: 'main',
	  select: 'select',
	  create: 'create',
	  market: 'market'
	});
	var _needNestedMenu = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("needNestedMenu");
	var _isOwnMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOwnMessage");
	var _isDeletedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	var _getFirstFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getFirstFile");
	var _isSingleFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSingleFile");
	var _isForwardedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isForwardedMessage");
	var _isRealMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isRealMessage");
	var _onDelete = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onDelete");
	var _isDeletionCancelled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletionCancelled");
	var _getDownloadSingleFileItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDownloadSingleFileItem");
	var _getDownloadSeveralFilesItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDownloadSeveralFilesItem");
	var _arePinsExceedLimit = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("arePinsExceedLimit");
	class MessageMenu extends BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _arePinsExceedLimit, {
	      value: _arePinsExceedLimit2
	    });
	    Object.defineProperty(this, _getDownloadSeveralFilesItem, {
	      value: _getDownloadSeveralFilesItem2
	    });
	    Object.defineProperty(this, _getDownloadSingleFileItem, {
	      value: _getDownloadSingleFileItem2
	    });
	    Object.defineProperty(this, _isDeletionCancelled, {
	      value: _isDeletionCancelled2
	    });
	    Object.defineProperty(this, _onDelete, {
	      value: _onDelete2
	    });
	    Object.defineProperty(this, _isRealMessage, {
	      value: _isRealMessage2
	    });
	    Object.defineProperty(this, _isForwardedMessage, {
	      value: _isForwardedMessage2
	    });
	    Object.defineProperty(this, _isSingleFile, {
	      value: _isSingleFile2
	    });
	    Object.defineProperty(this, _getFirstFile, {
	      value: _getFirstFile2
	    });
	    Object.defineProperty(this, _isDeletedMessage, {
	      value: _isDeletedMessage2
	    });
	    Object.defineProperty(this, _isOwnMessage, {
	      value: _isOwnMessage2
	    });
	    Object.defineProperty(this, _needNestedMenu, {
	      value: _needNestedMenu2
	    });
	    this.maxPins = 20;
	    this.id = 'bx-im-message-context-menu';
	    this.diskService = new im_v2_provider_service_disk.DiskService();
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 11,
	      minWidth: 238
	    };
	  }
	  getMenuItems() {
	    const mainGroupItems = [this.getReplyItem(), this.getCopyItem(), this.getEditItem(), this.getDownloadFileItem(), this.getPinItem(), this.getForwardItem(), ...this.getAdditionalItems(), this.getDeleteItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$1.main), ...this.groupItems([this.getSelectItem()], MenuSectionCode$1.select)];
	  }
	  getMenuGroups() {
	    return [{
	      code: MenuSectionCode$1.main
	    }, {
	      code: MenuSectionCode$1.select
	    }];
	  }
	  getNestedMenuGroups() {
	    return [{
	      code: MenuSectionCode$1.main
	    }, {
	      code: MenuSectionCode$1.create
	    }, {
	      code: MenuSectionCode$1.market
	    }];
	  }
	  getSelectItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || !babelHelpers.classPrivateFieldLooseBase(this, _isRealMessage)[_isRealMessage]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SELECT'),
	      icon: ui_iconSet_api_core.Outline.CIRCLE_CHECK,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onSelect(this.context.dialogId);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.openBulkActionsMode, {
	          messageId: this.context.id,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getReplyItem() {
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REPLY'),
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onReply(this.context.dialogId);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.replyMessage, {
	          messageId: this.context.id,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      },
	      icon: ui_iconSet_api_core.Outline.QUOTE
	    };
	  }
	  getForwardItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || !babelHelpers.classPrivateFieldLooseBase(this, _isRealMessage)[_isRealMessage]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_FORWARD'),
	      icon: ui_iconSet_api_core.Outline.FORWARD,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onForward(this.context.dialogId);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.showForwardPopup, {
	          messagesIds: [this.context.id]
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || this.context.text.trim().length === 0) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY'),
	      onClick: async () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onCopyText({
	          dialogId: this.context.dialogId,
	          messageId: this.context.id
	        });
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopy(this.context);
	        await im_v2_lib_utils.Utils.text.copyToClipboard(textToCopy);
	        im_v2_lib_notifier.Notifier.message.onCopyComplete();
	        this.menuInstance.close();
	      },
	      icon: ui_iconSet_api_core.Outline.COPY
	    };
	  }
	  getCopyLinkItem() {
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_LINK_MSGVER_1'),
	      icon: ui_iconSet_api_core.Outline.LINK,
	      onClick: () => {
	        var _BX$clipboard;
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onCopyLink(this.context.dialogId);
	        const textToCopy = im_v2_lib_chat.ChatManager.buildMessageLink(this.context.dialogId, this.context.id);
	        if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(textToCopy)) {
	          im_v2_lib_notifier.Notifier.message.onCopyLinkComplete();
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyFileItem() {
	    if (this.context.files.length !== 1) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE'),
	      icon: ui_iconSet_api_core.Outline.COPY,
	      onClick: () => {
	        var _BX$clipboard2;
	        const fileId = this.context.files[0];
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onCopyFile({
	          dialogId: this.context.dialogId,
	          fileId
	        });
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopyFile(this.context);
	        if ((_BX$clipboard2 = BX.clipboard) != null && _BX$clipboard2.copy(textToCopy)) {
	          im_v2_lib_notifier.Notifier.file.onCopyComplete();
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getPinItem() {
	    const canPin = im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.pinMessage, this.context.dialogId);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || !canPin) {
	      return null;
	    }
	    const isPinned = this.store.getters['messages/pin/isPinned']({
	      chatId: this.context.chatId,
	      messageId: this.context.id
	    });
	    return {
	      title: isPinned ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_UNPIN') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_PIN'),
	      icon: ui_iconSet_api_core.Outline.PIN,
	      onClick: () => {
	        const messageService = new im_v2_provider_service_message.MessageService({
	          chatId: this.context.chatId
	        });
	        if (isPinned) {
	          messageService.unpinMessage(this.context.chatId, this.context.id);
	          im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onUnpin(this.context.dialogId);
	        } else {
	          if (babelHelpers.classPrivateFieldLooseBase(this, _arePinsExceedLimit)[_arePinsExceedLimit]()) {
	            im_v2_lib_notifier.Notifier.chat.onMessagesPinLimitError(this.maxPins);
	            im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onReachingPinsLimit(this.context.dialogId);
	            this.menuInstance.close();
	            return;
	          }
	          messageService.pinMessage(this.context.chatId, this.context.id);
	          im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onPin(this.context.dialogId);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getFavoriteItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return null;
	    }
	    const isInFavorite = this.store.getters['sidebar/favorites/isFavoriteMessage'](this.context.chatId, this.context.id);
	    const menuItemText = isInFavorite ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REMOVE_FROM_SAVED') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE');
	    return {
	      title: menuItemText,
	      icon: ui_iconSet_api_core.Outline.FAVORITE,
	      onClick: () => {
	        const messageService = new im_v2_provider_service_message.MessageService({
	          chatId: this.context.chatId
	        });
	        if (isInFavorite) {
	          messageService.removeMessageFromFavorite(this.context.id);
	        } else {
	          im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onAddFavorite({
	            dialogId: this.context.dialogId,
	            messageId: this.context.id
	          });
	          messageService.addMessageToFavorite(this.context.id);
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getMarkItem() {
	    const canUnread = this.context.viewed && !babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]();
	    const dialog = this.store.getters['chats/getByChatId'](this.context.chatId);
	    const isMarked = this.context.id === dialog.markedId;
	    if (!canUnread || isMarked) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_MARK'),
	      icon: ui_iconSet_api_core.Outline.NEW_MESSAGE,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onMark(this.context.dialogId);
	        const messageService = new im_v2_provider_service_message.MessageService({
	          chatId: this.context.chatId
	        });
	        messageService.markMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateTaskItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_TASK_MSGVER_1'),
	      icon: ui_iconSet_api_core.Outline.TASK,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onCreateTask(this.context.dialogId);
	        const entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.context.chatId);
	        void entityCreator.createTaskForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateMeetingItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_MEETING_MSGVER_1'),
	      icon: ui_iconSet_api_core.Outline.CALENDAR_WITH_SLOTS,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onCreateEvent(this.context.dialogId);
	        const entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.context.chatId);
	        void entityCreator.createMeetingForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getEditItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isForwardedMessage)[_isForwardedMessage]()) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_EDIT'),
	      icon: ui_iconSet_api_core.Outline.EDIT_L,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onEdit(this.context.dialogId);
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.editMessage, {
	          messageId: this.context.id,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getDeleteItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return null;
	    }
	    const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	    const canDeleteOthersMessage = permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.deleteOthersMessage, this.context.dialogId);
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() && !canDeleteOthersMessage) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DELETE'),
	      design: ui_system_menu.MenuItemDesign.Alert,
	      icon: ui_iconSet_api_core.Outline.TRASHCAN,
	      onClick: babelHelpers.classPrivateFieldLooseBase(this, _onDelete)[_onDelete].bind(this)
	    };
	  }
	  getMarketItems() {
	    const {
	      dialogId,
	      id
	    } = this.context;
	    const placements = this.marketManager.getAvailablePlacementsByType(im_v2_const.PlacementType.contextMenu, dialogId);
	    const marketMenuItem = [];
	    const context = {
	      messageId: id,
	      dialogId
	    };
	    placements.forEach(placement => {
	      marketMenuItem.push({
	        title: placement.title,
	        icon: ui_iconSet_api_core.Outline.MARKET,
	        onClick: () => {
	          void im_v2_lib_market.MarketManager.openSlider(placement, context);
	          this.menuInstance.close();
	        }
	      });
	    });
	    const MARKET_ITEMS_LIMIT = 10;
	    return marketMenuItem.slice(0, MARKET_ITEMS_LIMIT);
	  }
	  getDownloadFileItem() {
	    if (!main_core.Type.isArrayFilled(this.context.files)) {
	      return null;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSingleFile)[_isSingleFile]()) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _getDownloadSingleFileItem)[_getDownloadSingleFileItem]();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDownloadSeveralFilesItem)[_getDownloadSeveralFilesItem]();
	  }
	  getSaveToDiskItem() {
	    if (!main_core.Type.isArrayFilled(this.context.files)) {
	      return null;
	    }
	    const menuItemText = babelHelpers.classPrivateFieldLooseBase(this, _isSingleFile)[_isSingleFile]() ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK_MSGVER_1') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ALL_ON_DISK');
	    return {
	      title: menuItemText,
	      icon: ui_iconSet_api_core.Outline.FOLDER_24,
	      onClick: async function () {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onSaveOnDisk({
	          messageId: this.context.id,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	        await this.diskService.save(this.context.files);
	        im_v2_lib_notifier.Notifier.file.onDiskSaveComplete(babelHelpers.classPrivateFieldLooseBase(this, _isSingleFile)[_isSingleFile]());
	      }.bind(this)
	    };
	  }
	  getAdditionalItems() {
	    const items = this.getNestedItems();
	    if (babelHelpers.classPrivateFieldLooseBase(this, _needNestedMenu)[_needNestedMenu](items)) {
	      return [{
	        title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_MORE'),
	        subMenu: {
	          items,
	          sections: this.getNestedMenuGroups()
	        }
	      }];
	    }
	    return items;
	  }
	  getNestedItems() {
	    const mainGroupItems = [this.getCopyLinkItem(), this.getCopyFileItem(), this.getMarkItem(), this.getFavoriteItem(), this.getSaveToDiskItem()];
	    const createGroupItems = [this.getCreateTaskItem(), this.getCreateMeetingItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$1.main), ...this.groupItems(createGroupItems, MenuSectionCode$1.create), ...this.groupItems(this.getMarketItems(), MenuSectionCode$1.market)];
	  }
	}
	function _needNestedMenu2(additionalItems) {
	  const NESTED_MENU_MIN_ITEMS = 3;
	  const menuItems = additionalItems.filter(item => item !== null);
	  return menuItems.length >= NESTED_MENU_MIN_ITEMS;
	}
	function _isOwnMessage2() {
	  return this.context.authorId === im_v2_application_core.Core.getUserId();
	}
	function _isDeletedMessage2() {
	  return this.context.isDeleted;
	}
	function _getFirstFile2() {
	  return this.store.getters['files/get'](this.context.files[0]);
	}
	function _isSingleFile2() {
	  return this.context.files.length === 1;
	}
	function _isForwardedMessage2() {
	  return main_core.Type.isStringFilled(this.context.forward.id);
	}
	function _isRealMessage2() {
	  return this.store.getters['messages/isRealMessage'](this.context.id);
	}
	async function _onDelete2() {
	  const {
	    id: messageId,
	    dialogId,
	    chatId
	  } = this.context;
	  im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onDelete({
	    messageId,
	    dialogId
	  });
	  this.menuInstance.close();
	  if (await babelHelpers.classPrivateFieldLooseBase(this, _isDeletionCancelled)[_isDeletionCancelled]()) {
	    return;
	  }
	  const messageService = new im_v2_provider_service_message.MessageService({
	    chatId
	  });
	  messageService.deleteMessages([messageId]);
	}
	async function _isDeletionCancelled2() {
	  const {
	    id: messageId,
	    dialogId
	  } = this.context;
	  if (!im_v2_lib_channel.ChannelManager.isChannel(dialogId)) {
	    return false;
	  }
	  const confirmResult = await im_v2_lib_confirm.showDeleteChannelPostConfirm();
	  if (!confirmResult) {
	    im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onCancelDelete({
	      messageId,
	      dialogId
	    });
	    return true;
	  }
	  return false;
	}
	function _getDownloadSingleFileItem2() {
	  const file = babelHelpers.classPrivateFieldLooseBase(this, _getFirstFile)[_getFirstFile]();
	  return {
	    title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILE'),
	    icon: ui_iconSet_api_core.Outline.DOWNLOAD,
	    onClick: function () {
	      im_v2_lib_utils.Utils.file.downloadFiles([file]);
	      im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onFileDownload({
	        messageId: this.context.id,
	        dialogId: this.context.dialogId
	      });
	      this.menuInstance.close();
	    }.bind(this)
	  };
	}
	function _getDownloadSeveralFilesItem2() {
	  const files = this.context.files.map(fileId => {
	    return this.store.getters['files/get'](fileId);
	  });
	  return {
	    title: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILES'),
	    icon: ui_iconSet_api_core.Outline.DOWNLOAD,
	    onClick: async () => {
	      var _this$menuInstance2;
	      im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onFileDownload({
	        messageId: this.context.id,
	        dialogId: this.context.dialogId
	      });
	      im_v2_lib_utils.Utils.file.downloadFiles(files);
	      const needToShowPopup = im_v2_lib_promo.PromoManager.getInstance().needToShow(im_v2_const.PromoId.downloadSeveralFiles);
	      if (needToShowPopup && im_v2_lib_utils.Utils.browser.isChrome() && !im_v2_lib_utils.Utils.platform.isBitrixDesktop()) {
	        var _this$menuInstance;
	        (_this$menuInstance = this.menuInstance) == null ? void 0 : _this$menuInstance.close();
	        await im_v2_lib_confirm.showDownloadAllFilesConfirm();
	        void im_v2_lib_promo.PromoManager.getInstance().markAsWatched(im_v2_const.PromoId.downloadSeveralFiles);
	      }
	      (_this$menuInstance2 = this.menuInstance) == null ? void 0 : _this$menuInstance2.close();
	    }
	  };
	}
	function _arePinsExceedLimit2() {
	  const pins = this.store.getters['messages/pin/getPinned'](this.context.chatId);
	  return pins.length >= this.maxPins;
	}

	const MenuSectionCode$2 = Object.freeze({
	  main: 'main',
	  select: 'select',
	  create: 'create'
	});
	class ChannelMessageMenu extends MessageMenu {
	  getMenuItems() {
	    const mainGroupItems = [this.getCopyItem(), this.getEditItem(), this.getDownloadFileItem(), this.getPinItem(), this.getForwardItem(), ...this.getAdditionalItems(), this.getDeleteItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$2.main), ...this.groupItems([this.getSelectItem()], MenuSectionCode$2.select)];
	  }
	  getNestedMenuGroups() {
	    return [{
	      code: MenuSectionCode$2.main
	    }, {
	      code: MenuSectionCode$2.create
	    }];
	  }
	  getNestedItems() {
	    const mainGroupItems = [this.getCopyLinkItem(), this.getCopyFileItem(), this.getMarkItem(), this.getFavoriteItem(), this.getSaveToDiskItem()];
	    const createGroupItems = [this.getCreateTaskItem(), this.getCreateMeetingItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$2.main), ...this.groupItems(createGroupItems, MenuSectionCode$2.create)];
	  }
	}

	const MenuSectionCode$3 = Object.freeze({
	  main: 'main',
	  select: 'select',
	  file: 'file',
	  open: 'open',
	  create: 'create'
	});
	class CommentsMessageMenu extends MessageMenu {
	  getMenuItems() {
	    const message = this.context;
	    const contextDialogId = this.context.dialogId;
	    if (im_v2_lib_channel.ChannelManager.isCommentsPostMessage(message, contextDialogId)) {
	      const mainGroupItems = [this.getCopyItem(), this.getCopyFileItem()];
	      const fileGroupItems = [this.getDownloadFileItem(), this.getSaveToDiskItem()];
	      return [...this.groupItems(mainGroupItems, MenuSectionCode$3.main), ...this.groupItems(fileGroupItems, MenuSectionCode$3.file), ...this.groupItems([this.getOpenInChannelItem()], MenuSectionCode$3.open)];
	    }
	    return [this.getReplyItem(), this.getCopyItem(), this.getEditItem(), this.getDownloadFileItem(), ...this.getAdditionalItems(), this.getDeleteItem()];
	  }
	  getMenuGroups() {
	    return [{
	      code: MenuSectionCode$3.main
	    }, {
	      code: MenuSectionCode$3.file
	    }, {
	      code: MenuSectionCode$3.open
	    }];
	  }
	  getNestedMenuGroups() {
	    return [{
	      code: MenuSectionCode$3.main
	    }, {
	      code: MenuSectionCode$3.create
	    }];
	  }
	  getNestedItems() {
	    const mainGroupItems = [this.getCopyFileItem(), this.getFavoriteItem(), this.getSaveToDiskItem()];
	    const createGroupItems = [this.getCreateTaskItem(), this.getCreateMeetingItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$3.main), ...this.groupItems(createGroupItems, MenuSectionCode$3.create)];
	  }
	  getOpenInChannelItem() {
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_COMMENTS_OPEN_IN_CHANNEL'),
	      icon: ui_iconSet_api_core.Outline.GO_TO_MESSAGE,
	      onClick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.closeComments);
	        this.menuInstance.close();
	      }
	    };
	  }
	}

	const MenuSectionCode$4 = Object.freeze({
	  main: 'main',
	  select: 'select',
	  create: 'create',
	  market: 'market'
	});
	var _openForm = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openForm");
	class CopilotMessageMenu extends MessageMenu {
	  constructor(...args) {
	    super(...args);
	    Object.defineProperty(this, _openForm, {
	      value: _openForm2
	    });
	  }
	  getMenuItems() {
	    const mainGroupItems = [this.getCopyItem(), this.getMarkItem(), this.getFavoriteItem(), this.getForwardItem(), this.getSendFeedbackItem(), this.getDeleteItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$4.main), ...this.groupItems([this.getSelectItem()], MenuSectionCode$4.select)];
	  }
	  getMenuGroups() {
	    return [{
	      code: MenuSectionCode$4.main
	    }, {
	      code: MenuSectionCode$4.select
	    }];
	  }
	  getSendFeedbackItem() {
	    const copilotManager = new im_v2_lib_copilot.CopilotManager();
	    if (!copilotManager.isCopilotBot(this.context.authorId)) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_AI_ASSISTANT_FEEDBACK'),
	      icon: ui_iconSet_api_core.Outline.FEEDBACK,
	      onClick: () => {
	        im_v2_lib_analytics.Analytics.getInstance().messageContextMenu.onSendFeedback(this.context.dialogId);
	        void babelHelpers.classPrivateFieldLooseBase(this, _openForm)[_openForm]();
	        this.menuInstance.close();
	      }
	    };
	  }
	  getUserCounter() {
	    const chat = this.store.getters['chats/get'](this.context.dialogId);
	    return chat.userCounter;
	  }
	}
	async function _openForm2() {
	  void new im_v2_lib_feedback.FeedbackManager().openCopilotForm({
	    userCounter: this.getUserCounter(),
	    text: this.context.text
	  });
	}

	const MenuSectionCode$5 = Object.freeze({
	  main: 'main',
	  select: 'select',
	  create: 'create',
	  market: 'market'
	});
	class AiAssistantMessageMenu extends MessageMenu {
	  getMenuItems() {
	    const mainGroupItems = [this.getCopyItem(), this.getDownloadFileItem(), this.getForwardItem(), ...this.getAdditionalItems()];
	    return this.groupItems(mainGroupItems, MenuSectionCode$5.main);
	  }
	  getNestedItems() {
	    const mainGroupItems = [this.getCopyFileItem(), this.getMarkItem(), this.getFavoriteItem(), this.getSaveToDiskItem()];
	    const createGroupItems = [this.getSendFeedbackItem(), this.getCreateTaskItem(), this.getCreateMeetingItem()];
	    return [...this.groupItems(mainGroupItems, MenuSectionCode$5.main), ...this.groupItems(createGroupItems, MenuSectionCode$5.create), ...this.groupItems(this.getMarketItems(), MenuSectionCode$5.market)];
	  }
	  getSendFeedbackItem() {
	    const isAiAssistantBot = im_v2_application_core.Core.getStore().getters['users/bots/isAiAssistant'](this.context.authorId);
	    if (!isAiAssistantBot) {
	      return null;
	    }
	    return {
	      title: main_core.Loc.getMessage('IM_LIB_MENU_AI_ASSISTANT_FEEDBACK'),
	      icon: ui_iconSet_api_core.Outline.FEEDBACK,
	      onClick: () => {
	        void new im_v2_lib_feedback.FeedbackManager().openAiAssistantForm();
	        this.menuInstance.close();
	      }
	    };
	  }
	}

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _defaultMenuByCallback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("defaultMenuByCallback");
	var _customMenuByCallback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("customMenuByCallback");
	var _menuByMessageType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menuByMessageType");
	var _menuInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("menuInstance");
	var _resolveMenuClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("resolveMenuClass");
	var _registerDefaultMenus = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("registerDefaultMenus");
	var _isCustomMenuAllowed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCustomMenuAllowed");
	var _getDefaultMenuClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDefaultMenuClass");
	var _getCustomMenuClass = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getCustomMenuClass");
	var _getClassByMap = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getClassByMap");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	var _isChannel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChannel");
	var _isComment = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isComment");
	var _isCopilot = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCopilot");
	var _isAiAssistant = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isAiAssistant");
	var _hasMenuForMessageType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasMenuForMessageType");
	var _getMenuForMessageType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMenuForMessageType");
	var _destroyMenuInstance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("destroyMenuInstance");
	class MessageMenuManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new MessageMenuManager();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  constructor() {
	    Object.defineProperty(this, _destroyMenuInstance, {
	      value: _destroyMenuInstance2
	    });
	    Object.defineProperty(this, _getMenuForMessageType, {
	      value: _getMenuForMessageType2
	    });
	    Object.defineProperty(this, _hasMenuForMessageType, {
	      value: _hasMenuForMessageType2
	    });
	    Object.defineProperty(this, _isAiAssistant, {
	      value: _isAiAssistant2
	    });
	    Object.defineProperty(this, _isCopilot, {
	      value: _isCopilot2
	    });
	    Object.defineProperty(this, _isComment, {
	      value: _isComment2
	    });
	    Object.defineProperty(this, _isChannel, {
	      value: _isChannel2
	    });
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _getClassByMap, {
	      value: _getClassByMap2
	    });
	    Object.defineProperty(this, _getCustomMenuClass, {
	      value: _getCustomMenuClass2
	    });
	    Object.defineProperty(this, _getDefaultMenuClass, {
	      value: _getDefaultMenuClass2
	    });
	    Object.defineProperty(this, _isCustomMenuAllowed, {
	      value: _isCustomMenuAllowed2
	    });
	    Object.defineProperty(this, _registerDefaultMenus, {
	      value: _registerDefaultMenus2
	    });
	    Object.defineProperty(this, _resolveMenuClass, {
	      value: _resolveMenuClass2
	    });
	    Object.defineProperty(this, _defaultMenuByCallback, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _customMenuByCallback, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _menuByMessageType, {
	      writable: true,
	      value: new Map()
	    });
	    Object.defineProperty(this, _menuInstance, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _registerDefaultMenus)[_registerDefaultMenus]();
	  }
	  openMenu(context, bindElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _destroyMenuInstance)[_destroyMenuInstance]();
	    const MenuClass = babelHelpers.classPrivateFieldLooseBase(this, _resolveMenuClass)[_resolveMenuClass](context);
	    babelHelpers.classPrivateFieldLooseBase(this, _menuInstance)[_menuInstance] = new MenuClass();
	    babelHelpers.classPrivateFieldLooseBase(this, _menuInstance)[_menuInstance].openMenu(context, bindElement);
	  }
	  registerMenuByCallback(callback, menuClass) {
	    babelHelpers.classPrivateFieldLooseBase(this, _customMenuByCallback)[_customMenuByCallback].set(callback, menuClass);
	  }
	  unregisterMenuByCallback(callback) {
	    babelHelpers.classPrivateFieldLooseBase(this, _customMenuByCallback)[_customMenuByCallback].delete(callback);
	  }
	  registerMenuByMessageType(messageType, menuClass) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasMenuForMessageType)[_hasMenuForMessageType](messageType)) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _menuByMessageType)[_menuByMessageType].set(messageType, menuClass);
	  }
	}
	function _resolveMenuClass2(context) {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _isCustomMenuAllowed)[_isCustomMenuAllowed](context)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDefaultMenuClass)[_getDefaultMenuClass](context);
	  }
	  const customMenu = babelHelpers.classPrivateFieldLooseBase(this, _getCustomMenuClass)[_getCustomMenuClass](context);
	  return customMenu != null ? customMenu : babelHelpers.classPrivateFieldLooseBase(this, _getDefaultMenuClass)[_getDefaultMenuClass](context);
	}
	function _registerDefaultMenus2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _defaultMenuByCallback)[_defaultMenuByCallback].set(babelHelpers.classPrivateFieldLooseBase(this, _isChannel)[_isChannel].bind(this), ChannelMessageMenu);
	  babelHelpers.classPrivateFieldLooseBase(this, _defaultMenuByCallback)[_defaultMenuByCallback].set(babelHelpers.classPrivateFieldLooseBase(this, _isComment)[_isComment].bind(this), CommentsMessageMenu);
	  babelHelpers.classPrivateFieldLooseBase(this, _defaultMenuByCallback)[_defaultMenuByCallback].set(babelHelpers.classPrivateFieldLooseBase(this, _isCopilot)[_isCopilot].bind(this), CopilotMessageMenu);
	  babelHelpers.classPrivateFieldLooseBase(this, _defaultMenuByCallback)[_defaultMenuByCallback].set(babelHelpers.classPrivateFieldLooseBase(this, _isAiAssistant)[_isAiAssistant].bind(this), AiAssistantMessageMenu);
	}
	function _isCustomMenuAllowed2(context) {
	  return !im_v2_lib_channel.ChannelManager.isCommentsPostMessage(context, context.dialogId);
	}
	function _getDefaultMenuClass2(context) {
	  const MenuClass = babelHelpers.classPrivateFieldLooseBase(this, _getClassByMap)[_getClassByMap](babelHelpers.classPrivateFieldLooseBase(this, _defaultMenuByCallback)[_defaultMenuByCallback], context);
	  return MenuClass != null ? MenuClass : MessageMenu;
	}
	function _getCustomMenuClass2(context) {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _hasMenuForMessageType)[_hasMenuForMessageType](context.componentId)) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getMenuForMessageType)[_getMenuForMessageType](context.componentId);
	  }
	  return babelHelpers.classPrivateFieldLooseBase(this, _getClassByMap)[_getClassByMap](babelHelpers.classPrivateFieldLooseBase(this, _customMenuByCallback)[_customMenuByCallback], context);
	}
	function _getClassByMap2(menuMap, context) {
	  const menuMapEntries = menuMap.entries();
	  for (const [callback, MenuClass] of menuMapEntries) {
	    if (callback(context)) {
	      return MenuClass;
	    }
	  }
	  return null;
	}
	function _getDialog2(dialogId) {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	}
	function _isChannel2(context) {
	  return im_v2_lib_channel.ChannelManager.isChannel(context.dialogId);
	}
	function _isComment2(context) {
	  const chat = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog](context.dialogId);
	  return chat.type === im_v2_const.ChatType.comment;
	}
	function _isCopilot2(context) {
	  return new im_v2_lib_copilot.CopilotManager().isCopilotChat(context.dialogId);
	}
	function _isAiAssistant2(context) {
	  return im_v2_application_core.Core.getStore().getters['users/bots/isAiAssistant'](context.dialogId);
	}
	function _hasMenuForMessageType2(messageType) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _menuByMessageType)[_menuByMessageType].has(messageType);
	}
	function _getMenuForMessageType2(messageType) {
	  return babelHelpers.classPrivateFieldLooseBase(this, _menuByMessageType)[_menuByMessageType].get(messageType);
	}
	function _destroyMenuInstance2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _menuInstance)[_menuInstance]) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _menuInstance)[_menuInstance].destroy();
	  babelHelpers.classPrivateFieldLooseBase(this, _menuInstance)[_menuInstance] = null;
	}
	Object.defineProperty(MessageMenuManager, _instance, {
	  writable: true,
	  value: null
	});

	exports.BaseMenu = BaseMenu;
	exports.RecentMenu = RecentMenu;
	exports.UserMenu = UserMenu;
	exports.MessageMenuManager = MessageMenuManager;
	exports.MessageMenu = MessageMenu;
	exports.AiAssistantMessageMenu = AiAssistantMessageMenu;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Vue3.Vuex,BX,BX.UI.Dialogs,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.UI.System,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.UI.IconSet,BX.Messenger.v2.Application,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
