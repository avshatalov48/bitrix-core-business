this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core,main_core_events,ui_vue3_vuex,im_v2_application_core,im_v2_lib_desktop) {
	'use strict';

	const NOTIFICATION_COUNTER_UPDATE_EVENT = 'onImUpdateCounterNotify';
	const CHAT_COUNTER_UPDATE_EVENT = 'onImUpdateCounterMessage';
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _prepareChatCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareChatCounters");
	var _subscribeToCountersChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToCountersChange");
	var _sendNotificationCounterChangeEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendNotificationCounterChangeEvent");
	var _sendChatCounterChangeEvent = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendChatCounterChangeEvent");
	var _onTotalCounterChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTotalCounterChange");
	var _updateBrowserTitleCounter = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("updateBrowserTitleCounter");
	class CounterManager {
	  static getInstance() {
	    if (!this.instance) {
	      this.instance = new this();
	    }
	    return this.instance;
	  }
	  static init(counters) {
	    CounterManager.getInstance().init(counters);
	  }
	  constructor() {
	    Object.defineProperty(this, _updateBrowserTitleCounter, {
	      value: _updateBrowserTitleCounter2
	    });
	    Object.defineProperty(this, _onTotalCounterChange, {
	      value: _onTotalCounterChange2
	    });
	    Object.defineProperty(this, _sendChatCounterChangeEvent, {
	      value: _sendChatCounterChangeEvent2
	    });
	    Object.defineProperty(this, _sendNotificationCounterChangeEvent, {
	      value: _sendNotificationCounterChangeEvent2
	    });
	    Object.defineProperty(this, _subscribeToCountersChange, {
	      value: _subscribeToCountersChange2
	    });
	    Object.defineProperty(this, _prepareChatCounters, {
	      value: _prepareChatCounters2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  init(counters) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('recent/setUnloadedChatCounters', babelHelpers.classPrivateFieldLooseBase(this, _prepareChatCounters)[_prepareChatCounters](counters));
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('notifications/setCounter', counters['TYPE']['NOTIFY']);
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeToCountersChange)[_subscribeToCountersChange]();
	    babelHelpers.classPrivateFieldLooseBase(this, _sendNotificationCounterChangeEvent)[_sendNotificationCounterChangeEvent](counters['TYPE']['NOTIFY']);
	  }
	  removeBrowserTitleCounter() {
	    const regexp = /^(?<counterWithWhitespace>\(\d+\)\s).*/;
	    const matchResult = document.title.match(regexp);
	    if (!(matchResult != null && matchResult.groups.counterWithWhitespace)) {
	      return;
	    }
	    const counterPrefixLength = matchResult.groups.counterWithWhitespace;
	    document.title = document.title.slice(counterPrefixLength);
	  }
	}
	function _prepareChatCounters2(counters) {
	  const chatCounters = main_core.Type.isArray(counters['CHAT']) ? {} : counters['CHAT'];
	  const markedChats = counters['CHAT_UNREAD'];
	  markedChats.forEach(markedChatId => {
	    const unreadChatHasCounter = !!chatCounters[markedChatId];
	    if (unreadChatHasCounter) {
	      return;
	    }
	    chatCounters[markedChatId] = 1;
	  });
	  return chatCounters;
	}
	function _subscribeToCountersChange2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(notificationCounterWatch, newValue => {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendNotificationCounterChangeEvent)[_sendNotificationCounterChangeEvent](newValue);
	    babelHelpers.classPrivateFieldLooseBase(this, _onTotalCounterChange)[_onTotalCounterChange]();
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(chatCounterWatch, newValue => {
	    babelHelpers.classPrivateFieldLooseBase(this, _sendChatCounterChangeEvent)[_sendChatCounterChangeEvent](newValue);
	    babelHelpers.classPrivateFieldLooseBase(this, _onTotalCounterChange)[_onTotalCounterChange]();
	  });
	}
	function _sendNotificationCounterChangeEvent2(notificationsCounter) {
	  const event = new main_core_events.BaseEvent({
	    compatData: [notificationsCounter]
	  });
	  main_core_events.EventEmitter.emit(window, NOTIFICATION_COUNTER_UPDATE_EVENT, event);
	}
	function _sendChatCounterChangeEvent2(chatCounter) {
	  const event = new main_core_events.BaseEvent({
	    compatData: [chatCounter]
	  });
	  main_core_events.EventEmitter.emit(window, CHAT_COUNTER_UPDATE_EVENT, event);
	}
	function _onTotalCounterChange2() {
	  const notificationCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getCounter'];
	  const chatCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['recent/getTotalCounter'];
	  const totalCounter = notificationCounter + chatCounter;
	  if (im_v2_lib_desktop.DesktopManager.getInstance().isDesktopActive()) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _updateBrowserTitleCounter)[_updateBrowserTitleCounter](totalCounter);
	}
	function _updateBrowserTitleCounter2(newCounter) {
	  const regexp = /^\((?<currentCounter>\d+)\)\s(?<text>.*)+/;
	  const matchResult = document.title.match(regexp);
	  if (matchResult != null && matchResult.groups.currentCounter) {
	    const currentCounter = Number.parseInt(matchResult.groups.currentCounter, 10);
	    if (newCounter !== currentCounter) {
	      const counterPrefix = newCounter > 0 ? `(${newCounter}) ` : '';
	      document.title = `${counterPrefix}${matchResult.groups.text}`;
	    }
	  } else if (newCounter > 0) {
	    document.title = `(${newCounter}) ${document.title}`;
	  }
	}
	const notificationCounterWatch = (state, getters) => {
	  return getters['notifications/getCounter'];
	};
	const chatCounterWatch = (state, getters) => {
	  return getters['recent/getTotalCounter'];
	};

	exports.CounterManager = CounterManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX,BX.Event,BX.Vue3.Vuex,BX.Messenger.v2.Application,BX.Messenger.v2.Lib));
//# sourceMappingURL=counter.bundle.js.map
