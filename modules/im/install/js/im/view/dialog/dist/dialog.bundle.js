(function (exports,main_polyfill_intersectionobserver,ui_vue,ui_vue_vuex,im_view_message,im_const,im_lib_utils,im_lib_animation,im_lib_logger) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Dialog Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var TemplateType = Object.freeze({
	  message: 'message',
	  delimiter: 'delimiter',
	  group: 'group',
	  historyLoader: 'historyLoader',
	  unreadLoader: 'unreadLoader',
	  button: 'button'
	});
	var ObserverType = Object.freeze({
	  history: 'history',
	  unread: 'unread',
	  read: 'read',
	  none: 'none'
	});
	var LoadButtonTypes = Object.freeze({
	  before: 'before',
	  after: 'after'
	});

	var _AnimationType = Object.freeze({
	  none: 'none',
	  mixed: 'mixed',
	  enter: 'enter',
	  leave: 'leave'
	});

	ui_vue.Vue.component('bx-im-view-dialog', {
	  /**
	   * @emits 'requestHistory' {lastId: number, limit: number}
	   * @emits 'requestUnread' {lastId: number, limit: number}
	   * @emits 'readMessage' {id: number}
	   * @emits 'quoteMessage' {message: object}
	   * @emits 'click' {event: MouseEvent}
	   * @emits 'clickByUserName' {user: object, event: MouseEvent}
	   * @emits 'clickByUploadCancel' {file: object, event: MouseEvent}
	   * @emits 'clickByKeyboardButton' {message: object, action: string, params: Object}
	   * @emits 'clickByChatTeaser' {message: object, event: MouseEvent}
	   * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	   * @emits 'clickByCommand' {type: string, value: string, event: MouseEvent}
	   * @emits 'clickByMention' {type: string, value: string, event: MouseEvent}
	   * @emits 'clickByMessageRetry' {message: object, event: MouseEvent}
	   * @emits 'clickByReadedList' {list: array, event: MouseEvent}
	   * @emits 'setMessageReaction' {message: object, reaction: object}
	   * @emits 'openMessageReactionList' {message: object, values: object}
	   */

	  /**
	   * @listens props.listenEventScrollToBottom {force:boolean, cancelIfScrollChange:boolean} (global|application) -- scroll dialog to bottom, see more in methods.onScrollToBottom()
	   * @listens props.listenEventRequestHistory {count:number} (application)
	   * @listens props.listenEventRequestUnread {count:number} (application)
	   * @listens props.listenEventSendReadMessages {} (application)
	   */
	  props: {
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: 0
	    },
	    chatId: {
	      default: 0
	    },
	    messageLimit: {
	      default: 20
	    },
	    messageExtraCount: {
	      default: 0
	    },
	    listenEventScrollToBottom: {
	      default: ''
	    },
	    listenEventRequestHistory: {
	      default: ''
	    },
	    listenEventRequestUnread: {
	      default: ''
	    },
	    listenEventSendReadMessages: {
	      default: ''
	    },
	    enableReadMessages: {
	      default: true
	    },
	    enableReactions: {
	      default: true
	    },
	    enableDateActions: {
	      default: true
	    },
	    enableCreateContent: {
	      default: true
	    },
	    enableGestureQuote: {
	      default: true
	    },
	    enableGestureQuoteFromRight: {
	      default: true
	    },
	    enableGestureMenu: {
	      default: false
	    },
	    showMessageUserName: {
	      default: true
	    },
	    showMessageAvatar: {
	      default: true
	    },
	    showMessageMenu: {
	      default: true
	    }
	  },
	  data: function data() {
	    return {
	      scrollAnimating: false,
	      showScrollButton: false,
	      messageShowCount: 0,
	      unreadLoaderShow: false,
	      historyLoaderBlocked: false,
	      historyLoaderShow: true,
	      startMessageLimit: 0,
	      templateMessageScrollOffset: 20,
	      templateMessageWithNameDifferent: 29,
	      // name block + padding top
	      TemplateType: TemplateType,
	      ObserverType: ObserverType,
	      DialogReferenceClassName: im_const.DialogReferenceClassName,
	      captureMove: false,
	      capturedMoveEvent: null,
	      lastMessageId: null,
	      maxMessageId: null
	    };
	  },
	  created: function created() {
	    this.showScrollButton = this.unreadCounter > 0;
	    this.scrollChangedByUser = false;
	    this.scrollButtonDiff = 100;
	    this.scrollButtonShowTimeout = null;
	    this.scrollPosition = 0;
	    this.scrollPositionChangeTime = new Date().getTime();
	    this.animationScrollHeightStart = 0;
	    this.animationScrollHeightEnd = 0;
	    this.animationScrollTop = 0;
	    this.animationScrollChange = 0;
	    this.animationScrollLastUserId = 0;
	    this.animationType = _AnimationType.none;
	    this.animationCollection = [];
	    this.animationCollectionOffset = {};
	    this.animationLastElementBeforeStart = 0;
	    this.observers = {};
	    this.requestHistoryInterval = null;
	    this.requestUnreadInterval = null;
	    this.lastAuthorId = 0;
	    this.firstMessageId = null;
	    this.firstUnreadMessageId = null;
	    this.dateFormatFunction = null;
	    this.cacheGroupTitle = {};
	    this.waitLoadHistory = false;
	    this.waitLoadUnread = false;
	    this.skipUnreadScroll = false;
	    this.readMessageQueue = [];
	    this.readMessageTarget = {};
	    this.readMessageDelayed = im_lib_utils.Utils.debounce(this.readMessage, 50, this);
	    this.requestHistoryBlockIntersect = false;
	    this.requestHistoryDelayed = im_lib_utils.Utils.debounce(this.requestHistory, 50, this);
	    this.requestUnreadBlockIntersect = false;
	    this.requestUnreadDelayed = im_lib_utils.Utils.debounce(this.requestUnread, 50, this);
	    this.startMessageLimit = this.messageLimit;

	    if (this.listenEventScrollToBottom) {
	      ui_vue.Vue.event.$on(this.listenEventScrollToBottom, this.onScrollToBottom);
	      this.$root.$on(this.listenEventScrollToBottom, this.onScrollToBottom);
	    }

	    if (this.listenEventRequestHistory) {
	      ui_vue.Vue.event.$on(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
	      this.$root.$on(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
	    }

	    if (this.listenEventRequestUnread) {
	      ui_vue.Vue.event.$on(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
	      this.$root.$on(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
	    }

	    if (this.listenEventSendReadMessages) {
	      ui_vue.Vue.event.$on(this.listenEventSendReadMessages, this.onSendReadMessages);
	      this.$root.$on(this.listenEventSendReadMessages, this.onSendReadMessages);
	    }

	    window.addEventListener("orientationchange", this.onOrientationChange);
	    window.addEventListener('focus', this.onWindowFocus);
	    window.addEventListener('blur', this.onWindowBlur);
	    ui_vue.Vue.event.$on('bitrixmobile:controller:focus', this.onWindowFocus);
	    ui_vue.Vue.event.$on('bitrixmobile:controller:blur', this.onWindowBlur);
	  },
	  beforeDestroy: function beforeDestroy() {
	    this.observers = {};
	    clearTimeout(this.scrollButtonShowTimeout);
	    clearInterval(this.requestHistoryInterval);
	    clearInterval(this.requestUnreadInterval);

	    if (this.listenEventScrollToBottom) {
	      ui_vue.Vue.event.$off(this.listenEventScrollToBottom, this.onScrollToBottom);
	      this.$root.$off(this.listenEventScrollToBottom, this.onScrollToBottom);
	    }

	    if (this.listenEventRequestHistory) {
	      ui_vue.Vue.event.$off(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
	      this.$root.$off(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
	    }

	    if (this.listenEventRequestUnread) {
	      ui_vue.Vue.event.$off(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
	      this.$root.$off(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
	    }

	    if (this.listenEventSendReadMessages) {
	      ui_vue.Vue.event.$off(this.listenEventSendReadMessages, this.onSendReadMessages);
	      this.$root.$off(this.listenEventSendReadMessages, this.onSendReadMessages);
	    }

	    window.removeEventListener("orientationchange", this.onOrientationChange);
	    window.removeEventListener('focus', this.onWindowFocus);
	    window.removeEventListener('blur', this.onWindowBlur);
	    ui_vue.Vue.event.$off('bitrixmobile:controller:focus', this.onWindowFocus);
	    ui_vue.Vue.event.$off('bitrixmobile:controller:blur', this.onWindowBlur);
	  },
	  mounted: function mounted() {
	    var unreadId = Utils.getFirstUnreadMessage(this.collection);

	    if (unreadId) {
	      Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true);
	    } else {
	      var body = this.$refs.body;
	      Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);
	    }

	    this.windowFocused = im_lib_utils.Utils.platform.isBitrixMobile() ? true : document.hasFocus();
	  },
	  computed: babelHelpers.objectSpread({
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_DIALOG_', this.$root.$bitrixMessages);
	    },
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    collectionMutationType: function collectionMutationType() {
	      return this.$store.getters['messages/getMutationType'](this.chatId);
	    },
	    collection: function collection() {
	      return this.$store.getters['messages/get'](this.chatId);
	    },
	    elementsWithLimit: function elementsWithLimit() {
	      var _this = this;

	      var unreadCount = this.collection.filter(function (element) {
	        return element.unread;
	      }).length;
	      var showLimit = this.messageExtraCount + this.messageLimit * 2;

	      if (unreadCount > showLimit) {
	        showLimit = unreadCount;
	      }

	      var start = this.collection.length - showLimit;

	      if (!this.historyLoaderShow || start < 0) {
	        start = 0;
	      }

	      var slicedCollection = start === 0 ? this.collection : this.collection.slice(start, this.collection.length);
	      this.messageShowCount = slicedCollection.length;
	      this.firstMessageId = null;
	      this.lastMessageId = 0;
	      this.maxMessageId = 0;
	      this.lastMessageAuthorId = 0;
	      var collection = [];
	      var lastAuthorId = 0;
	      var groupNode = {};
	      this.firstUnreadMessageId = 0;

	      if (this.messageShowCount > 0) {
	        slicedCollection.forEach(function (element) {
	          if (_this.firstMessageId === null || _this.firstMessageId > element.id) {
	            _this.firstMessageId = element.id;
	          }

	          if (_this.maxMessageId < element.id) {
	            _this.maxMessageId = element.id;
	          }

	          _this.lastMessageId = element.id;

	          var group = _this._groupTitle(element.date);

	          if (!groupNode[group.title]) {
	            groupNode[group.title] = group.id;
	            collection.push(Blocks.getGroup(group.id, group.title));
	          } else if (lastAuthorId !== element.authorId) {
	            collection.push(Blocks.getDelimiter(element.id));
	          }

	          collection.push(element);
	          lastAuthorId = element.authorId;

	          if (element.unread) {
	            if (!_this.firstUnreadMessageId) {
	              _this.firstUnreadMessageId = element.id;
	            }
	          }
	        });
	        this.lastMessageAuthorId = lastAuthorId;
	      } else {
	        this.firstMessageId = 0;
	      }

	      if (this.collection.length >= this.messageLimit && this.collection.length >= this.messageShowCount && this.historyLoaderBlocked === false) {
	        this.historyLoaderShow = true;
	      } else {
	        this.historyLoaderShow = false;
	      }

	      if (this.dialog.unreadLastId > this.maxMessageId) {
	        this.unreadLoaderShow = true;
	      } else {
	        this.unreadLoaderShow = false;
	      }

	      return collection;
	    },
	    statusWriting: function statusWriting() {
	      var _this2 = this;

	      clearTimeout(this.scrollToTimeout);

	      if (this.dialog.writingList.length === 0) {
	        return '';
	      }

	      if (!this.scrollChangedByUser && !this.showScrollButton) {
	        this.scrollToTimeout = setTimeout(function () {
	          return _this2.scrollTo({
	            duration: 500
	          });
	        }, 300);
	      }

	      return this.localize.IM_MESSENGER_DIALOG_WRITES_MESSAGE.replace('#USER#', this.dialog.writingList.map(function (element) {
	        return element.userName;
	      }).join(', '));
	    },
	    statusReaded: function statusReaded() {
	      var _this3 = this;

	      clearTimeout(this.scrollToTimeout);

	      if (this.dialog.readedList.length === 0) {
	        return '';
	      }

	      var text = '';

	      if (this.dialog.type === im_const.DialogType.private) {
	        var record = this.dialog.readedList[0];

	        if (record.messageId === this.lastMessageId && record.userId !== this.lastMessageAuthorId) {
	          var dateFormat = im_lib_utils.Utils.date.getFormatType(BX.Messenger.Const.DateFormat.readedTitle, this.$root.$bitrixMessages);
	          text = this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_USER.replace('#DATE#', this._getDateFormat().format(dateFormat, record.date));
	        }
	      } else {
	        var readedList = this.dialog.readedList.filter(function (record) {
	          return record.messageId === _this3.lastMessageId && record.userId !== _this3.lastMessageAuthorId;
	        });

	        if (readedList.length === 1) {
	          text = this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT.replace('#USERS#', readedList[0].userName);
	        } else if (readedList.length > 1) {
	          text = this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT.replace('#USERS#', this.localize.IM_MESSENGER_DIALOG_MESSAGES_READED_CHAT_PLURAL.replace('#USER#', readedList[0].userName).replace('#COUNT#', readedList.length - 1).replace('[LINK]', '').replace('[/LINK]', ''));
	        }
	      }

	      if (!text) {
	        return '';
	      }

	      if (!this.scrollChangedByUser && !this.showScrollButton) {
	        this.scrollToTimeout = setTimeout(function () {
	          return _this3.scrollTo({
	            duration: 500
	          });
	        }, 300);
	      }

	      return text;
	    },
	    unreadCounter: function unreadCounter() {
	      return this.dialog.counter > 999 ? 999 : this.dialog.counter;
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
	    AnimationType: function AnimationType() {
	      return _AnimationType;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  methods: {
	    onDialogClick: function onDialogClick(event) {
	      if (ui_vue.Vue.testNode(event.target, {
	        className: 'bx-im-message-command'
	      })) {
	        this.onCommandClick(event);
	      } else if (ui_vue.Vue.testNode(event.target, {
	        className: 'bx-im-mention'
	      })) {
	        this.onMentionClick(event);
	      }

	      this.windowFocused = true;
	      this.$emit('click', {
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

	      this.$emit('clickByCommand', {
	        type: event.target.dataset.entity,
	        value: value,
	        event: event
	      });
	    },
	    onMentionClick: function onMentionClick(event) {
	      this.$emit('clickByMention', {
	        type: event.target.dataset.type,
	        value: event.target.dataset.value,
	        event: event
	      });
	    },
	    onScroll: function onScroll(event) {
	      var _this4 = this;

	      clearTimeout(this.scrollToTimeout);
	      this.scrollPosition = event.target.scrollTop;
	      this.scrollPositionChangeTime = new Date().getTime();
	      this.scrollChangedByUser = !(event.target.scrollTop + this.scrollButtonDiff >= event.target.scrollHeight - event.target.clientHeight);
	      clearTimeout(this.scrollButtonShowTimeout);
	      this.scrollButtonShowTimeout = setTimeout(function () {
	        if (_this4.scrollChangedByUser) {
	          if (!_this4.showScrollButton) {
	            _this4.showScrollButton = true;
	          }
	        } else {
	          if (_this4.showScrollButton && !_this4.unreadLoaderShow) {
	            _this4.showScrollButton = false;
	          }
	        }
	      }, 200);

	      if (event.target.scrollTop === event.target.scrollHeight - event.target.offsetHeight) {
	        clearTimeout(this.scrollButtonShowTimeout);

	        if (this.showScrollButton && !this.unreadLoaderShow) {
	          this.showScrollButton = false;
	        }
	      }
	    },
	    scrollToBottom: function scrollToBottom() {
	      var _this5 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var _params$force = params.force,
	          force = _params$force === void 0 ? false : _params$force,
	          _params$cancelIfScrol = params.cancelIfScrollChange,
	          cancelIfScrollChange = _params$cancelIfScrol === void 0 ? false : _params$cancelIfScrol,
	          _params$duration = params.duration,
	          duration = _params$duration === void 0 ? null : _params$duration;

	      if (cancelIfScrollChange && this.scrollChangedByUser) {
	        return false;
	      }

	      var body = this.$refs.body;

	      if (this.dialog.counter > 0) {
	        var scrollToMessageId = this.dialog.counter > 1 && this.firstUnreadMessageId ? this.firstUnreadMessageId : this.lastMessageId;
	        Utils.scrollToFirstUnreadMessage(this, this.collection, scrollToMessageId);

	        if (this.dialog.counter < this.startMessageLimit) {
	          this.historyLoaderShow = true;
	          this.historyLoaderBlocked = false;
	        }

	        return true;
	      }

	      this.showScrollButton = false;

	      if (force) {
	        Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);

	        if (this.messageExtraCount) {
	          this.$store.commit('application/clearDialogExtraCount');
	        }

	        this.historyLoaderShow = true;
	        this.historyLoaderBlocked = false;
	      } else {
	        var scrollParams = {};

	        if (duration) {
	          scrollParams.duration = duration;
	        }

	        this.scrollTo(babelHelpers.objectSpread({
	          callback: function callback() {
	            if (_this5.messageExtraCount) {
	              _this5.$store.commit('application/clearDialogExtraCount');
	            }

	            _this5.historyLoaderShow = true;
	            _this5.historyLoaderBlocked = false;
	          }
	        }, scrollParams));
	      }
	    },
	    scrollTo: function scrollTo() {
	      var _this6 = this;

	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

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
	          _params$duration2 = _params.duration,
	          duration = _params$duration2 === void 0 ? 500 : _params$duration2;
	      var container = this.$refs.container;

	      if (container && end - start > container.offsetHeight * 3) {
	        start = end - container.offsetHeight * 3;
	        im_lib_logger.Logger.warn('Dialog.scrollTo: Scroll trajectory has been reduced');
	      }

	      this.scrollAnimating = true;
	      im_lib_logger.Logger.warn('Dialog.scrollTo: User scroll blocked while scrolling');
	      this.animateScrollId = im_lib_animation.Animation.start({
	        start: start,
	        end: end,
	        increment: increment,
	        duration: duration,
	        element: body,
	        elementProperty: 'scrollTop',
	        callback: function callback() {
	          _this6.animateScrollId = null;
	          _this6.scrollAnimating = false;

	          if (_callback && typeof _callback === 'function') {
	            _callback();
	          }
	        }
	      });
	    },
	    onScrollToBottom: function onScrollToBottom() {
	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      event.force = event.force === true;
	      event.cancelIfScrollChange = event.cancelIfScrollChange === true;

	      if (this.firstUnreadMessageId) {
	        im_lib_logger.Logger.warn('Dialog.onScrollToBottom: canceled - unread messages');
	        return false;
	      }

	      this.scrollToBottom(event);
	      return true;
	    },
	    onOrientationChange: function onOrientationChange() {
	      var _this7 = this;
	      clearTimeout(this.scrollToTimeout);

	      if (this.application.device.type !== im_const.DeviceType.mobile) {
	        return false;
	      }

	      im_lib_logger.Logger.log('Orientation changed');

	      if (!this.scrollChangedByUser) {
	        this.scrollToTimeout = setTimeout(function () {
	          return _this7.scrollToBottom({
	            force: true
	          });
	        }, 300);
	      }
	    },
	    onWindowFocus: function onWindowFocus() {
	      this.windowFocused = true;
	      this.readMessage();
	      return true;
	    },
	    onWindowBlur: function onWindowBlur() {
	      this.windowFocused = false;
	    },
	    requestHistory: function requestHistory() {
	      var _this8 = this;

	      if (!this.requestHistoryBlockIntersect) {
	        return false;
	      }

	      if (this.waitLoadHistory || !this.windowFocused || this.animateScrollId) {
	        this.requestHistoryDelayed();
	        return false;
	      }

	      if (this.scrollPositionChangeTime + 100 > new Date().getTime() //	|| this.$refs.body.scrollTop < 0
	      ) {
	          this.requestHistoryDelayed();
	          return true;
	        }

	      this.waitLoadHistory = true;
	      clearTimeout(this.waitLoadHistoryTimeout);
	      this.waitLoadHistoryTimeout = setTimeout(function () {
	        _this8.waitLoadHistory = false;
	      }, 10000);
	      var length = this.collection.length;
	      var messageShowCount = this.messageShowCount;

	      if (length > messageShowCount) {
	        var element = this.$refs.body.getElementsByClassName(im_const.DialogReferenceClassName.listItem)[0];
	        this.$store.commit('application/increaseDialogExtraCount', {
	          count: this.startMessageLimit
	        });
	        Utils.scrollToElementAfterLoadHistory(this, element);
	        return true;
	      }

	      this.$emit('requestHistory', {
	        lastId: this.firstMessageId
	      });
	    },
	    requestUnread: function requestUnread() {
	      if (!this.requestUnreadBlockIntersect) {
	        return false;
	      }

	      if (this.waitLoadUnread || !this.windowFocused || this.animateScrollId) {
	        this.requestUnreadDelayed();
	        return false;
	      }

	      if (this.scrollPositionChangeTime + 10 > new Date().getTime() //|| this.$refs.body.scrollTop > this.$refs.body.scrollHeight - this.$refs.body.clientHeight
	      ) {
	          this.requestUnreadDelayed();
	          return true;
	        }

	      this.waitLoadUnread = true;
	      this.skipUnreadScroll = true;
	      this.$emit('requestUnread', {
	        lastId: this.lastMessageId
	      });
	    },
	    onRequestHistoryAnswer: function onRequestHistoryAnswer() {
	      var _this9 = this;

	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (event.error) {
	        this.historyLoaderBlocked = false;
	      } else {
	        this.historyLoaderBlocked = event.count < this.startMessageLimit;
	        this.$store.commit('application/increaseDialogExtraCount', {
	          count: event.count
	        });
	      }

	      if (this.historyLoaderBlocked) {
	        this.historyLoaderShow = false;
	      }

	      var element = this.$refs.body.getElementsByClassName(im_const.DialogReferenceClassName.listItem)[0];

	      if (event.count > 0) {
	        if (element) {
	          Utils.scrollToElementAfterLoadHistory(this, element);
	        }
	      } else if (event.error) {
	        element.scrollIntoView(true);
	      } else {
	        Utils.scrollToPosition(this, 0);
	      }

	      clearTimeout(this.waitLoadHistoryTimeout);
	      this.waitLoadHistoryTimeout = setTimeout(function () {
	        _this9.waitLoadHistory = false;
	      }, 1000);
	      return true;
	    },
	    onRequestUnreadAnswer: function onRequestUnreadAnswer() {
	      var _this10 = this;

	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (event.error) {
	        this.historyLoaderBlocked = false;
	      } else {
	        if (event.count < this.startMessageLimit) {
	          this.unreadLoaderShow = false;
	        }

	        this.$store.commit('application/increaseDialogExtraCount', {
	          count: event.count
	        });
	      }

	      var body = this.$refs.body;

	      if (event.count > 0) ; else if (event.error) {
	        var element = this.$refs.body.getElementsByClassName(im_const.DialogReferenceClassName.listUnreadLoader)[0];

	        if (element) {
	          Utils.scrollToPosition(this, body.scrollTop - element.offsetHeight * 2);
	        } else {
	          Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);
	        }
	      } else {
	        Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);
	      }

	      setTimeout(function () {
	        return _this10.waitLoadUnread = false;
	      }, 1000);
	      return true;
	    },
	    onSendReadMessages: function onSendReadMessages() {
	      this.readMessageDelayed();
	      return true;
	    },
	    readMessage: function readMessage() {
	      var _this11 = this;

	      if (!this.windowFocused) {
	        return false;
	      }

	      this.readMessageQueue = this.readMessageQueue.filter(function (messageId) {
	        if (_this11.readMessageTarget[messageId]) {
	          if (_this11.observers[ObserverType.read]) {
	            _this11.observers[ObserverType.read].unobserve(_this11.readMessageTarget[messageId]);
	          }

	          delete _this11.readMessageTarget[messageId];
	        }

	        _this11.requestReadMessage(messageId);

	        return false;
	      });
	    },
	    requestReadMessage: function requestReadMessage(messageId) {
	      this.$emit('readMessage', {
	        id: messageId
	      });
	    },
	    onClickByUserName: function onClickByUserName(event) {
	      if (!this.windowFocused) {
	        return false;
	      }

	      this.$emit('clickByUserName', event);
	    },
	    onClickByUploadCancel: function onClickByUploadCancel(event) {
	      if (!this.windowFocused) {
	        return false;
	      }

	      this.$emit('clickByUploadCancel', event);
	    },
	    onClickByKeyboardButton: function onClickByKeyboardButton(event) {
	      if (!this.windowFocused) {
	        return false;
	      }

	      this.$emit('clickByKeyboardButton', event);
	    },
	    onClickByChatTeaser: function onClickByChatTeaser(event) {
	      this.$emit('clickByChatTeaser', event);
	    },
	    onClickByMessageMenu: function onClickByMessageMenu(event) {
	      if (!this.windowFocused) {
	        return false;
	      }

	      this.$emit('clickByMessageMenu', event);
	    },
	    onClickByMessageRetry: function onClickByMessageRetry(event) {
	      if (!this.windowFocused) {
	        return false;
	      }

	      this.$emit('clickByMessageRetry', event);
	    },
	    onClickByReadedList: function onClickByReadedList(event) {
	      var _this12 = this;

	      var readedList = this.dialog.readedList.filter(function (record) {
	        return record.messageId === _this12.lastMessageId && record.userId !== _this12.lastMessageAuthorId;
	      });
	      this.$emit('clickByReadedList', {
	        list: readedList,
	        event: event
	      });
	    },
	    onMessageReactionSet: function onMessageReactionSet(event) {
	      this.$emit('setMessageReaction', event);
	    },
	    onMessageReactionListOpen: function onMessageReactionListOpen(event) {
	      this.$emit('openMessageReactionList', event);
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
	    onQuoteMessage: function onQuoteMessage(event) {
	      if (!this.windowFocused) {
	        return false;
	      }

	      this.$emit('quoteMessage', event);
	    },
	    _getDateFormat: function _getDateFormat() {
	      var _this13 = this;

	      if (this.dateFormatFunction) {
	        return this.dateFormatFunction;
	      }

	      this.dateFormatFunction = Object.create(BX.Main.Date);

	      if (this.$root.$bitrixMessages) {
	        this.dateFormatFunction._getMessage = function (phrase) {
	          return _this13.$root.$bitrixMessages[phrase];
	        };
	      }

	      return this.dateFormatFunction;
	    },
	    _groupTitle: function _groupTitle(date) {
	      var id = Utils.getDateFormat(date);

	      if (this.cacheGroupTitle[id]) {
	        return {
	          id: id,
	          title: this.cacheGroupTitle[id]
	        };
	      }

	      var dateFormat = im_lib_utils.Utils.date.getFormatType(BX.Messenger.Const.DateFormat.groupTitle, this.$root.$bitrixMessages);
	      this.cacheGroupTitle[id] = this._getDateFormat().format(dateFormat, date);
	      return {
	        id: id,
	        title: this.cacheGroupTitle[id]
	      };
	    },
	    animationTrigger: function animationTrigger(type, start, element) {
	      var _this14 = this;

	      var templateId = element.dataset.templateId;
	      var templateType = element.dataset.type;
	      var body = this.$refs.body;

	      if (!body || !templateId) {
	        return false;
	      }

	      if (start) {
	        if (!this.animationScrollHeightStart) {
	          this.animationScrollHeightStart = body.scrollHeight;
	          this.animationScrollHeightEnd = body.scrollHeight;
	          this.animationScrollTop = body.scrollTop;
	          this.animationScrollChange = 0;
	          clearTimeout(this.scrollToTimeout);
	          this.scrollChangedByUser = !(body.scrollTop + this.scrollButtonDiff >= body.scrollHeight - body.clientHeight);

	          if (this.scrollChangedByUser && !this.showScrollButton && this.unreadCounter > 1) {
	            this.showScrollButton = true;
	          }
	        }
	      } else {
	        this.animationScrollHeightEnd = body.scrollHeight;
	      }

	      if (!this.collectionMutationType.applied && this.collectionMutationType.initialType !== im_const.MutationType.set) {
	        if (start) {
	          this.animationCollection.push(templateId);
	        } else {
	          this.animationCollection = this.animationCollection.filter(function (id) {
	            delete _this14.animationCollectionOffset[templateId];
	            return id !== templateId;
	          });
	        }

	        this.animationStart();
	        return false;
	      }

	      if (!this.collectionMutationType.applied && this.collectionMutationType.initialType === im_const.MutationType.set && this.collectionMutationType.appliedType === im_const.MutationType.setBefore) {
	        var unreadId = Utils.getFirstUnreadMessage(this.collection);

	        if (unreadId) {
	          Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true);
	          return false;
	        }

	        Utils.scrollToPosition(this, body.scrollHeight - body.clientHeight);

	        if (start) {
	          this.animationCollection.push(templateId);
	        } else {
	          this.animationCollection = this.animationCollection.filter(function (id) {
	            delete _this14.animationCollectionOffset[templateId];
	            return id !== templateId;
	          });
	        }

	        this.animationStart();
	        return false;
	      }

	      if (start) {
	        if (type === _AnimationType.leave) {
	          this.animationCollectionOffset[templateId] = element.offsetHeight;
	        }

	        if (this.animationType === _AnimationType.none) {
	          this.animationType = type;
	        } else if (this.animationType !== type) {
	          this.animationType = _AnimationType.mixed;
	        }

	        this.animationCollection.push(templateId);
	      } else {
	        if (type === _AnimationType.enter) {
	          var offset = element.offsetHeight;
	          this.animationScrollChange += offset;
	          body.scrollTop += offset;
	        } else if (type === _AnimationType.leave) {
	          var _offset = this.animationCollectionOffset[templateId] ? this.animationCollectionOffset[templateId] : 0;

	          this.animationScrollChange -= _offset;
	          body.scrollTop -= _offset;
	          this.animationScrollLastIsDelimeter = templateType !== TemplateType.message;
	        }

	        this.animationCollection = this.animationCollection.filter(function (id) {
	          delete _this14.animationCollectionOffset[templateId];
	          return id !== templateId;
	        });
	      }

	      this.animationStart();
	    },
	    animationStart: function animationStart() {
	      var _this15 = this;

	      if (this.animationCollection.length > 0) {
	        return false;
	      }

	      var body = this.$refs.body;

	      if (this.animationType === _AnimationType.leave) {
	        var newScrollPosition = 0; // fix for chrome dom rendering: while delete node, scroll change immediately

	        if (body.scrollTop !== this.animationScrollTop + this.animationScrollChange) {
	          newScrollPosition = this.animationScrollTop + this.animationScrollChange;
	        } else {
	          newScrollPosition = body.scrollTop;
	        } // fix position if last element the same type of new element


	        if (!this.animationScrollLastIsDelimeter) {
	          newScrollPosition += this.templateMessageWithNameDifferent;
	        }

	        if (newScrollPosition !== body.scrollTop) {
	          Utils.scrollToPosition(this, newScrollPosition);
	        }
	      } else if (this.animationType === _AnimationType.mixed) {
	        var unreadId = Utils.getFirstUnreadMessage(this.collection);

	        if (unreadId) {
	          Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true);
	        }
	      }

	      this.animationType = _AnimationType.none;
	      this.animationScrollHeightStart = 0;
	      this.animationScrollHeightEnd = 0;
	      this.animationScrollTop = 0;
	      this.animationScrollChange = 0;

	      if (Utils.scrollByMutationType(this)) {
	        return false;
	      }

	      if (this.scrollChangedByUser) {
	        im_lib_logger.Logger.warn('Dialog.animationStart: canceled: scroll changed by user');
	        return false;
	      }

	      if (this.unreadCounter > 0 && this.firstUnreadMessageId) {
	        if (this.skipUnreadScroll) {
	          this.skipUnreadScroll = false;
	          return;
	        }

	        Utils.scrollToFirstUnreadMessage(this, this.collection, this.firstUnreadMessageId);
	        return;
	      }

	      this.scrollTo(function () {
	        if (_this15.unreadCounter <= 0 && _this15.messageExtraCount) {
	          _this15.$store.commit('application/clearDialogExtraCount');
	        }
	      });
	    }
	  },
	  directives: {
	    'bx-im-directive-dialog-observer': {
	      inserted: function inserted(element, bindings, vnode) {
	        if (bindings.value === ObserverType.none) {
	          return false;
	        }

	        if (!vnode.context.observers[bindings.value]) {
	          vnode.context.observers[bindings.value] = Utils.getMessageLoaderObserver({
	            type: bindings.value,
	            context: vnode.context
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
	  template: "\n\t\t<div class=\"bx-im-dialog\" @click=\"onDialogClick\" @touchmove=\"onDialogMove\" ref=\"container\">\t\n\t\t\t<div :class=\"[DialogReferenceClassName.listBody, {\n\t\t\t\t'bx-im-dialog-list-scroll-blocked': scrollBlocked, \n\t\t\t\t'bx-im-dialog-dark-background': isDarkBackground,\n\t\t\t\t'bx-im-dialog-mobile': isMobile,\n\t\t\t}]\" @scroll.passive=\"onScroll\" ref=\"body\">\n\t\t\t\t<template v-if=\"historyLoaderShow\">\n\t\t\t\t\t<div class=\"bx-im-dialog-load-more bx-im-dialog-load-more-history\" v-bx-im-directive-dialog-observer=\"ObserverType.history\">\n\t\t\t\t\t\t<span class=\"bx-im-dialog-load-more-text\">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<transition-group \n\t\t\t\t\ttag=\"div\" class=\"bx-im-dialog-list-box\" name=\"bx-im-dialog-message-animation\" \n\t\t\t\t\t@before-enter=\"animationTrigger(AnimationType.enter, true, $event)\" \n\t\t\t\t\t@after-enter=\"animationTrigger(AnimationType.enter, false, $event)\" \n\t\t\t\t\t@before-leave=\"animationTrigger(AnimationType.leave, true, $event)\" \n\t\t\t\t\t@after-leave=\"animationTrigger(AnimationType.leave, false, $event)\"\n\t\t\t\t>\n\t\t\t\t\t<template v-for=\"element in elementsWithLimit\">\n\t\t\t\t\t\t<template v-if=\"element.templateType == TemplateType.message\">\n\t\t\t\t\t\t\t<div :class=\"['bx-im-dialog-list-item', DialogReferenceClassName.listItem, DialogReferenceClassName.listItem+'-'+element.id]\" :data-message-id=\"element.id\" :data-template-id=\"element.templateId\" :data-type=\"element.templateType\" :key=\"element.templateId\" v-bx-im-directive-dialog-observer=\"element.unread? ObserverType.read: ObserverType.none\">\t\t\t\n\t\t\t\t\t\t\t\t<component :is=\"element.params.COMPONENT_ID\"\n\t\t\t\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t\t\t\t\t:dialog=\"dialog\"\n\t\t\t\t\t\t\t\t\t:message=\"element\"\n\t\t\t\t\t\t\t\t\t:enableReactions=\"enableReactions\"\n\t\t\t\t\t\t\t\t\t:enableDateActions=\"enableDateActions\"\n\t\t\t\t\t\t\t\t\t:enableCreateContent=\"showMessageMenu\"\n\t\t\t\t\t\t\t\t\t:enableGestureQuote=\"enableGestureQuote\"\n\t\t\t\t\t\t\t\t\t:enableGestureQuoteFromRight=\"enableGestureQuoteFromRight\"\n\t\t\t\t\t\t\t\t\t:enableGestureMenu=\"enableGestureMenu\"\n\t\t\t\t\t\t\t\t\t:showName=\"showMessageUserName\"\n\t\t\t\t\t\t\t\t\t:showAvatar=\"showMessageAvatar\"\n\t\t\t\t\t\t\t\t\t:showMenu=\"showMessageMenu\"\n\t\t\t\t\t\t\t\t\t:capturedMoveEvent=\"capturedMoveEvent\"\n\t\t\t\t\t\t\t\t\t:referenceContentClassName=\"DialogReferenceClassName.listItem\"\n\t\t\t\t\t\t\t\t\t:referenceContentBodyClassName=\"DialogReferenceClassName.listItemBody\"\n\t\t\t\t\t\t\t\t\t:referenceContentNameClassName=\"DialogReferenceClassName.listItemName\"\n\t\t\t\t\t\t\t\t\t@clickByUserName=\"onClickByUserName\"\n\t\t\t\t\t\t\t\t\t@clickByUploadCancel=\"onClickByUploadCancel\"\n\t\t\t\t\t\t\t\t\t@clickByKeyboardButton=\"onClickByKeyboardButton\"\n\t\t\t\t\t\t\t\t\t@clickByChatTeaser=\"onClickByChatTeaser\"\n\t\t\t\t\t\t\t\t\t@clickByMessageMenu=\"onClickByMessageMenu\"\n\t\t\t\t\t\t\t\t\t@clickByMessageRetry=\"onClickByMessageRetry\"\n\t\t\t\t\t\t\t\t\t@setMessageReaction=\"onMessageReactionSet\"\n\t\t\t\t\t\t\t\t\t@openMessageReactionList=\"onMessageReactionListOpen\"\n\t\t\t\t\t\t\t\t\t@dragMessage=\"onDragMessage\"\n\t\t\t\t\t\t\t\t\t@quoteMessage=\"onQuoteMessage\"\n\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"element.templateType == TemplateType.group\">\n\t\t\t\t\t\t\t<div class=\"bx-im-dialog-group\" :data-template-id=\"element.templateId\" :data-type=\"element.templateType\" :key=\"element.templateId\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-dialog-group-date\">{{ element.text }}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"element.templateType == TemplateType.delimiter\">\n\t\t\t\t\t\t\t<div class=\"bx-im-dialog-delimiter\" :data-template-id=\"element.templateId\" :data-type=\"element.templateType\" :key=\"element.templateId\"></div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</template>\n\t\t\t\t</transition-group>\n\t\t\t\t<template v-if=\"unreadLoaderShow\">\n\t\t\t\t\t<div :class=\"['bx-im-dialog-load-more', 'bx-im-dialog-load-more-unread', DialogReferenceClassName.listUnreadLoader]\" v-bx-im-directive-dialog-observer=\"ObserverType.unread\">\n\t\t\t\t\t\t<span class=\"bx-im-dialog-load-more-text\">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<transition name=\"bx-im-dialog-status\">\n\t\t\t\t\t<template v-if=\"statusWriting\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-status\">\n\t\t\t\t\t\t\t<span class=\"bx-im-dialog-status-writing\"></span>\n\t\t\t\t\t\t\t{{ statusWriting }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<template v-else-if=\"statusReaded\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-status\" @click=\"onClickByReadedList\">\n\t\t\t\t\t\t\t{{ statusReaded }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t</transition>\n\t\t\t</div>\n\t\t\t<transition name=\"bx-im-dialog-scroll-button\">\n\t\t\t\t<div v-show=\"showScrollButton || unreadLoaderShow && unreadCounter\" class=\"bx-im-dialog-scroll-button-box\" @click=\"scrollToBottom()\">\n\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button\">\n\t\t\t\t\t\t<div v-show=\"unreadCounter\" class=\"bx-im-dialog-scroll-button-counter\">\n\t\t\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button-counter-digit\">{{unreadCounter}}</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button-arrow\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</transition>\n\t\t</div>\n\t"
	});
	var Utils = {
	  getDateFormat: function getDateFormat(date) {
	    return date.toJSON().slice(0, 10);
	  },
	  scrollToMessage: function scrollToMessage(context, collection) {
	    var messageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	    var force = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
	    var stickToTop = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;
	    var body = context.$refs.body;
	    var element = body.getElementsByClassName(im_const.DialogReferenceClassName.listItem + '-' + messageId)[0];
	    var end = 0;

	    if (!element) {
	      if (stickToTop) {
	        end = 10;
	      } else {
	        end = body.scrollHeight - body.clientHeight;
	      }
	    } else if (stickToTop) {
	      end = element.offsetTop - context.templateMessageScrollOffset / 2;
	    } else {
	      end = element.offsetTop + element.offsetHeight - body.clientHeight + context.templateMessageScrollOffset / 2;
	    }

	    if (force) {
	      this.scrollToPosition(context, end);
	    } else {
	      context.scrollTo({
	        end: end
	      });
	    }

	    return true;
	  },
	  getFirstUnreadMessage: function getFirstUnreadMessage(collection) {
	    var unreadId = null;

	    for (var index = collection.length - 1; index >= 0; index--) {
	      if (!collection[index].unread) {
	        break;
	      }

	      unreadId = collection[index].id;
	    }

	    return unreadId;
	  },
	  scrollToPosition: function scrollToPosition(context, position) {
	    var body = context.$refs.body;

	    if (!body) {
	      return false;
	    }

	    if (context.animateScrollId) {
	      im_lib_animation.Animation.cancel(context.animateScrollId);
	      this.scrollAnimating = false;
	      context.animateScrollId = null;
	    }

	    body.scrollTop = position;
	  },
	  scrollByMutationType: function scrollByMutationType(context) {
	    if (context.collectionMutationType.applied || context.collectionMutationType.initialType !== im_const.MutationType.set) {
	      return false;
	    }

	    context.$store.dispatch('messages/applyMutationType', {
	      chatId: context.chatId
	    });

	    if (context.collectionMutationType.appliedType === im_const.MutationType.setBefore) {
	      var body = context.$refs.body;
	      this.scrollToPosition(context, body.scrollHeight - body.clientHeight);
	      return true;
	    }

	    if (context.collectionMutationType.scrollMessageId > 0) {
	      var unreadId = Utils.getFirstUnreadMessage(context.collection);
	      var toMessageId = context.collectionMutationType.scrollMessageId;
	      var force = !context.collectionMutationType.scrollStickToTop;
	      var stickToTop = context.collectionMutationType.scrollStickToTop;

	      if (unreadId && toMessageId > unreadId) {
	        stickToTop = true;
	        force = true;
	        toMessageId = unreadId;
	        unreadId = null;
	      }

	      Utils.scrollToMessage(context, context.collection, toMessageId, force, stickToTop);

	      if (unreadId) {
	        Utils.scrollToMessage(context, context.collection, unreadId);
	        return true;
	      }
	    }

	    return false;
	  },
	  scrollToFirstUnreadMessage: function scrollToFirstUnreadMessage(context, collection) {
	    var unreadId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	    var force = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
	    var body = context.$refs.body;
	    var element = false;

	    if (unreadId !== null) {
	      element = body.getElementsByClassName(im_const.DialogReferenceClassName.listItem + '-' + unreadId)[0];
	    }

	    if (!element) {
	      unreadId = this.getFirstUnreadMessage(collection);
	    }

	    this.scrollToMessage(context, collection, unreadId, force);
	  },
	  scrollToElementAfterLoadHistory: function scrollToElementAfterLoadHistory(context, element) {
	    var _this16 = this;

	    var elementBody = element.getElementsByClassName(im_const.DialogReferenceClassName.listItemBody)[0];

	    if (elementBody) {
	      element = elementBody;
	    }

	    var previousOffsetTop = element.getBoundingClientRect().top;
	    context.$nextTick(function () {
	      clearTimeout(context.waitLoadHistoryTimeout);
	      context.waitLoadHistoryTimeout = setTimeout(function () {
	        context.waitLoadHistory = false;
	      }, 1000);

	      if (!element) {
	        return false;
	      }

	      _this16.scrollToPosition(context, element.getBoundingClientRect().top - previousOffsetTop);
	    });
	  },
	  scrollToElementAfterLoadUnread: function scrollToElementAfterLoadUnread(context) {
	    var firstMessageId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 0;
	    context.showScrollButton = true;

	    if (firstMessageId) {
	      this.scrollToMessage(context, context.collection, firstMessageId, false, false);
	    }
	  },
	  getMessageLoaderObserver: function getMessageLoaderObserver(config) {
	    if (typeof window.IntersectionObserver === 'undefined' || config.value === ObserverType.none) {
	      return {
	        observe: function observe() {},
	        unobserve: function unobserve() {}
	      };
	    }

	    var observerCallback, observerOptions;

	    if (config.type === ObserverType.read) {
	      observerCallback = function observerCallback(entries, observer) {
	        entries.forEach(function (entry) {
	          var sendReadEvent = false;

	          if (entry.isIntersecting) {
	            if (entry.intersectionRatio >= 1) {
	              sendReadEvent = true;
	            } else if (entry.intersectionRatio > 0 && entry.rootBounds.height < entry.boundingClientRect.height + 20 && entry.intersectionRect.height > entry.rootBounds.height / 2) {
	              sendReadEvent = true;
	            }
	          }

	          if (sendReadEvent) {
	            config.context.readMessageQueue.push(entry.target.dataset.messageId);
	            config.context.readMessageTarget[entry.target.dataset.messageId] = entry.target;
	          } else {
	            config.context.readMessageQueue = config.context.readMessageQueue.filter(function (messageId) {
	              return messageId !== entry.target.dataset.messageId;
	            });
	            delete config.context.readMessageTarget[entry.target.dataset.messageId];
	          }

	          if (config.context.enableReadMessages) {
	            config.context.readMessageDelayed();
	          }
	        });
	      };

	      observerOptions = {
	        root: config.context.$refs.body,
	        threshold: new Array(101).fill(0).map(function (zero, index) {
	          return index * 0.01;
	        })
	      };
	    } else {
	      observerCallback = function observerCallback(entries, observer) {
	        entries.forEach(function (entry) {
	          if (entry.isIntersecting) {
	            if (config.type === ObserverType.unread) {
	              config.context.requestUnreadBlockIntersect = true;
	              config.context.requestUnreadDelayed();
	            } else {
	              config.context.requestHistoryBlockIntersect = true;
	              config.context.requestHistoryDelayed();
	            }
	          } else {
	            if (config.type === ObserverType.unread) {
	              config.context.requestUnreadBlockIntersect = false;
	            } else {
	              config.context.requestHistoryBlockIntersect = false;
	            }
	          }
	        });
	      };

	      observerOptions = {
	        root: config.context.$refs.body,
	        threshold: [0, 0.01, 0.99, 1]
	      };
	    }

	    return new IntersectionObserver(observerCallback, observerOptions);
	  }
	};
	var Blocks = {
	  getDelimiter: function getDelimiter() {
	    var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	    return {
	      templateId: 'delimiter' + id,
	      templateType: TemplateType.delimiter
	    };
	  },
	  getGroup: function getGroup() {
	    var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	    var text = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	    return {
	      templateId: 'group' + id,
	      templateType: TemplateType.group,
	      text: text
	    };
	  },
	  getHistoryLoader: function getHistoryLoader() {
	    return {
	      templateId: 'historyLoader',
	      templateType: TemplateType.historyLoader
	    };
	  },
	  getUnreadLoader: function getUnreadLoader() {
	    return {
	      templateId: 'unreadLoader',
	      templateType: TemplateType.unreadLoader
	    };
	  },
	  getLoadButton: function getLoadButton() {
	    var id = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
	    var text = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	    var type = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : LoadButtonTypes.before;
	    return {
	      templateId: 'loadButton' + id + type,
	      templateType: TemplateType.button,
	      text: text,
	      type: type,
	      messageId: id
	    };
	  }
	};

}((this.window = this.window || {}),BX,BX,BX,window,BX.Messenger.Const,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib));
//# sourceMappingURL=dialog.bundle.js.map
