/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,main_core_events,main_core,im_v2_application_core,im_v2_lib_desktop,im_v2_lib_logger,im_v2_const) {
	'use strict';

	const updateBrowserTitleCounter = newCounter => {
	  const MAX_COUNTER_VALUE = 99;
	  const MAX_COUNTER_TEXT = `${MAX_COUNTER_VALUE}+`;
	  const regexp = /^\((?<currentCounter>\d+|\d+\+)\)\s(?<text>.*)/;
	  const matchResult = document.title.match(regexp);
	  const displayCounter = newCounter > MAX_COUNTER_VALUE ? MAX_COUNTER_TEXT : newCounter;
	  if (matchResult != null && matchResult.groups.currentCounter) {
	    const currentCounter = Number.parseInt(matchResult.groups.currentCounter, 10);
	    if (newCounter !== currentCounter) {
	      const counterPrefix = newCounter > 0 ? `(${displayCounter}) ` : '';
	      document.title = `${counterPrefix}${matchResult.groups.text}`;
	    }
	  } else if (newCounter > 0) {
	    document.title = `(${displayCounter}) ${document.title}`;
	  }
	};

	var _instance = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("instance");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _emitCountersUpdateWithDebounce = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitCountersUpdateWithDebounce");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	var _prepareChatCounters = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("prepareChatCounters");
	var _subscribeToCountersChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeToCountersChange");
	var _emitLegacyNotificationCounterUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitLegacyNotificationCounterUpdate");
	var _emitLegacyChatCounterUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitLegacyChatCounterUpdate");
	var _emitLegacyLinesCounterUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitLegacyLinesCounterUpdate");
	var _emitCountersUpdate = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("emitCountersUpdate");
	var _onTotalCounterChange = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("onTotalCounterChange");
	class CounterManager {
	  static getInstance() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance]) {
	      babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance] = new this();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _instance)[_instance];
	  }
	  constructor() {
	    Object.defineProperty(this, _onTotalCounterChange, {
	      value: _onTotalCounterChange2
	    });
	    Object.defineProperty(this, _emitCountersUpdate, {
	      value: _emitCountersUpdate2
	    });
	    Object.defineProperty(this, _emitLegacyLinesCounterUpdate, {
	      value: _emitLegacyLinesCounterUpdate2
	    });
	    Object.defineProperty(this, _emitLegacyChatCounterUpdate, {
	      value: _emitLegacyChatCounterUpdate2
	    });
	    Object.defineProperty(this, _emitLegacyNotificationCounterUpdate, {
	      value: _emitLegacyNotificationCounterUpdate2
	    });
	    Object.defineProperty(this, _subscribeToCountersChange, {
	      value: _subscribeToCountersChange2
	    });
	    Object.defineProperty(this, _prepareChatCounters, {
	      value: _prepareChatCounters2
	    });
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _emitCountersUpdateWithDebounce, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce] = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdate)[_emitCountersUpdate], 0, this);
	    const {
	      counters: _counters
	    } = im_v2_application_core.Core.getApplicationData();
	    im_v2_lib_logger.Logger.warn('CounterManager: counters', _counters);
	    babelHelpers.classPrivateFieldLooseBase(this, _init)[_init](_counters);
	  }
	  static init() {
	    CounterManager.getInstance();
	  }
	  emitCounters() {
	    babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdate)[_emitCountersUpdate]();
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
	function _init2(counters) {
	  const preparedChatCounters = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatCounters)[_prepareChatCounters](counters.CHAT, counters.CHAT_UNREAD);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('counters/setUnloadedChatCounters', preparedChatCounters);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('counters/setUnloadedLinesCounters', counters.LINES);
	  const preparedCollabCounters = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatCounters)[_prepareChatCounters](counters.COLLAB, counters.COLLAB_UNREAD);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('counters/setUnloadedCollabCounters', preparedCollabCounters);
	  const preparedCopilotCounters = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatCounters)[_prepareChatCounters](counters.COPILOT, counters.COPILOT_UNREAD);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('counters/setUnloadedCopilotCounters', preparedCopilotCounters);
	  const preparedTaskCounters = babelHelpers.classPrivateFieldLooseBase(this, _prepareChatCounters)[_prepareChatCounters](counters.TASKS_TASK, counters.TASKS_TASK_UNREAD);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('counters/setUnloadedTaskCounters', preparedTaskCounters);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('counters/setCommentCounters', counters.CHANNEL_COMMENT);
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('notifications/setCounter', counters.TYPE.NOTIFY);
	  babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdate)[_emitCountersUpdate]();
	  babelHelpers.classPrivateFieldLooseBase(this, _subscribeToCountersChange)[_subscribeToCountersChange]();
	  babelHelpers.classPrivateFieldLooseBase(this, _emitLegacyChatCounterUpdate)[_emitLegacyChatCounterUpdate](counters.TYPE.CHAT);
	  babelHelpers.classPrivateFieldLooseBase(this, _emitLegacyNotificationCounterUpdate)[_emitLegacyNotificationCounterUpdate](counters.TYPE.NOTIFY);
	  babelHelpers.classPrivateFieldLooseBase(this, _emitLegacyLinesCounterUpdate)[_emitLegacyLinesCounterUpdate](counters.TYPE.LINES);
	  babelHelpers.classPrivateFieldLooseBase(this, _onTotalCounterChange)[_onTotalCounterChange]();
	}
	function _prepareChatCounters2(counters, unreadCounters) {
	  const resultCounters = {
	    ...counters
	  };
	  unreadCounters.forEach(markedChatId => {
	    const unreadChatHasCounter = Boolean(counters[markedChatId]);
	    if (unreadChatHasCounter) {
	      return;
	    }
	    resultCounters[markedChatId] = 1;
	  });
	  return resultCounters;
	}
	function _subscribeToCountersChange2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(notificationCounterWatch, newValue => {
	    babelHelpers.classPrivateFieldLooseBase(this, _emitLegacyNotificationCounterUpdate)[_emitLegacyNotificationCounterUpdate](newValue);
	    babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce]();
	    babelHelpers.classPrivateFieldLooseBase(this, _onTotalCounterChange)[_onTotalCounterChange]();
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(chatCounterWatch, newValue => {
	    babelHelpers.classPrivateFieldLooseBase(this, _emitLegacyChatCounterUpdate)[_emitLegacyChatCounterUpdate](newValue);
	    babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce]();
	    babelHelpers.classPrivateFieldLooseBase(this, _onTotalCounterChange)[_onTotalCounterChange]();
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(linesCounterWatch, newValue => {
	    babelHelpers.classPrivateFieldLooseBase(this, _emitLegacyLinesCounterUpdate)[_emitLegacyLinesCounterUpdate](newValue);
	    babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce]();
	    babelHelpers.classPrivateFieldLooseBase(this, _onTotalCounterChange)[_onTotalCounterChange]();
	  });
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(copilotCounterWatch, () => babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce]());
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(collabCounterWatch, () => babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce]());
	  babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].watch(taskCounterWatch, () => babelHelpers.classPrivateFieldLooseBase(this, _emitCountersUpdateWithDebounce)[_emitCountersUpdateWithDebounce]());
	}
	function _emitLegacyNotificationCounterUpdate2(notificationsCounter) {
	  const event = new main_core_events.BaseEvent({
	    compatData: [notificationsCounter]
	  });
	  main_core_events.EventEmitter.emit(window, im_v2_const.EventType.counter.onNotificationCounterChange, event);
	}
	function _emitLegacyChatCounterUpdate2(chatCounter) {
	  const event = new main_core_events.BaseEvent({
	    compatData: [chatCounter]
	  });
	  main_core_events.EventEmitter.emit(window, im_v2_const.EventType.counter.onChatCounterChange, event);
	}
	function _emitLegacyLinesCounterUpdate2(linesCounter) {
	  const LINES_TYPE = 'LINES';
	  const event = new main_core_events.BaseEvent({
	    compatData: [linesCounter, LINES_TYPE]
	  });
	  main_core_events.EventEmitter.emit(window, im_v2_const.EventType.counter.onLinesCounterChange, event);
	}
	function _emitCountersUpdate2() {
	  const payload = {
	    [im_v2_const.NavigationMenuItem.chat]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalChatCounter'],
	    [im_v2_const.NavigationMenuItem.copilot]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalCopilotCounter'],
	    [im_v2_const.NavigationMenuItem.collab]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalCollabCounter'],
	    [im_v2_const.NavigationMenuItem.tasksTask]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalTaskCounter'],
	    [im_v2_const.NavigationMenuItem.openlines]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalLinesCounter'],
	    [im_v2_const.NavigationMenuItem.openlinesV2]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalLinesCounter'],
	    [im_v2_const.NavigationMenuItem.notification]: babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getCounter']
	  };
	  im_v2_lib_logger.Logger.warn('CounterManager: Emitting IM.Counters:onUpdate', payload);
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.counter.onUpdate, payload);
	}
	function _onTotalCounterChange2() {
	  const notificationCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['notifications/getCounter'];
	  const chatCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalChatCounter'];
	  const linesCounter = babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].getters['counters/getTotalLinesCounter'];
	  const totalCounter = notificationCounter + chatCounter + linesCounter;
	  if (im_v2_lib_desktop.DesktopManager.getInstance().isDesktopActive()) {
	    return;
	  }
	  updateBrowserTitleCounter(totalCounter);
	}
	Object.defineProperty(CounterManager, _instance, {
	  writable: true,
	  value: void 0
	});
	const notificationCounterWatch = (state, getters) => getters['notifications/getCounter'];
	const chatCounterWatch = (state, getters) => getters['counters/getTotalChatCounter'];
	const linesCounterWatch = (state, getters) => getters['counters/getTotalLinesCounter'];
	const copilotCounterWatch = (state, getters) => getters['counters/getTotalCopilotCounter'];
	const collabCounterWatch = (state, getters) => getters['counters/getTotalCollabCounter'];
	const taskCounterWatch = (state, getters) => getters['counters/getTotalTaskCounter'];

	exports.CounterManager = CounterManager;

}((this.BX.Messenger.v2.Lib = this.BX.Messenger.v2.Lib || {}),BX.Event,BX,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=counter.bundle.js.map
