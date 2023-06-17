this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_popup,im_v2_component_message_base,im_v2_component_message_chatCreation,im_v2_lib_call,im_v2_lib_animation,im_v2_lib_entityCreator,im_v2_lib_market,ui_notification,im_public,im_v2_lib_menu,im_v2_lib_utils,im_v2_provider_service,main_polyfill_intersectionobserver,main_core_events,im_v2_lib_parser,main_core,im_v2_lib_dateFormatter,im_v2_component_elements,im_v2_application_core,im_v2_const,im_v2_lib_user,im_v2_lib_logger) {
	'use strict';

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
	    let avatarId = message.authorId.toString();
	    // if (messageType === MessageType.system)
	    // {
	    // 	// show avatar of current chat
	    // 	avatarId = `chat${message.chatId}`;
	    // }

	    let isNeeded = true;
	    if (messageType === im_v2_const.MessageType.self || messageType === im_v2_const.MessageType.system || message.componentId !== im_v2_const.MessageComponent.base) {
	      isNeeded = false;
	    }
	    return {
	      isNeeded,
	      avatarId
	    };
	  }
	  getMessageType(message) {
	    if (!message.authorId) {
	      return im_v2_const.MessageType.system;
	    } else if (message.authorId === im_v2_application_core.Core.getUserId()) {
	      return im_v2_const.MessageType.self;
	    } else {
	      return im_v2_const.MessageType.opponent;
	    }
	  }
	}

	const QUOTE_DELIMITER = '-'.repeat(54);
	const QuoteManager = {
	  sendQuoteEvent(message) {
	    main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	      text: this.prepareQuoteText(message),
	      withNewLine: true
	    });
	  },
	  prepareQuoteText(message) {
	    let quoteTitle = main_core.Loc.getMessage('IM_DIALOG_CHAT_QUOTE_DEFAULT_TITLE');
	    if (message.authorId) {
	      const user = im_v2_application_core.Core.getStore().getters['users/get'](message.authorId);
	      quoteTitle = user.name;
	    }
	    const quoteDate = im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(message.date, im_v2_lib_dateFormatter.DateTemplate.notification);
	    const quoteText = im_v2_lib_parser.Parser.prepareQuote(message);
	    let quoteContext = '';
	    const dialog = im_v2_application_core.Core.getStore().getters['dialogues/getByChatId'](message.chatId);
	    if (dialog && dialog.type === im_v2_const.DialogType.user) {
	      quoteContext = `#${dialog.dialogId}:${im_v2_application_core.Core.getUserId()}/${message.id}`;
	    } else {
	      quoteContext = `#${dialog.dialogId}/${message.id}`;
	    }
	    return `${QUOTE_DELIMITER}\n` + `${quoteTitle} [${quoteDate}] ${quoteContext}\n` + `${quoteText}\n` + `${QUOTE_DELIMITER}\n`;
	  }
	};

	var _getDelimiter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getDelimiter");
	var _isOwnMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isOwnMessage");
	var _isDeletedMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDeletedMessage");
	class MessageMenu extends im_v2_lib_menu.BaseMenu {
	  constructor() {
	    super();
	    Object.defineProperty(this, _isDeletedMessage, {
	      value: _isDeletedMessage2
	    });
	    Object.defineProperty(this, _isOwnMessage, {
	      value: _isOwnMessage2
	    });
	    Object.defineProperty(this, _getDelimiter, {
	      value: _getDelimiter2
	    });
	    this.id = 'bx-im-message-context-menu';
	    this.chatService = new im_v2_provider_service.ChatService();
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
	    return [this.getQuoteItem(), this.getCopyItem(), this.getQuoteBlockDelimiter(), this.getPinItem(), this.getFavoriteItem(), this.getMarkItem(), this.getPinBlockDelimiter(), this.getCreateItem(), this.getCreateBlockDelimiter(), this.getEditItem(), this.getEditBlockDelimiter(), this.getDeleteItem()];
	  }
	  getQuoteItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_QUOTE'),
	      onclick: () => {
	        QuoteManager.sendQuoteEvent(this.context);
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
	  getQuoteBlockDelimiter() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter]();
	  }
	  getPinItem() {
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
	    const isInFavorite = this.store.getters['sidebar/favorites/isFavoriteMessage'](this.context.chatId, this.context.id);
	    return {
	      text: isInFavorite ? main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_REMOVE_FROM_SAVED') : main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_SAVE'),
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
	  getPinBlockDelimiter() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter]();
	  }
	  getCreateItem() {
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
	        entityCreator.createTaskForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateMeetingItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_CHAT_MENU_CREATE_MEETING'),
	      onclick: () => {
	        const entityCreator = new im_v2_lib_entityCreator.EntityCreator(this.context.chatId);
	        entityCreator.createMeetingForMessage(this.context.id);
	        this.menuInstance.close();
	      }
	    };
	  }
	  getCreateBlockDelimiter() {
	    if (!this.getEditItem() && !this.getDeleteItem()) {
	      return null;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter]();
	  }
	  getEditItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
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
	  getEditBlockDelimiter() {
	    if (!this.getEditItem()) {
	      return null;
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter]();
	  }
	  getDeleteItem() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isOwnMessage)[_isOwnMessage]() || babelHelpers.classPrivateFieldLooseBase(this, _isDeletedMessage)[_isDeletedMessage]()) {
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
	      marketMenuItem.push(babelHelpers.classPrivateFieldLooseBase(this, _getDelimiter)[_getDelimiter]());
	    }
	    const context = {
	      messageId: id,
	      dialogId: dialogId
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
	}
	function _getDelimiter2() {
	  return {
	    delimiter: true
	  };
	}
	function _isOwnMessage2() {
	  return this.context.authorId === im_v2_application_core.Core.getUserId();
	}
	function _isDeletedMessage2() {
	  return this.context.isDeleted;
	}

	class AvatarMenu extends im_v2_lib_menu.BaseMenu {
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
	  getMentionItem() {
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_MENTION'),
	      onclick: () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertMention, {
	          mentionText: this.context.user.name,
	          mentionReplacement: im_v2_lib_utils.Utils.user.getMentionBbCode(this.context.user.id, this.context.user.name)
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
	    const isOwner = im_v2_application_core.Core.getUserId() === this.context.dialog.owner;
	    const isUser = this.context.dialog.type === im_v2_const.DialogType.user;
	    if (!isOwner || isUser) {
	      return null;
	    }
	    return {
	      text: main_core.Loc.getMessage('IM_DIALOG_AVATAR_MENU_KICK'),
	      onclick: () => {
	        const chatService = new im_v2_provider_service.ChatService();
	        chatService.kickUserFromChat(this.context.dialog.dialogId, this.context.user.id);
	        this.menuInstance.close();
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

	const FLOATING_DATE_OFFSET = 52;
	const LOAD_MESSAGE_ON_EXIT_DELAY = 200;

	// @vue/component
	const ChatDialog = {
	  name: 'ChatDialog',
	  components: {
	    Avatar: im_v2_component_elements.Avatar,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    ChatCreationMessage: im_v2_component_message_chatCreation.ChatCreationMessage,
	    PinnedMessages,
	    NewMessagesBlock,
	    MarkedMessagesBlock,
	    DateGroupTitle,
	    ChatInfoPopup: im_v2_component_elements.ChatInfoPopup,
	    DialogStatus,
	    DialogLoader
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
	      windowFocused: false
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
	      return `${this.dialog.counter}`;
	    },
	    showDialogStatus() {
	      return this.messageCollection.some(message => {
	        return message.id === this.dialog.lastMessageId;
	      });
	    }
	  },
	  watch: {
	    dialogInited(newValue, oldValue) {
	      if (!newValue || oldValue) {
	        return false;
	      }
	      // first opening
	      this.onChatInited();
	    },
	    textareaHeight() {
	      if (this.isScrolledUp || !this.dialogInited) {
	        return;
	      }
	      this.$nextTick(() => {
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
	  },
	  methods: {
	    readVisibleMessages() {
	      if (!this.dialogInited || !this.windowFocused || this.hasVisibleCall()) {
	        return;
	      }
	      this.getObserverManager().getMessagesToRead().forEach(messageId => {
	        this.getChatService().readMessage(this.dialog.chatId, messageId);
	        this.getObserverManager().onReadMessage(messageId);
	      });
	    },
	    scrollOnStart() {
	      this.$nextTick(() => {
	        // we loaded chat with context
	        if (this.contextMode.active && this.contextMode.messageIsLoaded) {
	          this.getScrollManager().scrollToMessage(this.layout.contextId, -FLOATING_DATE_OFFSET);
	          this.$nextTick(() => {
	            this.highlightMessage(this.layout.contextId);
	          });
	        }
	        // chat was loaded before
	        else if (this.contextMode.active && !this.contextMode.messageIsLoaded) {
	          this.goToMessageContext(this.layout.contextId);
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
	        messageId: messageId
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
	        console.error('goToMessageContext error', error);
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
	        this.getMessageService().reloadMessageList();
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
	    /* endregion Init methods */
	    /* region Event handlers */
	    onChatInited() {
	      if (!this.dialog.loading) {
	        this.scrollOnStart();
	        this.debouncedReadHandler();
	        this.getObserverManager().setDialogInited(true);
	      }
	      this.$nextTick(() => {
	        this.getChatService().clearDialogMark(this.dialogId);
	      });
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onDialogInited, {
	        dialogId: this.dialogId
	      });
	    },
	    onScrollTriggerUp() {
	      if (!this.dialogInited || !this.getContainer()) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll triggered UP');
	      const container = this.getContainer();
	      const oldHeight = container.scrollHeight - container.clientHeight;

	      // Insert messages if there are some
	      if (this.getMessageService().hasPreparedHistoryMessages()) {
	        return this.getMessageService().drawPreparedHistoryMessages().then(() => {
	          this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
	        });
	      }

	      // check if already loading or no more history
	      if (this.getMessageService().isLoading() || !this.dialog.hasPrevPage) {
	        return false;
	      }

	      // Load messages and save them
	      this.getMessageService().loadHistory().then(() => {
	        // Messages loaded and we are at the top
	        if (this.getScrollManager().isAtTheTop()) {
	          im_v2_lib_logger.Logger.warn('Dialog: we are at the top after history request, inserting messages');
	          this.getMessageService().drawPreparedHistoryMessages().then(() => {
	            this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
	          });
	        }
	      });
	    },
	    onScrollTriggerDown() {
	      if (!this.dialogInited || !this.getContainer()) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll triggered DOWN');
	      // Insert messages if there are some
	      if (this.getMessageService().hasPreparedUnreadMessages()) {
	        return this.getMessageService().drawPreparedUnreadMessages();
	      }

	      // check if already loading or no more history
	      if (this.getMessageService().isLoading() || !this.dialog.hasNextPage) {
	        return false;
	      }

	      // Load messages and save them
	      this.getMessageService().loadUnread().then(() => {
	        // Messages loaded and we are at the bottom
	        if (this.getScrollManager().isAroundBottom()) {
	          im_v2_lib_logger.Logger.warn('Dialog: we are at the bottom after unread request, inserting messages');
	          this.getMessageService().drawPreparedUnreadMessages().then(() => {
	            this.getScrollManager().checkIfChatIsScrolledUp();
	          });
	        }
	      });
	    },
	    onScrollToBottom(event) {
	      const {
	        chatId,
	        threshold = im_v2_const.DialogScrollThreshold.halfScreenUp
	      } = event.getData();
	      if (this.dialog.chatId !== chatId) {
	        return;
	      }
	      if (!this.windowFocused || this.hasVisibleCall()) {
	        const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	        this.$nextTick(() => {
	          this.getScrollManager().scrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
	        });
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll to bottom', chatId, threshold);
	      if (threshold === im_v2_const.DialogScrollThreshold.halfScreenUp && this.isScrolledUp) {
	        return;
	      }
	      if (threshold === im_v2_const.DialogScrollThreshold.nearTheBottom && !this.getScrollManager().isAroundBottom()) {
	        return;
	      }
	      this.$nextTick(() => {
	        this.getScrollManager().animatedScrollToBottom();
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
	      this.goToMessageContext(messageId);
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
	      this.goToMessageContext(messageId);
	    },
	    onPinnedMessageUnpin(messageId) {
	      this.getMessageService().unpinMessage(this.dialog.chatId, messageId);
	    },
	    onMessageContextMenuClick(event) {
	      const context = {
	        dialogId: this.dialogId,
	        ...event.message
	      };
	      this.messageMenu.openMenu(context, event.$event.currentTarget);
	      this.messageMenuIsActiveForId = event.message.id;
	    },
	    onMessageQuote(event) {
	      const {
	        message
	      } = event;
	      QuoteManager.sendQuoteEvent(message);
	    },
	    onScroll(event) {
	      this.closeDialogPopups();
	      this.debouncedScrollHandler(event);
	    },
	    onScrollButtonClick() {
	      if (this.getScrollManager().scrollButtonClicked) {
	        this.handleSecondScrollButtonClick();
	        return;
	      }
	      this.getScrollManager().scrollButtonClicked = true;
	      if (this.dialog.counter === 0) {
	        this.getMessageService().loadInitialMessages().then(() => {
	          this.getScrollManager().scrollToBottom();
	        });
	        return;
	      }
	      const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	      if (!firstUnreadId) {
	        this.getMessageService().loadInitialMessages().then(() => {
	          this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
	        });
	      }
	      this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
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
	          mentionReplacement: im_v2_lib_utils.Utils.user.getMentionBbCode(user.id, user.name)
	        });
	        return;
	      }
	      this.avatarMenu.openMenu({
	        user,
	        dialog: this.dialog
	      }, event.currentTarget);
	    },
	    handleSecondScrollButtonClick() {
	      this.getScrollManager().scrollButtonClicked = false;
	      if (this.dialog.hasNextPage) {
	        this.getMessageService().loadContext(this.dialog.lastMessageId).then(() => {
	          main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	            chatId: this.dialog.chatId
	          });
	        }).catch(error => {
	          console.error('ChatDialog: scroll to chat end loadContext error', error);
	        });
	        return;
	      }
	      this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId);
	    },
	    /* endregion Event handlers */
	    hasVisibleCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasVisibleCall();
	    },
	    closeDialogPopups() {
	      var _PopupManager$getPopu, _PopupManager$getPopu2;
	      this.closeMessageMenu();
	      this.chatInfoPopup.show = false;
	      this.avatarMenu.close();
	      (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogReactionUsers)) == null ? void 0 : _PopupManager$getPopu.close();
	      (_PopupManager$getPopu2 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogReadUsers)) == null ? void 0 : _PopupManager$getPopu2.close();
	    },
	    closeMessageMenu() {
	      this.messageMenu.close();
	      this.messageMenuIsActiveForId = 0;
	    },
	    subscribeToEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.mention.openChatInfo, this.onOpenChatInfo);
	      main_core.Event.bind(window, 'focus', this.onWindowFocus);
	      main_core.Event.bind(window, 'blur', this.onWindowBlur);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.mention.openChatInfo, this.onOpenChatInfo);
	      main_core.Event.unbind(window, 'focus', this.onWindowFocus);
	      main_core.Event.unbind(window, 'blur', this.onWindowBlur);
	    },
	    getContainer() {
	      return this.$refs['container'];
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
			<div @scroll="onScroll" class="bx-im-dialog-chat__scroll-container" ref="container">
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
										:is="message.componentId"
										:withTitle="index === 0"
										:item="message"
										:dialogId="dialogId"
										:key="message.id"
										:menuIsActiveForId="messageMenuIsActiveForId"
										:withAvatar="dateGroupItem.avatar.isNeeded"
										:data-viewed="message.viewed"
										@contextMenuClick="onMessageContextMenuClick"
										@quoteMessage="onMessageQuote"
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
				<div v-if="isScrolledUp" @click="onScrollButtonClick" class="bx-im-dialog-chat__scroll-button">
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
		</div>
	`
	};

	exports.ChatDialog = ChatDialog;

}((this.BX.Messenger.v2.Component.Dialog = this.BX.Messenger.v2.Component.Dialog || {}),BX.Main,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX,BX.Event,BX.Messenger.v2.Lib,BX,BX.Im.V2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=chat-dialog.bundle.js.map
