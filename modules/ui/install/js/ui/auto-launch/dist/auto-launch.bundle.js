/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core_collections,main_core_zIndexManager,main_core,main_core_events) {
	'use strict';

	const LaunchPriority = {
	  LOW: 1,
	  NORMAL: 2,
	  HIGH: 3,
	  CRITICAL: 4
	};

	const LaunchState = {
	  IDLE: 'idle',
	  RUNNING: 'running',
	  DONE: 'done'
	};

	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _callback = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("callback");
	var _priority = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("priority");
	var _delay = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("delay");
	var _allowLaunchAfterOthers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("allowLaunchAfterOthers");
	var _forceShowOnTop = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("forceShowOnTop");
	var _state = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("state");
	var _context = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("context");
	class LaunchItem extends main_core_events.EventEmitter {
	  constructor(itemOptions) {
	    super();
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _callback, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _priority, {
	      writable: true,
	      value: LaunchPriority.NORMAL
	    });
	    Object.defineProperty(this, _delay, {
	      writable: true,
	      value: 5000
	    });
	    Object.defineProperty(this, _allowLaunchAfterOthers, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _forceShowOnTop, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _state, {
	      writable: true,
	      value: LaunchState.IDLE
	    });
	    Object.defineProperty(this, _context, {
	      writable: true,
	      value: {}
	    });
	    const options = main_core.Type.isPlainObject(itemOptions) ? itemOptions : {};
	    if (!main_core.Type.isFunction(options.callback)) {
	      throw new TypeError('BX.Launcher: "callback" parameter is required.');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _callback)[_callback] = options.callback;
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = main_core.Type.isStringFilled(options.id) ? options.id : `launch-item-${BX.Text.getRandom().toLowerCase()}`;
	    babelHelpers.classPrivateFieldLooseBase(this, _priority)[_priority] = main_core.Type.isNumber(options.priority) ? options.priority : babelHelpers.classPrivateFieldLooseBase(this, _priority)[_priority];
	    babelHelpers.classPrivateFieldLooseBase(this, _delay)[_delay] = main_core.Type.isNumber(options.delay) && options.delay >= 0 ? options.delay : babelHelpers.classPrivateFieldLooseBase(this, _delay)[_delay];
	    babelHelpers.classPrivateFieldLooseBase(this, _allowLaunchAfterOthers)[_allowLaunchAfterOthers] = options.allowLaunchAfterOthers === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _forceShowOnTop)[_forceShowOnTop] = main_core.Type.isBoolean(options.forceShowOnTop) || main_core.Type.isFunction(options.forceShowOnTop) ? options.forceShowOnTop : babelHelpers.classPrivateFieldLooseBase(this, _forceShowOnTop)[_forceShowOnTop];
	    babelHelpers.classPrivateFieldLooseBase(this, _context)[_context] = main_core.Type.isPlainObject(options.context) ? options.context : {};
	    this.setEventNamespace('BX.Main.Launcher.Item');
	  }
	  launch(done) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] !== LaunchState.IDLE) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = LaunchState.RUNNING;
	    const onDone = () => {
	      babelHelpers.classPrivateFieldLooseBase(this, _state)[_state] = LaunchState.DONE;
	      done();
	    };
	    babelHelpers.classPrivateFieldLooseBase(this, _callback)[_callback](onDone);
	  }
	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }
	  getState() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _state)[_state];
	  }
	  getPriority() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _priority)[_priority];
	  }
	  getDelay() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _delay)[_delay];
	  }
	  getContext() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _context)[_context];
	  }
	  canLaunchAfterOthers() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _allowLaunchAfterOthers)[_allowLaunchAfterOthers];
	  }
	  canShowOnTop() {
	    if (main_core.Type.isFunction(babelHelpers.classPrivateFieldLooseBase(this, _forceShowOnTop)[_forceShowOnTop])) {
	      return babelHelpers.classPrivateFieldLooseBase(this, _forceShowOnTop)[_forceShowOnTop]();
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _forceShowOnTop)[_forceShowOnTop];
	  }
	}

	const LauncherState = {
	  IDLE: 'idle',
	  WAITING_READY: 'waiting_ready',
	  READY: 'ready'
	};
	var _enabled = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("enabled");
	var _queue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("queue");
	var _currentItem = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("currentItem");
	var _state$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("state");
	var _documentReady = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("documentReady");
	var _launchCount = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("launchCount");
	var _launchTimeoutId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("launchTimeoutId");
	var _startDebounced = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startDebounced");
	var _hasOpenPopup = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOpenPopup");
	var _hasOpenSlider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOpenSlider");
	var _hasOverlayDialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOverlayDialog");
	var _hasOpenViewer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hasOpenViewer");
	var _start = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("start");
	var _tryDequeue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("tryDequeue");
	class Launcher {
	  constructor() {
	    Object.defineProperty(this, _tryDequeue, {
	      value: _tryDequeue2
	    });
	    Object.defineProperty(this, _start, {
	      value: _start2
	    });
	    Object.defineProperty(this, _enabled, {
	      writable: true,
	      value: true
	    });
	    Object.defineProperty(this, _queue, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _currentItem, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _state$1, {
	      writable: true,
	      value: LauncherState.IDLE
	    });
	    Object.defineProperty(this, _documentReady, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _launchCount, {
	      writable: true,
	      value: 0
	    });
	    Object.defineProperty(this, _launchTimeoutId, {
	      writable: true,
	      value: null
	    });
	    Object.defineProperty(this, _startDebounced, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue] = new main_core_collections.OrderedArray((itemA, itemB) => {
	      const result = itemB.getPriority() - itemA.getPriority();
	      return result === 0 ? -1 : result;
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _startDebounced)[_startDebounced] = main_core.Runtime.debounce(babelHelpers.classPrivateFieldLooseBase(this, _start)[_start], 1000, this);
	  }
	  static canShowOnTop(context = {}) {
	    return !babelHelpers.classPrivateFieldLooseBase(this, _hasOpenPopup)[_hasOpenPopup]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasOpenSlider)[_hasOpenSlider](context) && !babelHelpers.classPrivateFieldLooseBase(this, _hasOverlayDialog)[_hasOverlayDialog]() && !babelHelpers.classPrivateFieldLooseBase(this, _hasOpenViewer)[_hasOpenViewer]();
	  }
	  register(callback, options = {}) {
	    const launchItem = new LaunchItem({
	      callback,
	      ...options
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].add(launchItem);
	    babelHelpers.classPrivateFieldLooseBase(this, _startDebounced)[_startDebounced]();
	  }
	  unregister(id) {
	    for (const launchItem of babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue]) {
	      if (launchItem.getId() === id) {
	        babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].delete(launchItem);
	        break;
	      }
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem] !== null && babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].getId() === id) {
	      babelHelpers.classPrivateFieldLooseBase(this, _tryDequeue)[_tryDequeue]();
	    }
	  }
	  isEnabled() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _enabled)[_enabled];
	  }
	  enable() {
	    babelHelpers.classPrivateFieldLooseBase(this, _enabled)[_enabled] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _startDebounced)[_startDebounced]();
	  }
	  disable() {
	    babelHelpers.classPrivateFieldLooseBase(this, _enabled)[_enabled] = false;
	    babelHelpers.classPrivateFieldLooseBase(this, _state$1)[_state$1] = LauncherState.IDLE;
	  }
	}
	function _hasOpenPopup2() {
	  const popupManager = main_core.Reflection.getClass('BX.Main.PopupManager');
	  if (popupManager) {
	    const popups = popupManager.getPopups();
	    for (const popup of popups) {
	      if (!popup.isShown()) {
	        continue;
	      }
	      if (popup.getId().startsWith('timeman_weekly_report_popup_') || popup.getId().startsWith('timeman_daily_report_popup_') || BX.Dom.hasClass(popup.getPopupContainer(), 'b24-whatsnew__popup')) {
	        return true;
	      }
	    }
	  }
	  return false;
	}
	function _hasOpenSlider2(context) {
	  const sidePanel = main_core.Reflection.getClass('BX.SidePanel.Instance');
	  if (sidePanel) {
	    var _sidePanel$getTopSlid;
	    const topSlider = sidePanel.getTopSlider();
	    if (topSlider === null || topSlider === context.slider || topSlider.getUrl() === context.sliderId) {
	      return false;
	    }
	    const isIframe = window !== window.top;
	    const isInsideTopSlider = isIframe && ((_sidePanel$getTopSlid = sidePanel.getTopSlider()) == null ? void 0 : _sidePanel$getTopSlid.getWindow()) === window;
	    if (!isInsideTopSlider && sidePanel.getOpenSlidersCount() > 0) {
	      return true;
	    }
	  }
	  return false;
	}
	function _hasOverlayDialog2() {
	  const stack = main_core_zIndexManager.ZIndexManager.getStack(document.body);
	  const components = stack === null ? [] : stack.getComponents();
	  for (const component of components) {
	    if (component.getOverlay() !== null && component.getOverlay().offsetWidth > 0) {
	      return true;
	    }
	  }
	  return false;
	}
	function _hasOpenViewer2() {
	  const viewer = main_core.Reflection.getClass('BX.UI.Viewer.Instance');
	  return viewer !== null && viewer.isOpen();
	}
	function _start2() {
	  if (!this.isEnabled() || babelHelpers.classPrivateFieldLooseBase(this, _state$1)[_state$1] !== LauncherState.IDLE) {
	    return;
	  }
	  const onReady = () => {
	    babelHelpers.classPrivateFieldLooseBase(this, _documentReady)[_documentReady] = true;
	    babelHelpers.classPrivateFieldLooseBase(this, _state$1)[_state$1] = LauncherState.READY;
	    setTimeout(() => {
	      babelHelpers.classPrivateFieldLooseBase(this, _tryDequeue)[_tryDequeue]();
	    }, 1000);
	  };
	  if (babelHelpers.classPrivateFieldLooseBase(this, _documentReady)[_documentReady]) {
	    onReady();
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _state$1)[_state$1] = LauncherState.WAITING_READY;
	    if (main_core.Type.isUndefined(window.frameCacheVars)) {
	      main_core.Event.ready(onReady);
	    } else {
	      var _BX, _BX$frameCache;
	      const compositeReady = ((_BX = BX) == null ? void 0 : (_BX$frameCache = _BX.frameCache) == null ? void 0 : _BX$frameCache.frameDataInserted) === true || !main_core.Type.isUndefined(window.frameRequestFail);
	      if (compositeReady) {
	        onReady();
	      } else {
	        main_core_events.EventEmitter.subscribe('onFrameDataProcessed', onReady);
	        main_core_events.EventEmitter.subscribe('onFrameDataRequestFail', onReady);
	      }
	    }
	  }
	}
	function _tryDequeue2() {
	  clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _launchTimeoutId)[_launchTimeoutId]);
	  babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem] = babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].getFirst();
	  if (babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem] === null) {
	    return;
	  }
	  babelHelpers.classPrivateFieldLooseBase(this, _queue)[_queue].delete(babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem]);
	  if (!babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].canLaunchAfterOthers() && babelHelpers.classPrivateFieldLooseBase(this, _launchCount)[_launchCount] > 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _tryDequeue)[_tryDequeue]();
	  } else if (this.constructor.canShowOnTop(babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].getContext()) || babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].canShowOnTop()) {
	    babelHelpers.classPrivateFieldLooseBase(this, _launchTimeoutId)[_launchTimeoutId] = setTimeout(() => {
	      if (this.constructor.canShowOnTop(babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].getContext()) || babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].canShowOnTop()) {
	        babelHelpers.classPrivateFieldLooseBase(this, _launchCount)[_launchCount]++;
	        babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].launch(() => {
	          babelHelpers.classPrivateFieldLooseBase(this, _tryDequeue)[_tryDequeue]();
	        });
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _tryDequeue)[_tryDequeue]();
	      }
	    }, babelHelpers.classPrivateFieldLooseBase(this, _currentItem)[_currentItem].getDelay());
	  } else {
	    babelHelpers.classPrivateFieldLooseBase(this, _tryDequeue)[_tryDequeue]();
	  }
	}
	Object.defineProperty(Launcher, _hasOpenViewer, {
	  value: _hasOpenViewer2
	});
	Object.defineProperty(Launcher, _hasOverlayDialog, {
	  value: _hasOverlayDialog2
	});
	Object.defineProperty(Launcher, _hasOpenSlider, {
	  value: _hasOpenSlider2
	});
	Object.defineProperty(Launcher, _hasOpenPopup, {
	  value: _hasOpenPopup2
	});

	const AutoLauncher = new Launcher();

	exports.AutoLauncher = AutoLauncher;
	exports.Launcher = Launcher;
	exports.LaunchItem = LaunchItem;
	exports.LaunchPriority = LaunchPriority;

}((this.BX.UI.AutoLaunch = this.BX.UI.AutoLaunch || {}),BX.Collections,BX,BX,BX.Event));
//# sourceMappingURL=auto-launch.bundle.js.map
