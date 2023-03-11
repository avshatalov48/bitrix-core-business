this.BX = this.BX || {};
this.BX.Bizproc = this.BX.Bizproc || {};
(function (exports,main_core,ui_dialogs_messagebox,bizproc_debugger,ui_buttons) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var namespace = main_core.Reflection.namespace('BX.Bizproc.Component');

	var _documentSigned = /*#__PURE__*/new WeakMap();

	var _activeSession = /*#__PURE__*/new WeakMap();

	var _currentUserId = /*#__PURE__*/new WeakMap();

	var _disableButtons = /*#__PURE__*/new WeakSet();

	var _enableButtons = /*#__PURE__*/new WeakSet();

	var _setActiveSessionHint = /*#__PURE__*/new WeakSet();

	var _renderFinishSessionButton = /*#__PURE__*/new WeakSet();

	var _initEvents = /*#__PURE__*/new WeakSet();

	var _onStartSessionClick = /*#__PURE__*/new WeakSet();

	var _onFinishSessionClick = /*#__PURE__*/new WeakSet();

	var _onAfterFinishSession = /*#__PURE__*/new WeakSet();

	var _reject = /*#__PURE__*/new WeakSet();

	var DebuggerStartComponent = /*#__PURE__*/function () {
	  function DebuggerStartComponent(options) {
	    babelHelpers.classCallCheck(this, DebuggerStartComponent);

	    _classPrivateMethodInitSpec(this, _reject);

	    _classPrivateMethodInitSpec(this, _onAfterFinishSession);

	    _classPrivateMethodInitSpec(this, _onFinishSessionClick);

	    _classPrivateMethodInitSpec(this, _onStartSessionClick);

	    _classPrivateMethodInitSpec(this, _initEvents);

	    _classPrivateMethodInitSpec(this, _renderFinishSessionButton);

	    _classPrivateMethodInitSpec(this, _setActiveSessionHint);

	    _classPrivateMethodInitSpec(this, _enableButtons);

	    _classPrivateMethodInitSpec(this, _disableButtons);

	    _classPrivateFieldInitSpec(this, _documentSigned, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _activeSession, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec(this, _currentUserId, {
	      writable: true,
	      value: null
	    });

	    babelHelpers.classPrivateFieldSet(this, _documentSigned, options.documentSigned);
	    babelHelpers.classPrivateFieldSet(this, _activeSession, options.activeSession ? new bizproc_debugger.Session(options.activeSession) : null);
	    babelHelpers.classPrivateFieldSet(this, _currentUserId, main_core.Text.toInteger(options.currentUserId));
	  }

	  babelHelpers.createClass(DebuggerStartComponent, [{
	    key: "init",
	    value: function init() {
	      if (babelHelpers.classPrivateFieldGet(this, _activeSession)) {
	        _classPrivateMethodGet(this, _disableButtons, _disableButtons2).call(this);

	        _classPrivateMethodGet(this, _setActiveSessionHint, _setActiveSessionHint2).call(this);

	        if (babelHelpers.classPrivateFieldGet(this, _currentUserId) === babelHelpers.classPrivateFieldGet(this, _activeSession).startedBy) {
	          _classPrivateMethodGet(this, _renderFinishSessionButton, _renderFinishSessionButton2).call(this);
	        }
	      } else {
	        _classPrivateMethodGet(this, _initEvents, _initEvents2).call(this);
	      }
	    }
	  }, {
	    key: "buttons",
	    get: function get() {
	      var buttons = {};
	      buttons[bizproc_debugger.Mode.experimental.id] = document.getElementById('bizproc-debugger-start-experimental-element');
	      buttons[bizproc_debugger.Mode.interception.id] = document.getElementById('bizproc-debugger-start-interception-element');
	      return buttons;
	    }
	  }]);
	  return DebuggerStartComponent;
	}();

	function _disableButtons2() {
	  var buttons = this.buttons;
	  Object.keys(buttons).forEach(function (key) {
	    main_core.Dom.addClass(buttons[key], 'ui-btn-disabled');
	  });
	}

	function _enableButtons2() {
	  var buttons = this.buttons;
	  Object.keys(buttons).forEach(function (key) {
	    main_core.Dom.removeClass(buttons[key], 'ui-btn-disabled');
	    main_core.Dom.attr(buttons[key], 'disabled', null);
	  });
	}

	function _setActiveSessionHint2() {
	  var _this = this;

	  if (!babelHelpers.classPrivateFieldGet(this, _activeSession)) {
	    return;
	  }

	  var buttons = this.buttons;
	  Object.keys(buttons).forEach(function (key) {
	    main_core.Dom.attr(buttons[key], 'data-hint', main_core.Text.encode(babelHelpers.classPrivateFieldGet(_this, _activeSession).shortDescription));
	    main_core.Dom.attr(buttons[key], 'data-hint-no-icon', 'y');
	    BX.UI.Hint.init(BX(buttons[key].id).parentElement);
	  });
	}

	function _renderFinishSessionButton2() {
	  var buttons = this.buttons;
	  var mode = babelHelpers.classPrivateFieldGet(this, _activeSession).modeId;

	  if (buttons[mode]) {
	    var buttonId = buttons[mode].id;
	    var button = new ui_buttons.Button({
	      props: {
	        id: buttonId
	      },
	      color: ui_buttons.ButtonColor.LIGHT_BORDER,
	      size: ui_buttons.ButtonSize.SMALL,
	      text: main_core.Loc.getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_FINISH'),
	      round: true,
	      onclick: _classPrivateMethodGet(this, _onFinishSessionClick, _onFinishSessionClick2).bind(this)
	    });
	    main_core.Dom.replace(buttons[mode], button.render());
	  }
	}

	function _initEvents2() {
	  var _this2 = this;

	  var buttons = this.buttons;
	  Object.keys(buttons).forEach(function (key) {
	    main_core.Event.bind(buttons[key], 'click', function () {
	      _classPrivateMethodGet(_this2, _onStartSessionClick, _onStartSessionClick2).call(_this2, buttons[key], key);
	    });
	  });
	}

	function _onStartSessionClick2(btn, modeId) {
	  var _this3 = this;

	  top.BX.Runtime.loadExtension('bizproc.debugger').then(function (exports) {
	    _classPrivateMethodGet(_this3, _disableButtons, _disableButtons2).call(_this3);

	    main_core.Dom.addClass(btn, 'ui-btn-wait');
	    var Manager = exports.Manager;
	    Manager.Instance.startSession(babelHelpers.classPrivateFieldGet(_this3, _documentSigned), main_core.Text.toInteger(modeId)).then(function () {
	      _classPrivateMethodGet(_this3, _enableButtons, _enableButtons2).call(_this3);

	      BX.SidePanel.Instance.closeAll();
	      return true;
	    }, function (response) {
	      _classPrivateMethodGet(_this3, _reject, _reject2).call(_this3, response, function () {
	        _classPrivateMethodGet(_this3, _enableButtons, _enableButtons2).call(_this3);

	        main_core.Dom.removeClass(btn, 'ui-btn-wait');
	        return true;
	      });
	    });
	  });
	}

	function _onFinishSessionClick2(button) {
	  var _this4 = this;

	  button.setDisabled(true);
	  button.setWaiting(true);

	  if (top.BX.Bizproc.Debugger) {
	    top.BX.Bizproc.Debugger.Manager.Instance.askFinishSession(babelHelpers.classPrivateFieldGet(this, _activeSession)).then(_classPrivateMethodGet(this, _onAfterFinishSession, _onAfterFinishSession2).bind(this), function (response) {
	      _classPrivateMethodGet(_this4, _reject, _reject2).call(_this4, response, function () {
	        button.setDisabled(false);
	        button.setWaiting(false);
	        return true;
	      });
	    });
	  } else {
	    babelHelpers.classPrivateFieldGet(this, _activeSession).finish().then(_classPrivateMethodGet(this, _onAfterFinishSession, _onAfterFinishSession2).bind(this), function (response) {
	      _classPrivateMethodGet(_this4, _reject, _reject2).call(_this4, response, function () {
	        button.setDisabled(false);
	        button.setWaiting(false);
	        return true;
	      });
	    });
	  }
	}

	function _onAfterFinishSession2() {
	  var buttons = this.buttons;
	  var mode = babelHelpers.classPrivateFieldGet(this, _activeSession).modeId;
	  var buttonId = buttons[mode].id;

	  _classPrivateMethodGet(this, _enableButtons, _enableButtons2).call(this);

	  babelHelpers.classPrivateFieldSet(this, _activeSession, null);
	  main_core.Dom.replace(buttons[mode], new ui_buttons.Button({
	    props: {
	      id: buttonId
	    },
	    color: ui_buttons.ButtonColor.SUCCESS,
	    size: ui_buttons.ButtonSize.SMALL,
	    text: main_core.Loc.getMessage('BIZPROC_DEBUGGER_START_TEMPLATE_START'),
	    round: true
	  }).render());
	  this.init();
	}

	function _reject2(response, callback) {
	  if (main_core.Type.isArrayFilled(response.errors)) {
	    var message = '';
	    response.errors.forEach(function (error) {
	      message = message + '\n' + error.message;
	    });
	    ui_dialogs_messagebox.MessageBox.alert(message, callback);
	  } else if (main_core.Type.isFunction(callback)) {
	    callback();
	  }
	}

	namespace.DebuggerStartComponent = DebuggerStartComponent;

}((this.BX.Bizproc.Component = this.BX.Bizproc.Component || {}),BX,BX.UI.Dialogs,BX.Bizproc.Debugger,BX.UI));
//# sourceMappingURL=script.js.map
