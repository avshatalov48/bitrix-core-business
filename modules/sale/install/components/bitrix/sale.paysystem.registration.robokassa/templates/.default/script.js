(function (exports,main_core,main_core_events) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	function _classStaticPrivateMethodGet(receiver, classConstructor, method) { _classCheckPrivateStaticAccess(receiver, classConstructor); return method; }

	function _classCheckPrivateStaticAccess(receiver, classConstructor) { if (receiver !== classConstructor) { throw new TypeError("Private static access of wrong provenance"); } }
	var namespace = main_core.Reflection.namespace('BX.Sale.Component');

	var _rkReg = /*#__PURE__*/new WeakMap();

	var _originUrl = /*#__PURE__*/new WeakMap();

	var _url = /*#__PURE__*/new WeakMap();

	var _robokassaWindow = /*#__PURE__*/new WeakMap();

	var _button = /*#__PURE__*/new WeakMap();

	var _siteUrl = /*#__PURE__*/new WeakMap();

	var _resultUrl = /*#__PURE__*/new WeakMap();

	var _successUrl = /*#__PURE__*/new WeakMap();

	var _failUrl = /*#__PURE__*/new WeakMap();

	var _callbackUrl = /*#__PURE__*/new WeakMap();

	var _openForm = /*#__PURE__*/new WeakSet();

	var _sendData = /*#__PURE__*/new WeakSet();

	var RegistrationRobokassa = /*#__PURE__*/function () {
	  function RegistrationRobokassa(options) {
	    babelHelpers.classCallCheck(this, RegistrationRobokassa);

	    _classPrivateMethodInitSpec(this, _sendData);

	    _classPrivateMethodInitSpec(this, _openForm);

	    _classPrivateFieldInitSpec(this, _rkReg, {
	      writable: true,
	      value: true
	    });

	    _classPrivateFieldInitSpec(this, _originUrl, {
	      writable: true,
	      value: 'https://reg.robokassa.ru'
	    });

	    _classPrivateFieldInitSpec(this, _url, {
	      writable: true,
	      value: 'https://reg.robokassa.ru/form_register_merch_bitrix.php'
	    });

	    _classPrivateFieldInitSpec(this, _robokassaWindow, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec(this, _button, {
	      writable: true,
	      value: null
	    });

	    _classPrivateFieldInitSpec(this, _siteUrl, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _resultUrl, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _successUrl, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _failUrl, {
	      writable: true,
	      value: ''
	    });

	    _classPrivateFieldInitSpec(this, _callbackUrl, {
	      writable: true,
	      value: ''
	    });

	    if (main_core.Type.isPlainObject(options)) {
	      babelHelpers.classPrivateFieldSet(this, _siteUrl, options.siteUrl);
	      babelHelpers.classPrivateFieldSet(this, _resultUrl, options.resultUrl);
	      babelHelpers.classPrivateFieldSet(this, _successUrl, options.successUrl);
	      babelHelpers.classPrivateFieldSet(this, _failUrl, options.failUrl);
	      babelHelpers.classPrivateFieldSet(this, _callbackUrl, options.callbackUrl);
	      babelHelpers.classPrivateFieldSet(this, _button, BX(options.buttonId));
	    }
	  }

	  babelHelpers.createClass(RegistrationRobokassa, [{
	    key: "run",
	    value: function run() {
	      var _this = this;

	      _classStaticPrivateMethodGet(RegistrationRobokassa, RegistrationRobokassa, _subscribeToEvent).call(RegistrationRobokassa);

	      main_core.Event.ready(function () {
	        window.addEventListener('message', function (event) {
	          if (event.origin === babelHelpers.classPrivateFieldGet(_this, _originUrl) && event.data.rk_reg_ready === true && babelHelpers.classPrivateFieldGet(_this, _robokassaWindow)) {
	            _classPrivateMethodGet(_this, _sendData, _sendData2).call(_this, babelHelpers.classPrivateFieldGet(_this, _robokassaWindow));
	          }
	        }, false);

	        if (babelHelpers.classPrivateFieldGet(_this, _button)) {
	          babelHelpers.classPrivateFieldGet(_this, _button).onclick = function () {
	            return _classPrivateMethodGet(_this, _openForm, _openForm2).call(_this);
	          };
	        }
	      });
	    }
	  }]);
	  return RegistrationRobokassa;
	}();

	function _openForm2() {
	  babelHelpers.classPrivateFieldSet(this, _robokassaWindow, BX.util.popup(babelHelpers.classPrivateFieldGet(this, _url), 800, 600));
	  babelHelpers.classPrivateFieldGet(this, _robokassaWindow).focus({
	    preventScroll: true
	  });
	}

	function _sendData2(robokassaWindow) {
	  var data = {
	    rk_reg: babelHelpers.classPrivateFieldGet(this, _rkReg),
	    site_url: babelHelpers.classPrivateFieldGet(this, _siteUrl),
	    result_url: babelHelpers.classPrivateFieldGet(this, _resultUrl),
	    success_url: babelHelpers.classPrivateFieldGet(this, _successUrl),
	    fail_url: babelHelpers.classPrivateFieldGet(this, _failUrl),
	    callback_url: babelHelpers.classPrivateFieldGet(this, _callbackUrl)
	  };
	  robokassaWindow.postMessage(data, '*');
	}

	function _subscribeToEvent() {
	  var inCompatMode = {
	    compatMode: true
	  };
	  main_core_events.EventEmitter.subscribe('onPullEvent-sale', function (command, params) {
	    if (command !== 'on_add_paysystem_settings_robokassa') {
	      return;
	    }

	    main_core_events.EventEmitter.emit(window, 'BX.Sale.PaySystem.Registration.Robokassa:onAddSettings', params);
	  }, inCompatMode);
	}

	namespace.RegistrationRobokassa = RegistrationRobokassa;

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
