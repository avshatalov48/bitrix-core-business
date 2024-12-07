this.BX = this.BX || {};
(function (exports,main_core_events,ui_notification,main_core) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var LOAD_ITEMS_DELAY = 5000;
	var MAX_PENDING_ITEMS = 30;
	var _queue = /*#__PURE__*/new WeakMap();
	var _isProgress = /*#__PURE__*/new WeakMap();
	var _isFreeze = /*#__PURE__*/new WeakMap();
	var _loadItemsTimer = /*#__PURE__*/new WeakMap();
	var _callbacks = /*#__PURE__*/new WeakMap();
	var _loadItemsDelay = /*#__PURE__*/new WeakMap();
	var _maxPendingItems = /*#__PURE__*/new WeakMap();
	var _isExecuteInProgress = /*#__PURE__*/new WeakSet();
	var _isInaccessibleQueue = /*#__PURE__*/new WeakSet();
	var _isFrozen = /*#__PURE__*/new WeakSet();
	var Queue = /*#__PURE__*/function () {
	  function Queue(options) {
	    babelHelpers.classCallCheck(this, Queue);
	    _classPrivateMethodInitSpec(this, _isFrozen);
	    _classPrivateMethodInitSpec(this, _isInaccessibleQueue);
	    _classPrivateMethodInitSpec(this, _isExecuteInProgress);
	    _classPrivateFieldInitSpec(this, _queue, {
	      writable: true,
	      value: new Map()
	    });
	    _classPrivateFieldInitSpec(this, _isProgress, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec(this, _isFreeze, {
	      writable: true,
	      value: false
	    });
	    _classPrivateFieldInitSpec(this, _loadItemsTimer, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(this, _callbacks, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _loadItemsDelay, {
	      writable: true,
	      value: LOAD_ITEMS_DELAY
	    });
	    _classPrivateFieldInitSpec(this, _maxPendingItems, {
	      writable: true,
	      value: MAX_PENDING_ITEMS
	    });
	    if (main_core.Type.isPlainObject(options.callbacks)) {
	      babelHelpers.classPrivateFieldSet(this, _callbacks, options.callbacks);
	    }
	    if (main_core.Type.isNumber(options.loadItemsDelay)) {
	      babelHelpers.classPrivateFieldSet(this, _loadItemsDelay, options.loadItemsDelay);
	    }
	    if (main_core.Type.isNumber(options.maxPendingItems)) {
	      babelHelpers.classPrivateFieldSet(this, _maxPendingItems, options.maxPendingItems);
	    }
	  }
	  babelHelpers.createClass(Queue, [{
	    key: "loadItem",
	    value: function loadItem() {
	      var _this = this;
	      var ignoreProgressStatus = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      var ignoreDelay = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      if (babelHelpers.classPrivateFieldGet(this, _loadItemsTimer) && !ignoreDelay) {
	        return;
	      }
	      babelHelpers.classPrivateFieldSet(this, _loadItemsTimer, setTimeout(function () {
	        return _this.loadItemHandler(ignoreProgressStatus);
	      }, ignoreDelay ? 0 : babelHelpers.classPrivateFieldGet(this, _loadItemsDelay)));
	    }
	  }, {
	    key: "loadItemHandler",
	    value: function loadItemHandler() {
	      var _this2 = this;
	      var ignoreProgressStatus = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      if (_classPrivateMethodGet(this, _isExecuteInProgress, _isExecuteInProgress2).call(this, ignoreProgressStatus) || _classPrivateMethodGet(this, _isInaccessibleQueue, _isInaccessibleQueue2).call(this)) {
	        babelHelpers.classPrivateFieldSet(this, _loadItemsTimer, null);
	        return;
	      }
	      var items = this.getAllAsArray();
	      babelHelpers.classPrivateFieldGet(this, _queue).clear();
	      if (!main_core.Type.isArrayFilled(items)) {
	        return;
	      }
	      var promise = null;
	      var _babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _callbacks),
	        onBeforeExecute = _babelHelpers$classPr.onBeforeExecute;
	      if (main_core.Type.isFunction(onBeforeExecute)) {
	        // eslint-disable-next-line no-promise-executor-return
	        promise = new Promise(function (resolve) {
	          return onBeforeExecute(items).then(resolve);
	        });
	      } else {
	        promise = Promise.resolve();
	      }

	      // eslint-disable-next-line promise/catch-or-return
	      promise.then(function () {
	        return _this2.process(items);
	      });
	    }
	  }, {
	    key: "process",
	    value: function process(items) {
	      babelHelpers.classPrivateFieldSet(this, _isProgress, true);
	      var _babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _callbacks),
	        onExecute = _babelHelpers$classPr2.onExecute;
	      if (main_core.Type.isFunction(onExecute)) {
	        onExecute(items).then(this.loadNextOnSuccess.bind(this), this.doNothingOnError.bind(this))["catch"](function () {
	          return console.error('error');
	        });
	      } else {
	        this.loadNextOnSuccess();
	      }
	    }
	  }, {
	    key: "loadNextOnSuccess",
	    value: function loadNextOnSuccess() {
	      babelHelpers.classPrivateFieldSet(this, _loadItemsTimer, null);
	      if (!this.isEmpty()) {
	        this.loadItem(true);
	      }
	      babelHelpers.classPrivateFieldSet(this, _isProgress, false);
	    }
	  }, {
	    key: "doNothingOnError",
	    value: function doNothingOnError() {
	      babelHelpers.classPrivateFieldSet(this, _loadItemsTimer, null);
	    }
	  }, {
	    key: "push",
	    value: function push(id, item) {
	      if (this.has(id)) {
	        this["delete"](id);
	      }
	      babelHelpers.classPrivateFieldGet(this, _queue).set(id, item);
	      return this;
	    }
	  }, {
	    key: "getAllAsArray",
	    value: function getAllAsArray() {
	      return Array.from(babelHelpers.classPrivateFieldGet(this, _queue), function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          id = _ref2[0],
	          data = _ref2[1];
	        return {
	          id: id,
	          data: data
	        };
	      });
	    }
	  }, {
	    key: "delete",
	    value: function _delete(id) {
	      babelHelpers.classPrivateFieldGet(this, _queue)["delete"](id);
	    }
	  }, {
	    key: "has",
	    value: function has(id) {
	      return babelHelpers.classPrivateFieldGet(this, _queue).has(id);
	    }
	  }, {
	    key: "clear",
	    value: function clear() {
	      babelHelpers.classPrivateFieldGet(this, _queue).clear();
	    }
	  }, {
	    key: "isOverflow",
	    value: function isOverflow() {
	      return babelHelpers.classPrivateFieldGet(this, _queue).size > babelHelpers.classPrivateFieldGet(this, _maxPendingItems);
	    }
	  }, {
	    key: "isEmpty",
	    value: function isEmpty() {
	      return babelHelpers.classPrivateFieldGet(this, _queue).size === 0;
	    }
	  }, {
	    key: "freeze",
	    value: function freeze() {
	      babelHelpers.classPrivateFieldSet(this, _isFreeze, true);
	    }
	  }, {
	    key: "unfreeze",
	    value: function unfreeze() {
	      babelHelpers.classPrivateFieldSet(this, _isFreeze, false);
	    }
	  }, {
	    key: "getLoadItemsDelay",
	    value: function getLoadItemsDelay() {
	      return babelHelpers.classPrivateFieldGet(this, _loadItemsDelay);
	    }
	  }]);
	  return Queue;
	}();
	function _isExecuteInProgress2(ignoreProgressStatus) {
	  return babelHelpers.classPrivateFieldGet(this, _isProgress) && !ignoreProgressStatus;
	}
	function _isInaccessibleQueue2() {
	  return document.hidden || this.isOverflow() || _classPrivateMethodGet(this, _isFrozen, _isFrozen2).call(this);
	}
	function _isFrozen2() {
	  return babelHelpers.classPrivateFieldGet(this, _isFreeze);
	}

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _options = /*#__PURE__*/new WeakMap();
	var _queue$1 = /*#__PURE__*/new WeakMap();
	var _notifier = /*#__PURE__*/new WeakMap();
	var _openedSlidersCount = /*#__PURE__*/new WeakMap();
	var _hasManyOpenSliders = /*#__PURE__*/new WeakSet();
	var _getSliderInstance = /*#__PURE__*/new WeakSet();
	var _createAndShowNotify = /*#__PURE__*/new WeakSet();
	var QueueManager = /*#__PURE__*/function () {
	  babelHelpers.createClass(QueueManager, null, [{
	    key: "registerRandomEventId",
	    value: function registerRandomEventId() {
	      var prefix = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var eventId = main_core.Text.getRandom(12);
	      if (main_core.Type.isStringFilled(prefix)) {
	        eventId = "".concat(prefix, "-").concat(eventId);
	      }
	      this.registerEventId(eventId);
	      return eventId;
	    }
	  }, {
	    key: "registerEventId",
	    value: function registerEventId(eventId) {
	      this.eventIds.add(eventId);
	    }
	  }]);
	  function QueueManager(options) {
	    var _this = this;
	    babelHelpers.classCallCheck(this, QueueManager);
	    _classPrivateMethodInitSpec$1(this, _createAndShowNotify);
	    _classPrivateMethodInitSpec$1(this, _getSliderInstance);
	    _classPrivateMethodInitSpec$1(this, _hasManyOpenSliders);
	    _classPrivateFieldInitSpec$1(this, _options, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _queue$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _notifier, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _openedSlidersCount, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _options, options);
	    var config = options.config,
	      callbacks = options.callbacks;
	    babelHelpers.classPrivateFieldSet(this, _queue$1, new Queue({
	      loadItemsDelay: config === null || config === void 0 ? void 0 : config.loadItemsDelay,
	      maxPendingItems: config === null || config === void 0 ? void 0 : config.maxPendingItems,
	      callbacks: {
	        onBeforeExecute: callbacks.onBeforeQueueExecute,
	        onExecute: callbacks.onQueueExecute
	      }
	    }));
	    babelHelpers.classPrivateFieldSet(this, _openedSlidersCount, 0);
	    this.initEventEmitter();
	    var moduleId = options.moduleId,
	      userId = options.userId;
	    if (main_core.Type.isStringFilled(moduleId) && userId > 0) {
	      main_core.Event.ready(function () {
	        return _this.init();
	      });
	    }
	  }
	  babelHelpers.createClass(QueueManager, [{
	    key: "initEventEmitter",
	    value: function initEventEmitter() {
	      this.eventEmitter = new main_core_events.EventEmitter();
	      this.eventEmitter.setEventNamespace('BX.Pull.QueueManager');
	    }
	  }, {
	    key: "init",
	    value: function init() {
	      if (!BX.PULL) {
	        console.error('BX.PULL is not initialized');
	        return;
	      }
	      this.subscribe();
	      this.bindEvents();
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe() {
	      var _this2 = this;
	      var _babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _options),
	        moduleId = _babelHelpers$classPr.moduleId,
	        pullTag = _babelHelpers$classPr.pullTag;
	      BX.PULL.subscribe({
	        moduleId: moduleId,
	        callback: function callback(data) {
	          return _this2.onPullSubscribeCallback(data);
	        }
	      });
	      if (main_core.Type.isStringFilled(pullTag)) {
	        BX.PULL.extendWatch(pullTag);
	      }
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents() {
	      var _this3 = this;
	      if (main_core.Type.isPlainObject(babelHelpers.classPrivateFieldGet(this, _options).events)) {
	        var _loop = function _loop() {
	          var _Object$entries$_i = babelHelpers.slicedToArray(_Object$entries[_i], 2),
	            eventName = _Object$entries$_i[0],
	            callback = _Object$entries$_i[1];
	          if (main_core.Type.isFunction(callback)) {
	            _this3.eventEmitter.subscribe(eventName, function (event) {
	              return callback(event);
	            });
	          }
	        };
	        for (var _i = 0, _Object$entries = Object.entries(babelHelpers.classPrivateFieldGet(this, _options).events); _i < _Object$entries.length; _i++) {
	          _loop();
	        }
	      }
	      main_core.Event.bind(document, 'visibilitychange', function () {
	        return _this3.onDocumentVisibilityChange();
	      });
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onOpen', function () {
	        var _this$openedSlidersCo, _this$openedSlidersCo2;
	        babelHelpers.classPrivateFieldSet(_this3, _openedSlidersCount, (_this$openedSlidersCo = babelHelpers.classPrivateFieldGet(_this3, _openedSlidersCount), _this$openedSlidersCo2 = _this$openedSlidersCo++, _this$openedSlidersCo)), _this$openedSlidersCo2;
	        babelHelpers.classPrivateFieldGet(_this3, _queue$1).freeze();
	      });
	      main_core_events.EventEmitter.subscribe('SidePanel.Slider:onClose', function () {
	        var _this$openedSlidersCo3, _this$openedSlidersCo4;
	        babelHelpers.classPrivateFieldSet(_this3, _openedSlidersCount, (_this$openedSlidersCo3 = babelHelpers.classPrivateFieldGet(_this3, _openedSlidersCount), _this$openedSlidersCo4 = _this$openedSlidersCo3--, _this$openedSlidersCo3)), _this$openedSlidersCo4;
	        if (babelHelpers.classPrivateFieldGet(_this3, _openedSlidersCount) <= 0) {
	          babelHelpers.classPrivateFieldSet(_this3, _openedSlidersCount, 0);
	          babelHelpers.classPrivateFieldGet(_this3, _queue$1).unfreeze();
	          _this3.onTabActivated();
	        }
	      });
	    }
	  }, {
	    key: "onDocumentVisibilityChange",
	    value: function onDocumentVisibilityChange() {
	      if (!document.hidden) {
	        this.onTabActivated();
	      }
	    }
	  }, {
	    key: "onPullSubscribeCallback",
	    value: function onPullSubscribeCallback(pullData) {
	      var _this4 = this;
	      var _babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _options),
	        pullTag = _babelHelpers$classPr2.pullTag;
	      if (main_core.Type.isStringFilled(pullTag) && pullData.command !== pullTag) {
	        return;
	      }
	      var event = new main_core_events.BaseEvent({
	        data: {
	          pullData: pullData,
	          queueItems: babelHelpers.classPrivateFieldGet(this, _queue$1).getAllAsArray(),
	          options: babelHelpers.classPrivateFieldGet(this, _options),
	          promises: []
	        }
	      });
	      this.eventEmitter.emit('onBeforePull', event);
	      if (event.isDefaultPrevented()) {
	        return;
	      }
	      var params = pullData.params;
	      if (!main_core.Type.isStringFilled(params.eventName)) {
	        return;
	      }
	      if (QueueManager.eventIds.has(params.eventId)) {
	        return;
	      }
	      if (babelHelpers.classPrivateFieldGet(this, _queue$1).isOverflow()) {
	        return;
	      }
	      this.eventEmitter.emit('onPull', event);
	      if (event.isDefaultPrevented()) {
	        return;
	      }
	      void Promise.all(event.data.promises).then(function (values) {
	        if (!main_core.Type.isArrayFilled(values)) {
	          return;
	        }
	        values.forEach(function (item) {
	          var data = item.data;
	          babelHelpers.classPrivateFieldGet(_this4, _queue$1).push("".concat(data.id, "_").concat(params.eventName), data);
	        });
	        babelHelpers.classPrivateFieldGet(_this4, _queue$1).loadItem(false, params.ignoreDelay || false);
	      });
	    }
	  }, {
	    key: "showOutdatedDataDialog",
	    value: function showOutdatedDataDialog() {
	      if (_classPrivateMethodGet$1(this, _hasManyOpenSliders, _hasManyOpenSliders2).call(this)) {
	        return;
	      }
	      var sliderInstance = _classPrivateMethodGet$1(this, _getSliderInstance, _getSliderInstance2).call(this);
	      if (sliderInstance) {
	        main_core_events.EventEmitter.subscribe(sliderInstance, 'SidePanel.Slider:onClose', _classPrivateMethodGet$1(this, _createAndShowNotify, _createAndShowNotify2).bind(this));
	      } else {
	        _classPrivateMethodGet$1(this, _createAndShowNotify, _createAndShowNotify2).call(this);
	      }
	    }
	  }, {
	    key: "onTabActivated",
	    value: function onTabActivated() {
	      if (babelHelpers.classPrivateFieldGet(this, _queue$1).isOverflow()) {
	        this.showOutdatedDataDialog();
	        return;
	      }
	      if (!babelHelpers.classPrivateFieldGet(this, _queue$1).isEmpty()) {
	        babelHelpers.classPrivateFieldGet(this, _queue$1).loadItem();
	      }
	    }
	  }, {
	    key: "hasInQueue",
	    value: function hasInQueue(id) {
	      return babelHelpers.classPrivateFieldGet(this, _queue$1).has(id);
	    }
	  }, {
	    key: "deleteFromQueue",
	    value: function deleteFromQueue(id) {
	      babelHelpers.classPrivateFieldGet(this, _queue$1)["delete"](id);
	    }
	  }, {
	    key: "getLoadItemsDelay",
	    value: function getLoadItemsDelay() {
	      return babelHelpers.classPrivateFieldGet(this, _queue$1).getLoadItemsDelay();
	    }
	  }]);
	  return QueueManager;
	}();
	function _hasManyOpenSliders2() {
	  return top.BX && top.BX.SidePanel && top.BX.SidePanel.Instance.getOpenSlidersCount() > 1;
	}
	function _getSliderInstance2() {
	  if (top.BX && top.BX.SidePanel) {
	    var slider = top.BX.SidePanel.Instance.getTopSlider();
	    if (slider && slider.isOpen()) {
	      return slider;
	    }
	  }
	  return null;
	}
	function _createAndShowNotify2() {
	  var _babelHelpers$classPr3,
	    _this5 = this;
	  var showOutdatedDataDialog = (_babelHelpers$classPr3 = babelHelpers.classPrivateFieldGet(this, _options).config) === null || _babelHelpers$classPr3 === void 0 ? void 0 : _babelHelpers$classPr3.showOutdatedDataDialog;
	  var onReload = babelHelpers.classPrivateFieldGet(this, _options).callbacks.onReload;
	  if (main_core.Type.isBoolean(showOutdatedDataDialog) && showOutdatedDataDialog === false || !main_core.Type.isFunction(onReload)) {
	    return;
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _notifier)) {
	    if (babelHelpers.classPrivateFieldGet(this, _notifier).getState() === BX.UI.Notification.State.OPENING || babelHelpers.classPrivateFieldGet(this, _notifier).getState() === BX.UI.Notification.State.OPEN) {
	      return;
	    }
	    babelHelpers.classPrivateFieldGet(this, _notifier).show();
	    return;
	  }
	  babelHelpers.classPrivateFieldSet(this, _notifier, ui_notification.UI.Notification.Center.notify({
	    content: main_core.Loc.getMessage('PULL_QUEUEMANAGER_NOTIFY_OUTDATED_DATA'),
	    closeButton: false,
	    autoHide: false,
	    actions: [{
	      title: main_core.Loc.getMessage('PULL_QUEUEMANAGER_RELOAD'),
	      events: {
	        click: function click(event, balloon) {
	          balloon.close();
	          onReload();
	          babelHelpers.classPrivateFieldGet(_this5, _queue$1).clear();
	        }
	      }
	    }]
	  }));
	}
	babelHelpers.defineProperty(QueueManager, "eventIds", new Set());

	exports.QueueManager = QueueManager;

}((this.BX.Pull = this.BX.Pull || {}),BX.Event,BX,BX));
//# sourceMappingURL=queuemanager.js.map
