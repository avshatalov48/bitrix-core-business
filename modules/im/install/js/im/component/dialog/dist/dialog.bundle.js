(function (exports,main_polyfill_intersectionobserver,ui_vue) {
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
	var ReferenceClassName = Object.freeze({
	  listItem: 'bx-im-dialog-list-item-reference',
	  listItemBody: 'bx-im-dialog-list-item-content-reference',
	  listUnreadLoader: 'bx-im-dialog-list-unread-loader-reference'
	});
	ui_vue.Vue.component('bx-messenger-dialog', {
	  /**
	   * @emits 'requestHistory' {lastId: number, limit: number}
	   * @emits 'requestUnread' {lastId: number, limit: number}
	   * @emits 'readMessage' {id: number}
	   * @emits 'click' {event: MouseEvent}
	   * @emits 'clickByUserName' {userData: object, event: MouseEvent}
	   * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	   * @emits 'clickByCommand' {type: string, value: string, event: MouseEvent}
	   */

	  /**
	   * @listens props.listenEventScrollToBottom {force:boolean} (global|application) -- scroll dialog to bottom, see more in methods.onScrollToBottom()
	   * @listens props.listenEventRequestHistory {count:number} (application)
	   * @listens props.listenEventRequestUnread {count:number} (application)
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
	    listenEventScrollToBottom: {
	      default: ''
	    },
	    listenEventRequestHistory: {
	      default: ''
	    },
	    listenEventRequestUnread: {
	      default: ''
	    },
	    enableEmotions: {
	      default: true
	    },
	    enableDateActions: {
	      default: true
	    },
	    enableCreateContent: {
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
	      showScrollButton: false,
	      messageShowCount: 0,
	      messageExtraCount: 0,
	      unreadLoaderShow: false,
	      unreadLoaderBlocked: false,
	      historyLoaderBlocked: false,
	      historyLoaderShow: false,
	      startMessageLimit: 0,
	      TemplateType: TemplateType,
	      ObserverType: ObserverType,
	      ReferenceClassName: ReferenceClassName
	    };
	  },
	  created: function created() {
	    this.scrollIsChanged = false;
	    this.scrollBlocked = false;
	    this.scrollButtonDiff = 30;
	    this.scrollButtonShowTimeout = null;
	    this.scrollPosition = 0;
	    this.scrollPositionChangeTime = new Date().getTime();
	    this.observers = {};
	    this.requestHistoryInterval = null;
	    this.requestUnreadInterval = null;
	    this.lastAuthorId = 0;
	    this.firstMessageId = null;
	    this.firstUnreadMessageId = null;
	    this.lastMessageId = null;
	    this.dateFormatFunction = null;
	    this.cacheGroupTitle = {};
	    this.waitLoadHistory = false;
	    this.waitLoadUnread = false;
	    this.readMessageQueue = [];
	    this.unreadLoaderBlocked = this.dialog.counter === 0;
	    this.startMessageLimit = this.messageLimit;

	    if (this.listenEventScrollToBottom) {
	      ui_vue.Vue.event.$on(this.listenEventScrollToBottom, this.onScrollToBottom);
	      this.$root.$on(this.listenEventScrollToBottom, this.onScrollToBottom);
	    }

	    if (this.listenEventRequestHistory) {
	      this.$root.$on(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
	    }

	    if (this.listenEventRequestUnread) {
	      this.$root.$on(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
	    }

	    window.addEventListener('focus', this.onWindowFocus);
	    window.addEventListener('blur', this.onWindowBlur);
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
	      this.$root.$off(this.listenEventRequestHistory, this.onRequestHistoryAnswer);
	    }

	    if (this.listenEventRequestUnread) {
	      this.$root.$off(this.listenEventRequestUnread, this.onRequestUnreadAnswer);
	    }

	    window.removeEventListener('focus', this.onWindowFocus);
	    window.removeEventListener('blur', this.onWindowBlur);
	  },
	  mounted: function mounted() {
	    var body = this.$refs.body;
	    var unreadId = this.dialog.unreadId;

	    if (unreadId) {
	      Utils.scrollToFirstUnreadMessage(this, this.collection, unreadId, true);
	    } else {
	      body.scrollTop = body.scrollHeight - body.offsetHeight;
	    }

	    this.windowFocused = document.hasFocus();
	  },
	  beforeUpdate: function beforeUpdate() {
	    var body = this.$refs.body;

	    if (this.scrollBlocked) {
	      this.scrollIsChanged = false;
	    } else {
	      this.scrollIsChanged = body.scrollTop + this.scrollButtonDiff >= body.scrollHeight - body.offsetHeight;

	      if (!this.scrollIsChanged && !this.showScrollButton && this.unreadCounter > 1) {
	        this.showScrollButton = true;
	      }
	    }
	  },
	  updated: function updated() {
	    var _this = this;

	    if (!this.scrollIsChanged) {
	      return;
	    }

	    this.$nextTick(function () {
	      var body = _this.$refs.body;

	      if (_this.scrollIsChanged) {
	        if (!_this.windowFocused && _this.unreadCounter > 0 && !_this.showScrollButton) {
	          Utils.scrollToFirstUnreadMessage(_this, _this.collection, _this.firstUnreadMessageId);
	          return;
	        }

	        _this.scrollTo(function () {
	          clearTimeout(_this.scrollButtonShowTimeout);

	          if (_this.showScrollButton && _this.windowFocused) {
	            _this.showScrollButton = false;
	          }
	        });
	      }
	    });
	  },
	  computed: {
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_DIALOG_', this.$root.$bitrixMessages);
	    },
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    collection: function collection() {
	      return this.$store.getters['messages/get'](this.chatId);
	    },
	    elementsWithLimit: function elementsWithLimit() {
	      var _this2 = this;

	      var start = this.collection.length - (this.messageExtraCount + this.messageLimit);

	      if (!this.historyLoaderShow || start < 0) {
	        start = 0;
	      }

	      var collection = [];
	      var lastAuthorId = 0;
	      var groupNode = {};
	      var slicedCollection = start == 0 ? this.collection : this.collection.slice(start, this.collection.length);
	      this.messageShowCount = slicedCollection.length;

	      if (this.messageShowCount > 0) {
	        this.firstMessageId = slicedCollection[0].id;
	        this.lastMessageId = slicedCollection[slicedCollection.length - 1].id;
	      }

	      if (this.collection.length >= this.messageLimit && this.collection.length >= this.messageShowCount && this.historyLoaderBlocked === false) {
	        this.historyLoaderShow = true;
	      } else {
	        this.historyLoaderShow = false;
	      }

	      this.firstUnreadMessageId = 0;
	      slicedCollection.forEach(function (element) {
	        var group = _this2._groupTitle(element.date);

	        if (!groupNode[group.id]) {
	          groupNode[group.id] = true;
	          collection.push(Blocks.getGroup(group.id, group.title));
	        } else if (lastAuthorId != element.authorId) {
	          collection.push(Blocks.getDelimiter(element.id));
	        }

	        collection.push(element);
	        lastAuthorId = element.authorId;

	        if (element.unread) {
	          if (!_this2.firstUnreadMessageId) {
	            _this2.firstUnreadMessageId = element.id;
	          }
	        }
	      });

	      if (this.dialog.unreadLastId > this.lastMessageId && this.unreadLoaderBlocked === false) {
	        this.unreadLoaderShow = true;
	      } else {
	        this.unreadLoaderShow = false;
	      }

	      return collection;
	    },
	    statusWriting: function statusWriting() {
	      if (this.dialog.writingList.length == 0) return '';
	      var users = this.dialog.writingList.map(function (element) {
	        return element.userName;
	      });
	      return this.localize.IM_MESSENGER_DIALOG_WRITES_MESSAGE.replace('#USER#', users.join(', '));
	    },
	    statusReaded: function statusReaded() {
	      return false;
	    },
	    unreadCounter: function unreadCounter() {
	      return this.dialog.counter > 999 ? 999 : this.dialog.counter;
	    }
	  },
	  methods: {
	    onDialogClick: function onDialogClick(event) {
	      if (ui_vue.Vue.testNode(event.target, {
	        className: 'bx-im-message-command'
	      })) {
	        this.onCommandClick(event);
	      }

	      this.windowFocused = true;
	      this.$emit('click', {
	        event: event
	      });
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
	    onScroll: function onScroll(event) {
	      var _this3 = this;

	      this.scrollPosition = event.target.scrollTop;
	      this.scrollPositionChangeTime = new Date().getTime();
	      clearTimeout(this.scrollButtonShowTimeout);
	      this.scrollButtonShowTimeout = setTimeout(function () {
	        if (event.target.scrollTop + _this3.scrollButtonDiff >= event.target.scrollHeight - event.target.offsetHeight) {
	          if (_this3.showScrollButton && !_this3.unreadLoaderShow && _this3.windowFocused) {
	            _this3.showScrollButton = false;
	          }
	        } else {
	          if (!_this3.showScrollButton) {
	            _this3.showScrollButton = true;
	          }
	        }
	      }, 200);

	      if (event.target.scrollTop == event.target.scrollHeight - event.target.offsetHeight) {
	        clearTimeout(this.scrollButtonShowTimeout);

	        if (this.showScrollButton && !this.unreadLoaderShow && this.windowFocused) {
	          this.showScrollButton = false;
	        }
	      }
	    },
	    scrollToBottom: function scrollToBottom() {
	      var _this4 = this;

	      var force = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var body = this.$refs.body;

	      if (this.dialog.counter > 0) {
	        var scrollToMessageId = this.dialog.counter > 1 ? this.firstUnreadMessageId : this.lastMessageId;
	        Utils.scrollToFirstUnreadMessage(this, this.collection, scrollToMessageId);

	        if (this.dialog.counter < this.startMessageLimit) {
	          this.messageExtraCount = 0;
	          this.historyLoaderShow = true;
	          this.historyLoaderBlocked = false;
	        }

	        return true;
	      }

	      this.showScrollButton = false;

	      if (force) {
	        body.scrollTop = body.scrollHeight - body.offsetHeight;
	        this.messageExtraCount = 0;
	        this.historyLoaderShow = true;
	        this.historyLoaderBlocked = false;
	      } else {
	        this.scrollTo(function () {
	          _this4.messageExtraCount = 0;
	          _this4.historyLoaderShow = true;
	          _this4.historyLoaderBlocked = false;
	        });
	      }
	    },
	    scrollTo: function scrollTo(params) {
	      var _this5 = this;

	      var body = this.$refs.body;

	      if (typeof params === 'function') {
	        params = {
	          callback: params
	        };
	      }

	      if (!body) {
	        if (params.callback && typeof params.callback === 'function') {
	          params.callback();
	        }

	        return true;
	      }

	      var _params = params,
	          _params$start = _params.start,
	          start = _params$start === void 0 ? body.scrollTop : _params$start,
	          _params$end = _params.end,
	          end = _params$end === void 0 ? body.scrollHeight - body.offsetHeight : _params$end,
	          _params$increment = _params.increment,
	          increment = _params$increment === void 0 ? 20 : _params$increment,
	          callback = _params.callback,
	          _params$duration = _params.duration,
	          duration = _params$duration === void 0 ? 300 : _params$duration;
	      var diff = end - start;
	      var currentPosition = 0;

	      var easeInOutQuad = function easeInOutQuad(current, start, diff, duration) {
	        current /= duration / 2;

	        if (current < 1) {
	          return diff / 2 * current * current + start;
	        }

	        current--;
	        return -diff / 2 * (current * (current - 2) - 1) + start;
	      };

	      var requestFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (callback) {
	        window.setTimeout(callback, 1000 / 60);
	      };

	      var animateScroll = function animateScroll() {
	        currentPosition += increment;
	        _this5.$refs.body.scrollTop = easeInOutQuad(currentPosition, start, diff, duration);

	        if (currentPosition < duration) {
	          requestFrame(animateScroll);
	        } else {
	          if (callback && typeof callback === 'function') {
	            callback();
	          }
	        }
	      };

	      animateScroll();
	    },
	    onScrollToBottom: function onScrollToBottom() {
	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      event.force = event.force === true;
	      this.scrollToBottom(event.force);
	      return true;
	    },
	    onWindowFocus: function onWindowFocus() {
	      var _this6 = this;
	      this.windowFocused = true;
	      this.readMessageQueue = this.readMessageQueue.map(function (messageId) {
	        _this6.requestReadMessage(messageId);

	        return false;
	      });
	    },
	    onWindowBlur: function onWindowBlur() {
	      this.windowFocused = false;
	    },
	    requestHistoryDelayed: function requestHistoryDelayed() {
	      var _this7 = this;

	      if (this.requestHistoryInterval) {
	        BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestHistoryDelayed: skipped');
	        return false;
	      }

	      if (this.scrollPositionChangeTime + 100 < new Date().getTime() && this.$refs.body.scrollTop >= 0) {
	        clearInterval(this.requestHistoryInterval);
	        this.requestHistoryInterval = null;
	        this.requestHistory();
	        return true;
	      }

	      clearInterval(this.requestHistoryInterval);
	      this.requestHistoryInterval = setInterval(function () {
	        if (_this7.scrollPositionChangeTime + 100 < new Date().getTime() && _this7.$refs.body.scrollTop >= 0) {
	          clearInterval(_this7.requestHistoryInterval);
	          _this7.requestHistoryInterval = null;

	          _this7.requestHistory();

	          return true;
	        }
	      }, 50);
	      return true;
	    },
	    requestHistory: function requestHistory() {
	      if (this.waitLoadHistory) {
	        BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestHistory: waitLoadHistory not empty');
	        return false;
	      }

	      this.waitLoadHistory = true;
	      var length = this.collection.length;
	      var messageShowCount = this.messageShowCount;

	      if (length > messageShowCount) {
	        var element = this.$refs.body.getElementsByClassName(ReferenceClassName.listItem)[0];
	        this.messageExtraCount += this.messageLimit;
	        Utils.scrollToElementAfterLoadHistory(this, element);
	        return true;
	      }

	      this.$emit('requestHistory', {
	        lastId: this.firstMessageId
	      });
	    },
	    requestUnreadDelayed: function requestUnreadDelayed() {
	      var _this8 = this;

	      if (this.requestUnreadInterval) {
	        BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestUnreadDelayed: skipped');
	        return false;
	      }

	      var body = this.$refs.body;

	      if (this.scrollPositionChangeTime + 100 < new Date().getTime() && body.scrollTop <= body.scrollHeight - body.offsetHeight) {
	        clearInterval(this.requestUnreadInterval);
	        this.requestUnreadInterval = null;
	        this.requestUnread();
	        return true;
	      }

	      clearInterval(this.requestUnreadInterval);
	      this.requestUnreadInterval = setInterval(function () {
	        if (_this8.scrollPositionChangeTime + 100 < new Date().getTime() && body.scrollTop <= body.scrollHeight - body.offsetHeight) {
	          clearInterval(_this8.requestUnreadInterval);
	          _this8.requestUnreadInterval = null;

	          _this8.requestUnread();

	          return true;
	        }
	      }, 50);
	      return true;
	    },
	    onRequestHistoryAnswer: function onRequestHistoryAnswer() {
	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (event.error) {
	        this.historyLoaderBlocked = false;
	      } else {
	        this.historyLoaderBlocked = event.count < this.startMessageLimit;
	        this.messageExtraCount += event.count;
	      }

	      if (this.historyLoaderBlocked) {
	        this.historyLoaderShow = false;
	      }

	      var element = this.$refs.body.getElementsByClassName(ReferenceClassName.listItem)[0];

	      if (event.count > 0) {
	        Utils.scrollToElementAfterLoadHistory(this, element);
	      } else if (event.error) {
	        element.scrollIntoView(true);
	        this.waitLoadHistory = false;
	      } else {
	        this.$refs.body.scrollTop = 0;
	        this.waitLoadHistory = false;
	      }

	      return true;
	    },
	    requestUnread: function requestUnread() {
	      if (this.waitLoadUnread) {
	        BX.Messenger.Logger.log('bx-messenger-dialog.methods.requestUnread: waitLoadUnread not empty');
	        return false;
	      }

	      this.waitLoadUnread = true;
	      this.$emit('requestUnread', {
	        lastId: this.lastMessageId
	      });
	    },
	    onRequestUnreadAnswer: function onRequestUnreadAnswer() {
	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (event.error) {
	        this.historyLoaderBlocked = false;
	      } else {
	        this.unreadLoaderBlocked = event.count < this.startMessageLimit;
	        this.messageExtraCount += event.count;
	      }

	      if (this.unreadLoaderBlocked) {
	        this.unreadLoaderShow = false;
	      }

	      var body = this.$refs.body;

	      if (event.count > 0) {
	        Utils.scrollToElementAfterLoadUnread(this);
	      } else if (event.error) {
	        var element = this.$refs.body.getElementsByClassName(ReferenceClassName.listUnreadLoader)[0];

	        if (element) {
	          body.scrollTop = body.scrollTop - element.offsetHeight * 2;
	        } else {
	          body.scrollTop = body.scrollHeight - body.offsetHeight;
	        }

	        this.waitLoadUnread = false;
	      } else {
	        body.scrollTop = body.scrollHeight - body.offsetHeight;
	        this.waitLoadUnread = false;
	      }

	      return true;
	    },
	    readMessage: function readMessage(messageId) {
	      if (this.windowFocused) {
	        this.$emit('readMessage', {
	          id: messageId
	        });
	      } else {
	        this.readMessageQueue.push(messageId);
	      }
	    },
	    requestReadMessage: function requestReadMessage(messageId) {
	      this.$emit('readMessage', {
	        id: messageId
	      });
	    },
	    onClickByUserName: function onClickByUserName(event) {
	      this.$emit('clickByUserName', event);
	    },
	    onClickByMessageMenu: function onClickByMessageMenu(event) {
	      this.$emit('clickByMessageMenu', event);
	    },
	    onClickByMessageRetry: function onClickByMessageRetry(event) {
	      this.$emit('clickByMessageRetry', event);
	    },
	    _getDateFormat: function _getDateFormat() {
	      var _this9 = this;

	      if (this.dateFormatFunction) {
	        return this.dateFormatFunction;
	      }

	      this.dateFormatFunction = Object.create(BX.Main.Date);

	      if (this.$root.$bitrixMessages) {
	        this.dateFormatFunction._getMessage = function (phrase) {
	          return _this9.$root.$bitrixMessages[phrase];
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

	      var dateFormat = BX.Messenger.Utils.getDateFormatType(BX.Messenger.Const.DateFormat.groupTitle, this.$root.$bitrixMessages);
	      this.cacheGroupTitle[id] = this._getDateFormat().format(dateFormat, date);
	      return {
	        id: id,
	        title: this.cacheGroupTitle[id]
	      };
	    }
	  },
	  directives: {
	    'bx-messenger-dialog-observer': {
	      inserted: function inserted(element, bindings, vnode) {
	        if (bindings.value == ObserverType.none) {
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
	        if (bindings.value == ObserverType.none) {
	          return true;
	        }

	        if (vnode.context.observers[bindings.value]) {
	          vnode.context.observers[bindings.value].unobserve(element);
	        }

	        return true;
	      }
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-dialog\" @click=\"onDialogClick\">\t\n\t\t\t<div class=\"bx-im-dialog-list\" @scroll.passive=\"onScroll\" ref=\"body\">\n\t\t\t\t<template v-if=\"historyLoaderShow\">\n\t\t\t\t\t<div class=\"bx-im-dialog-load-more bx-im-dialog-load-more-history\" v-bx-messenger-dialog-observer=\"ObserverType.history\">\n\t\t\t\t\t\t<span class=\"bx-im-dialog-load-more-text\">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<transition-group tag=\"div\" class=\"bx-im-dialog-list-box\" name=\"bx-im-dialog-message-animation\" >\n\t\t\t\t\t<template v-for=\"element in elementsWithLimit\">\n\t\t\t\t\t\t<template v-if=\"element.templateType == TemplateType.message\">\n\t\t\t\t\t\t\t<div :class=\"['bx-im-dialog-list-item', ReferenceClassName.listItem, ReferenceClassName.listItem+'-'+element.id]\" :data-message-id=\"element.id\" :key=\"element.templateId\" v-bx-messenger-dialog-observer=\"element.unread? ObserverType.read: ObserverType.none\">\t\t\t\n\t\t\t\t\t\t\t\t<component :is=\"element.params.COMPONENT_ID\"\n\t\t\t\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t\t\t\t\t:message=\"element\"\n\t\t\t\t\t\t\t\t\t:enableEmotions=\"enableEmotions\"\n\t\t\t\t\t\t\t\t\t:enableDateActions=\"enableDateActions\"\n\t\t\t\t\t\t\t\t\t:enableCreateContent=\"showMessageMenu\"\n\t\t\t\t\t\t\t\t\t:showAvatar=\"showMessageAvatar\"\n\t\t\t\t\t\t\t\t\t:showMenu=\"showMessageMenu\"\n\t\t\t\t\t\t\t\t\t:referenceContentClassName=\"ReferenceClassName.listItem\"\n\t\t\t\t\t\t\t\t\t:referenceContentBodyClassName=\"ReferenceClassName.listItemBody\"\n\t\t\t\t\t\t\t\t\t@clickByUserName=\"onClickByUserName\"\n\t\t\t\t\t\t\t\t\t@clickByMessageMenu=\"onClickByMessageMenu\"\n\t\t\t\t\t\t\t\t\t@clickByMessageRetry=\"onClickByMessageRetry\"\n\t\t\t\t\t\t\t\t/>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"element.templateType == TemplateType.group\">\n\t\t\t\t\t\t\t<div class=\"bx-im-dialog-group\" :key=\"element.templateId\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-dialog-group-date\">{{ element.text }}</div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"element.templateType == TemplateType.delimiter\">\n\t\t\t\t\t\t\t<div class=\"bx-im-dialog-delimiter\" :key=\"element.templateId\" ></div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</template>\n\t\t\t\t</transition-group>\n\t\t\t\t<template v-if=\"unreadLoaderShow\">\n\t\t\t\t\t<div :class=\"['bx-im-dialog-load-more', 'bx-im-dialog-load-more-unread', ReferenceClassName.listUnreadLoader]\" v-bx-messenger-dialog-observer=\"ObserverType.unread\">\n\t\t\t\t\t\t<span class=\"bx-im-dialog-load-more-text\">{{ localize.IM_MESSENGER_DIALOG_LOAD_MESSAGES }}</span>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<transition name=\"bx-im-dialog-status\">\n\t\t\t\t\t<template v-if=\"statusWriting\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-status\">\n\t\t\t\t\t\t\t<span class=\"bx-im-dialog-status-writing\"></span>\n\t\t\t\t\t\t\t{{ statusWriting }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<template v-else-if=\"statusReaded\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-status\">\n\t\t\t\t\t\t\t<span class=\"bx-im-dialog-status-readed\"></span>\n\t\t\t\t\t\t\t{{ statusReaded }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template>\n\t\t\t\t</transition>\n\t\t\t</div>\n\t\t\t<transition name=\"bx-im-dialog-scroll-button\">\n\t\t\t\t<div v-show=\"showScrollButton || unreadLoaderShow && unreadCounter\" class=\"bx-im-dialog-scroll-button\" @click=\"scrollToBottom()\">\n\t\t\t\t\t<div v-show=\"unreadCounter\" class=\"bx-im-dialog-scroll-button-counter\">\n\t\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button-counter-digit\">{{unreadCounter}}</div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"bx-im-dialog-scroll-button-arrow\"></div>\n\t\t\t\t</div>\n\t\t\t</transition>\n\t\t</div>\n\t"
	});
	var Utils = {
	  getDateFormat: function getDateFormat(date) {
	    return date.toJSON().slice(0, 10);
	  },
	  scrollToFirstUnreadMessage: function scrollToFirstUnreadMessage(context, collection) {
	    var unreadId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
	    var force = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : false;
	    var body = context.$refs.body;
	    var element = false;

	    if (unreadId !== null) {
	      element = body.getElementsByClassName(ReferenceClassName.listItem + '-' + unreadId)[0];
	    }

	    if (!element) {
	      for (var index = collection.length - 1; index >= 0; index--) {
	        if (!collection[index].unread) {
	          break;
	        }

	        unreadId = collection[index].id;
	      }

	      element = body.getElementsByClassName(ReferenceClassName.listItem + '-' + unreadId)[0];
	    }

	    var end = 0;

	    if (element) {
	      end = element.offsetTop - 20;
	    } else {
	      end = body.scrollHeight - body.offsetHeight;
	    }

	    if (force) {
	      body.scrollTop = end;
	    } else {
	      context.scrollTo({
	        end: end
	      });
	    }
	  },
	  scrollToElementAfterLoadHistory: function scrollToElementAfterLoadHistory(context, element) {
	    if (!element) {
	      context.waitLoadHistory = false;
	      return false;
	    }

	    var elementBody = element.getElementsByClassName(ReferenceClassName.listItemBody)[0];

	    if (elementBody) {
	      element = elementBody;
	    }

	    var previousOffsetTop = element.offsetTop;
	    context.$nextTick(function () {
	      if (!element) {
	        return false;
	      }

	      context.$refs.body.scrollTop = element.offsetTop - previousOffsetTop;
	      context.waitLoadHistory = false;
	    });
	  },
	  scrollToElementAfterLoadUnread: function scrollToElementAfterLoadUnread(context) {
	    context.scrollBlocked = true;
	    context.showScrollButton = true;
	    context.$nextTick(function () {
	      context.scrollBlocked = false;
	      context.waitLoadUnread = false;
	    });
	  },
	  getMessageLoaderObserver: function getMessageLoaderObserver(config) {
	    if (typeof window.IntersectionObserver === 'undefined' || config.value == ObserverType.none) {
	      return {
	        observe: function observe() {},
	        unobserve: function unobserve() {}
	      };
	    }

	    var observerCallback, observerOptions;

	    if (config.type == ObserverType.read) {
	      observerCallback = function observerCallback(entries, observer) {
	        entries.forEach(function (entry) {
	          var sendReadEvent = false;

	          if (entry.isIntersecting) {
	            if (entry.intersectionRatio >= 1) {
	              sendReadEvent = true;
	            } else if (entry.intersectionRatio > 0 && entry.rootBounds.height < entry.boundingClientRect.height + 20 && entry.intersectionRect.height > entry.rootBounds.height - 20) {
	              sendReadEvent = true;
	            }
	          }

	          if (sendReadEvent) {
	            config.context.readMessage(entry.target.dataset.messageId);
	            config.context.observers[config.type].unobserve(entry.target);
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
	          if (entry.isIntersecting && entry.intersectionRatio > 0) {
	            if (config.type == ObserverType.unread) {
	              config.context.requestUnreadDelayed();
	            } else {
	              config.context.requestHistoryDelayed();
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

}((this.window = this.window || {}),BX,BX));
//# sourceMappingURL=dialog.bundle.js.map
