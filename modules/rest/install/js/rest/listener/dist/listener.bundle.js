/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core_events) {
	'use strict';

	var _command = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("command");
	var _handlerCommand = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handlerCommand");
	var _listeningState = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("listeningState");
	var _handleCommand = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("handleCommand");
	class Listener extends main_core_events.EventEmitter {
	  constructor(command, handlerCommand) {
	    super();
	    Object.defineProperty(this, _handleCommand, {
	      value: _handleCommand2
	    });
	    Object.defineProperty(this, _command, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _handlerCommand, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _listeningState, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace('BX.Rest.Listener');
	    babelHelpers.classPrivateFieldLooseBase(this, _command)[_command] = command;
	    babelHelpers.classPrivateFieldLooseBase(this, _handlerCommand)[_handlerCommand] = handlerCommand;
	  }
	  listen() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _listeningState)[_listeningState]) {
	      return;
	    }
	    BX.PULL.subscribe({
	      type: BX.PullClient.SubscriptionType.Server,
	      moduleId: 'rest',
	      callback: data => {
	        babelHelpers.classPrivateFieldLooseBase(this, _handleCommand)[_handleCommand](data);
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _listeningState)[_listeningState] = true;
	  }
	}
	function _handleCommand2(data) {
	  if (data.command === babelHelpers.classPrivateFieldLooseBase(this, _command)[_command]) {
	    this.emit('pull');
	    babelHelpers.classPrivateFieldLooseBase(this, _handlerCommand)[_handlerCommand](data);
	  }
	}

	exports.Listener = Listener;

}((this.BX.Rest = this.BX.Rest || {}),BX.Event));
//# sourceMappingURL=listener.bundle.js.map
