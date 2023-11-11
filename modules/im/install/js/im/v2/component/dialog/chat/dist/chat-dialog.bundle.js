/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_popup,pull_vue3_status,im_v2_component_message_file,im_v2_component_message_default,im_v2_component_message_callInvite,im_v2_component_message_deleted,im_v2_component_message_unsupported,im_v2_component_message_smile,im_v2_component_message_system,im_v2_component_message_chatCreation,im_v2_component_message_conferenceCreation,im_v2_lib_call,im_v2_lib_smileManager,im_v2_lib_animation,im_v2_lib_entityCreator,im_v2_lib_market,ui_notification,im_public,im_v2_lib_menu,im_v2_lib_permission,im_v2_lib_confirm,im_v2_provider_service,main_polyfill_intersectionobserver,main_core_events,im_v2_lib_rest,im_v2_lib_parser,im_v2_lib_dateFormatter,im_v2_component_elements,im_v2_application_core,im_v2_const,im_v2_lib_user,im_v2_lib_logger,main_core,im_v2_lib_quote,im_v2_lib_utils,im_v2_lib_slider) {
	'use strict';

	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	var _hasFiles = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasFiles");
	var _hasText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasText");
	var _hasAttach = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasAttach");
	var _isEmptyMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEmptyMessage");
	var _isDeletedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	var _isSystemMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isSystemMessage");
	var _isUnsupportedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isUnsupportedMessage");
	var _isChatCreationMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChatCreationMessage");
	var _isConferenceCreationMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isConferenceCreationMessage");
	var _isCallInviteMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCallInviteMessage");
	var _isEmojiOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isEmojiOnly");
	var _hasSmilesOnly = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasSmilesOnly");
	var _hasOnlyText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOnlyText");
	class MessageComponentManager {
	  constructor(message) {
	    Object.defineProperty(this, _hasOnlyText, {
	      value: _hasOnlyText2
	    });
	    Object.defineProperty(this, _hasSmilesOnly, {
	      value: _hasSmilesOnly2
	    });
	    Object.defineProperty(this, _isEmojiOnly, {
	      value: _isEmojiOnly2
	    });
	    Object.defineProperty(this, _isCallInviteMessage, {
	      value: _isCallInviteMessage2
	    });
	    Object.defineProperty(this, _isConferenceCreationMessage, {
	      value: _isConferenceCreationMessage2
	    });
	    Object.defineProperty(this, _isChatCreationMessage, {
	      value: _isChatCreationMessage2
	    });
	    Object.defineProperty(this, _isUnsupportedMessage, {
	      value: _isUnsupportedMessage2
	    });
	    Object.defineProperty(this, _isSystemMessage, {
	      value: _isSystemMessage2
	    });
	    Object.defineProperty(this, _isDeletedMessage, {
	      value: _isDeletedMessage2
	    });
	    Object.defineProperty(this, _isEmptyMessage, {
	      value: _isEmptyMessage2
	    });
	    Object.defineProperty(this, _hasAttach, {
	      value: _hasAttach2
	    });
	    Object.defineProperty(this, _hasText, {
	      value: _hasText2
	    });
	    Object.defineProperty(this, _hasFiles, {
	      value: _hasFiles2
	    });
	    Object.defineProperty(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message] = message;
	  }
	  getName() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
	      return im_v2_const.MessageComponent.deleted;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isCallInviteMessage)[_isCallInviteMessage]()) {
	      return im_v2_const.MessageComponent.callInvite;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isUnsupportedMessage)[_isUnsupportedMessage]()) {
	      return im_v2_const.MessageComponent.unsupported;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isChatCreationMessage)[_isChatCreationMessage]()) {
	      return im_v2_const.MessageComponent.chatCreation;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isConferenceCreationMessage)[_isConferenceCreationMessage]()) {
	      return im_v2_const.MessageComponent.conferenceCreation;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isSystemMessage)[_isSystemMessage]()) {
	      return im_v2_const.MessageComponent.system;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]()) {
	      return im_v2_const.MessageComponent.file;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isEmojiOnly)[_isEmojiOnly]() || babelHelpers.classPrivateFieldLooseBase(this, _hasSmilesOnly)[_hasSmilesOnly]()) {
	      return im_v2_const.MessageComponent.smile;
	    }
	    return im_v2_const.MessageComponent.default;
	  }
	}
	function _hasFiles2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].files.length > 0;
	}
	function _hasText2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text.length > 0;
	}
	function _hasAttach2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].attach.length > 0;
	}
	function _isEmptyMessage2() {
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasText)[_hasText]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasAttach)[_hasAttach]();
	}
	function _isDeletedMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].isDeleted || babelHelpers.classPrivateFieldLooseBase(this, _isEmptyMessage)[_isEmptyMessage]();
	}
	function _isSystemMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].authorId === 0;
	}
	function _isUnsupportedMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId === im_v2_const.MessageComponent.unsupported;
	}
	function _isChatCreationMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId === im_v2_const.MessageComponent.chatCreation;
	}
	function _isConferenceCreationMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId === im_v2_const.MessageComponent.conferenceCreation;
	}
	function _isCallInviteMessage2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].componentId === im_v2_const.MessageComponent.callInvite;
	}
	function _isEmojiOnly2() {
	  if (babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].replyId > 0) {
	    return false;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyText)[_hasOnlyText]()) {
	    return false;
	  }
	  return im_v2_lib_utils.Utils.text.isEmojiOnly(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text);
	}
	function _hasSmilesOnly2() {
	  var _smileManager$smileLi, _smileManager$smileLi2;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].replyId > 0) {
	    return false;
	  }
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasOnlyText)[_hasOnlyText]()) {
	    return false;
	  }

	  // todo: need to sync with getSmileRatio in lib/parser/src/functions/smile.js
	  const smileManager = im_v2_lib_smileManager.SmileManager.getInstance();
	  const smiles = (_smileManager$smileLi = (_smileManager$smileLi2 = smileManager.smileList) == null ? void 0 : _smileManager$smileLi2.smiles) != null ? _smileManager$smileLi : [];
	  const sortedSmiles = [...smiles].sort((a, b) => {
	    return b.typing.localeCompare(a.typing);
	  });
	  const pattern = sortedSmiles.map(smile => {
	    return im_v2_lib_utils.Utils.text.escapeRegex(smile.typing);
	  }).join('|');
	  const replacedText = babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text.replaceAll(new RegExp(pattern, 'g'), '');
	  const hasOnlySmiles = replacedText.trim().length === 0;
	  const matchOnlySmiles = new RegExp(`(?:(?:${pattern})\\s*){4,}`);
	  return hasOnlySmiles && !matchOnlySmiles.test(babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].text);
	}
	function _hasOnlyText2() {
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _hasText)[_hasText]()) {
	    return false;
	  }
	  return !babelHelpers.classPrivateFieldLooseBase(this, _hasFiles)[_hasFiles]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasAttach)[_hasAttach]();
	}

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Dialog.ScrollManager';
	const SCROLLING_THRESHOLD = 1500;
	const POSITION_THRESHOLD = 40;
	const SCROLLED_UP_THRESHOLD = 400;
	class ScrollManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.isScrolling = false;
	    this.currentScroll = 0;
	    this.lastScroll = 0;
	    this.chatIsScrolledUp = false;
	    this.scrollButtonClicked = false;
	    this.setEventNamespace(EVENT_NAMESPACE);
	  }
	  setContainer(container) {
	    this.container = container;
	  }
	  onScroll(event) {
	    // if (this.isScrolling || !event.target || this.currentScroll === event.target.scrollTop)
	    if (this.isScrolling || !event.target) {
	      return false;
	    }
	    this.currentScroll = event.target.scrollTop;
	    const isScrollingDown = this.lastScroll < this.currentScroll;
	    const isScrollingUp = !isScrollingDown;
	    if (isScrollingUp) {
	      this.scrollButtonClicked = false;
	    }
	    const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
	    if (isScrollingDown && this.lastScroll > 0 && leftSpaceBottom < SCROLLING_THRESHOLD) {
	      this.emit(ScrollManager.events.onScrollTriggerDown);
	    } else if (isScrollingUp && this.currentScroll <= SCROLLING_THRESHOLD) {
	      this.emit(ScrollManager.events.onScrollTriggerUp);
	    }
	    this.lastScroll = this.currentScroll;
	    this.checkIfChatIsScrolledUp();
	  }
	  checkIfChatIsScrolledUp() {
	    const availableScrollHeight = this.container.scrollHeight - this.container.clientHeight;
	    const newFlag = this.currentScroll + SCROLLED_UP_THRESHOLD < availableScrollHeight;
	    if (newFlag !== this.chatIsScrolledUp) {
	      this.emit(ScrollManager.events.onScrollThresholdPass, newFlag);
	    }
	    this.chatIsScrolledUp = newFlag;
	  }
	  scrollToBottom() {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: scroll to bottom');
	    this.forceScrollTo(this.container.scrollHeight - this.container.clientHeight);
	  }
	  animatedScrollToBottom() {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: animated scroll to bottom');
	    this.animatedScrollTo(this.container.scrollHeight - this.container.clientHeight);
	  }
	  scrollToMessage(messageId, offset = -10) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: scroll to message - ', messageId);
	    const element = this.getDomElementById(messageId);
	    if (!element) {
	      im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
	      return;
	    }
	    const position = element.offsetTop + offset;
	    this.forceScrollTo(position);
	  }
	  animatedScrollToMessage(messageId, offset = -10) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: animated scroll to message - ', messageId);
	    const element = this.getDomElementById(messageId);
	    if (!element) {
	      im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
	      return;
	    }
	    const position = element.offsetTop + offset;
	    return this.animatedScrollTo(position);
	  }
	  forceScrollTo(position) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: Force scroll to - ', position);
	    this.cancelAnimatedScroll();
	    this.container.scroll({
	      top: position,
	      behavior: 'instant'
	    });
	  }
	  adjustScrollOnHistoryAddition(oldContainerHeight) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: Adjusting scroll after history addition');
	    const newContainerHeight = this.container.scrollHeight - this.container.clientHeight;
	    const newScrollPosition = this.container.scrollTop + newContainerHeight - oldContainerHeight;
	    this.forceScrollTo(newScrollPosition);
	  }
	  animatedScrollTo(position) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: Animated scroll to - ', position);
	    return new Promise(resolve => {
	      im_v2_lib_animation.Animation.start({
	        start: this.container.scrollTop,
	        end: position,
	        element: this.container,
	        elementProperty: 'scrollTop',
	        callback: () => {
	          this.checkIfChatIsScrolledUp();
	          resolve();
	        }
	      });
	    });
	  }
	  cancelAnimatedScroll() {
	    if (!this.isScrolling) {
	      return;
	    }
	    im_v2_lib_animation.Animation.cancel();
	    this.isScrolling = false;
	  }
	  isAtTheTop() {
	    return this.container.scrollTop === 0;
	  }
	  isAtTheBottom() {
	    return this.container.scrollTop + this.container.clientHeight >= this.container.scrollHeight;
	  }
	  isAroundBottom() {
	    return this.container.scrollHeight - this.container.scrollTop - this.container.clientHeight < POSITION_THRESHOLD;
	  }
	  getDomElementById(id) {
	    return this.container.querySelector(`[data-id="${id}"]`);
	  }
	}
	ScrollManager.events = {
	  onScrollTriggerUp: 'onScrollTriggerUp',
	  onScrollTriggerDown: 'onScrollTriggerDown',
	  onScrollThresholdPass: 'onScrollThresholdPass'
	};

	class CollectionManager {
	  constructor(dialogId) {
	    this.firstIteration = true;
	    this.cachedDateGroups = {};
	    this.store = im_v2_application_core.Core.getStore();
	    this.dialogId = dialogId;
	  }
	  formatMessageCollection(messageCollection) {
	    const dateGroups = {};
	    const collection = [];
	    let lastDateItems = null;
	    let lastAuthorId = null;
	    let lastAuthorItems = null;
	    const dialog = this.store.getters['dialogues/get'](this.dialogId);
	    const {
	      markedId,
	      inited
	    } = dialog;
	    let markInserted = false;
	    const lastReadId = this.store.getters['dialogues/getLastReadId'](this.dialogId);
	    if (this.firstIteration) {
	      this.initialLastReadMessage = lastReadId;
	      this.initialMarkedId = markedId;
	    }
	    if (markedId !== this.initialMarkedId && markedId !== 0) {
	      this.initialMarkedId = markedId;
	      this.initialLastReadMessage = null;
	    }
	    messageCollection.forEach((message, index) => {
	      const dateGroup = this.getDateGroup(message.date);
	      // new date = new date group + new author group
	      if (!dateGroups[dateGroup.title]) {
	        dateGroups[dateGroup.title] = dateGroup.id;
	        lastDateItems = [];
	        collection.push({
	          type: im_v2_const.DialogBlockType.dateGroup,
	          date: dateGroup,
	          items: lastDateItems
	        });
	        lastAuthorId = null;
	      }

	      // marked messages
	      if (message.id === this.initialMarkedId) {
	        lastDateItems.push({
	          type: im_v2_const.DialogBlockType.markedMessages
	        });
	        lastAuthorId = null;
	        markInserted = true;
	      }

	      // new author = new author group
	      if (message.authorId !== lastAuthorId) {
	        lastAuthorId = message.authorId;
	        lastAuthorItems = [];
	        lastDateItems.push({
	          type: im_v2_const.DialogBlockType.authorGroup,
	          userId: message.authorId,
	          avatar: this.getAvatarConfig(message),
	          messageType: this.getMessageType(message),
	          items: lastAuthorItems
	        });
	      }

	      // add current message to last active author group
	      lastAuthorItems.push(message);

	      // new messages block
	      const isLastMessage = index === messageCollection.length - 1;
	      if (!markInserted && !isLastMessage && message.id === this.initialLastReadMessage) {
	        lastDateItems.push({
	          type: im_v2_const.DialogBlockType.newMessages
	        });
	        lastAuthorId = null;
	      }
	    });
	    if (inited) {
	      this.firstIteration = false;
	    }
	    return collection;
	  }
	  getDateGroup(date) {
	    const INDEX_BETWEEN_DATE_AND_TIME = 10;
	    // 2022-10-25T14:58:44.000Z => 2022-10-25
	    const shortDate = date.toJSON().slice(0, INDEX_BETWEEN_DATE_AND_TIME);
	    if (this.cachedDateGroups[shortDate]) {
	      return this.cachedDateGroups[shortDate];
	    }
	    this.cachedDateGroups[shortDate] = {
	      id: shortDate,
	      title: im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.dateGroup)
	    };
	    return this.cachedDateGroups[shortDate];
	  }
	  getAvatarConfig(message) {
	    const messageType = this.getMessageType(message);
	    const isSystem = messageType === im_v2_const.MessageType.system;
	    const isSelf = messageType === im_v2_const.MessageType.self;
	    const alignment = this.store.getters['application/settings/get'](im_v2_const.Settings.appearance.alignment);
	    let isNeeded = true;
	    if (alignment === im_v2_const.DialogAlignment.left) {
	      isNeeded = !isSystem;
	    } else if (alignment === im_v2_const.DialogAlignment.center) {
	      isNeeded = !isSelf && !isSystem;
	    }
	    return {
	      isNeeded,
	      avatarId: message.authorId.toString()
	    };
	  }
	  getMessageType(message) {
	    if (!message.authorId) {
	      return im_v2_const.MessageType.system;
	    }
	    if (message.authorId === im_v2_application_core.Core.getUserId()) {
	      return im_v2_const.MessageType.self;
	    }
	    return im_v2_const.MessageType.opponent;
	  }
	}

	var _isOwnMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOwnMessage");
	var _isDeletedMessage$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	var _getMessageFile = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageFile");
	class MessageMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _getMessageFile, {
	      value: _getMessageFile2
	    });
	    Object.defineProperty(this, _isDeletedMessage$1, {
	      value: _isDeletedMessage2$1
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
	    return [this.getReplyItem(), this.getCopyItem(), this.getDelimiter(), this.getDownloadFileItem(), this.getSaveToDisk(), this.getPinItem(), this.getFavoriteItem(), this.getMarkItem(), this.getDelimiter(), this.getCreateItem(), this.getDelimiter(), this.getEditItem(), this.getDelimiter(), this.getDeleteItem()];
	  }
	  getReplyItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REPLY'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.replyMessage, {
	          messageId: this.context.id
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCopyItem() {
	    if (this.context.files.length === 0) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE'),
	      onclick: () => {
	        var _BX$clipboard;
	        const textToCopy = im_v2_lib_parser.Parser.prepareCopy(this.context);
	        if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(textToCopy)) {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_COPY_FILE_SUCCESS')
	          });
	        }
	        this.menuInstance.close();
	      }
	    };
	  }
	  getPinItem() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
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
	    const dialog = this.store.getters['dialogues/getByChatId'](this.context.chatId);
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
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
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
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_EDIT'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.editMessage, {
	          messageId: this.context.id
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getDeleteItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage$1)[_isDeletedMessage$1]()) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DELETE'),
	      onclick: () => {
	        const messageService = new im_v2_provider_service.MessageService({
	          chatId: this.context.chatId
	        });
	        messageService.deleteMessage(this.context.id);
	        this.menuInstance.close();
	      }
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
	    const file = babelHelpers.classPrivateFieldLooseBase(this, _getMessageFile)[_getMessageFile]();
	    if (!file) {
	      return null;
	    }
	    return {
	      html: im_v2_lib_utils.Utils.file.createDownloadLink(main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_DOWNLOAD_FILE'), file.urlDownload, file.name),
	      onclick: function () {
	        this.menuInstance.close();
	      }.bind(this)
	    };
	  }
	  getSaveToDisk() {
	    const file = babelHelpers.classPrivateFieldLooseBase(this, _getMessageFile)[_getMessageFile]();
	    if (!file) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK'),
	      onclick: function () {
	        void this.diskService.save(file.id).then(() => {
	          BX.UI.Notification.Center.notify({
	            content: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE_ON_DISK_SUCCESS')
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
	function _isDeletedMessage2$1() {
	  return this.context.isDeleted;
	}
	function _getMessageFile2() {
	  if (this.context.files.length === 0) {
	    return null;
	  }

	  // for now, we have only one file in one message. In the future we need to change this logic.
	  return this.store.getters['files/get'](this.context.files[0]);
	}

	class AvatarMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    this.id = 'bx-im-avatar-context-menu';
	    this.permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
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
	  getMentionItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_MENTION_2'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: this.context.user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(this.context.user.id, this.context.user.name)
	        });
	        this.menuInstance.close();
	      }
	    };
	  }
	  getSendItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_SEND_MESSAGE'),
	      onclick: () => {
	        im_public.Messenger.openChat(this.context.user.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getProfileItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_OPEN_PROFILE'),
	      href: im_v2_lib_utils.Utils.user.getProfileLink(this.context.user.id),
	      onclick: () => {
	        this.menuInstance.close();
	      }
	    };
	  }
	  getKickItem() {
	    const canKick = this.permissionManager.canPerformKick(this.context.dialog.dialogId, this.context.user.id);
	    if (!canKick) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_KICK'),
	      onclick: async () => {
	        this.menuInstance.close();
	        const userChoice = await im_v2_lib_confirm.showKickUserConfirm();
	        if (userChoice === true) {
	          const chatService = new im_v2_provider_service.ChatService();
	          chatService.kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
	        }
	      }
	    };
	  }
	}

	const EVENT_NAMESPACE$1 = 'BX.Messenger.v2.Dialog.ObserverManager';
	var _observer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observer");
	var _observedElements = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observedElements");
	var _visibleMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("visibleMessages");
	var _messagesToRead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messagesToRead");
	var _dialogInited = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogInited");
	var _initObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initObserver");
	var _getMessageIdFromElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageIdFromElement");
	var _messageIsViewed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageIsViewed");
	class ObserverManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _messageIsViewed, {
	      value: _messageIsViewed2
	    });
	    Object.defineProperty(this, _getMessageIdFromElement, {
	      value: _getMessageIdFromElement2
	    });
	    Object.defineProperty(this, _initObserver, {
	      value: _initObserver2
	    });
	    Object.defineProperty(this, _observer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _observedElements, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _visibleMessages, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _messagesToRead, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _dialogInited, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace(EVENT_NAMESPACE$1);
	    babelHelpers.classPrivateFieldLooseBase(this, _initObserver)[_initObserver]();
	  }
	  setDialogInited(flag) {
	    Object.values(babelHelpers.classPrivateFieldLooseBase(this, _observedElements)[_observedElements]).forEach(element => {
	      this.unobserveMessage(element);
	      this.observeMessage(element);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogInited)[_dialogInited] = flag;
	  }
	  observeMessage(messageElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].observe(messageElement);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](messageElement)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _observedElements)[_observedElements][messageElement.dataset.id] = messageElement;
	    }
	  }
	  unobserveMessage(messageElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].unobserve(messageElement);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](messageElement)) {
	      delete babelHelpers.classPrivateFieldLooseBase(this, _observedElements)[_observedElements][messageElement.dataset.id];
	    }
	  }
	  onReadMessage(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead].delete(messageId);
	  }
	  getMessagesToRead() {
	    return [...babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead]];
	  }
	  getFirstVisibleMessage() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].size === 0) {
	      return 0;
	    }
	    const [firstVisibleMessage] = [...babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages]].sort((a, b) => a - b);
	    return firstVisibleMessage;
	  }
	}
	function _initObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer] = new IntersectionObserver(entries => {
	    entries.forEach(entry => {
	      const messageId = babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](entry.target);
	      if (!messageId || !entry.rootBounds || !babelHelpers.classPrivateFieldLooseBase(this, _dialogInited)[_dialogInited]) {
	        return;
	      }
	      const messageIsFullyVisible = entry.isIntersecting && entry.intersectionRatio >= 0.99;
	      const messageTakesHalfOfViewport = entry.intersectionRect.height >= entry.rootBounds.height / 2.2;
	      // const messageIsBiggerThanViewport = entry.boundingClientRect.height + 20 > entry.rootBounds.height;
	      // const messageCountsAsVisible = messageIsBiggerThanViewport && messageTakesMostOfViewport;
	      if (messageIsFullyVisible || messageTakesHalfOfViewport) {
	        babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].add(messageId);
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _messageIsViewed)[_messageIsViewed](entry.target)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead].add(messageId);
	          this.emit(ObserverManager.events.onMessageIsVisible);
	        }
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].delete(messageId);
	        if (babelHelpers.classPrivateFieldLooseBase(this, _messageIsViewed)[_messageIsViewed](entry.target)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead].delete(messageId);
	        }
	      }
	    });
	  }, {
	    threshold: Array.from({
	      length: 101
	    }).fill(0).map((zero, index) => index * 0.01)
	  });
	}
	function _getMessageIdFromElement2(messageElement) {
	  return +messageElement.dataset.id;
	}
	function _messageIsViewed2(messageElement) {
	  return messageElement.dataset['viewed'] === 'true';
	}
	ObserverManager.events = {
	  onMessageIsVisible: 'onMessageIsVisible'
	};

	const TAG_PREFIX = 'IM_PUBLIC_';
	var _dialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialog");
	var _pullClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullClient");
	var _requestWatchStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestWatchStart");
	var _isGuest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isGuest");
	class PullWatchManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _isGuest, {
	      value: _isGuest2
	    });
	    Object.defineProperty(this, _requestWatchStart, {
	      value: _requestWatchStart2
	    });
	    Object.defineProperty(this, _dialog, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pullClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog] = im_v2_application_core.Core.getStore().getters['dialogues/get'](dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient] = im_v2_application_core.Core.getPullClient();
	  }
	  onChatLoad() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	  }
	  onChatExit() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].clearWatch(`${TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	  }
	  onLoadedChatEnter() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _requestWatchStart)[_requestWatchStart]();
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`, true);
	  }
	}
	function _requestWatchStart2() {
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatExtendPullWatch, {
	    data: {
	      dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].dialogId
	    }
	  });
	}
	function _isGuest2() {
	  return babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].role === im_v2_const.UserRole.guest && babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].dialogId !== 'settings';
	}

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
		<div class="bx-im-dialog-chat__new-message-block">
			<div class="bx-im-dialog-chat__new-message-block_text">{{ loc('IM_DIALOG_CHAT_BLOCK_NEW_MESSAGES_2') }}</div>
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
		<div class="bx-im-dialog-chat__new-message-block">
			<div class="bx-im-dialog-chat__new-message-block_text">{{ loc('IM_DIALOG_CHAT_BLOCK_MARKED_MESSAGES') }}</div>
		</div>
	`
	};

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
		<div class="bx-im-dialog-chat__date-group_title_container">
			<div class="bx-im-dialog-chat__date-group_title">{{ title }}</div>
		</div>
	`
	};

	// @vue/component
	const PinnedMessage = {
	  props: {
	    message: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    internalMessage() {
	      return this.message;
	    },
	    text() {
	      return im_v2_lib_parser.Parser.purifyMessage(this.internalMessage);
	    },
	    authorId() {
	      return this.internalMessage.authorId;
	    },
	    author() {
	      return this.$store.getters['users/get'](this.authorId);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pinned_item">
			<span v-if="author" class="bx-im-dialog-chat__pinned_item_user">{{ author.name }}:</span> {{ text }}
		</div>
	`
	};

	// @vue/component
	const PinnedMessages = {
	  components: {
	    PinnedMessage
	  },
	  props: {
	    messages: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['messageClick', 'messageUnpin'],
	  data() {
	    return {};
	  },
	  computed: {
	    firstMessage() {
	      return this.messagesToShow[0];
	    },
	    messagesToShow() {
	      return this.messages.slice(-1);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="$emit('messageClick', firstMessage.id)" class="bx-im-dialog-chat__pinned_container">
			<div class="bx-im-dialog-chat__pinned_title">{{ loc('IM_DIALOG_CHAT_PINNED_TITLE') }}</div>
			<PinnedMessage
				v-for="message in messagesToShow"
				:message="message"
				:key="message.id"
				@click="$emit('messageClick', message.id)"
			/>
			<div @click.stop="$emit('messageUnpin', firstMessage.id)" class="bx-im-dialog-chat__pinned_unpin"></div>
		</div>
	`
	};

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class UserService {
	  constructor() {
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  loadReadUsers(messageId) {
	    let users = [];
	    im_v2_lib_logger.Logger.warn('Dialog: UserService: loadReadUsers', messageId);
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageTailViewers, {
	      id: messageId
	    }).then(response => {
	      users = response.data().users;
	      return babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(Object.values(users));
	    }).then(() => {
	      return users.map(user => user.id);
	    }).catch(error => {
	      console.error('Dialog: UserService: loadReadUsers error', error);
	      throw new Error(error);
	    });
	  }
	}

	// @vue/component
	const AdditionalUsers = {
	  components: {
	    UserListPopup: im_v2_component_elements.UserListPopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    show: {
	      type: Boolean,
	      required: true
	    },
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      showPopup: false,
	      loadingAdditionalUsers: false,
	      additionalUsers: []
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    }
	  },
	  watch: {
	    show(newValue, oldValue) {
	      if (!oldValue && newValue) {
	        this.showPopup = true;
	        this.loadUsers();
	      }
	    }
	  },
	  methods: {
	    loadUsers() {
	      this.loadingAdditionalUsers = true;
	      this.getUserService().loadReadUsers(this.dialog.lastMessageId).then(userIds => {
	        this.additionalUsers = this.prepareAdditionalUsers(userIds);
	        this.loadingAdditionalUsers = false;
	      }).catch(() => {
	        this.loadingAdditionalUsers = false;
	      });
	    },
	    onPopupClose() {
	      this.showPopup = false;
	      this.$emit('close');
	    },
	    prepareAdditionalUsers(userIds) {
	      const firstViewerId = this.dialog.lastMessageViews.firstViewer.userId;
	      return userIds.filter(userId => {
	        return userId !== im_v2_application_core.Core.getUserId() && userId !== firstViewerId;
	      });
	    },
	    getUserService() {
	      if (!this.userService) {
	        this.userService = new UserService();
	      }
	      return this.userService;
	    }
	  },
	  template: `
		<UserListPopup
			id="bx-im-dialog-read-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
	};

	const TYPING_USERS_COUNT = 3;
	const MORE_USERS_CSS_CLASS = 'bx-im-dialog-chat-status__user-count';

	// @vue/component
	const DialogStatus = {
	  components: {
	    AdditionalUsers
	  },
	  props: {
	    dialogId: {
	      required: true,
	      type: String
	    }
	  },
	  data() {
	    return {
	      showAdditionalUsers: false,
	      additionalUsersLinkElement: null
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    typingStatus() {
	      if (!this.dialog.inited || this.dialog.writingList.length === 0) {
	        return '';
	      }
	      const firstTypingUsers = this.dialog.writingList.slice(0, TYPING_USERS_COUNT);
	      const text = firstTypingUsers.map(element => element.userName).join(', ');
	      const remainingUsersCount = this.dialog.writingList.length - TYPING_USERS_COUNT;
	      if (remainingUsersCount > 0) {
	        return this.loc('IM_DIALOG_CHAT_STATUS_TYPING_PLURAL', {
	          '#USER#': text,
	          '#COUNT#': remainingUsersCount
	        });
	      }
	      return this.loc('IM_DIALOG_CHAT_STATUS_TYPING', {
	        '#USER#': text
	      });
	    },
	    readStatus() {
	      if (!this.dialog.inited) {
	        return '';
	      }
	      if (this.lastMessageViews.countOfViewers === 0) {
	        return '';
	      }
	      if (this.isUser) {
	        return this.formatUserViewStatus();
	      }
	      return this.formatChatViewStatus();
	    },
	    lastMessageViews() {
	      return this.dialog.lastMessageViews;
	    }
	  },
	  methods: {
	    formatUserViewStatus() {
	      const {
	        date
	      } = this.lastMessageViews.firstViewer;
	      return this.loc('IM_DIALOG_CHAT_STATUS_READ_USER', {
	        '#DATE#': im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.messageReadStatus)
	      });
	    },
	    formatChatViewStatus() {
	      const {
	        countOfViewers,
	        firstViewer
	      } = this.lastMessageViews;
	      if (countOfViewers === 1) {
	        return this.loc('IM_DIALOG_CHAT_STATUS_READ_CHAT', {
	          '#USER#': firstViewer.userName
	        });
	      }
	      return this.loc('IM_DIALOG_CHAT_STATUS_READ_CHAT_PLURAL', {
	        '#USERS#': main_core.Text.encode(firstViewer.userName),
	        '#LINK_START#': `<span class="${MORE_USERS_CSS_CLASS}" ref="moreUsersLink">`,
	        '#COUNT#': countOfViewers - 1,
	        '#LINK_END#': '</span>'
	      });
	    },
	    onClick(event) {
	      if (!event.target.matches(`.${MORE_USERS_CSS_CLASS}`)) {
	        return;
	      }
	      this.onMoreUsersClick();
	    },
	    onMoreUsersClick() {
	      this.additionalUsersLinkElement = document.querySelector(`.${MORE_USERS_CSS_CLASS}`);
	      this.showAdditionalUsers = true;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div @click="onClick" class="bx-im-dialog-chat-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --typing"></div>
				<div class="bx-im-dialog-chat-status__text">{{ typingStatus }}</div>
			</div>
			<div v-else-if="readStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --read"></div>
				<div v-html="readStatus" class="bx-im-dialog-chat-status__text"></div>
			</div>
			<AdditionalUsers
				:dialogId="dialogId"
				:show="showAdditionalUsers"
				:bindElement="additionalUsersLinkElement || {}"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`
	};

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

	var _sliderRect$top;
	const CONTAINER_HEIGHT = 44;
	const CONTAINER_WIDTH = 60;
	const CONTAINER_OFFSET = 10;
	const slider = im_v2_lib_slider.MessengerSlider.getInstance().getCurrent();
	const sliderRect = slider == null ? void 0 : slider.layout.container.getBoundingClientRect();
	const offsetY = (_sliderRect$top = sliderRect == null ? void 0 : sliderRect.top) != null ? _sliderRect$top : 0;

	// @vue/component
	const QuoteButton = {
	  name: 'QuoteButton',
	  data() {
	    return {
	      text: '',
	      message: null,
	      mouseX: 0,
	      mouseY: 0
	    };
	  },
	  computed: {
	    containerStyle() {
	      return {
	        top: `${this.mouseY - CONTAINER_HEIGHT - CONTAINER_OFFSET - offsetY}px`,
	        left: `${this.mouseX - CONTAINER_WIDTH / 2}px`,
	        width: `${CONTAINER_WIDTH}px`,
	        height: `${CONTAINER_HEIGHT}px`
	      };
	    }
	  },
	  mounted() {
	    main_core.Event.bind(window, 'mousedown', this.onMouseDown);
	  },
	  methods: {
	    onMessageMouseUp(message, event) {
	      if (event.button === 2) {
	        return;
	      }
	      this.prepareSelectedText();
	      this.message = message;
	      this.mouseX = event.clientX;
	      this.mouseY = event.clientY;
	    },
	    onMouseDown(event) {
	      const container = this.$refs.container;
	      if (!container || container.contains(event.target)) {
	        return;
	      }
	      this.$emit('close');
	    },
	    prepareSelectedText() {
	      if (im_v2_lib_utils.Utils.browser.isFirefox()) {
	        this.text = window.getSelection().toString();
	        return;
	      }
	      const range = window.getSelection().getRangeAt(0);
	      const selectedNodes = range.cloneContents().childNodes;
	      for (const node of selectedNodes) {
	        if (this.isImage(node)) {
	          var _node$getAttribute;
	          this.text += (_node$getAttribute = node.getAttribute('data-code')) != null ? _node$getAttribute : node.getAttribute('alt');
	        } else if (this.isLineBreak(node)) {
	          this.text += '\n';
	        } else if (this.isMessageTextNode(node) || this.isText(node)) {
	          this.text += node.textContent;
	        }
	      }
	    },
	    isImage(node) {
	      if (!(node instanceof HTMLElement)) {
	        return false;
	      }
	      return node.tagName.toLowerCase() === 'img';
	    },
	    isLineBreak(node) {
	      return node.nodeName.toLowerCase() === 'br';
	    },
	    isText(node) {
	      return node.nodeName === '#text';
	    },
	    isMessageTextNode(node) {
	      if (!(node instanceof HTMLElement)) {
	        return false;
	      }
	      const MESSAGE_TEXT_NODE_CLASS = '.bx-im-message-default-content__text';
	      const textNode = node.querySelector(MESSAGE_TEXT_NODE_CLASS);
	      return Boolean(textNode);
	    },
	    onQuoteClick() {
	      im_v2_lib_quote.Quote.sendQuoteEvent(this.message, this.text);
	      this.$emit('close');
	    }
	  },
	  template: `
		<div ref="container" @click="onQuoteClick" :style="containerStyle" class="bx-im-dialog-chat__quote-button">
			<div class="bx-im-dialog-chat__quote-icon"></div>
			<div class="bx-im-dialog-chat__quote-icon --hover"></div>
		</div>
	`
	};

	const FLOATING_DATE_OFFSET = 52;
	const LOAD_MESSAGE_ON_EXIT_DELAY = 200;

	// @vue/component
	const ChatDialog = {
	  name: 'ChatDialog',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    DefaultMessage: im_v2_component_message_default.DefaultMessage,
	    FileMessage: im_v2_component_message_file.FileMessage,
	    SmileMessage: im_v2_component_message_smile.SmileMessage,
	    CallInviteMessage: im_v2_component_message_callInvite.CallInviteMessage,
	    DeletedMessage: im_v2_component_message_deleted.DeletedMessage,
	    SystemMessage: im_v2_component_message_system.SystemMessage,
	    UnsupportedMessage: im_v2_component_message_unsupported.UnsupportedMessage,
	    ChatCreationMessage: im_v2_component_message_chatCreation.ChatCreationMessage,
	    ConferenceCreationMessage: im_v2_component_message_conferenceCreation.ConferenceCreationMessage,
	    PinnedMessages,
	    NewMessagesBlock,
	    MarkedMessagesBlock,
	    DateGroupTitle,
	    ChatInfoPopup: im_v2_component_elements.ChatInfoPopup,
	    DialogStatus,
	    DialogLoader,
	    QuoteButton,
	    PullStatus: pull_vue3_status.PullStatus
	  },
	  directives: {
	    'message-observer': {
	      mounted(element, binding) {
	        binding.instance.observerManager.observeMessage(element);
	      },
	      beforeUnmount(element, binding) {
	        binding.instance.observerManager.unobserveMessage(element);
	      }
	    }
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    textareaHeight: {
	      type: Number,
	      default: 0
	    }
	  },
	  data() {
	    return {
	      messageMenuIsActiveForId: 0,
	      chatInfoPopup: {
	        element: null,
	        dialogId: 0,
	        show: false
	      },
	      contextMode: {
	        active: false,
	        messageIsLoaded: false
	      },
	      initialScrollCompleted: false,
	      isScrolledUp: false,
	      windowFocused: false,
	      showQuoteButton: false,
	      selectedText: null,
	      quoteButtonStyles: {},
	      quoteButtonMessage: 0
	    };
	  },
	  computed: {
	    BlockType: () => im_v2_const.DialogBlockType,
	    AvatarSize: () => im_v2_component_elements.AvatarSize,
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
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
	    messageCollection() {
	      return this.$store.getters['messages/get'](this.dialog.chatId);
	    },
	    pinnedMessages() {
	      return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId);
	    },
	    isOpened() {
	      const openedDialogId = this.$store.getters['application/getLayout'].entityId;
	      return this.dialogId === openedDialogId;
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    debouncedScrollHandler() {
	      const SCROLLING_DEBOUNCE_DELAY = 200;
	      return main_core.Runtime.debounce(this.getScrollManager().onScroll, SCROLLING_DEBOUNCE_DELAY, this.getScrollManager());
	    },
	    debouncedReadHandler() {
	      return main_core.Runtime.debounce(this.readVisibleMessages, 50, this);
	    },
	    formattedCounter() {
	      if (this.dialog.counter === 0) {
	        return '';
	      }
	      if (this.dialog.counter > 99) {
	        return '99+';
	      }
	      return String(this.dialog.counter);
	    },
	    showDialogStatus() {
	      return this.messageCollection.some(message => {
	        return message.id === this.dialog.lastMessageId;
	      });
	    },
	    showScrollButton() {
	      return this.isScrolledUp || this.dialog.hasNextPage;
	    }
	  },
	  watch: {
	    dialogInited(newValue, oldValue) {
	      if (!newValue || oldValue) {
	        return;
	      }
	      // first opening
	      this.getPullWatchManager().onChatLoad();
	      this.onChatInited();
	    },
	    textareaHeight() {
	      if (this.isScrolledUp || !this.dialogInited) {
	        return;
	      }
	      void this.$nextTick(() => {
	        this.getScrollManager().scrollToBottom();
	      });
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Dialog: Chat created', this.dialogId);
	    this.getCollectionManager();
	    this.initContextMenu();
	    this.initObserverManager();
	    this.initContextMode();
	  },
	  mounted() {
	    this.getScrollManager().setContainer(this.getContainer());
	    if (this.dialogInited) {
	      // second+ opening
	      this.getPullWatchManager().onLoadedChatEnter();
	      this.onChatInited();
	    }
	    // there are P&P messages
	    else if (!this.dialogInited && this.messageCollection.length > 0) {
	      this.scrollOnStart();
	    }
	    this.windowFocused = document.hasFocus();
	    this.subscribeToEvents();
	  },
	  beforeUnmount() {
	    this.closeMessageMenu();
	    this.unsubscribeFromEvents();
	    if (this.dialogInited) {
	      this.saveScrollPosition();
	      this.loadMessagesOnExit();
	    }
	    this.getPullWatchManager().onChatExit();
	  },
	  methods: {
	    readVisibleMessages() {
	      if (!this.dialogInited || !this.windowFocused || this.hasVisibleCall() || this.isGuest) {
	        return;
	      }
	      this.getObserverManager().getMessagesToRead().forEach(messageId => {
	        this.getChatService().readMessage(this.dialog.chatId, messageId);
	        this.getObserverManager().onReadMessage(messageId);
	      });
	    },
	    scrollOnStart() {
	      void this.$nextTick(() => {
	        // we loaded chat with context
	        if (this.contextMode.active && this.contextMode.messageIsLoaded) {
	          this.getScrollManager().scrollToMessage(this.layout.contextId, -FLOATING_DATE_OFFSET);
	          void this.$nextTick(() => {
	            this.highlightMessage(this.layout.contextId);
	          });
	        }
	        // chat was loaded before
	        else if (this.contextMode.active && !this.contextMode.messageIsLoaded) {
	          void this.goToMessageContext(this.layout.contextId);
	        }
	        // marked message
	        else if (this.dialog.markedId) {
	          this.getScrollManager().scrollToMessage(im_v2_const.DialogBlockType.newMessages, -FLOATING_DATE_OFFSET);
	        }
	        // saved position
	        else if (this.dialog.savedPositionMessageId) {
	          im_v2_lib_logger.Logger.warn('Dialog: saved scroll position, scrolling to', this.dialog.savedPositionMessageId);
	          this.getScrollManager().scrollToMessage(this.dialog.savedPositionMessageId);
	        }
	        // unread message
	        else if (this.$store.getters['dialogues/getLastReadId'](this.dialogId)) {
	          this.getScrollManager().scrollToMessage(im_v2_const.DialogBlockType.newMessages, -FLOATING_DATE_OFFSET);
	        }
	        // new chat with unread messages
	        else if (this.$store.getters['messages/getFirstUnread'](this.dialog.chatId)) {
	          im_v2_lib_logger.Logger.warn('Dialog: new chat with unread messages, dont scroll');
	        } else {
	          this.getScrollManager().scrollToBottom();
	        }
	      });
	    },
	    goToMessageContext(messageId) {
	      const hasMessage = this.$store.getters['messages/hasMessage']({
	        chatId: this.dialog.chatId,
	        messageId
	      });
	      if (hasMessage) {
	        im_v2_lib_logger.Logger.warn('Dialog: we have this message, scrolling to it', messageId);
	        return this.getScrollManager().animatedScrollToMessage(messageId, -FLOATING_DATE_OFFSET).then(() => {
	          this.highlightMessage(messageId);
	          return true;
	        });
	      }
	      return this.getMessageService().loadContext(messageId).then(() => {
	        return this.$nextTick();
	      }).then(() => {
	        this.getScrollManager().scrollToMessage(messageId, -FLOATING_DATE_OFFSET);
	        return this.$nextTick();
	      }).then(() => {
	        this.highlightMessage(messageId);
	        return true;
	      }).catch(error => {
	        im_v2_lib_logger.Logger.error('goToMessageContext error', error);
	      });
	    },
	    highlightMessage(messageId) {
	      const HIGHLIGHT_CLASS = 'bx-im-dialog-chat__highlighted-message';
	      const HIGHLIGHT_DURATION = 2000;
	      const message = this.getScrollManager().getDomElementById(messageId);
	      if (!message) {
	        return;
	      }
	      main_core.Dom.addClass(message, HIGHLIGHT_CLASS);
	      setTimeout(() => {
	        main_core.Dom.removeClass(message, HIGHLIGHT_CLASS);
	      }, HIGHLIGHT_DURATION);
	    },
	    saveScrollPosition() {
	      let savedPositionMessageId = this.getObserverManager().getFirstVisibleMessage();
	      if (this.getScrollManager().isAroundBottom()) {
	        savedPositionMessageId = 0;
	      }
	      this.$store.dispatch('dialogues/update', {
	        dialogId: this.dialogId,
	        fields: {
	          savedPositionMessageId
	        }
	      });
	    },
	    loadMessagesOnExit() {
	      setTimeout(() => {
	        void this.getMessageService().reloadMessageList();
	      }, LOAD_MESSAGE_ON_EXIT_DELAY);
	    },
	    /* region Init methods */
	    initContextMode() {
	      if (!this.layout.contextId) {
	        return;
	      }
	      this.contextMode.active = true;
	      // chat was loaded before, we didn't load context specifically
	      // if chat wasn't loaded before - we load it with context
	      this.contextMode.messageIsLoaded = !this.dialogInited;
	    },
	    initContextMenu() {
	      this.messageMenu = new MessageMenu();
	      this.messageMenu.subscribe(MessageMenu.events.onCloseMenu, () => {
	        this.messageMenuIsActiveForId = 0;
	      });
	      this.avatarMenu = new AvatarMenu();
	    },
	    initObserverManager() {
	      this.observerManager = new ObserverManager();
	      this.observerManager.subscribe(ObserverManager.events.onMessageIsVisible, () => {
	        this.debouncedReadHandler();
	      });
	    },
	    getObserverManager() {
	      return this.observerManager;
	    },
	    getCollectionManager() {
	      if (!this.collectionManager) {
	        this.collectionManager = new CollectionManager(this.dialogId);
	      }
	      return this.collectionManager;
	    },
	    getMessageService() {
	      if (!this.messageService) {
	        this.messageService = new im_v2_provider_service.MessageService({
	          chatId: this.dialog.chatId
	        });
	      }
	      return this.messageService;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
	    },
	    getScrollManager() {
	      if (!this.scrollManager) {
	        this.scrollManager = new ScrollManager();
	        this.scrollManager.subscribe(ScrollManager.events.onScrollTriggerUp, this.onScrollTriggerUp);
	        this.scrollManager.subscribe(ScrollManager.events.onScrollTriggerDown, this.onScrollTriggerDown);
	        this.scrollManager.subscribe(ScrollManager.events.onScrollThresholdPass, event => {
	          this.isScrolledUp = event.getData();
	        });
	      }
	      return this.scrollManager;
	    },
	    getPullWatchManager() {
	      if (!this.pullWatchManager) {
	        this.pullWatchManager = new PullWatchManager(this.dialogId);
	      }
	      return this.pullWatchManager;
	    },
	    /* endregion Init methods */
	    /* region Event handlers */
	    onChatInited() {
	      if (!this.dialog.loading) {
	        this.scrollOnStart();
	        this.readVisibleMessages();
	        this.getObserverManager().setDialogInited(true);
	      }
	      void this.$nextTick(() => {
	        this.getChatService().clearDialogMark(this.dialogId);
	      });
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onDialogInited, {
	        dialogId: this.dialogId
	      });
	    },
	    async onScrollTriggerUp() {
	      if (!this.dialogInited || !this.getContainer()) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll triggered UP');
	      const container = this.getContainer();
	      const oldHeight = container.scrollHeight - container.clientHeight;

	      // Insert messages if there are some
	      if (this.getMessageService().hasPreparedHistoryMessages()) {
	        await this.getMessageService().drawPreparedHistoryMessages();
	        this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
	        return;
	      }

	      // check if already loading or no more history
	      if (this.getMessageService().isLoading() || !this.dialog.hasPrevPage) {
	        return;
	      }

	      // Load messages and save them
	      await this.getMessageService().loadHistory();
	      // Messages loaded and we are at the top
	      if (this.getScrollManager().isAtTheTop()) {
	        im_v2_lib_logger.Logger.warn('Dialog: we are at the top after history request, inserting messages');
	        await this.getMessageService().drawPreparedHistoryMessages();
	        this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
	      }
	    },
	    async onScrollTriggerDown() {
	      if (!this.dialogInited || !this.getContainer()) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll triggered DOWN');
	      // Insert messages if there are some
	      if (this.getMessageService().hasPreparedUnreadMessages()) {
	        await this.getMessageService().drawPreparedUnreadMessages();
	        return;
	      }

	      // check if already loading or no more history
	      if (this.getMessageService().isLoading() || !this.dialog.hasNextPage) {
	        return;
	      }

	      // Load messages and save them
	      await this.getMessageService().loadUnread();
	      // Messages loaded and we are at the bottom
	      if (this.getScrollManager().isAroundBottom()) {
	        im_v2_lib_logger.Logger.warn('Dialog: we are at the bottom after unread request, inserting messages');
	        await this.getMessageService().drawPreparedUnreadMessages();
	        this.getScrollManager().checkIfChatIsScrolledUp();
	      }
	    },
	    onScrollToBottom(event) {
	      const {
	        chatId,
	        threshold = im_v2_const.DialogScrollThreshold.halfScreenUp,
	        animation = true
	      } = event.getData();
	      if (this.dialog.chatId !== chatId) {
	        return;
	      }
	      if (!this.windowFocused || this.hasVisibleCall()) {
	        const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	        if (firstUnreadId) {
	          void this.$nextTick(() => {
	            this.getScrollManager().scrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
	          });
	          return;
	        }
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll to bottom', chatId, threshold);
	      if (threshold === im_v2_const.DialogScrollThreshold.halfScreenUp && this.isScrolledUp) {
	        return;
	      }
	      if (threshold === im_v2_const.DialogScrollThreshold.nearTheBottom && !this.getScrollManager().isAroundBottom()) {
	        return;
	      }
	      void this.$nextTick(() => {
	        if (animation) {
	          this.getScrollManager().animatedScrollToBottom();
	          return;
	        }
	        this.getScrollManager().scrollToBottom();
	      });
	    },
	    onGoToMessageContext(event) {
	      const {
	        dialogId,
	        messageId
	      } = event.getData();
	      if (this.dialog.dialogId !== dialogId) {
	        return;
	      }
	      void this.goToMessageContext(messageId);
	    },
	    onOpenChatInfo(event) {
	      const {
	        dialogId,
	        event: $event
	      } = event.getData();
	      this.chatInfoPopup.element = $event.target;
	      this.chatInfoPopup.dialogId = dialogId;
	      this.chatInfoPopup.show = true;
	    },
	    onPinnedMessageClick(messageId) {
	      void this.goToMessageContext(messageId);
	    },
	    onPinnedMessageUnpin(messageId) {
	      this.getMessageService().unpinMessage(this.dialog.chatId, messageId);
	    },
	    onMessageContextMenuClick(eventData) {
	      const {
	        message,
	        event
	      } = eventData.getData();
	      const context = {
	        dialogId: this.dialogId,
	        ...message
	      };
	      this.messageMenu.openMenu(context, event.currentTarget);
	      this.messageMenuIsActiveForId = message.id;
	    },
	    onScroll(event) {
	      this.closeDialogPopups();
	      this.debouncedScrollHandler(event);
	    },
	    async onScrollButtonClick() {
	      if (this.getScrollManager().scrollButtonClicked) {
	        this.handleSecondScrollButtonClick();
	        return;
	      }
	      this.getScrollManager().scrollButtonClicked = true;
	      if (this.dialog.counter === 0) {
	        await this.getMessageService().loadInitialMessages();
	        this.getScrollManager().scrollToBottom();
	        return;
	      }
	      const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	      if (!firstUnreadId) {
	        await this.getMessageService().loadInitialMessages();
	        await this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
	      }
	      await this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
	    },
	    onWindowFocus() {
	      this.windowFocused = true;
	      this.readVisibleMessages();
	    },
	    onWindowBlur() {
	      this.windowFocused = false;
	    },
	    onAvatarClick(dialogId, event) {
	      const user = this.$store.getters['users/get'](dialogId);
	      const userId = Number.parseInt(dialogId, 10);
	      if (!user || im_v2_application_core.Core.getUserId() === userId) {
	        return;
	      }
	      if (im_v2_lib_utils.Utils.key.isAltOrOption(event)) {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.text.getMentionBbCode(user.id, user.name)
	        });
	        return;
	      }
	      this.avatarMenu.openMenu({
	        user,
	        dialog: this.dialog
	      }, event.currentTarget);
	    },
	    onCallFold() {
	      const callDialogId = im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId();
	      if (callDialogId !== this.dialogId) {
	        return;
	      }
	      this.readVisibleMessages();
	    },
	    onChatClick(event) {
	      if (this.isGuest) {
	        event.stopPropagation();
	      }
	    },
	    handleSecondScrollButtonClick() {
	      this.getScrollManager().scrollButtonClicked = false;
	      if (this.dialog.hasNextPage) {
	        this.getMessageService().loadContext(this.dialog.lastMessageId).then(() => {
	          main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	            chatId: this.dialog.chatId
	          });
	        }).catch(error => {
	          im_v2_lib_logger.Logger.error('ChatDialog: scroll to chat end loadContext error', error);
	        });
	        return;
	      }
	      void this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId);
	    },
	    /* endregion Event handlers */
	    hasVisibleCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasVisibleCall();
	    },
	    closeDialogPopups() {
	      var _PopupManager$getPopu, _PopupManager$getPopu2, _PopupManager$getPopu3;
	      this.closeMessageMenu();
	      this.chatInfoPopup.show = false;
	      this.showQuoteButton = false;
	      this.avatarMenu.close();
	      (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogReactionUsers)) == null ? void 0 : _PopupManager$getPopu.close();
	      (_PopupManager$getPopu2 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogReadUsers)) == null ? void 0 : _PopupManager$getPopu2.close();
	      (_PopupManager$getPopu3 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.messageBaseFileMenu)) == null ? void 0 : _PopupManager$getPopu3.close();
	    },
	    closeMessageMenu() {
	      this.messageMenu.close();
	      this.messageMenuIsActiveForId = 0;
	    },
	    subscribeToEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.mention.openChatInfo, this.onOpenChatInfo);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.call.onFold, this.onCallFold);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
	      main_core.Event.bind(window, 'focus', this.onWindowFocus);
	      main_core.Event.bind(window, 'blur', this.onWindowBlur);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.mention.openChatInfo, this.onOpenChatInfo);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.call.onFold, this.onCallFold);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onClickMessageContextMenu, this.onMessageContextMenuClick);
	      main_core.Event.unbind(window, 'focus', this.onWindowFocus);
	      main_core.Event.unbind(window, 'blur', this.onWindowBlur);
	    },
	    getContainer() {
	      return this.$refs.container;
	    },
	    async onMessageMouseUp(message, event) {
	      await im_v2_lib_utils.Utils.browser.waitForSelectionToUpdate();
	      const selection = window.getSelection().toString().trim();
	      if (selection.length === 0 || this.isGuest) {
	        return;
	      }
	      this.showQuoteButton = true;
	      await this.$nextTick();
	      this.$refs.quoteButton.onMessageMouseUp(message, event);
	    },
	    getMessageComponentName(message) {
	      return new MessageComponentManager(message).getName();
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__block bx-im-dialog-chat__scope">
			<PinnedMessages
				v-if="pinnedMessages.length > 0"
				:messages="pinnedMessages"
				@messageClick="onPinnedMessageClick"
				@messageUnpin="onPinnedMessageUnpin"
			/>
			<PullStatus/>
			<div @scroll="onScroll" @click.capture="onChatClick" class="bx-im-dialog-chat__scroll-container" ref="container">
				<div class="bx-im-dialog-chat__content">
					<!-- Loader -->
					<DialogLoader v-if="!dialogInited" :fullHeight="formattedCollection.length === 0" />
					<!-- Date groups -->
					<div v-for="dateGroup in formattedCollection" :key="dateGroup.date.id" class="bx-im-dialog-chat__date-group_container">
						<!-- Date mark -->
						<DateGroupTitle :title="dateGroup.date.title" />
						<!-- Single date group -->
						<template v-for="dateGroupItem in dateGroup.items">
							<!-- 'New messages' mark -->
							<MarkedMessagesBlock v-if="dateGroupItem.type === BlockType.markedMessages" data-id="newMessages" />
							<NewMessagesBlock v-else-if="dateGroupItem.type === BlockType.newMessages" data-id="newMessages" />
							<!-- Author group -->
							<div v-else-if="dateGroupItem.type === BlockType.authorGroup" :class="'--' + dateGroupItem.messageType" class="bx-im-dialog-chat__author-group_container">
								<!-- Author group avatar -->
								<div v-if="dateGroupItem.avatar.isNeeded" class="bx-im-dialog-chat__author-group_avatar">
									<Avatar
										:dialogId="dateGroupItem.avatar.avatarId"
										:size="AvatarSize.L"
										:withStatus="false"
										@click="onAvatarClick(dateGroupItem.avatar.avatarId, $event)"
									/>
								</div>
								<!-- Messages -->
								<div class="bx-im-dialog-chat__messages_container">
									<component
										v-for="(message, index) in dateGroupItem.items"
										v-message-observer
										:is="getMessageComponentName(message)"
										:withTitle="index === 0"
										:item="message"
										:dialogId="dialogId"
										:key="message.id"
										:menuIsActiveForId="messageMenuIsActiveForId"
										:withAvatar="dateGroupItem.avatar.isNeeded"
										:data-viewed="message.viewed"
										@mouseup="onMessageMouseUp(message, $event)"
									>
									</component>
								</div>
							</div>
						</template>
					</div>
					<DialogStatus v-if="showDialogStatus" :dialogId="dialogId" />
				</div>
			</div>
			<Transition name="scroll-button-transition">
				<div v-if="showScrollButton" @click="onScrollButtonClick" class="bx-im-dialog-chat__scroll-button">
					<div v-if="dialog.counter" class="bx-im-dialog-chat__scroll-button_counter">{{ formattedCounter }}</div>
				</div>
			</Transition>
			<ChatInfoPopup
				v-if="chatInfoPopup.show"
				:dialogId="chatInfoPopup.dialogId"
				:bindElement="chatInfoPopup.element"
				:showPopup="chatInfoPopup.show"
				@close="chatInfoPopup.show = false"
			/>
            <Transition name="fade-up">
				<QuoteButton 
					v-if="showQuoteButton" 
					ref="quoteButton"
					@close="showQuoteButton = false" 
					class="bx-im-message-base__quote-button" 
				/>
            </Transition>
		</div>
	`
	};

	exports.ChatDialog = ChatDialog;

}((this.BX.Messenger.v2.Component.Dialog = this.BX.Messenger.v2.Component.Dialog || {}),BX.Main,window,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Im.V2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=chat-dialog.bundle.js.map
