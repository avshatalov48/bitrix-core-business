/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_lib_quote,im_v2_component_animation,im_v2_lib_messageComponentManager,im_v2_lib_promo,im_v2_lib_channel,im_v2_lib_menu,im_v2_lib_parser,im_v2_lib_entityCreator,im_v2_provider_service,im_v2_lib_market,im_v2_lib_utils,im_v2_lib_confirm,ui_notification,main_polyfill_intersectionobserver,im_v2_component_elements,im_v2_lib_copilot,im_v2_lib_permission,main_core,main_core_events,im_v2_lib_analytics,im_v2_lib_feature,im_v2_application_core,im_v2_const,im_v2_lib_dateFormatter,im_v2_component_message_file,im_v2_component_message_default,im_v2_component_message_callInvite,im_v2_component_message_deleted,im_v2_component_message_unsupported,im_v2_component_message_smile,im_v2_component_message_system,im_v2_component_message_chatCreation,im_v2_component_message_copilot_creation,im_v2_component_message_copilot_answer,im_v2_component_message_copilot_addedUsers,im_v2_component_message_support_vote,im_v2_component_message_support_sessionNumber,im_v2_component_message_support_chatCreation,im_v2_component_message_conferenceCreation,im_v2_component_message_supervisor_updateFeature,im_v2_component_message_supervisor_enableFeature,im_v2_component_message_sign,im_v2_component_message_checkIn,im_v2_component_message_ownChatCreation,im_v2_component_message_zoomInvite,im_v2_component_message_generalChatCreation,im_v2_component_message_generalChannelCreation,im_v2_component_message_channelCreation,imopenlines_v2_component_message_startDialog,imopenlines_v2_component_message_hidden,imopenlines_v2_component_message_feedbackForm,im_v2_component_message_call,im_v2_component_message_vote) {
	'use strict';

	// @vue/component
	const DialogLoader = {
	  name: 'DialogLoader',
	  props: {
	    fullHeight: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-loader__container" :class="{'--full-height': fullHeight}">
			<div class="bx-im-dialog-loader__spinner"></div>
			<div class="bx-im-dialog-loader__text">{{ loc('IM_DIALOG_CHAT_LOADER_TEXT') }}</div>
		</div>
	`
	};

	class AvatarMenu extends im_v2_lib_menu.UserMenu {
	  constructor() {
	    super();
	    this.id = 'bx-im-avatar-context-menu';
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 21
	    };
	  }
	  getMenuItems() {
	    return [this.getMentionItem(), this.getSendItem(), this.getProfileItem(), this.getKickItem()];
	  }
	}

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
	class MessageMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
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
	    this.id = 'bx-im-message-context-menu';
	    this.diskService = new im_v2_provider_service.DiskService();
	    this.marketManager = im_v2_lib_market.MarketManager.getInstance();
	  }
	  getMenuOptions() {
	    return {
	      ...super.getMenuOptions(),
	      className: this.getMenuClassName(),
	      angle: true,
	      offsetLeft: 11
	    };
	  }
	  getMenuItems() {
	    return [this.getReplyItem(), this.getCopyItem(), this.getCopyLinkItem(), this.getCopyFileItem(), this.getPinItem(), this.getForwardItem(), this.getDelimiter(), this.getMarkItem(), this.getFavoriteItem(), this.getDelimiter(), this.getCreateItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDiskItem(), this.getDelimiter(), this.getEditItem(), this.getDeleteItem(), this.getDelimiter(), this.getSelectItem()];
	  }
	  getSelectItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || !babelHelpers.classPrivateFieldLooseBase(this, _isRealMessage)[_isRealMessage]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SELECT'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.openBulkActionsMode, {
	          messageId: this.context.id
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getReplyItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REPLY'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.replyMessage, {
	          messageId: this.context.id,
	          dialogId: this.context.dialogId
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getForwardItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]() || !babelHelpers.classPrivateFieldLooseBase(this, _isRealMessage)[_isRealMessage]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_FORWARD'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.showForwardPopup, {
	          messagesIds: [this.context.id]
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyItem() {
	    if (this.context.text.trim().length === 0) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY'),
	      onclick: async () => {
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopy(this.context);
	        await im_v2_lib_utils.Utils.text.copyToClipboard(textToCopy);
	        BX.UI.Notification.Center.notify({
	          content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_SUCCESS')
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyLinkItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_LINK_MSGVER_1'),
	      onclick: () => {
	        var _BX$clipboard;
	        const textToCopy = im_v2_lib_utils.Utils.text.getMessageLink(this.context.dialogId, this.context.id);
	        if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(textToCopy)) {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_LINK_SUCCESS')
	          });
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
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE'),
	      onclick: () => {
	        var _BX$clipboard2;
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopyFile(this.context);
	        if ((_BX$clipboard2 = BX.clipboard) != null && _BX$clipboard2.copy(textToCopy)) {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE_SUCCESS')
	          });
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
	      text: isPinned ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_UNPIN') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_PIN'),
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        if (isPinned) {
	          messageService.unpinMessage(this.context.chatId, this.context.id);
	        } else {
	          messageService.pinMessage(this.context.chatId, this.context.id);
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
	      text: menuItemText,
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        if (isInFavorite) {
	          messageService.removeMessageFromFavorite(this.context.id);
	        } else {
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
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_MARK'),
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        messageService.markMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE'),
	      items: [this.getCreateTaskItem(), this.getCreateMeetingItem(), ...this.getMarketItems()]
	    };
	  }
	  getCreateTaskItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_TASK'),
	      onclick: () => {
	        const entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.context.chatId);
	        void entityCreator.createTaskForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateMeetingItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_MEETING'),
	      onclick: () => {
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
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_EDIT'),
	      onclick: () => {
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
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DELETE'),
	      className: 'menu-popup-no-icon bx-im-dialog-chat__message-menu_delete',
	      onclick: babelHelpers.classPrivateFieldLooseBase(this, _onDelete)[_onDelete].bind(this)
	    };
	  }
	  getMarketItems() {
	    const {
	      dialogId,
	      id
	    } = this.context;
	    const placements = this.marketManager.getAvailablePlacementsByType(im_v2_const.PlacementType.contextMenu, dialogId);
	    const marketMenuItem = [];
	    if (placements.length > 0) {
	      marketMenuItem.push(this.getDelimiter());
	    }
	    const context = {
	      messageId: id,
	      dialogId
	    };
	    placements.forEach(placement => {
	      marketMenuItem.push({
	        text: placement.title,
	        onclick: () => {
	          im_v2_lib_market.MarketManager.openSlider(placement, context);
	          this.menuInstance.close();
	        }
	      });
	    });

	    // (10 items + 1 delimiter), because we don't want to show long context menu.
	    const itemLimit = 11;
	    return marketMenuItem.slice(0, itemLimit);
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
	    const successNotification = babelHelpers.classPrivateFieldLooseBase(this, _isSingleFile)[_isSingleFile]() ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK_SUCCESS_MSGVER_1') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ALL_ON_DISK_SUCCESS');
	    return {
	      text: menuItemText,
	      onclick: function () {
	        im_v2_lib_analytics.Analytics.getInstance().messageFiles.onClickSaveOnDisk({
	          messageId: this.context.id,
	          dialogId: this.context.dialogId
	        });
	        void this.diskService.save(this.context.files).then(() => {
	          BX.UI.Notification.Center.notify({
	            content: successNotification
	          });
	        });
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getDelimiter() {
	    return {
	      delimiter: true
	    };
	  }
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
	  im_v2_lib_analytics.Analytics.getInstance().messageDelete.onClickDelete({
	    messageId,
	    dialogId
	  });
	  this.menuInstance.close();
	  if (await babelHelpers.classPrivateFieldLooseBase(this, _isDeletionCancelled)[_isDeletionCancelled]()) {
	    return;
	  }
	  const messageService = new im_v2_provider_service.MessageService({
	    chatId
	  });
	  void messageService.deleteMessage(messageId);
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
	    im_v2_lib_analytics.Analytics.getInstance().messageDelete.onCancel({
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
	    html: im_v2_lib_utils.Utils.file.createDownloadLink(main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILE'), file.urlDownload, file.name),
	    onclick: function () {
	      im_v2_lib_analytics.Analytics.getInstance().messageFiles.onClickDownload({
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
	    text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILES'),
	    onclick: async () => {
	      var _this$menuInstance2;
	      im_v2_lib_analytics.Analytics.getInstance().messageFiles.onClickDownload({
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

	var _dialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	var _observer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observer");
	var _initObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initObserver");
	var _sendVisibleEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendVisibleEvent");
	var _sendNotVisibleEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendNotVisibleEvent");
	var _getThreshold = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getThreshold");
	var _getMessageIdFromElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageIdFromElement");
	class ObserverManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _getMessageIdFromElement, {
	      value: _getMessageIdFromElement2
	    });
	    Object.defineProperty(this, _getThreshold, {
	      value: _getThreshold2
	    });
	    Object.defineProperty(this, _sendNotVisibleEvent, {
	      value: _sendNotVisibleEvent2
	    });
	    Object.defineProperty(this, _sendVisibleEvent, {
	      value: _sendVisibleEvent2
	    });
	    Object.defineProperty(this, _initObserver, {
	      value: _initObserver2
	    });
	    Object.defineProperty(this, _dialogId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _observer, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId] = dialogId;
	    babelHelpers.classPrivateFieldLooseBase(this, _initObserver)[_initObserver]();
	  }
	  observeMessage(messageElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].observe(messageElement);
	  }
	  unobserveMessage(messageElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].unobserve(messageElement);
	  }
	}
	function _initObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer] = new IntersectionObserver(entries => {
	    entries.forEach(entry => {
	      const messageId = babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](entry.target);
	      if (!messageId || !entry.rootBounds) {
	        return;
	      }
	      const messageIsFullyVisible = entry.isIntersecting && entry.intersectionRatio >= 0.99;
	      const messageTakesHalfOfViewport = entry.intersectionRect.height >= entry.rootBounds.height / 2.2;
	      // const messageIsBiggerThanViewport = entry.boundingClientRect.height + 20 > entry.rootBounds.height;
	      // const messageCountsAsVisible = messageIsBiggerThanViewport && messageTakesMostOfViewport;
	      if (messageIsFullyVisible || messageTakesHalfOfViewport) {
	        babelHelpers.classPrivateFieldLooseBase(this, _sendVisibleEvent)[_sendVisibleEvent](messageId);
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _sendNotVisibleEvent)[_sendNotVisibleEvent](messageId);
	      }
	    });
	  }, {
	    threshold: babelHelpers.classPrivateFieldLooseBase(this, _getThreshold)[_getThreshold]()
	  });
	}
	function _sendVisibleEvent2(messageId) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onMessageIsVisible, {
	    messageId,
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]
	  });
	}
	function _sendNotVisibleEvent2(messageId) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onMessageIsNotVisible, {
	    messageId,
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]
	  });
	}
	function _getThreshold2() {
	  const arrayWithZeros = Array.from({
	    length: 101
	  }).fill(0);
	  return arrayWithZeros.map((zero, index) => index * 0.01);
	}
	function _getMessageIdFromElement2(messageElement) {
	  return Number(messageElement.dataset.id);
	}

	// @vue/component
	const DateGroupTitle = {
	  props: {
	    title: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-message-list-date-group-title__container">
			<div class="bx-im-message-list-date-group-title__text">{{ title }}</div>
		</div>
	`
	};

	// @vue/component
	const DateGroup = {
	  name: 'DateGroup',
	  components: {
	    DateGroupTitle
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    BlockType: () => im_v2_const.DialogBlockType,
	    dateGroup() {
	      return this.item;
	    }
	  },
	  template: `
		<div class="bx-im-message-list-date-group__container">
			<DateGroupTitle :title="dateGroup.dateTitle" />
			<template v-for="dateGroupItem in dateGroup.items" >
				<slot
					name="dateGroupItem"
					:dateGroupItem="dateGroupItem"
					:isMarkedBlock="dateGroupItem.type === BlockType.markedMessages"
					:isNewMessagesBlock="dateGroupItem.type === BlockType.newMessages"
					:isAuthorBlock="dateGroupItem.type === BlockType.authorGroup"
				></slot>
			</template>
		</div>
	`
	};

	const forwardMessageComponents = new Set([im_v2_const.MessageComponent.default, im_v2_const.MessageComponent.copilotMessage, im_v2_const.MessageComponent.checkIn, im_v2_const.MessageComponent.FeedbackFormMessage, im_v2_const.MessageComponent.ImOpenLinesMessage, im_v2_const.MessageComponent.ImOpenLinesForm]);

	// @vue/component
	const MessageSelectButton = {
	  name: 'MessageSelectButton',
	  props: {
	    message: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    messageItem() {
	      return this.message;
	    },
	    selectedMessages() {
	      return this.$store.getters['messages/select/getCollection'];
	    },
	    bulkActionMessageLimit() {
	      const settings = main_core.Extension.getSettings('im.v2.component.message-list');
	      return settings.get('multipleActionMessageLimit');
	    },
	    isMessageSelected() {
	      return this.$store.getters['messages/select/isMessageSelected'](this.messageItem.id);
	    },
	    isSelectionLimitReached() {
	      return this.selectedMessages.size === this.bulkActionMessageLimit && !this.isMessageSelected;
	    },
	    isRealMessage() {
	      return this.$store.getters['messages/isRealMessage'](this.messageItem.id);
	    },
	    canSelectMessage() {
	      if (this.messageItem.isDeleted || !this.isRealMessage) {
	        return false;
	      }
	      return forwardMessageComponents.has(this.messageItem.componentId);
	    }
	  },
	  methods: {
	    onSelectMessage() {
	      if (!this.canSelectMessage) {
	        return;
	      }
	      if (this.isSelectionLimitReached) {
	        this.showNotification(this.loc('IM_MESSAGE_LIST_MAX_LIMIT_SELECTED_MESSAGES'));
	        return;
	      }
	      this.$store.dispatch('messages/select/toggle', this.messageItem.id);
	    },
	    showNotification(text) {
	      BX.UI.Notification.Center.notify({
	        content: text
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="onSelectMessage" class="bx-im-message-list-select-button__container">
			<div
				v-if="canSelectMessage"
				class="bx-im-message-list-select-button__checkbox-circle"
				:class="{'--selected': isMessageSelected}"
			/>
		</div>
	`
	};

	// @vue/component
	const AuthorGroup = {
	  name: 'AuthorGroup',
	  components: {
	    MessageAvatar: im_v2_component_elements.MessageAvatar,
	    MessageSelectButton
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    withAvatarMenu: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['avatarClick'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    contextDialog() {
	      return this.$store.getters['chats/get'](this.contextDialogId, true);
	    },
	    contextUser() {
	      return this.$store.getters['users/get'](this.contextDialogId, true);
	    },
	    isUser() {
	      return this.contextDialog.type === im_v2_const.ChatType.user;
	    },
	    isBulkActionsMode() {
	      return this.$store.getters['messages/select/getBulkActionsMode'];
	    },
	    authorGroup() {
	      return this.item;
	    },
	    authorDialogId() {
	      return this.authorGroup.avatar.avatarId;
	    },
	    firstMessageIdInAuthorGroup() {
	      // this is potentially dangerous.
	      // for now, we always have the same avatar in one authorGroup
	      // in future it can be different: several support answers (with different avatars) in one authorGroup
	      return this.authorGroup.messages[0].id;
	    },
	    avatarMenuAvailable() {
	      if (!this.withAvatarMenu) {
	        return false;
	      }
	      const authorUser = this.$store.getters['users/get'](this.authorDialogId);
	      if (!authorUser) {
	        return false;
	      }
	      const copilotManager = new im_v2_lib_copilot.CopilotManager();
	      if (copilotManager.isCopilotBot(this.authorDialogId)) {
	        return false;
	      }
	      const isCurrentUser = authorUser.id === im_v2_application_core.Core.getUserId();
	      if (isCurrentUser) {
	        return false;
	      }
	      const isBotChat = this.isUser && this.contextUser.type === im_v2_const.UserType.bot;
	      if (isBotChat) {
	        return false;
	      }
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      return permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.openAvatarMenu, this.contextDialogId);
	    },
	    containerClasses() {
	      const classes = [`--${this.authorGroup.messageType}`];
	      if (!this.avatarMenuAvailable) {
	        classes.push('--no-menu');
	      }
	      if (this.isBulkActionsMode) {
	        classes.push('--is-bulk-actions-mode');
	      }
	      if (this.authorGroup.avatar.isNeeded) {
	        classes.push('--has-avatar');
	      }
	      return classes;
	    }
	  },
	  methods: {
	    isAvatarNeeded(index) {
	      const lastIndexMessageInGroup = this.authorGroup.messages.length - 1;
	      return this.authorGroup.avatar.isNeeded && index === lastIndexMessageInGroup;
	    },
	    onAvatarClick(event) {
	      if (!this.avatarMenuAvailable) {
	        return;
	      }
	      this.$emit('avatarClick', {
	        dialogId: this.authorGroup.avatar.avatarId,
	        $event: event
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-author-group__container" :class="containerClasses">
			<template v-for="(message, index) in authorGroup.messages">
				<Transition name="bx-im-select-button-transition">
					<MessageSelectButton v-if="isBulkActionsMode" :message="message" />
				</Transition>
				<div v-if="isAvatarNeeded(index)" class="bx-im-message-list-author-group__avatar">
					<MessageAvatar
						:messageId="firstMessageIdInAuthorGroup"
						:authorId="authorGroup.avatar.avatarId"
						:size="AvatarSize.L"
						@click="onAvatarClick"
					/>
				</div>
				<slot name="message" :message="message" :index="index"></slot>
			</template>
		</div>
	`
	};

	// @vue/component
	const NewMessagesBlock = {
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-new-message__container">
			<div class="bx-im-message-list-new-message__text">
				{{ loc('IM_DIALOG_CHAT_BLOCK_NEW_MESSAGES_2') }}
			</div>
		</div>
	`
	};

	// @vue/component
	const MarkedMessagesBlock = {
	  data() {
	    return {};
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-new-message__container">
			<div class="bx-im-message-list-new-message__text">
				{{ loc('IM_DIALOG_CHAT_BLOCK_MARKED_MESSAGES') }}
			</div>
		</div>
	`
	};

	const defaultMessages = [main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_1'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_2'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_3'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_4'), main_core.Loc.getMessage('IM_MESSAGE_LIST_EMPTY_STATE_DEFAULT_MESSAGE_5')];

	// @vue/component
	const EmptyState = {
	  name: 'EmptyState',
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    defaultMessages: () => defaultMessages
	  },
	  methods: {
	    onMessageClick(text) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	        text,
	        dialogId: this.dialogId
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-message-list-empty-state__container">
			<div class="bx-im-message-list-empty-state__content">
				<div class="bx-im-message-list-empty-state__icon"></div>
				<div class="bx-im-message-list-empty-state__title">{{ loc('IM_MESSAGE_LIST_EMPTY_STATE_TITLE') }}</div>
				<div class="bx-im-message-list-empty-state__action-list">
					<div
						v-for="(message, index) in defaultMessages"
						:key="index"
						@click="onMessageClick(message)"
						class="bx-im-message-list-empty-state__action-list_item"
					>
						{{ message }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const HistoryLimitBanner = {
	  name: 'HistoryLimitBanner',
	  props: {
	    noMessages: {
	      type: Boolean,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    title() {
	      return im_v2_lib_feature.FeatureManager.chatHistory.getLimitTitle();
	    },
	    subtitle() {
	      return im_v2_lib_feature.FeatureManager.chatHistory.getLimitSubtitle();
	    },
	    buttonText() {
	      return im_v2_lib_feature.FeatureManager.chatHistory.getLearnMoreText();
	    }
	  },
	  mounted() {
	    this.sendAnalytics();
	  },
	  methods: {
	    onButtonClick() {
	      im_v2_lib_analytics.Analytics.getInstance().historyLimit.onDialogBannerClick({
	        dialogId: this.dialogId
	      });
	      im_v2_lib_feature.FeatureManager.chatHistory.openFeatureSlider();
	    },
	    sendAnalytics() {
	      im_v2_lib_analytics.Analytics.getInstance().historyLimit.onDialogLimitExceeded({
	        dialogId: this.dialogId,
	        noMessages: this.noMessages
	      });
	    }
	  },
	  // language=Vue
	  template: `
		<div class="bx-im-message-list-history-banner__container" :class="{'--no-messages': noMessages}">
			<div class="bx-im-message-list-history-banner__left">
				<div class="bx-im-message-list-history-banner__title">
					<div class="bx-im-message-list-history-banner__icon bx-im-messenger__lock-icon"></div>
					<div class="bx-im-message-list-history-banner__title_text --ellipsis" :title="title">
						{{ title }}
					</div>
				</div>
				<div class="bx-im-message-list-history-banner__subtitle --line-clamp-2" :title="subtitle">
					{{ subtitle }}
				</div>
			</div>
			<div class="bx-im-message-list-history-banner__right">
				<div class="bx-im-message-list-history-banner__button" @click="onButtonClick">
					{{ buttonText }}
				</div>
			</div>
		</div>
	`
	};

	var _getAvatarConfig = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getAvatarConfig");
	var _getMessageType = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageType");
	var _checkIfAvatarIsNeeded = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("checkIfAvatarIsNeeded");
	class BlockManager {
	  constructor() {
	    Object.defineProperty(this, _checkIfAvatarIsNeeded, {
	      value: _checkIfAvatarIsNeeded2
	    });
	    Object.defineProperty(this, _getMessageType, {
	      value: _getMessageType2
	    });
	    Object.defineProperty(this, _getAvatarConfig, {
	      value: _getAvatarConfig2
	    });
	  }
	  getAuthorBlock(message) {
	    return {
	      type: im_v2_const.DialogBlockType.authorGroup,
	      userId: message.authorId,
	      avatar: babelHelpers.classPrivateFieldLooseBase(this, _getAvatarConfig)[_getAvatarConfig](message),
	      messageType: babelHelpers.classPrivateFieldLooseBase(this, _getMessageType)[_getMessageType](message)
	    };
	  }
	  getMarkedBlock() {
	    return {
	      type: im_v2_const.DialogBlockType.markedMessages
	    };
	  }
	  getNewMessagesBlock() {
	    return {
	      type: im_v2_const.DialogBlockType.newMessages
	    };
	  }
	}
	function _getAvatarConfig2(message) {
	  return {
	    isNeeded: babelHelpers.classPrivateFieldLooseBase(this, _checkIfAvatarIsNeeded)[_checkIfAvatarIsNeeded](message),
	    avatarId: message.authorId.toString()
	  };
	}
	function _getMessageType2(message) {
	  if (!message.authorId) {
	    return im_v2_const.MessageType.system;
	  }
	  if (message.authorId === im_v2_application_core.Core.getUserId()) {
	    return im_v2_const.MessageType.self;
	  }
	  return im_v2_const.MessageType.opponent;
	}
	function _checkIfAvatarIsNeeded2(message) {
	  const messageType = babelHelpers.classPrivateFieldLooseBase(this, _getMessageType)[_getMessageType](message);
	  const isSystem = messageType === im_v2_const.MessageType.system;
	  if (isSystem) {
	    return false;
	  }
	  const isSelf = messageType === im_v2_const.MessageType.self;
	  const alignment = im_v2_application_core.Core.getStore().getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	  if (alignment === im_v2_const.DialogAlignment.center) {
	    return !isSelf;
	  }
	  return true;
	}

	var _blockManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("blockManager");
	var _collection = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("collection");
	var _currentDateTitles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentDateTitles");
	var _markedIndicatorInserted = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("markedIndicatorInserted");
	var _lastDateItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastDateItems");
	var _lastAuthorId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastAuthorId");
	var _lastAuthorItems = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("lastAuthorItems");
	var _clearLastAuthor = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("clearLastAuthor");
	class Collection {
	  constructor() {
	    Object.defineProperty(this, _clearLastAuthor, {
	      value: _clearLastAuthor2
	    });
	    Object.defineProperty(this, _blockManager, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _collection, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _currentDateTitles, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _markedIndicatorInserted, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _lastDateItems, {
	      writable: true,
	      value: []
	    });
	    Object.defineProperty(this, _lastAuthorId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _lastAuthorItems, {
	      writable: true,
	      value: []
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _blockManager)[_blockManager] = new BlockManager();
	  }
	  get() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection];
	  }
	  hasDateTitle(dateTitle) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _currentDateTitles)[_currentDateTitles].has(dateTitle);
	  }
	  addDateGroup(dateTitle) {
	    babelHelpers.classPrivateFieldLooseBase(this, _currentDateTitles)[_currentDateTitles].add(dateTitle);
	    babelHelpers.classPrivateFieldLooseBase(this, _lastDateItems)[_lastDateItems] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _collection)[_collection].push({
	      dateTitle,
	      items: babelHelpers.classPrivateFieldLooseBase(this, _lastDateItems)[_lastDateItems]
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _clearLastAuthor)[_clearLastAuthor]();
	  }
	  addAuthorGroup(message) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastAuthorId)[_lastAuthorId] = message.authorId;
	    babelHelpers.classPrivateFieldLooseBase(this, _lastAuthorItems)[_lastAuthorItems] = [];
	    babelHelpers.classPrivateFieldLooseBase(this, _lastDateItems)[_lastDateItems].push({
	      ...babelHelpers.classPrivateFieldLooseBase(this, _blockManager)[_blockManager].getAuthorBlock(message),
	      messages: babelHelpers.classPrivateFieldLooseBase(this, _lastAuthorItems)[_lastAuthorItems]
	    });
	  }
	  addMessage(message) {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastAuthorItems)[_lastAuthorItems].push(message);
	  }
	  addMarkedIndicator() {
	    babelHelpers.classPrivateFieldLooseBase(this, _lastDateItems)[_lastDateItems].push(babelHelpers.classPrivateFieldLooseBase(this, _blockManager)[_blockManager].getMarkedBlock());
	    babelHelpers.classPrivateFieldLooseBase(this, _markedIndicatorInserted)[_markedIndicatorInserted] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _clearLastAuthor)[_clearLastAuthor]();
	  }
	  addNewMessagesIndicator() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _markedIndicatorInserted)[_markedIndicatorInserted]) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _lastDateItems)[_lastDateItems].push(babelHelpers.classPrivateFieldLooseBase(this, _blockManager)[_blockManager].getNewMessagesBlock());
	    babelHelpers.classPrivateFieldLooseBase(this, _clearLastAuthor)[_clearLastAuthor]();
	  }
	  getLastAuthorId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _lastAuthorId)[_lastAuthorId];
	  }
	}
	function _clearLastAuthor2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _lastAuthorId)[_lastAuthorId] = null;
	}

	var _getLocalShortDate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLocalShortDate");
	class DateManager {
	  constructor() {
	    Object.defineProperty(this, _getLocalShortDate, {
	      value: _getLocalShortDate2
	    });
	    this.cachedDateGroups = {};
	  }
	  getDateTitle(date) {
	    const shortDate = babelHelpers.classPrivateFieldLooseBase(this, _getLocalShortDate)[_getLocalShortDate](date);
	    if (this.cachedDateGroups[shortDate]) {
	      return this.cachedDateGroups[shortDate];
	    }
	    this.cachedDateGroups[shortDate] = im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.dateGroup);
	    return this.cachedDateGroups[shortDate];
	  }
	}
	function _getLocalShortDate2(date) {
	  const timestampWithTimezoneOffset = date.getTime() - date.getTimezoneOffset() * 60000;
	  const localDateInJSON = new Date(timestampWithTimezoneOffset).toJSON();

	  // 2022-10-25T14:58:44.000Z => 2022-10-25
	  const INDEX_BETWEEN_DATE_AND_TIME = 10;
	  return localDateInJSON.slice(0, INDEX_BETWEEN_DATE_AND_TIME);
	}

	var _setInitialValues = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setInitialValues");
	var _handleMarkedMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleMarkedMessageId");
	var _getLastReadMessageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getLastReadMessageId");
	var _getDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDialog");
	class CollectionManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _getDialog, {
	      value: _getDialog2
	    });
	    Object.defineProperty(this, _getLastReadMessageId, {
	      value: _getLastReadMessageId2
	    });
	    Object.defineProperty(this, _handleMarkedMessageId, {
	      value: _handleMarkedMessageId2
	    });
	    Object.defineProperty(this, _setInitialValues, {
	      value: _setInitialValues2
	    });
	    this.firstIteration = true;
	    this.dialogId = dialogId;
	    this.dateManager = new DateManager();
	  }
	  formatMessageCollection(messageCollection) {
	    /*
	    Collection
	    ├── Date Group
	    │   ├── Marked Message Indicator
	    │   ├── Author Group
	    │   │   └── Messages
	    │   └── New Messages Indicator
	    */

	    const collection = new Collection();
	    babelHelpers.classPrivateFieldLooseBase(this, _setInitialValues)[_setInitialValues]();
	    babelHelpers.classPrivateFieldLooseBase(this, _handleMarkedMessageId)[_handleMarkedMessageId]();
	    messageCollection.forEach((message, index) => {
	      const dateTitle = this.dateManager.getDateTitle(message.date);
	      if (!collection.hasDateTitle(dateTitle)) {
	        collection.addDateGroup(dateTitle);
	      }
	      if (message.id === this.markedMessageId) {
	        collection.addMarkedIndicator();
	      }
	      if (message.authorId !== collection.getLastAuthorId()) {
	        collection.addAuthorGroup(message);
	      }
	      collection.addMessage(message);
	      const isLastMessage = index === messageCollection.length - 1;
	      if (!isLastMessage && message.id === this.lastReadMessageId) {
	        collection.addNewMessagesIndicator();
	      }
	    });
	    const {
	      inited
	    } = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]();
	    if (inited) {
	      this.firstIteration = false;
	    }
	    return collection.get();
	  }
	  formatAuthorGroup(message) {
	    const blockManager = new BlockManager();
	    return {
	      ...blockManager.getAuthorBlock(message),
	      messages: [message]
	    };
	  }
	}
	function _setInitialValues2() {
	  if (!this.firstIteration) {
	    return;
	  }
	  const {
	    markedId
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]();
	  this.lastReadMessageId = babelHelpers.classPrivateFieldLooseBase(this, _getLastReadMessageId)[_getLastReadMessageId]();
	  this.markedMessageId = markedId;
	}
	function _handleMarkedMessageId2() {
	  const {
	    markedId
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]();
	  if (markedId === this.markedMessageId || markedId === 0) {
	    return;
	  }

	  // if mark was set after chat load - remember marked message and remove "new messages" block
	  this.markedMessageId = markedId;
	  this.lastReadMessageId = null;
	}
	function _getLastReadMessageId2() {
	  const {
	    lastMessageId
	  } = babelHelpers.classPrivateFieldLooseBase(this, _getDialog)[_getDialog]();
	  const lastReadId = im_v2_application_core.Core.getStore().getters['chats/getLastReadId'](this.dialogId);
	  if (lastReadId === lastMessageId) {
	    return 0;
	  }
	  return lastReadId;
	}
	function _getDialog2() {
	  return im_v2_application_core.Core.getStore().getters['chats/get'](this.dialogId);
	}

	const MessageComponents = {
	  DefaultMessage: im_v2_component_message_default.DefaultMessage,
	  FileMessage: im_v2_component_message_file.FileMessage,
	  SmileMessage: im_v2_component_message_smile.SmileMessage,
	  CallInviteMessage: im_v2_component_message_callInvite.CallInviteMessage,
	  DeletedMessage: im_v2_component_message_deleted.DeletedMessage,
	  SystemMessage: im_v2_component_message_system.SystemMessage,
	  UnsupportedMessage: im_v2_component_message_unsupported.UnsupportedMessage,
	  ChatCreationMessage: im_v2_component_message_chatCreation.ChatCreationMessage,
	  OwnChatCreationMessage: im_v2_component_message_ownChatCreation.OwnChatCreationMessage,
	  ChatCopilotCreationMessage: im_v2_component_message_copilot_creation.ChatCopilotCreationMessage,
	  CopilotMessage: im_v2_component_message_copilot_answer.CopilotMessage,
	  SupportVoteMessage: im_v2_component_message_support_vote.SupportVoteMessage,
	  SupportSessionNumberMessage: im_v2_component_message_support_sessionNumber.SupportSessionNumberMessage,
	  SupportChatCreationMessage: im_v2_component_message_support_chatCreation.SupportChatCreationMessage,
	  ConferenceCreationMessage: im_v2_component_message_conferenceCreation.ConferenceCreationMessage,
	  ZoomInviteMessage: im_v2_component_message_zoomInvite.ZoomInviteMessage,
	  CheckInMessage: im_v2_component_message_checkIn.CheckInMessage,
	  SupervisorUpdateFeatureMessage: im_v2_component_message_supervisor_updateFeature.SupervisorUpdateFeatureMessage,
	  SupervisorEnableFeatureMessage: im_v2_component_message_supervisor_enableFeature.SupervisorEnableFeatureMessage,
	  ChatCopilotAddedUsersMessage: im_v2_component_message_copilot_addedUsers.ChatCopilotAddedUsersMessage,
	  SignMessage: im_v2_component_message_sign.SignMessage,
	  GeneralChatCreationMessage: im_v2_component_message_generalChatCreation.GeneralChatCreationMessage,
	  GeneralChannelCreationMessage: im_v2_component_message_generalChannelCreation.GeneralChannelCreationMessage,
	  ChannelCreationMessage: im_v2_component_message_channelCreation.ChannelCreationMessage,
	  CallMessage: im_v2_component_message_call.CallMessage,
	  StartDialogMessage: imopenlines_v2_component_message_startDialog.StartDialogMessage,
	  FeedbackFormMessage: imopenlines_v2_component_message_feedbackForm.FeedbackFormMessage,
	  HiddenMessage: imopenlines_v2_component_message_hidden.HiddenMessage,
	  VoteMessage: im_v2_component_message_vote.VoteMessage
	};

	// @vue/component
	const MessageList = {
	  name: 'MessageList',
	  directives: {
	    'message-observer': {
	      mounted(element, binding) {
	        binding.instance.observer.observeMessage(element);
	      },
	      beforeUnmount(element, binding) {
	        binding.instance.observer.unobserveMessage(element);
	      }
	    }
	  },
	  components: {
	    DateGroup,
	    AuthorGroup,
	    NewMessagesBlock,
	    MarkedMessagesBlock,
	    DialogStatus: im_v2_component_elements.DialogStatus,
	    DialogLoader,
	    EmptyState,
	    FadeAnimation: im_v2_component_animation.FadeAnimation,
	    HistoryLimitBanner,
	    ...MessageComponents
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    messageMenuClass: {
	      type: Function,
	      default: MessageMenu
	    }
	  },
	  data() {
	    return {
	      windowFocused: false,
	      messageMenuIsActiveForId: 0
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    messageCollection() {
	      return this.$store.getters['messages/getByChatId'](this.dialog.chatId);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    formattedCollection() {
	      if (!this.dialogInited && this.messageCollection.length === 0) {
	        return [];
	      }
	      return this.getCollectionManager().formatMessageCollection(this.messageCollection);
	    },
	    noMessages() {
	      return this.formattedCollection.length === 0;
	    },
	    isHistoryLimitExceeded() {
	      return !im_v2_lib_feature.FeatureManager.chatHistory.isAvailable() && this.dialog.tariffRestrictions.isHistoryLimitExceeded;
	    },
	    showDialogStatus() {
	      return this.messageCollection.some(message => {
	        return message.id === this.dialog.lastMessageId;
	      });
	    },
	    showEmptyState() {
	      return this.dialogInited && this.noMessages && this.isUser && !this.isHistoryLimitExceeded;
	    }
	  },
	  created() {
	    this.initContextMenu();
	    this.initCollectionManager();
	    this.initObserverManager();
	  },
	  mounted() {
	    this.subscribeToEvents();
	  },
	  beforeUnmount() {
	    this.unsubscribeFromEvents();
	  },
	  methods: {
	    subscribeToEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
	    },
	    insertTextQuote(message) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	        text: im_v2_lib_quote.Quote.prepareQuoteText(message),
	        withNewLine: true,
	        replace: false,
	        dialogId: this.dialogId
	      });
	    },
	    insertMention(user) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	        mentionText: user.name,
	        mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(user.id, user.name),
	        dialogId: this.dialogId
	      });
	    },
	    openReplyPanel(messageId) {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.replyMessage, {
	        messageId,
	        dialogId: this.dialogId
	      });
	    },
	    onAvatarClick(params) {
	      const {
	        dialogId,
	        $event: event
	      } = params;
	      const user = this.$store.getters['users/get'](dialogId);
	      if (im_v2_lib_utils.Utils.key.isAltOrOption(event)) {
	        this.insertMention(user);
	        return;
	      }
	      this.avatarMenu.openMenu({
	        user,
	        dialog: this.dialog
	      }, event.currentTarget);
	    },
	    onMessageContextMenuClick(eventData) {
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      if (!permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.openMessageMenu, this.dialogId)) {
	        return;
	      }
	      const {
	        message,
	        event,
	        dialogId
	      } = eventData.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      if (im_v2_lib_utils.Utils.key.isCombination(event, ['Alt+Ctrl'])) {
	        this.insertTextQuote(message);
	        return;
	      }
	      if (im_v2_lib_utils.Utils.key.isCmdOrCtrl(event)) {
	        this.openReplyPanel(message.id);
	        return;
	      }
	      const context = {
	        dialogId: this.dialogId,
	        ...message
	      };
	      this.messageMenu.openMenu(context, event.currentTarget);
	      this.messageMenuIsActiveForId = message.id;
	    },
	    async onMessageMouseUp(message, event) {
	      await im_v2_lib_utils.Utils.browser.waitForSelectionToUpdate();
	      const selection = window.getSelection().toString().trim();
	      if (selection.length === 0) {
	        return;
	      }
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.showQuoteButton, {
	        message,
	        event
	      });
	    },
	    initObserverManager() {
	      this.observer = new ObserverManager(this.dialogId);
	    },
	    initContextMenu() {
	      const MessageMenuClass = this.messageMenuClass;
	      this.messageMenu = new MessageMenuClass();
	      this.messageMenu.subscribe(MessageMenu.events.onCloseMenu, () => {
	        this.messageMenuIsActiveForId = 0;
	      });
	      this.avatarMenu = new AvatarMenu();
	    },
	    getMessageComponentName(message) {
	      return new im_v2_lib_messageComponentManager.MessageComponentManager(message).getName();
	    },
	    initCollectionManager() {
	      this.collectionManager = new CollectionManager(this.dialogId);
	    },
	    getCollectionManager() {
	      return this.collectionManager;
	    }
	  },
	  template: `
		<slot v-if="!dialogInited" name="loader">
			<DialogLoader />
		</slot>
		<FadeAnimation :duration="200">
			<div v-if="dialogInited" class="bx-im-message-list__container">
				<EmptyState v-if="showEmptyState" :dialogId="dialogId" />
				<slot name="before-messages" :getMessageComponentName="getMessageComponentName"></slot>
				<HistoryLimitBanner v-if="isHistoryLimitExceeded" :dialogId="dialogId" :noMessages="noMessages" />
				<DateGroup v-for="dateGroup in formattedCollection" :key="dateGroup.dateTitle" :item="dateGroup">
					<!-- Slot for every date group item -->
					<template #dateGroupItem="{ dateGroupItem, isMarkedBlock, isNewMessagesBlock, isAuthorBlock }">
						<MarkedMessagesBlock v-if="isMarkedBlock" data-id="newMessages" />
						<NewMessagesBlock v-else-if="isNewMessagesBlock" data-id="newMessages" />
						<AuthorGroup 
							v-else-if="isAuthorBlock" 
							:item="dateGroupItem"
							:contextDialogId="dialogId"
							@avatarClick="onAvatarClick"
						>
							<!-- Slot for every message -->
							<template #message="{ message, index }">
								<component
									v-message-observer
									:is="getMessageComponentName(message)"
									:withTitle="index === 0"
									:item="message"
									:dialogId="dialogId"
									:key="message.id"
									:menuIsActiveForId="messageMenuIsActiveForId"
									:data-viewed="message.viewed"
									@mouseup="onMessageMouseUp(message, $event)"
								>
								</component>
							</template>
						</AuthorGroup>
					</template>
				</DateGroup>
				<DialogStatus v-if="showDialogStatus" :dialogId="dialogId" />
			</div>
		</FadeAnimation>
	`
	};

	exports.MessageList = MessageList;
	exports.AvatarMenu = AvatarMenu;
	exports.MessageMenu = MessageMenu;
	exports.AuthorGroup = AuthorGroup;
	exports.MessageComponents = MessageComponents;
	exports.CollectionManager = CollectionManager;

}((this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {}),BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.OpenLines.v2.Component.Message,BX.OpenLines.v2.Component.Message,BX.OpenLines.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message));
//# sourceMappingURL=message-list.bundle.js.map
