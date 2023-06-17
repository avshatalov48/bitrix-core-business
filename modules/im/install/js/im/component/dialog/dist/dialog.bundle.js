this.BX = this.BX || {};
(function (exports,ui_fonts_opensans,ui_designTokens,im_view_message,im_lib_utils,im_lib_animation,im_lib_logger,main_polyfill_intersectionobserver,ui_vue,im_const,main_core,main_core_events,ui_vue_vuex) {
	'use strict';

	var ObserverType = Object.freeze({
	  read: 'read',
	  none: 'none'
	});
	var RequestMode = Object.freeze({
	  history: 'history',
	  unread: 'unread'
	});
	var DateFormat = Object.freeze({
	  groupTitle: 'groupTitle',
	  readedTitle: 'readedTitle'
	});

	var Placeholder1 = {
	  props: ['element'],
	  created: function created() {
	    var modes = ['self', 'opponent'];
	    var randomIndex = Math.floor(Math.random() * modes.length);
	    this.mode = modes[randomIndex];
	  },
	  computed: {
	    itemClasses: function itemClasses() {
	      var itemClasses = ['im-skeleton-item', 'im-skeleton-item--sm', "".concat(im_const.DialogReferenceClassName.listItem, "-").concat(this.element.id)];
	      if (this.mode === 'self') {
	        itemClasses.push('im-skeleton-item-self');
	      } else {
	        itemClasses.push('im-skeleton-item-opponent');
	      }
	      return itemClasses;
	    }
	  },
	  template: "\n\t\t<div :class=\"itemClasses\" :key=\"element.templateId\">\n\t\t\t<div v-if=\"mode === 'opponent'\" class=\"im-skeleton-logo\"></div>\n\t\t\t<div class=\"im-skeleton-content\">\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 70%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 100%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t<div style=\"max-width: 26px; margin-left: auto;\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-like\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Placeholder2 = {
	  props: ['element'],
	  created: function created() {
	    var modes = ['self', 'opponent'];
	    var randomIndex = Math.floor(Math.random() * modes.length);
	    this.mode = modes[randomIndex];
	  },
	  computed: {
	    itemClasses: function itemClasses() {
	      var itemClasses = ['im-skeleton-item', 'im-skeleton-item--md', "".concat(im_const.DialogReferenceClassName.listItem, "-").concat(this.element.id)];
	      if (this.mode === 'self') {
	        itemClasses.push('im-skeleton-item-self');
	      } else {
	        itemClasses.push('im-skeleton-item-opponent');
	      }
	      return itemClasses;
	    }
	  },
	  template: "\n\t\t<div :class=\"itemClasses\" :key=\"element.templateId\">\n\t\t\t<div v-if=\"mode === 'opponent'\" class=\"im-skeleton-logo\"></div>\n\t\t\t<div class=\"im-skeleton-content\">\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 35%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 100%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 55%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t<div style=\"max-width: 26px; margin-left: auto;\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-like\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var Placeholder3 = {
	  props: ['element'],
	  created: function created() {
	    var modes = ['self', 'opponent'];
	    var randomIndex = Math.floor(Math.random() * modes.length);
	    this.mode = modes[randomIndex];
	  },
	  computed: {
	    itemClasses: function itemClasses() {
	      var itemClasses = ['im-skeleton-item', 'im-skeleton-item--md', "".concat(im_const.DialogReferenceClassName.listItem, "-").concat(this.element.id)];
	      if (this.mode === 'self') {
	        itemClasses.push('im-skeleton-item-self');
	      } else {
	        itemClasses.push('im-skeleton-item-opponent');
	      }
	      return itemClasses;
	    }
	  },
	  template: "\n\t\t<div :class=\"itemClasses\" :key=\"element.templateId\">\n\t\t\t<div v-if=\"mode === 'opponent'\" class=\"im-skeleton-logo\"></div>\n\t\t\t<div class=\"im-skeleton-content\">\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 35%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 100%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 55%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t<div style=\"max-width: 26px; margin-left: auto;\" class=\"im-skeleton-line\"></div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"im-skeleton-like\"></div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var MessageList = {
	  /**
	   * @emits EventType.dialog.readMessage
	   * @emits EventType.dialog.clickOnDialog
	   * @emits EventType.dialog.clickOnCommand
	   * @emits EventType.dialog.clickOnMention
	   * @emits EventType.dialog.clickOnReadList
	   */
	  props: {
	    userId: {
	      type: Number,
	      "default": 0
	    },
	    dialogId: {
	      type: String,
	      "default": "0"
	    },
	    messageLimit: {
	      type: Number,
	      "default": 50
	    },
	    enableReadMessages: {
	      type: Boolean,
	      "default": true
	    },
	    enableReactions: {
	      type: Boolean,
	      "default": true
	    },
	    enableDateActions: {
	      type: Boolean,
	      "default": true
	    },
	    enableCreateContent: {
	      type: Boolean,
	      "default": true
	    },
	    enableGestureQuote: {
	      type: Boolean,
	      "default": true
	    },
	    enableGestureQuoteFromRight: {
	      type: Boolean,
	      "default": true
	    },
	    enableGestureMenu: {
	      type: Boolean,
	      "default": false
	    },
	    showMessageUserName: {
	      type: Boolean,
	      "default": true
	    },
	    showMessageAvatar: {
	      type: Boolean,
	      "default": true
	    },
	    showMessageMenu: {
	      type: Boolean,
	      "default": true
	    }
	  },
	  components: {
	    Placeholder1: Placeholder1,
	    Placeholder2: Placeholder2,
	    Placeholder3: Placeholder3
	  },
	  data: function data() {
	    return {
	      messagesSet: false,
	      scrollAnimating: false,
	      showScrollButton: false,
	      captureMove: false,
	      capturedMoveEvent: null,
	      lastMessageId: null,
	      isRequestingHistory: false,
	      historyPagesRequested: 0,
	      stopHistoryLoading: false,
	      isRequestingUnread: false,
	      unreadPagesRequested: 0,
	      placeholderCount: 0,
	      pagesLoaded: 0
	    };
	  },
	  created: function created() {
	    im_lib_logger.Logger.warn('MessageList component is created');
	    this.initParams();
	    this.initEvents();
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.observers = {};
	    clearTimeout(this.scrollButtonShowTimeout);
	    this.clearEvents();
	  },
	  mounted: function mounted() {
	    this.windowFocused = im_lib_utils.Utils.platform.isBitrixMobile() ? true : document.hasFocus();
	    this.getMessageIdsForPagination();
	    this.scrollOnStart();
	  },
	  watch: {
	    // after each dialog switch (without switching to loading state)
	    // we reset messagesSet flag and run scroll on start routine
	    dialogId: function dialogId(newValue, oldValue) {
	      var _this = this;
	      im_lib_logger.Logger.warn('new dialogId in message-list', newValue);
	      this.messagesSet = false;
	      this.$nextTick(function () {
	        _this.scrollOnStart();
	      });
	    }
	  },
	  computed: _objectSpread({
	    TemplateType: function TemplateType() {
	      return im_const.DialogTemplateType;
	    },
	    ObserverType: function ObserverType$$1() {
	      return ObserverType;
	    },
	    DialogReferenceClassName: function DialogReferenceClassName() {
	      return im_const.DialogReferenceClassName;
	    },
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases('IM_MESSENGER_DIALOG_', this);
	    },
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    chatId: function chatId() {
	      if (this.application) {
	        return this.application.dialog.chatId;
	      }
	    },
	    collection: function collection() {
	      return this.$store.getters['messages/get'](this.chatId);
	    },
	    formattedCollection: function formattedCollection() {
	      var _this2 = this;
	      this.lastMessageId = 0; //used in readed status
	      this.lastMessageAuthorId = 0; //used in readed status
	      this.firstUnreadMessageId = 0;
	      var lastAuthorId = 0; //used for delimeters
	      var dateGroups = {}; //date grouping nodes
	      var collection = []; //array to return

	      this.collection.forEach(function (element) {
	        if (_this2.messagesSet && (_this2.lastHistoryMessageId === null || _this2.lastHistoryMessageId > element.id)) {
	          im_lib_logger.Logger.warn('setting new lastHistoryMessageId', element.id);
	          _this2.lastHistoryMessageId = element.id;
	        }
	        _this2.lastMessageId = element.id;
	        var group = _this2.getDateGroup(element.date);
	        if (!dateGroups[group.title]) {
	          dateGroups[group.title] = group.id;
	          collection.push(_this2.getDateGroupBlock(group.id, group.title));
	        } else if (lastAuthorId !== element.authorId) {
	          collection.push(_this2.getDelimiterBlock(element.id));
	        }
	        if (element.unread && !_this2.firstUnreadMessageId) {
	          _this2.firstUnreadMessageId = element.id;
	        }
	        collection.push(element);
	        lastAuthorId = element.authorId;
	      });

	      //remembering author of last message - used in readed status
	      this.lastMessageAuthorId = lastAuthorId;
	      return collection;
	    },
	    writingStatusText: function writingStatusText() {
	      var _this3 = this;
	      clearTimeout(this.scrollToTimeout);
	      if (this.dialog.writingList.length === 0) {
	        return '';
	      }

	      //scroll to bottom
	      if (!this.scrollChangedByUser && !this.showScrollButton) {
	        this.scrollToTimeout = setTimeout(function () {
	          return _this3.animatedScrollToPosition({
	            duration: 500
	          });
	        }, 300);
	      }
	      var text = this.dialog.writingList.map(function (element) {
	        return element.userName;
	      }).join(', ');
	      return this.localize['IM_MESSENGER_DIALOG_WRITES_MESSAGE'].replace('#USER#', text);
	    },
	    statusReaded: function statusReaded() {
	      var _this4 = this;
	      clearTimeout(this.scrollToTimeout);
	      if (this.dialog.readedList.length === 0) {
	        return '';
	      }
	      var text = '';
	      if (this.dialog.type === im_const.DialogType["private"]) {
	        var record = this.dialog.readedList[0];
	        if (record.messageId === this.lastMessageId && record.userId !== this.lastMessageAuthorId) {
	          var dateFormat = this.getDateFormat(DateFormat.readedTitle);
	          var formattedDate = this.getDateObject().format(dateFormat, record.date);
	          text = this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_USER'].replace('#DATE#', formattedDate);
	        }
	      } else {
	        var readedList = this.dialog.readedList.filter(function (record) {
	          return record.messageId === _this4.lastMessageId && record.userId !== _this4.lastMessageAuthorId;
	        });
	        if (readedList.length === 1) {
	          text = this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT'].replace('#USERS#', readedList[0].userName);
	        } else if (readedList.length > 1) {
	          text = this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT'].replace('#USERS#', this.localize['IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT_PLURAL'].replace('#USER#', readedList[0].userName).replace('#COUNT#', readedList.length - 1).replace('[LINK]', '').replace('[/LINK]', ''));
	        }
	      }
	      if (!text) {
	        return '';
	      }

	      //scroll to bottom
	      if (!this.scrollChangedByUser && !this.showScrollButton) {
	        this.scrollToTimeout = setTimeout(function () {
	          return _this4.animatedScrollToPosition({
	            duration: 500
	          });
	        }, 300);
	      }
	      return text;
	    },
	    unreadCounter: function unreadCounter() {
	      return this.dialog.counter > 99 ? 999 : this.dialog.counter;
	    },
	    formattedUnreadCounter: function formattedUnreadCounter() {
	      return this.unreadCounter > 99 ? '99+' : this.unreadCounter;
	    },
	    scrollBlocked: function scrollBlocked() {
	      if (this.application.device.type !== im_const.DeviceType.mobile) {
	        return false;
	      }
	      return this.scrollAnimating || this.captureMove;
	    },
	    isDarkBackground: function isDarkBackground() {
	      return this.application.options.darkBackground;
	    },
	    isMobile: function isMobile() {
	      return this.application.device.type === im_const.DeviceType.mobile;
	    },
	    //new
	    isRequestingData: function isRequestingData() {
	      return this.isRequestingHistory || this.isRequestingUnread;
	    },
	    remainingHistoryPages: function remainingHistoryPages() {
	      return Math.ceil((this.dialog.messageCount - this.collection.length) / this.historyMessageLimit);
	    },
	    remainingUnreadPages: function remainingUnreadPages() {
	      // we dont use unread counter now - we reverted unread counter to be max at 99, so we dont know actual counter

	      if (this.isLastIdInCollection) {
	        return 0;
	      }
	      return Math.ceil((this.dialog.messageCount - this.collection.length) / this.unreadMessageLimit);
	    },
	    unreadInCollection: function unreadInCollection() {
	      return this.collection.filter(function (item) {
	        return item.unread === true;
	      });
	    },
	    isLastIdInCollection: function isLastIdInCollection() {
	      return this.collection.map(function (message) {
	        return message.id;
	      }).includes(this.dialog.lastMessageId);
	    },
	    showStatusPlaceholder: function showStatusPlaceholder() {
	      return !this.writingStatusText && !this.statusReaded;
	    },
	    bodyClasses: function bodyClasses() {
	      return [im_const.DialogReferenceClassName.listBody, {
	        'bx-im-dialog-list-scroll-blocked': this.scrollBlocked,
	        'bx-im-dialog-dark-background': this.isDarkBackground,
	        'bx-im-dialog-mobile': this.isMobile
	      }];
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  methods: {
	    /* region 01. Init and destroy */initParams: function initParams() {
	      this.placeholdersComposition = this.getPlaceholdersComposition();
	      this.historyMessageLimit = 50;
	      this.unreadMessageLimit = 50;
	      this.showScrollButton = this.unreadCounter > 0;
	      this.scrollingDownThreshold = 1000;
	      this.scrollingUpThreshold = 1000;
	      this.messageScrollOffset = 20;
	      this.lastScroll = 0;
	      this.scrollChangedByUser = false;
	      this.scrollButtonDiff = 100;
	      this.scrollButtonShowTimeout = null;
	      this.scrollPositionChangeTime = new Date().getTime();
	      this.lastRequestTime = new Date().getTime();
	      this.observers = {};
	      this.lastAuthorId = 0;
	      this.lastHistoryMessageId = null;
	      this.firstUnreadMessageId = null;
	      this.lastUnreadMessageId = null;
	      this.dateFormatFunction = null;
	      this.cachedDateGroups = {};
	      this.readMessageQueue = [];
	      this.readMessageTarget = {};
	      this.readVisibleMessagesDelayed = im_lib_utils.Utils.debounce(this.readVisibleMessages, 50, this);
	      this.requestHistoryDelayed = im_lib_utils.Utils.debounce(this.requestHistory, 50, this);
	    },
	    initEvents: function initEvents() {
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.scrollOnStart, this.onScrollOnStart);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.readVisibleMessages, this.onReadVisibleMessages);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.newMessage, this.onNewMessage);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.requestUnread, this.onExternalUnreadRequest);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.messagesSet, this.onMessagesSet);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.beforeMobileKeyboard, this.onBeforeMobileKeyboard);
	      window.addEventListener("orientationchange", this.onOrientationChange);
	      window.addEventListener('focus', this.onWindowFocus);
	      window.addEventListener('blur', this.onWindowBlur);
	      ui_vue.BitrixVue.event.$on('bitrixmobile:controller:focus', this.onWindowFocus);
	      ui_vue.BitrixVue.event.$on('bitrixmobile:controller:blur', this.onWindowBlur);
	    },
	    clearEvents: function clearEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.scrollOnStart, this.onScrollOnStart);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.readVisibleMessages, this.onReadVisibleMessages);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.newMessage, this.onNewMessage);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.requestUnread, this.onExternalUnreadRequest);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.messagesSet, this.onMessagesSet);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.beforeMobileKeyboard, this.onBeforeMobileKeyboard);
	      window.removeEventListener("orientationchange", this.onOrientationChange);
	      window.removeEventListener('focus', this.onWindowFocus);
	      window.removeEventListener('blur', this.onWindowBlur);
	      ui_vue.BitrixVue.event.$off('bitrixmobile:controller:focus', this.onWindowFocus);
	      ui_vue.BitrixVue.event.$off('bitrixmobile:controller:blur', this.onWindowBlur);
	    },
	    /* endregion 01. Init and destroy */
	    /* region 02. Event handlers */
	    onDialogClick: function onDialogClick(event) {
	      if (ui_vue.BitrixVue.testNode(event.target, {
	        className: 'bx-im-message-command'
	      })) {
	        this.onCommandClick(event);
	      } else if (ui_vue.BitrixVue.testNode(event.target, {
	        className: 'bx-im-mention'
	      })) {
	        this.onMentionClick(event);
	      }
	      this.windowFocused = true;
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnDialog, {
	        event: event
	      });
	    },
	    onDialogMove: function onDialogMove(event) {
	      if (!this.captureMove) {
	        return;
	      }
	      this.capturedMoveEvent = event;
	    },
	    onCommandClick: function onCommandClick(event) {
	      var value = '';
	      if (event.target.dataset.entity === 'send' || event.target.dataset.entity === 'put') {
	        value = event.target.nextSibling.innerHTML;
	      } else if (event.target.dataset.entity === 'call') {
	        value = event.target.dataset.command;
	      }
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnCommand, {
	        type: event.target.dataset.entity,
	        value: value,
	        event: event
	      });
	    },
	    onMentionClick: function onMentionClick(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnMention, {
	        type: event.target.dataset.type,
	        value: event.target.dataset.value,
	        event: event
	      });
	    },
	    onOrientationChange: function onOrientationChange() {
	      var _this5 = this;
	      clearTimeout(this.scrollToTimeout);
	      if (this.application.device.type !== im_const.DeviceType.mobile) {
	        return false;
	      }
	      im_lib_logger.Logger.log('Orientation changed');
	      if (!this.scrollChangedByUser) {
	        this.scrollToTimeout = setTimeout(function () {
	          return _this5.scrollToBottom({
	            force: true
	          });
	        }, 300);
	      }
	    },
	    onWindowFocus: function onWindowFocus() {
	      this.windowFocused = true;
	      this.readVisibleMessages();
	      return true;
	    },
	    onWindowBlur: function onWindowBlur() {
	      this.windowFocused = false;
	    },
	    onScrollToBottom: function onScrollToBottom() {
	      var _ref = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	        _ref$data = _ref.data,
	        event = _ref$data === void 0 ? {
	          chatId: 0,
	          force: false,
	          cancelIfScrollChange: false,
	          duration: null
	        } : _ref$data;
	      if (event.chatId !== this.chatId) {
	        return false;
	      }
	      im_lib_logger.Logger.warn('onScrollToBottom', event);
	      event.force = event.force === true;
	      event.cancelIfScrollChange = event.cancelIfScrollChange === true;
	      if (this.firstUnreadMessageId) {
	        im_lib_logger.Logger.warn('Dialog.onScrollToBottom: canceled - unread messages');
	        return false;
	      }
	      if (event.cancelIfScrollChange && this.scrollChangedByUser && this.scrollBeforeMobileKeyboard) {
	        var body = this.$refs.body;
	        this.scrollAfterMobileKeyboard = body.scrollHeight - body.scrollTop - body.clientHeight;
	        var scrollDiff = this.scrollAfterMobileKeyboard - this.scrollBeforeMobileKeyboard;
	        this.animatedScrollToPosition({
	          start: body.scrollTop,
	          end: body.scrollTop + scrollDiff
	        });
	        return true;
	      }
	      this.scrollToBottom(event);
	      return true;
	    },
	    onReadVisibleMessages: function onReadVisibleMessages() {
	      var _ref2 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	        _ref2$data = _ref2.data,
	        event = _ref2$data === void 0 ? {
	          chatId: 0
	        } : _ref2$data;
	      if (event.chatId !== this.chatId) {
	        return false;
	      }
	      im_lib_logger.Logger.warn('onReadVisibleMessages');
	      this.readVisibleMessagesDelayed();
	      return true;
	    },
	    onClickOnReadList: function onClickOnReadList(event) {
	      var _this6 = this;
	      var readedList = this.dialog.readedList.filter(function (record) {
	        return record.messageId === _this6.lastMessageId && record.userId !== _this6.lastMessageAuthorId;
	      });
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnReadList, {
	        list: readedList,
	        event: event
	      });
	    },
	    onDragMessage: function onDragMessage(event) {
	      if (!this.windowFocused) {
	        return false;
	      }
	      this.captureMove = event.result;
	      if (!event.result) {
	        this.capturedMoveEvent = null;
	      }
	    },
	    onScroll: function onScroll(event) {
	      if (this.isScrolling) {
	        return false;
	      }
	      clearTimeout(this.scrollToTimeout);
	      this.currentScroll = event.target.scrollTop;
	      var isScrollingDown = this.lastScroll < this.currentScroll;
	      var isScrollingUp = !isScrollingDown;
	      if (isScrollingUp && this.scrollButtonClicked) {
	        im_lib_logger.Logger.warn('scrollUp - reset scroll button clicks');
	        this.scrollButtonClicked = false;
	      }
	      var leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
	      if (this.currentScroll > 0 && isScrollingDown && leftSpaceBottom < this.scrollingDownThreshold) {
	        this.onScrollDown();
	      } else if (isScrollingUp && this.currentScroll <= this.scrollingUpThreshold) {
	        this.onScrollUp();
	      }

	      //remember current scroll to compare with new ones
	      this.lastScroll = this.currentScroll;
	      this.scrollPositionChangeTime = new Date().getTime();
	      //show or hide scroll button
	      this.manageScrollButton(event);
	    },
	    onScrollDown: function onScrollDown() {
	      var _this7 = this;
	      if (!this.messagesSet || this.isLastIdInCollection) {
	        return false;
	      }
	      // Logger.warn('---');
	      // Logger.warn('Want to load unread');
	      // Logger.warn('this.isRequestingData', this.isRequestingData);
	      // Logger.warn('this.unreadPagesRequested', this.unreadPagesRequested);
	      // Logger.warn('this.remainingUnreadPages', this.remainingUnreadPages);
	      if (this.isRequestingData && this.remainingUnreadPages > 0) {
	        this.drawPlaceholders(RequestMode.unread).then(function () {
	          _this7.unreadPagesRequested += 1;
	          im_lib_logger.Logger.warn('Already loading! Draw placeholders and add request, total - ', _this7.unreadPagesRequested);
	        });
	      } else if (!this.isRequestingData && this.remainingUnreadPages > 0) {
	        im_lib_logger.Logger.warn('Starting new unread request');
	        this.isRequestingUnread = true;
	        this.drawPlaceholders(RequestMode.unread).then(function () {
	          _this7.requestUnread();
	        });
	      }
	    },
	    onScrollUp: function onScrollUp() {
	      var _this8 = this;
	      if (!this.messagesSet || this.stopHistoryLoading) {
	        return false;
	      }
	      this.projectedPagesToLoad = 1;

	      //draw 3 sets of placeholders if we are close to top of container
	      if (!this.isMobile && this.$refs.body.scrollTop < this.$refs.body.scrollHeight / 4) {
	        this.projectedPagesToLoad = 3;
	      }

	      // Logger.warn('---');
	      // Logger.warn('Want to load history');
	      // Logger.warn('this.isRequestingData', this.isRequestingData);
	      // Logger.warn('this.historyPagesRequested', this.historyPagesRequested);
	      // Logger.warn('this.remainingHistoryPages', this.remainingHistoryPages);
	      if (this.isRequestingData && this.remainingHistoryPages > 0) {
	        var currentBodyHeight = this.$refs.body.scrollHeight;
	        this.drawPlaceholders(RequestMode.history, this.projectedPagesToLoad).then(function () {
	          if (!_this8.isOverflowAnchorSupported()) {
	            _this8.enableUserScroll();
	          }
	          _this8.historyPagesRequested += _this8.projectedPagesToLoad;
	          im_lib_logger.Logger.warn('Already loading! Draw placeholders and add request, total - ', _this8.historyPagesRequested);
	        });
	        if (!this.isOverflowAnchorSupported()) {
	          im_lib_logger.Logger.warn('Disabling user scroll');
	          this.$nextTick(function () {
	            var heightDifference = _this8.$refs.body.scrollHeight - currentBodyHeight;
	            _this8.disableUserScroll();
	            _this8.forceScrollToPosition(_this8.$refs.body.scrollTop + heightDifference);
	          });
	        }
	      } else if (!this.isRequestingData && this.remainingHistoryPages > 0) {
	        im_lib_logger.Logger.warn('Starting new history request');
	        this.isRequestingHistory = true;
	        var _currentBodyHeight = this.$refs.body.scrollHeight;
	        this.drawPlaceholders(RequestMode.history, this.projectedPagesToLoad).then(function () {
	          _this8.historyPagesRequested = _this8.projectedPagesToLoad - 1;
	          if (!_this8.isOverflowAnchorSupported()) {
	            _this8.enableUserScroll();
	          }
	          _this8.requestHistory();
	        });
	        //will run right after drawing placeholders, before .then()
	        if (!this.isOverflowAnchorSupported()) {
	          im_lib_logger.Logger.warn('Disabling user scroll');
	          this.$nextTick(function () {
	            var heightDifference = _this8.$refs.body.scrollHeight - _currentBodyHeight;
	            _this8.disableUserScroll();
	            _this8.forceScrollToPosition(_this8.$refs.body.scrollTop + heightDifference);
	          });
	        }
	      }
	    },
	    //TODO: move
	    isOverflowAnchorSupported: function isOverflowAnchorSupported() {
	      return !im_lib_utils.Utils.platform.isBitrixMobile() && !im_lib_utils.Utils.browser.isIe() && !im_lib_utils.Utils.browser.isSafari() && !im_lib_utils.Utils.browser.isSafariBased();
	    },
	    disableUserScroll: function disableUserScroll() {
	      this.$refs.body.classList.add('bx-im-dialog-list-scroll-blocked');
	    },
	    enableUserScroll: function enableUserScroll() {
	      this.$refs.body.classList.remove('bx-im-dialog-list-scroll-blocked');
	    },
	    onScrollButtonClick: function onScrollButtonClick() {
	      im_lib_logger.Logger.warn('Scroll button click', this.scrollButtonClicked);
	      // TODO: now we just do nothing if button was clicked during data request (history or unread)
	      if (this.isRequestingData) {
	        return false;
	      }

	      //we dont have unread - just scroll to bottom
	      if (this.unreadCounter === 0) {
	        this.scrollToBottom();
	        return true;
	      }

	      //it's a second click on button - scroll to last page if we have one
	      if (this.scrollButtonClicked && this.remainingUnreadPages > 0) {
	        im_lib_logger.Logger.warn('Second click on scroll button');
	        this.scrollToLastPage();
	        return true;
	      }

	      //it's a first click - just set the flag and move on
	      this.scrollButtonClicked = true;
	      this.scrollToBottom();
	    },
	    onNewMessage: function onNewMessage(_ref3) {
	      var _this9 = this;
	      var _ref3$data = _ref3.data,
	        chatId = _ref3$data.chatId,
	        messageId = _ref3$data.messageId;
	      if (chatId !== this.chatId) {
	        return false;
	      }
	      im_lib_logger.Logger.warn('Received new message from pull', messageId);
	      if (this.showScrollButton) {
	        return false;
	      }
	      this.$nextTick(function () {
	        //non-focus handling
	        if (!_this9.windowFocused) {
	          var availableScrollHeight = _this9.$refs['body'].scrollHeight - _this9.$refs['body'].clientHeight;
	          if (_this9.currentScroll < availableScrollHeight) {
	            //show scroll button when out of focus and all visible space is filled with unread messaages already
	            _this9.showScrollButton = true;
	          }
	          _this9.scrollToFirstUnreadMessage();
	          return true;
	        }

	        //big message handling
	        var messageElement = _this9.getElementById(messageId);
	        if (!messageElement) {
	          return false;
	        }
	        //if big message - scroll to top of it
	        var body = _this9.$refs.body;
	        if (messageElement.clientHeight > body.clientHeight) {
	          _this9.scrollToMessage({
	            messageId: messageId
	          });
	          return true;
	        }
	        //else - scroll to bottom
	        _this9.animatedScrollToPosition();
	      });
	    },
	    onMessagesSet: function onMessagesSet(_ref4) {
	      var event = _ref4.data;
	      if (event.chatId !== this.chatId) {
	        return false;
	      }
	      if (this.messagesSet === true) {
	        im_lib_logger.Logger.warn('messages are already set');
	        return false;
	      }
	      im_lib_logger.Logger.warn('onMessagesSet', event.chatId);
	      this.messagesSet = true;
	      var force = false;
	      //if we are in top half of container - force scroll to first unread, else - animated scroll
	      if (this.$refs.body.scrollTop < this.$refs.body.scrollHeight / 2) {
	        force = true;
	      }
	      this.scrollToBottom({
	        force: force,
	        cancelIfScrollChange: false
	      });
	    },
	    onBeforeMobileKeyboard: function onBeforeMobileKeyboard(_ref5) {
	      var event = _ref5.data;
	      var body = this.$refs.body;
	      this.scrollBeforeMobileKeyboard = body.scrollHeight - body.scrollTop - body.clientHeight;
	    },
	    onExternalUnreadRequest: function onExternalUnreadRequest() {
	      var _this10 = this;
	      var _ref6 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	        _ref6$data = _ref6.data,
	        event = _ref6$data === void 0 ? {
	          chatId: 0
	        } : _ref6$data;
	      if (event.chatId !== this.chatId) {
	        return false;
	      }
	      im_lib_logger.Logger.warn('onExternalUnreadRequest');
	      this.isRequestingUnread = true;
	      this.drawPlaceholders(RequestMode.unread).then(function () {
	        return _this10.requestUnread();
	      });
	      this.externalUnreadRequestResolve = null;
	      return new Promise(function (resolve, reject) {
	        _this10.externalUnreadRequestResolve = resolve;
	      });
	    },
	    onScrollOnStart: function onScrollOnStart(_ref7) {
	      var event = _ref7.data;
	      if (event.chatId !== this.chatId) {
	        return false;
	      }
	      this.scrollOnStart({
	        force: false
	      });
	    },
	    /* endregion 02. Event handlers */
	    /* region 03. Scrolling */
	    scrollOnStart: function scrollOnStart() {
	      var _ref8 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	        _ref8$force = _ref8.force,
	        force = _ref8$force === void 0 ? true : _ref8$force;
	      im_lib_logger.Logger.warn('scrolling on start of dialog');
	      var unreadId = this.getFirstUnreadMessage();
	      if (unreadId) {
	        this.scrollToFirstUnreadMessage(unreadId, force);
	      } else {
	        var body = this.$refs.body;
	        this.forceScrollToPosition(body.scrollHeight - body.clientHeight);
	      }
	    },
	    //scroll to first unread if counter > 0, else scroll to bottom
	    scrollToBottom: function scrollToBottom() {
	      var _ref9 = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {},
	        _ref9$force = _ref9.force,
	        force = _ref9$force === void 0 ? false : _ref9$force,
	        _ref9$cancelIfScrollC = _ref9.cancelIfScrollChange,
	        cancelIfScrollChange = _ref9$cancelIfScrollC === void 0 ? false : _ref9$cancelIfScrollC,
	        _ref9$duration = _ref9.duration,
	        duration = _ref9$duration === void 0 ? null : _ref9$duration;
	      im_lib_logger.Logger.warn('scroll to bottom', force, cancelIfScrollChange, duration);
	      if (cancelIfScrollChange && this.scrollChangedByUser) {
	        return false;
	      }
	      var body = this.$refs.body;

	      //scroll to first unread message if there are unread messages
	      if (this.dialog.counter > 0) {
	        var scrollToMessageId = this.dialog.counter > 1 && this.firstUnreadMessageId ? this.firstUnreadMessageId : this.lastMessageId;
	        this.scrollToFirstUnreadMessage(scrollToMessageId, force);
	        return true;
	      }

	      //hide scroll button because we will scroll to bottom
	      this.showScrollButton = false;

	      //without animation
	      if (force) {
	        this.forceScrollToPosition(body.scrollHeight - body.clientHeight);
	      }
	      //with animation
	      else {
	        var scrollParams = {};
	        if (duration) {
	          scrollParams.duration = duration;
	        }
	        this.animatedScrollToPosition(_objectSpread({}, scrollParams));
	      }
	    },
	    scrollToFirstUnreadMessage: function scrollToFirstUnreadMessage() {
	      var unreadId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      im_lib_logger.Logger.warn('scroll to first unread');
	      var element = false;
	      if (unreadId !== null) {
	        element = this.getElementById(unreadId);
	      }
	      if (!element) {
	        unreadId = this.getFirstUnreadMessage();
	      }
	      this.scrollToMessage({
	        messageId: unreadId,
	        force: force
	      });
	    },
	    //scroll to message - can be set at the top or at the bottom of screen
	    scrollToMessage: function scrollToMessage(_ref10) {
	      var _ref10$messageId = _ref10.messageId,
	        messageId = _ref10$messageId === void 0 ? 0 : _ref10$messageId,
	        _ref10$force = _ref10.force,
	        force = _ref10$force === void 0 ? false : _ref10$force,
	        _ref10$stickToTop = _ref10.stickToTop,
	        stickToTop = _ref10$stickToTop === void 0 ? true : _ref10$stickToTop;
	      im_lib_logger.Logger.warn('scroll to message');
	      var body = this.$refs.body;
	      var element = this.getElementById(messageId);
	      var end = 0;
	      if (!element) {
	        //if no element found in DOM - scroll to top
	        if (stickToTop) {
	          end = 10;
	        }
	        //if no element and stickToTop = false - scroll to bottom
	        else {
	          end = body.scrollHeight - body.clientHeight;
	        }
	      } else if (stickToTop) {
	        //message will be at the top of screen (+little offset)
	        end = element.offsetTop - this.messageScrollOffset / 2;
	      } else {
	        //message will be at the bottom of screen (+little offset)
	        end = element.offsetTop + element.offsetHeight - body.clientHeight + this.messageScrollOffset / 2;
	      }
	      if (force) {
	        this.forceScrollToPosition(end);
	      } else {
	        this.animatedScrollToPosition({
	          end: end
	        });
	      }
	      return true;
	    },
	    forceScrollToPosition: function forceScrollToPosition(position) {
	      im_lib_logger.Logger.warn('Force scroll to position - ', position);
	      var body = this.$refs.body;
	      if (!body) {
	        return false;
	      }
	      if (this.animateScrollId) {
	        im_lib_animation.Animation.cancel(this.animateScrollId);
	        this.scrollAnimating = false;
	        this.animateScrollId = null;
	      }
	      body.scrollTop = position;
	    },
	    //scroll to provided position with animation, by default - to the bottom
	    animatedScrollToPosition: function animatedScrollToPosition() {
	      var _this11 = this;
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      im_lib_logger.Logger.warn('Animated scroll to - ', params);
	      if (this.animateScrollId) {
	        im_lib_animation.Animation.cancel(this.animateScrollId);
	        this.scrollAnimating = false;
	      }
	      if (typeof params === 'function') {
	        params = {
	          callback: params
	        };
	      }
	      var body = this.$refs.body;
	      if (!body) {
	        if (params.callback && typeof params.callback === 'function') {
	          params.callback();
	        }
	        this.animateScrollId = null;
	        this.scrollAnimating = false;
	        return true;
	      }
	      if (im_lib_utils.Utils.platform.isIos() && im_lib_utils.Utils.platform.getIosVersion() > 12 && im_lib_utils.Utils.platform.getIosVersion() < 13.2) {
	        body.scrollTop = body.scrollHeight - body.clientHeight;
	        return true;
	      }
	      var _params = params,
	        _params$start = _params.start,
	        start = _params$start === void 0 ? body.scrollTop : _params$start,
	        _params$end = _params.end,
	        end = _params$end === void 0 ? body.scrollHeight - body.clientHeight : _params$end,
	        _params$increment = _params.increment,
	        increment = _params$increment === void 0 ? 20 : _params$increment,
	        _callback = _params.callback,
	        _params$duration = _params.duration,
	        duration = _params$duration === void 0 ? 500 : _params$duration;
	      var container = this.$refs.container;
	      if (container && end - start > container.offsetHeight * 3) {
	        start = end - container.offsetHeight * 3;
	        im_lib_logger.Logger.warn('Dialog.animatedScroll: Scroll trajectory has been reduced');
	      }
	      this.scrollAnimating = true;
	      im_lib_logger.Logger.warn('Dialog.animatedScroll: User scroll blocked while scrolling');
	      this.animateScrollId = im_lib_animation.Animation.start({
	        start: start,
	        end: end,
	        increment: increment,
	        duration: duration,
	        element: body,
	        elementProperty: 'scrollTop',
	        callback: function callback() {
	          _this11.animateScrollId = null;
	          _this11.scrollAnimating = false;
	          if (_callback && typeof _callback === 'function') {
	            _callback();
	          }
	        }
	      });
	    },
	    /* endregion 03. Scrolling */
	    /* region 04. Placeholders */
	    drawPlaceholders: function drawPlaceholders(requestMode) {
	      var pagesCount = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 1;
	      var limit = requestMode === RequestMode.history ? this.historyMessageLimit : this.unreadMessageLimit;
	      var placeholders = this.generatePlaceholders(limit, pagesCount);
	      return this.$store.dispatch('messages/addPlaceholders', {
	        placeholders: placeholders,
	        requestMode: requestMode
	      });
	    },
	    generatePlaceholders: function generatePlaceholders(amount, pagesCount) {
	      var placeholders = [];
	      for (var i = 0; i < pagesCount; i++) {
	        for (var j = 0; j < this.placeholdersComposition.length; j++) {
	          placeholders.push({
	            id: "placeholder".concat(this.placeholderCount),
	            chatId: this.chatId,
	            templateType: im_const.DialogTemplateType.placeholder,
	            placeholderType: this.placeholdersComposition[j],
	            unread: false
	          });
	          this.placeholderCount++;
	        }
	      }
	      return placeholders;
	    },
	    getPlaceholdersComposition: function getPlaceholdersComposition() {
	      //randomize set of placeholder types (sums up to ~2400px height)
	      //placeholder1 x8, placeholder2 x6, placeholder3 x8
	      return [1, 1, 1, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3].sort(function () {
	        return 0.5 - Math.random();
	      });
	    },
	    /* endregion 04. Placeholders */
	    /* region 05. History request */
	    requestHistory: function requestHistory() {
	      var _this12 = this;
	      return this.$Bitrix.RestClient.get().callMethod(im_const.RestMethod.imDialogMessagesGet, {
	        chat_id: this.chatId,
	        last_id: this.lastHistoryMessageId,
	        limit: this.historyMessageLimit,
	        convert_text: 'Y'
	      }).then(function (result) {
	        var newMessages = result.data().messages;
	        if (newMessages.length > 0) {
	          _this12.lastHistoryMessageId = newMessages[newMessages.length - 1].id;
	        }
	        if (newMessages.length < _this12.historyMessageLimit) {
	          _this12.stopHistoryLoading = true;
	        }

	        //files and users
	        _this12.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGet, result);
	        return new Promise(function (resolve, reject) {
	          var currentBodyHeight = _this12.$refs.body.scrollHeight;
	          _this12.$store.dispatch('messages/updatePlaceholders', {
	            chatId: _this12.chatId,
	            data: newMessages,
	            firstMessage: _this12.pagesLoaded * _this12.placeholdersComposition.length,
	            amount: _this12.placeholdersComposition.length
	          }).then(function () {
	            if (!_this12.isOverflowAnchorSupported()) {
	              _this12.enableUserScroll();
	            }
	            resolve();
	          });
	          if (!_this12.isOverflowAnchorSupported()) {
	            im_lib_logger.Logger.warn('Disabling user scroll in updating placeholders');
	            _this12.$nextTick(function () {
	              var heightDifference = _this12.$refs.body.scrollHeight - currentBodyHeight;
	              _this12.disableUserScroll();
	              _this12.forceScrollToPosition(_this12.$refs.body.scrollTop + heightDifference);
	            });
	          }
	        });
	      }).then(function () {
	        _this12.pagesLoaded += 1;
	        im_lib_logger.Logger.warn('History page loaded. Total loaded - ', _this12.pagesLoaded);
	        return _this12.onAfterHistoryRequest();
	      })["catch"](function (result) {
	        im_lib_logger.Logger.warn('Request history error', result);
	      });
	    },
	    onAfterHistoryRequest: function onAfterHistoryRequest() {
	      var _this13 = this;
	      im_lib_logger.Logger.warn('onAfterHistoryRequest');
	      if (this.stopHistoryLoading) {
	        im_lib_logger.Logger.warn('stopHistoryLoading, deleting all delayed requests');
	        this.historyPagesRequested = 0;
	      }
	      if (this.historyPagesRequested > 0) {
	        im_lib_logger.Logger.warn('We have delayed requests -', this.historyPagesRequested);
	        this.historyPagesRequested--;
	        return this.requestHistory();
	      } else if (this.$refs.body.scrollTop <= this.scrollingUpThreshold && this.remainingHistoryPages > 0) {
	        im_lib_logger.Logger.warn('currentScroll <= scrollingUpThreshold, requesting next page and scrolling');
	        return this.drawPlaceholders(RequestMode.history).then(function (firstPlaceholderId) {
	          _this13.scrollToMessage({
	            messageId: firstPlaceholderId,
	            force: true,
	            stickToTop: false
	          });
	          return _this13.requestHistory();
	        });
	      } else {
	        im_lib_logger.Logger.warn('No more delayed requests, clearing placeholders');
	        this.$store.dispatch('messages/clearPlaceholders', {
	          chatId: this.chatId
	        });
	        this.isRequestingHistory = false;
	        return true;
	      }
	    },
	    /* endregion 05. History request */
	    /* region 06. Unread request */
	    prepareUnreadRequestParams: function prepareUnreadRequestParams() {
	      var _ref11;
	      return _ref11 = {}, babelHelpers.defineProperty(_ref11, im_const.RestMethodHandler.imDialogRead, [im_const.RestMethod.imDialogRead, {
	        dialog_id: this.dialogId,
	        message_id: this.lastUnreadMessageId
	      }]), babelHelpers.defineProperty(_ref11, im_const.RestMethodHandler.imChatGet, [im_const.RestMethod.imChatGet, {
	        dialog_id: this.dialogId
	      }]), babelHelpers.defineProperty(_ref11, im_const.RestMethodHandler.imDialogMessagesGetUnread, [im_const.RestMethod.imDialogMessagesGet, {
	        chat_id: this.chatId,
	        first_id: this.lastUnreadMessageId,
	        limit: this.unreadMessageLimit,
	        convert_text: 'Y'
	      }]), _ref11;
	    },
	    requestUnread: function requestUnread() {
	      var _this14 = this;
	      if (!this.lastUnreadMessageId) {
	        this.lastUnreadMessageId = this.$store.getters['messages/getLastId'](this.chatId);
	      }
	      if (!this.lastUnreadMessageId) {
	        return false;
	      }
	      main_core_events.EventEmitter.emitAsync(im_const.EventType.dialog.readMessage, {
	        id: this.lastUnreadMessageId,
	        skipTimer: true,
	        skipAjax: true
	      }).then(function () {
	        _this14.$Bitrix.RestClient.get().callBatch(_this14.prepareUnreadRequestParams(), function (response) {
	          return _this14.onUnreadRequest(response);
	        });
	      });
	    },
	    onUnreadRequest: function onUnreadRequest(response) {
	      var _this15 = this;
	      if (!response) {
	        im_lib_logger.Logger.warn('Unread request: callBatch error');
	        return false;
	      }
	      var chatGetResult = response[im_const.RestMethodHandler.imChatGet];
	      if (chatGetResult.error()) {
	        im_lib_logger.Logger.warn('Unread request: imChatGet error', chatGetResult.error());
	        return false;
	      }
	      this.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imChatGet, chatGetResult);
	      var dialogMessageUnread = response[im_const.RestMethodHandler.imDialogMessagesGetUnread];
	      if (dialogMessageUnread.error()) {
	        im_lib_logger.Logger.warn('Unread request: imDialogMessagesGetUnread error', dialogMessageUnread.error());
	        return false;
	      }
	      var newMessages = dialogMessageUnread.data().messages;
	      if (newMessages.length > 0) {
	        this.lastUnreadMessageId = newMessages[newMessages.length - 1].id;
	      }
	      this.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGetUnread, dialogMessageUnread);
	      this.$store.dispatch('messages/updatePlaceholders', {
	        chatId: this.chatId,
	        data: newMessages,
	        firstMessage: this.pagesLoaded * this.placeholdersComposition.length,
	        amount: this.placeholdersComposition.length
	      }).then(function () {
	        _this15.pagesLoaded += 1;
	        im_lib_logger.Logger.warn('Unread page loaded. Total loaded - ', _this15.pagesLoaded);
	        return _this15.onAfterUnreadRequest();
	      })["catch"](function (result) {
	        im_lib_logger.Logger.warn('Unread history error', result);
	      });
	    },
	    onAfterUnreadRequest: function onAfterUnreadRequest() {
	      if (this.unreadPagesRequested > 0) {
	        im_lib_logger.Logger.warn('We have delayed requests -', this.unreadPagesRequested);
	        this.unreadPagesRequested--;
	        return this.requestUnread();
	      } else {
	        im_lib_logger.Logger.warn('No more delayed requests, clearing placeholders');
	        this.$store.dispatch('messages/clearPlaceholders', {
	          chatId: this.chatId
	        });
	        this.isRequestingUnread = false;
	        if (this.externalUnreadRequestResolve) {
	          this.externalUnreadRequestResolve();
	        }
	        return true;
	      }
	    },
	    /* endregion 06. Unread request */
	    /* region 07. Last page request */
	    scrollToLastPage: function scrollToLastPage() {
	      var _this16 = this;
	      im_lib_logger.Logger.warn('Load last page');
	      //draw placeholders at the bottom
	      this.drawPlaceholders(RequestMode.unread).then(function () {
	        //block unread and history requests
	        _this16.isScrolling = true;
	        _this16.animatedScrollToPosition({
	          callback: function callback() {
	            return _this16.onScrollToLastPage();
	          }
	        });
	      });
	    },
	    onScrollToLastPage: function onScrollToLastPage() {
	      var _this17 = this;
	      //hide scroll button
	      this.showScrollButton = false;
	      //set counter to 0
	      this.$store.dispatch('dialogues/update', {
	        dialogId: this.dialogId,
	        fields: {
	          counter: 0
	        }
	      });
	      //clear all messages except placeholders
	      this.$store.dispatch('messages/clear', {
	        chatId: this.chatId,
	        keepPlaceholders: true
	      });
	      //call batch - imDialogRead, imChatGet, imDialogMessagesGet
	      this.$Bitrix.RestClient.get().callBatch(this.prepareLastPageRequestParams(), function (response) {
	        return _this17.onLastPageRequest(response);
	      });
	    },
	    prepareLastPageRequestParams: function prepareLastPageRequestParams() {
	      var _ref12;
	      return _ref12 = {}, babelHelpers.defineProperty(_ref12, im_const.RestMethodHandler.imDialogRead, [im_const.RestMethod.imDialogRead, {
	        dialog_id: this.dialogId
	      }]), babelHelpers.defineProperty(_ref12, im_const.RestMethodHandler.imChatGet, [im_const.RestMethod.imChatGet, {
	        dialog_id: this.dialogId
	      }]), babelHelpers.defineProperty(_ref12, im_const.RestMethodHandler.imDialogMessagesGet, [im_const.RestMethod.imDialogMessagesGet, {
	        chat_id: this.chatId,
	        limit: this.unreadMessageLimit,
	        convert_text: 'Y'
	      }]), _ref12;
	    },
	    onLastPageRequest: function onLastPageRequest(response) {
	      var _this18 = this;
	      if (!response) {
	        im_lib_logger.Logger.warn('Last page request: callBatch error');
	        return false;
	      }

	      //imChatGet handle
	      var chatGetResult = response[im_const.RestMethodHandler.imChatGet];
	      if (chatGetResult.error()) {
	        im_lib_logger.Logger.warn('Last page request: imChatGet error', chatGetResult.error());
	        return false;
	      }
	      this.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imChatGet, chatGetResult);

	      //imDialogMessagesGet handle
	      var lastPageMessages = response[im_const.RestMethodHandler.imDialogMessagesGet];
	      if (lastPageMessages.error()) {
	        im_lib_logger.Logger.warn('Last page request: imDialogMessagesGet error', lastPageMessages.error());
	        return false;
	      }
	      var newMessages = lastPageMessages.data().messages.reverse();
	      //handle files and users
	      this.$Bitrix.Data.get('controller').executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGet, lastPageMessages);
	      //update placeholders to real messages
	      this.$store.dispatch('messages/updatePlaceholders', {
	        chatId: this.chatId,
	        data: newMessages,
	        firstMessage: this.pagesLoaded * this.placeholdersComposition.length,
	        amount: this.placeholdersComposition.length
	      }).then(function () {
	        //get id for history requests and increase pages counter to count placeholders on next requests
	        _this18.lastHistoryMessageId = _this18.collection[0].id;
	        _this18.pagesLoaded += 1;

	        //clear remaining placeholders
	        return _this18.$store.dispatch('messages/clearPlaceholders', {
	          chatId: _this18.chatId
	        });
	      }).then(function () {
	        _this18.scrollToBottom({
	          force: true
	        });
	        //enable history requests on scroll up
	        _this18.stopHistoryLoading = false;
	        _this18.isScrolling = false;
	      })["catch"](function (result) {
	        im_lib_logger.Logger.warn('Unread history error', result);
	      });
	    },
	    /* endregion 07. Last page request */
	    /* region 08. Read messages */
	    readVisibleMessages: function readVisibleMessages() {
	      var _this19 = this;
	      if (!this.windowFocused || !this.messagesSet) {
	        im_lib_logger.Logger.warn('reading is disabled!');
	        return false;
	      }

	      //need to filter that way to empty array after async method on every element was completed
	      this.readMessageQueue = this.readMessageQueue.filter(function (messageId) {
	        if (_this19.readMessageTarget[messageId]) {
	          if (_this19.observers[ObserverType.read]) {
	            _this19.observers[ObserverType.read].unobserve(_this19.readMessageTarget[messageId]);
	          }
	          delete _this19.readMessageTarget[messageId];
	        }
	        _this19.requestReadVisibleMessages(messageId);
	        return false;
	      });
	    },
	    requestReadVisibleMessages: function requestReadVisibleMessages(messageId) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.readMessage, {
	        id: messageId
	      });
	    },
	    /* endregion 08. Read messages */
	    /* region 09. Helpers */
	    getMessageIdsForPagination: function getMessageIdsForPagination() {
	      // console.warn('this.collection.length', this.collection.length);
	      // if (this.collection.length > 0)
	      // {
	      // 	console.warn('this.collection.length', this.collection[0].id);
	      // 	this.lastHistoryMessageId = this.collection[0].id;
	      // }
	      //
	      if (this.unreadInCollection.length > 0) {
	        this.lastUnreadMessageId = this.unreadInCollection[this.unreadInCollection.length - 1].id;
	      }
	    },
	    getFirstUnreadMessage: function getFirstUnreadMessage() {
	      var unreadId = null;
	      for (var index = this.collection.length - 1; index >= 0; index--) {
	        if (!this.collection[index].unread) {
	          break;
	        }
	        unreadId = this.collection[index].id;
	      }
	      return unreadId;
	    },
	    manageScrollButton: function manageScrollButton(event) {
	      var _this20 = this;
	      var availableScrollHeight = event.target.scrollHeight - event.target.clientHeight;
	      this.scrollChangedByUser = this.currentScroll + this.scrollButtonDiff < availableScrollHeight;
	      clearTimeout(this.scrollButtonShowTimeout);
	      this.scrollButtonShowTimeout = setTimeout(function () {
	        if (_this20.scrollChangedByUser) {
	          //if user scroll and there is no scroll button - show it
	          if (!_this20.showScrollButton) {
	            _this20.showScrollButton = true;
	          }
	        } else {
	          //if not user scroll, there was scroll button and no more unread to load - hide it
	          if (_this20.showScrollButton && _this20.remainingUnreadPages === 0) {
	            _this20.showScrollButton = false;
	          }
	        }
	      }, 200);

	      //if we are at the bottom
	      if (event.target.scrollTop === event.target.scrollHeight - event.target.offsetHeight) {
	        clearTimeout(this.scrollButtonShowTimeout);
	        if (this.showScrollButton && this.remainingUnreadPages === 0) {
	          this.showScrollButton = false;
	        }
	      }
	    },
	    getDateObject: function getDateObject() {
	      var _this21 = this;
	      if (this.dateFormatFunction) {
	        return this.dateFormatFunction;
	      }
	      this.dateFormatFunction = Object.create(BX.Main.Date);
	      this.dateFormatFunction._getMessage = function (phrase) {
	        return _this21.$Bitrix.Loc.getMessage(phrase);
	      };
	      return this.dateFormatFunction;
	    },
	    getDateGroup: function getDateGroup(date) {
	      var id = date.toJSON().slice(0, 10);
	      if (this.cachedDateGroups[id]) {
	        return this.cachedDateGroups[id];
	      }
	      var dateFormat = this.getDateFormat(DateFormat.groupTitle);
	      this.cachedDateGroups[id] = {
	        id: id,
	        title: this.getDateObject().format(dateFormat, date)
	      };
	      return this.cachedDateGroups[id];
	    },
	    getDateFormat: function getDateFormat(type) {
	      return im_lib_utils.Utils.date.getFormatType(BX.Messenger.Const.DateFormat[type], this.$Bitrix.Loc.getMessages());
	    },
	    getDateGroupBlock: function getDateGroupBlock() {
	      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	      var text = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	      return {
	        templateId: 'group' + id,
	        templateType: im_const.DialogTemplateType.group,
	        text: text
	      };
	    },
	    getDelimiterBlock: function getDelimiterBlock() {
	      var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	      return {
	        templateId: 'delimiter' + id,
	        templateType: im_const.DialogTemplateType.delimiter
	      };
	    },
	    getObserver: function getObserver(config) {
	      var _this22 = this;
	      if (typeof window.IntersectionObserver === 'undefined' || config.type === ObserverType.none) {
	        return {
	          observe: function observe() {},
	          unobserve: function unobserve() {}
	        };
	      }
	      var observerCallback, observerOptions;
	      observerCallback = function observerCallback(entries) {
	        entries.forEach(function (entry) {
	          var sendReadEvent = false;
	          if (entry.isIntersecting) {
	            //on windows with interface scaling intersectionRatio will never be 1
	            if (entry.intersectionRatio >= 0.99) {
	              sendReadEvent = true;
	            } else if (entry.intersectionRatio > 0 && entry.rootBounds.height < entry.boundingClientRect.height + 20 && entry.intersectionRect.height > entry.rootBounds.height / 2) {
	              sendReadEvent = true;
	            }
	          }
	          if (sendReadEvent) {
	            _this22.readMessageQueue.push(entry.target.dataset.messageId);
	            _this22.readMessageTarget[entry.target.dataset.messageId] = entry.target;
	          } else {
	            _this22.readMessageQueue = _this22.readMessageQueue.filter(function (messageId) {
	              return messageId !== entry.target.dataset.messageId;
	            });
	            delete _this22.readMessageTarget[entry.target.dataset.messageId];
	          }
	          if (_this22.enableReadMessages) {
	            _this22.readVisibleMessagesDelayed();
	          }
	        });
	      };
	      observerOptions = {
	        root: this.$refs.body,
	        threshold: new Array(101).fill(0).map(function (zero, index) {
	          return index * 0.01;
	        })
	      };
	      return new IntersectionObserver(observerCallback, observerOptions);
	    },
	    getElementClass: function getElementClass(elementId) {
	      var classWithId = im_const.DialogReferenceClassName.listItem + '-' + elementId;
	      return ['bx-im-dialog-list-item', im_const.DialogReferenceClassName.listItem, classWithId];
	    },
	    getElementById: function getElementById(elementId) {
	      var body = this.$refs.body;
	      var className = im_const.DialogReferenceClassName.listItem + '-' + elementId;
	      return body.getElementsByClassName(className)[0];
	    },
	    getPlaceholderClass: function getPlaceholderClass(elementId) {
	      var classWithId = im_const.DialogReferenceClassName.listItem + '-' + elementId;
	      return ['im-skeleton-item', 'im-skeleton-item-1', 'im-skeleton-item--sm', classWithId];
	    } /* endregion 09. Helpers */
	  },
	  directives: {
	    'bx-im-directive-dialog-observer': {
	      inserted: function inserted(element, bindings, vnode) {
	        if (bindings.value === ObserverType.none) {
	          return false;
	        }
	        if (!vnode.context.observers[bindings.value]) {
	          vnode.context.observers[bindings.value] = vnode.context.getObserver({
	            type: bindings.value
	          });
	        }
	        vnode.context.observers[bindings.value].observe(element);
	        return true;
	      },
	      unbind: function unbind(element, bindings, vnode) {
	        if (bindings.value === ObserverType.none) {
	          return true;
	        }
	        if (vnode.context.observers[bindings.value]) {
	          vnode.context.observers[bindings.value].unobserve(element);
	        }
	        return true;
	      }
	    }
	  },
	  // language=Vue
	  template: "\n\t<div class=\"bx-im-dialog\" @click=\"onDialogClick\" @touchmove=\"onDialogMove\" ref=\"container\">\n\t\t<div :class=\"bodyClasses\" @scroll.passive=\"onScroll\" ref=\"body\">\n\t\t\t<!-- Main elements loop -->\n\t\t\t<template v-for=\"(element, index) in formattedCollection\">\n\t\t\t\t<!-- Message -->\n\t\t\t\t<template v-if=\"element.templateType === TemplateType.message\">\n\t\t\t\t\t<div\n\t\t\t\t\t\t:class=\"getElementClass(element.id)\"\n\t\t\t\t\t\t:data-message-id=\"element.id\"\n\t\t\t\t\t\t:data-template-id=\"element.templateId\"\n\t\t\t\t\t\t:data-type=\"element.templateType\" \n\t\t\t\t\t\t:key=\"element.templateId\"\n\t\t\t\t\t\tv-bx-im-directive-dialog-observer=\"element.unread? ObserverType.read: ObserverType.none\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<component :is=\"element.params.COMPONENT_ID\"\n\t\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t\t\t:message=\"element\"\n\t\t\t\t\t\t\t:enableReactions=\"enableReactions\"\n\t\t\t\t\t\t\t:enableDateActions=\"enableDateActions\"\n\t\t\t\t\t\t\t:enableCreateContent=\"showMessageMenu\"\n\t\t\t\t\t\t\t:enableGestureQuote=\"enableGestureQuote\"\n\t\t\t\t\t\t\t:enableGestureQuoteFromRight=\"enableGestureQuoteFromRight\"\n\t\t\t\t\t\t\t:enableGestureMenu=\"enableGestureMenu\"\n\t\t\t\t\t\t\t:showName=\"showMessageUserName\"\n\t\t\t\t\t\t\t:showAvatar=\"showMessageAvatar\"\n\t\t\t\t\t\t\t:showMenu=\"showMessageMenu\"\n\t\t\t\t\t\t\t:capturedMoveEvent=\"capturedMoveEvent\"\n\t\t\t\t\t\t\t:referenceContentClassName=\"DialogReferenceClassName.listItem\"\n\t\t\t\t\t\t\t:referenceContentBodyClassName=\"DialogReferenceClassName.listItemBody\"\n\t\t\t\t\t\t\t:referenceContentNameClassName=\"DialogReferenceClassName.listItemName\"\n\t\t\t\t\t\t\t@dragMessage=\"onDragMessage\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Date groups -->\n\t\t\t\t<template v-else-if=\"element.templateType === TemplateType.group\">\n\t\t\t\t\t<div class=\"bx-im-dialog-group\" :data-template-id=\"element.templateId\" :data-type=\"element.templateType\" :key=\"element.templateId\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-group-date\">{{ element.text }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Delimiters -->\n\t\t\t\t<template v-else-if=\"element.templateType === TemplateType.delimiter\">\n\t\t\t\t\t<div class=\"bx-im-dialog-delimiter\" :data-template-id=\"element.templateId\" :data-type=\"element.templateType\" :key=\"element.templateId\"></div>\n\t\t\t\t</template>\n\t\t\t\t<!-- Placeholders -->\n\t\t\t\t<template v-else-if=\"element.templateType === TemplateType.placeholder\">\n\t\t\t\t\t<component :is=\"'Placeholder'+element.placeholderType\" :element=\"element\"/>\n\t\t\t\t</template>\n\t\t\t</template>\n\t\t\t<!-- Writing and readed statuses -->\n\t\t\t<transition name=\"bx-im-dialog-status\">\n\t\t\t\t<template v-if=\"writingStatusText\">\n\t\t\t\t\t<div class=\"bx-im-dialog-status\">\n\t\t\t\t\t\t<span class=\"bx-im-dialog-status-writing\"></span>\n\t\t\t\t\t\t{{ writingStatusText }}\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<template v-else-if=\"statusReaded\">\n\t\t\t\t\t<div class=\"bx-im-dialog-status\" @click=\"onClickOnReadList\">\n\t\t\t\t\t\t{{ statusReaded }}\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</transition>\n\t\t\t<div v-if=\"showStatusPlaceholder\" class=\"bx-im-dialog-status-placeholder\"></div>\n\t\t</div>\n\t\t<!-- Scroll button -->\n\t\t<transition name=\"bx-im-dialog-scroll-button\">\n\t\t\t<div v-show=\"showScrollButton || (unreadCounter > 0 && !isLastIdInCollection)\" class=\"bx-im-dialog-scroll-button-box\" @click=\"onScrollButtonClick\">\n\t\t\t\t<div class=\"bx-im-dialog-scroll-button\">\n\t\t\t\t\t<div v-show=\"unreadCounter\" class=\"bx-im-dialog-scroll-button-counter\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button-counter-digit\">{{ formattedUnreadCounter }}</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button-arrow\"></div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</transition>\n\t</div>\n"
	};

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var ErrorState = {
	  computed: _objectSpread$1({}, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-mobilechat-body\">\n\t\t\t<div class=\"bx-mobilechat-warning-window\">\n\t\t\t\t<div class=\"bx-mobilechat-warning-icon\"></div>\n\t\t\t\t<template v-if=\"application.error.description\">\n\t\t\t\t\t<div class=\"bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg\" v-html=\"application.error.description\"></div>\n\t\t\t\t</template>\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div class=\"bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-warning-msg\">{{$Bitrix.Loc.getMessage('IM_DIALOG_ERROR_TITLE')}}</div>\n\t\t\t\t\t<div class=\"bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg\">{{$Bitrix.Loc.getMessage('IM_DIALOG_ERROR_DESC')}}</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var LoadingState = {
	  data: function data() {
	    return {
	      placeholdersComposition: [],
	      placeholderTypes: [0, 1],
	      placeholderModes: ['self', 'opponent'],
	      placeholdersCount: 20
	    };
	  },
	  created: function created() {
	    for (var i = 0; i < this.placeholdersCount; i++) {
	      var randomType = Math.floor(Math.random() * this.placeholderTypes.length);
	      var randomMode = Math.floor(Math.random() * this.placeholderModes.length);
	      this.placeholdersComposition.push({
	        index: i,
	        type: randomType,
	        mode: this.placeholderModes[randomMode],
	        classes: this.getItemClasses(randomType, randomMode)
	      });
	    }
	  },
	  methods: {
	    getItemClasses: function getItemClasses(type, modeIndex) {
	      var itemClasses = ['im-skeleton-item'];
	      if (this.placeholderModes[modeIndex] === 'self') {
	        itemClasses.push('im-skeleton-item-self');
	      } else {
	        itemClasses.push('im-skeleton-item-opponent');
	      }
	      if (type === 0) {
	        itemClasses.push('im-skeleton-item--sm');
	      } else {
	        itemClasses.push('im-skeleton-item--md');
	      }
	      return itemClasses;
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-mobilechat-placeholder-wrap\">\n\t\t\t<div class=\"bx-mobilechat-placeholder-wrap-visible\">\n\t\t\t\t<template v-for=\"item in placeholdersComposition\">\n\t\t\t\t\t<div :class=\"item.classes\" :key=\"item.index\">\n\t\t\t\t\t\t<div v-if=\"item.mode === 'opponent'\" class=\"im-skeleton-logo\"></div>\n\t\t\t\t\t\t<div class=\"im-skeleton-content\">\n\t\t\t\t\t\t\t<template v-if=\"item.type === 0\">\n\t\t\t\t\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 70%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 100%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 26px; margin-left: auto;\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 35%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 100%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t\t<div class=\"im-skeleton-line-row\">\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 55%\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t\t<div style=\"max-width: 26px; margin-left: auto;\" class=\"im-skeleton-line\"></div>\n\t\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div class=\"im-skeleton-like\"></div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	var EmptyState = {
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-mobilechat-loading-window\">\n\t\t\t<h3 class=\"bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-loading-msg\">\n\t\t  \t\t{{ $Bitrix.Loc.getMessage('IM_DIALOG_EMPTY') }}\n\t\t\t</h3>\n\t\t</div>\n\t"
	};

	var QuotePanel = {
	  /**
	   * @emits EventType.dialog.quotePanelClose
	   */
	  props: {
	    quotePanelData: {
	      type: Object,
	      "default": function _default() {
	        return {
	          id: 0,
	          title: '',
	          description: '',
	          color: ''
	        };
	      }
	    },
	    canClose: {
	      "default": true
	    }
	  },
	  methods: {
	    close: function close(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.quotePanelClose, event);
	    }
	  },
	  computed: {
	    formattedTittle: function formattedTittle() {
	      return this.quotePanelData.title ? this.quotePanelData.title.substr(0, 255) : this.$Bitrix.Loc.getMessage('IM_QUOTE_PANEL_DEFAULT_TITLE');
	    },
	    formattedDescription: function formattedDescription() {
	      return this.quotePanelData.description ? this.quotePanelData.description.substr(0, 255) : '';
	    }
	  },
	  template: "\n\t<transition enter-active-class=\"bx-im-quote-panel-animation-show\" leave-active-class=\"bx-im-quote-panel-animation-close\">\t\t\t\t\n\t\t<div v-if=\"quotePanelData.id > 0\" class=\"bx-im-quote-panel\">\n\t\t\t<div class=\"bx-im-quote-panel-wrap\">\n\t\t\t\t<div class=\"bx-im-quote-panel-box\" :style=\"{borderLeftColor: quotePanelData.color}\">\n\t\t\t\t\t<div class=\"bx-im-quote-panel-box-title\" :style=\"{color: quotePanelData.color}\">{{formattedTittle}}</div>\n\t\t\t\t\t<div class=\"bx-im-quote-panel-box-desc\">{{formattedDescription}}</div>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"canClose\" class=\"bx-im-quote-panel-close\" @click=\"close\"></div>\n\t\t\t</div>\n\t\t</div>\n\t</transition>\n"
	};

	function ownKeys$2(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$2(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$2(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$2(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	ui_vue.BitrixVue.component('bx-im-component-dialog', {
	  components: {
	    MessageList: MessageList,
	    ErrorState: ErrorState,
	    LoadingState: LoadingState,
	    EmptyState: EmptyState,
	    QuotePanel: QuotePanel
	  },
	  props: {
	    userId: {
	      "default": 0
	    },
	    dialogId: {
	      "default": 0
	    },
	    skipDataRequest: {
	      "default": false
	    },
	    showLoadingState: {
	      "default": true
	    },
	    showEmptyState: {
	      "default": true
	    },
	    enableGestureQuote: {
	      "default": true
	    },
	    enableGestureQuoteFromRight: {
	      "default": true
	    },
	    enableGestureMenu: {
	      "default": false
	    },
	    showMessageUserName: {
	      "default": true
	    },
	    showMessageAvatar: {
	      "default": true
	    }
	  },
	  data: function data() {
	    return {
	      messagesSet: false,
	      dialogState: im_const.DialogState.loading
	    };
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.messagesSet, this.onMessagesSet);
	    this.onDialogOpen();
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.messagesSet, this.onMessagesSet);
	  },
	  watch: {
	    dialogId: function dialogId(newValue, oldValue) {
	      im_lib_logger.Logger.warn('Switching dialogId from ', oldValue, ' to ', newValue);
	      this.messagesSet = false;
	      this.onDialogOpen();
	    }
	  },
	  computed: _objectSpread$2(_objectSpread$2({
	    EventType: function EventType() {
	      return im_const.EventType;
	    },
	    DialogState: function DialogState() {
	      return im_const.DialogState;
	    },
	    dialogWrapClasses: function dialogWrapClasses() {
	      return ['bx-mobilechat-wrapper', {
	        'bx-mobilechat-chat-start': this.isDialogShowingMessages
	      }];
	    },
	    dialogBoxClasses: function dialogBoxClasses() {
	      return ['bx-mobilechat-box', {
	        'bx-mobilechat-box-dark-background': this.isDarkBackground
	      }];
	    },
	    dialogBodyClasses: function dialogBodyClasses() {
	      return ['bx-mobilechat-body', {
	        'bx-mobilechat-body-with-message': this.dialogState === im_const.DialogState.show
	      }];
	    },
	    quotePanelData: function quotePanelData() {
	      var result = {
	        id: 0,
	        title: '',
	        description: '',
	        color: ''
	      };
	      if (!this.isDialogShowingMessages || !this.dialog.quoteId) {
	        return result;
	      }
	      var message = this.$store.getters['messages/getMessage'](this.dialog.chatId, this.dialog.quoteId);
	      if (!message) {
	        return result;
	      }
	      var user = this.$store.getters['users/get'](message.authorId);
	      var files = this.$store.getters['files/getList'](this.dialog.chatId);
	      return {
	        id: this.dialog.quoteId,
	        title: message.params.NAME ? main_core.Text.decode(message.params.NAME) : user ? user.name : '',
	        color: user ? user.color : '',
	        description: im_lib_utils.Utils.text.purify(message.text, message.params, files, this.localize)
	      };
	    },
	    isLoading: function isLoading() {
	      if (!this.showLoadingState) {
	        return false;
	      }
	      // show placeholders if we don't have chatId for current dialogId
	      // or we have chatId, but there is no messages collection for this chatId and messages are not set yet
	      // (because if chat is empty - there will be no messages collection, but we should not show loading state)
	      return !this.isChatIdInModel || this.isChatIdInModel && !this.isMessagesModelInited && !this.messagesSet;
	    },
	    isEmpty: function isEmpty() {
	      return this.showEmptyState && this.messagesSet && this.messageCollection.length === 0;
	    },
	    isChatIdInModel: function isChatIdInModel() {
	      var dialogues = this.$store.state.dialogues.collection;
	      return dialogues[this.dialogId] && dialogues[this.dialogId].chatId > 0;
	    },
	    isMessagesModelInited: function isMessagesModelInited() {
	      var messages = this.$store.state.messages.collection;
	      return messages[this.chatId];
	    },
	    isDialogShowingMessages: function isDialogShowingMessages() {
	      var messagesNotEmpty = this.messageCollection && this.messageCollection.length > 0;
	      if (messagesNotEmpty) {
	        this.dialogState = im_const.DialogState.show;
	      } else if (this.dialog && this.dialog.init) {
	        this.dialogState = im_const.DialogState.empty;
	      } else {
	        this.dialogState = im_const.DialogState.loading;
	      }
	      return messagesNotEmpty;
	    },
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.application.dialog.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    chatId: function chatId() {
	      if (!this.application) {
	        return 0;
	      }
	      return this.application.dialog.chatId;
	    },
	    messageCollection: function messageCollection() {
	      return this.$store.getters['messages/get'](this.application.dialog.chatId);
	    },
	    isDarkBackground: function isDarkBackground() {
	      return this.application.options.darkBackground;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })), {}, {
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases(['IM_DIALOG_', 'IM_UTILS_', 'IM_MESSENGER_DIALOG_', 'IM_QUOTE_'], this);
	    }
	  }),
	  methods: {
	    prepareRequestDataQuery: function prepareRequestDataQuery() {
	      var _query;
	      var query = (_query = {}, babelHelpers.defineProperty(_query, im_const.RestMethodHandler.mobileBrowserConstGet, [im_const.RestMethod.mobileBrowserConstGet, {}]), babelHelpers.defineProperty(_query, im_const.RestMethodHandler.imChatGet, [im_const.RestMethod.imChatGet, {
	        dialog_id: this.dialogId
	      }]), babelHelpers.defineProperty(_query, im_const.RestMethodHandler.imDialogMessagesGetInit, [im_const.RestMethod.imDialogMessagesGet, {
	        dialog_id: this.dialogId,
	        limit: this.getController().application.getRequestMessageLimit(),
	        convert_text: 'Y'
	      }]), _query);
	      if (im_lib_utils.Utils.dialog.isChatId(this.dialogId)) {
	        query[im_const.RestMethodHandler.imUserGet] = [im_const.RestMethod.imUserGet, {}];
	      } else {
	        query[im_const.RestMethodHandler.imUserListGet] = [im_const.RestMethod.imUserListGet, {
	          id: [this.userId, this.dialogId]
	        }];
	      }
	      return query;
	    },
	    requestData: function requestData() {
	      var _this = this;
	      im_lib_logger.Logger.log('requesting dialog data');
	      var query = this.prepareRequestDataQuery();
	      this.$Bitrix.RestClient.get().callBatch(query, function (response) {
	        if (!response) {
	          return false;
	        }

	        //const.get
	        var constGetResult = response[im_const.RestMethodHandler.mobileBrowserConstGet];
	        if (!constGetResult.error()) {
	          _this.executeRestAnswer(im_const.RestMethodHandler.mobileBrowserConstGet, constGetResult);
	        }

	        //user.get
	        var userGetResult = response[im_const.RestMethodHandler.imUserGet];
	        if (userGetResult && !userGetResult.error()) {
	          _this.executeRestAnswer(im_const.RestMethodHandler.imUserGet, userGetResult);
	        }

	        //user.list.get
	        var userListGetResult = response[im_const.RestMethodHandler.imUserListGet];
	        if (userListGetResult && !userListGetResult.error()) {
	          _this.executeRestAnswer(im_const.RestMethodHandler.imUserListGet, userListGetResult);
	        }

	        //chat.get
	        var chatGetResult = response[im_const.RestMethodHandler.imChatGet];
	        if (!chatGetResult.error()) {
	          _this.executeRestAnswer(im_const.RestMethodHandler.imChatGet, chatGetResult);
	        }

	        //dialog.messages.get
	        var dialogMessagesGetResult = response[im_const.RestMethodHandler.imDialogMessagesGetInit];
	        if (!dialogMessagesGetResult.error()) {
	          _this.$store.dispatch('application/set', {
	            dialog: {
	              enableReadMessages: true
	            }
	          }).then(function () {
	            _this.executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGetInit, dialogMessagesGetResult);
	            // this.messagesSet = true;
	          });
	        }
	      }, false, false, im_lib_utils.Utils.getLogTrackingParams({
	        name: 'im.dialog',
	        dialog: this.getController().application.getDialogData()
	      }));
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    },
	    onDialogOpen: function onDialogOpen() {
	      if (this.isChatIdInModel) {
	        var dialogues = this.$store.state.dialogues.collection;
	        this.$store.commit('application/set', {
	          dialog: {
	            chatId: dialogues[this.dialogId].chatId,
	            dialogId: this.dialogId
	          }
	        });
	      }
	      if (!this.skipDataRequest) {
	        this.requestData();
	      }
	    },
	    onMessagesSet: function onMessagesSet(_ref) {
	      var event = _ref.data;
	      if (event.chatId !== this.chatId) {
	        return false;
	      }
	      if (this.messagesSet === true) {
	        return false;
	      }
	      this.messagesSet = true;
	    },
	    getController: function getController() {
	      return this.$Bitrix.Data.get('controller');
	    },
	    executeRestAnswer: function executeRestAnswer(method, queryResult, extra) {
	      this.getController().executeRestAnswer(method, queryResult, extra);
	    }
	  },
	  // language=Vue
	  template: "\n\t\t<div :class=\"dialogWrapClasses\">\n\t\t\t<div :class=\"dialogBoxClasses\" ref=\"chatBox\">\n\t\t\t\t<!-- Error state -->\n\t\t\t\t<ErrorState v-if=\"application.error.active\" />\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div :class=\"dialogBodyClasses\" key=\"with-message\">\n\t\t\t\t\t\t<!-- Loading state -->\n\t\t\t\t\t  \t<LoadingState v-if=\"isLoading\" />\n\t\t\t\t\t\t<!-- Empty state -->\n\t\t\t\t\t  \t<EmptyState v-else-if=\"isEmpty\" />\n\t\t\t\t\t\t<!-- Message list state -->\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<div class=\"bx-mobilechat-dialog\">\n\t\t\t\t\t\t\t\t<MessageList\n\t\t\t\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t\t:messageLimit=\"application.dialog.messageLimit\"\n\t\t\t\t\t\t\t\t\t:enableReadMessages=\"application.dialog.enableReadMessages\"\n\t\t\t\t\t\t\t\t\t:enableReactions=\"true\"\n\t\t\t\t\t\t\t\t\t:enableDateActions=\"false\"\n\t\t\t\t\t\t\t\t\t:enableCreateContent=\"false\"\n\t\t\t\t\t\t\t\t\t:enableGestureQuote=\"enableGestureQuote\"\n\t\t\t\t\t\t\t\t\t:enableGestureQuoteFromRight=\"enableGestureQuoteFromRight\"\n\t\t\t\t\t\t\t\t\t:enableGestureMenu=\"enableGestureMenu\"\n\t\t\t\t\t\t\t\t\t:showMessageUserName=\"showMessageUserName\"\n\t\t\t\t\t\t\t\t\t:showMessageAvatar=\"showMessageAvatar\"\n\t\t\t\t\t\t\t\t\t:showMessageMenu=\"false\"\n\t\t\t\t\t\t\t\t />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<!-- Quote panel -->\n\t\t\t\t\t\t\t<QuotePanel :quotePanelData=\"quotePanelData\" />\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,window,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX,BX,BX.Messenger.Const,BX,BX.Event,BX));
//# sourceMappingURL=dialog.bundle.js.map
