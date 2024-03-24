/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _command = /*#__PURE__*/new WeakMap();
	var _handlerCommand = /*#__PURE__*/new WeakMap();
	var _listeningState = /*#__PURE__*/new WeakMap();
	var _handleCommand = /*#__PURE__*/new WeakSet();
	var Listener = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Listener, _EventEmitter);
	  function Listener(command, handlerCommand) {
	    var _this;
	    babelHelpers.classCallCheck(this, Listener);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Listener).call(this));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _handleCommand);
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _command, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _handlerCommand, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _listeningState, {
	      writable: true,
	      value: false
	    });
	    _this.setEventNamespace('BX.Rest.Listener');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _command, command);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _handlerCommand, handlerCommand);
	    return _this;
	  }
	  babelHelpers.createClass(Listener, [{
	    key: "listen",
	    value: function listen() {
	      var _this2 = this;
	      if (babelHelpers.classPrivateFieldGet(this, _listeningState)) {
	        return;
	      }
	      BX.PULL.subscribe({
	        type: BX.PullClient.SubscriptionType.Server,
	        moduleId: 'rest',
	        callback: function callback(data) {
	          _classPrivateMethodGet(_this2, _handleCommand, _handleCommand2).call(_this2, data);
	        }
	      });
	      babelHelpers.classPrivateFieldSet(this, _listeningState, true);
	    }
	  }]);
	  return Listener;
	}(main_core_events.EventEmitter);
	function _handleCommand2(data) {
	  if (data.command === babelHelpers.classPrivateFieldGet(this, _command)) {
	    this.emit('pull');
	    babelHelpers.classPrivateFieldGet(this, _handlerCommand).call(this, data);
	  }
	}

	exports.Listener = Listener;

}((this.BX.Rest = this.BX.Rest || {}),BX.Event));
//# sourceMappingURL=listener.bundle.js.map
