this.BX = this.BX || {};
(function (exports,main_core) {
	'use strict';

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var ENTITY_TYPE = 'mail';
	var instances = {};
	/**
	 * Mail Secretary
	 * @see control-button.js
	 */

	var _messageId = /*#__PURE__*/new WeakMap();

	var _displayErrors = /*#__PURE__*/new WeakSet();

	var Secretary = /*#__PURE__*/function () {
	  function Secretary(messageId) {
	    babelHelpers.classCallCheck(this, Secretary);

	    _classPrivateMethodInitSpec(this, _displayErrors);

	    _classPrivateFieldInitSpec(this, _messageId, {
	      writable: true,
	      value: void 0
	    });

	    babelHelpers.classPrivateFieldSet(this, _messageId, messageId);
	    this.sliderId = "MailSecretary:".concat(ENTITY_TYPE + babelHelpers.classPrivateFieldGet(this, _messageId)).concat(Math.floor(Math.random() * 1000));
	    this.contextBx = window.top.BX || window.BX;
	    this.subscribe();
	  }

	  babelHelpers.createClass(Secretary, [{
	    key: "openChat",
	    value: function openChat() {
	      var _this = this;

	      return BX.ajax.runAction('mail.secretary.createChatFromMessage', {
	        data: {
	          messageId: babelHelpers.classPrivateFieldGet(this, _messageId)
	        }
	      }).then(function (response) {
	        if (top.window.BXIM && response.data) {
	          top.BXIM.openMessenger('chat' + parseInt(response.data));
	        }
	      }, function (response) {
	        _classPrivateMethodGet(_this, _displayErrors, _displayErrors2).call(_this, response.errors);
	      });
	    }
	  }, {
	    key: "openCalendarEvent",
	    value: function openCalendarEvent() {
	      var _this2 = this;

	      return BX.ajax.runAction('mail.secretary.getCalendarEventDataFromMessage', {
	        data: {
	          messageId: babelHelpers.classPrivateFieldGet(this, _messageId)
	        }
	      }).then(function (response) {
	        // let users = [];
	        // if (Type.isArrayLike(response.data.userIds))
	        // {
	        // 	users = response.data.userIds.map((userId) => {
	        // 		return {id: parseInt(userId), entityId: 'user'};
	        // 	});
	        // }
	        new (window.top.BX || window.BX).Calendar.SliderLoader(0, {
	          sliderId: _this2.sliderId,
	          entryName: response.data.name,
	          entryDescription: response.data.desc // participantsEntityList: users,

	        }).show();
	      }, function (response) {
	        _classPrivateMethodGet(_this2, _displayErrors, _displayErrors2).call(_this2, response.errors);
	      });
	    }
	  }, {
	    key: "onCalendarSave",
	    value: function onCalendarSave(event) {
	      if (event instanceof this.contextBx.Event.BaseEvent) {
	        var data = event.getData();

	        if (data.sliderId === this.sliderId) {
	          BX.ajax.runAction('mail.secretary.onCalendarSave', {
	            data: {
	              messageId: babelHelpers.classPrivateFieldGet(this, _messageId),
	              calendarEventId: data.responseData.entryId
	            }
	          });
	        }
	      }
	    }
	  }, {
	    key: "subscribe",
	    value: function subscribe() {
	      this.contextBx.Event.EventEmitter.subscribe('BX.Calendar:onEntrySave', this.onCalendarSave.bind(this));
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.contextBx.Event.EventEmitter.unsubscribe('BX.Calendar:onEntrySave', this.onCalendarSave);
	    }
	  }], [{
	    key: "getInstance",
	    value: function getInstance(messageId) {
	      if (main_core.Type.isUndefined(instances[messageId])) {
	        instances[messageId] = new Secretary(messageId);
	      }

	      return instances[messageId];
	    }
	  }]);
	  return Secretary;
	}();

	function _displayErrors2(errors) {
	  if (main_core.Type.isArray(errors)) {
	    var errorMessages = [];
	    errors.forEach(function (error) {
	      errorMessages.push(error.message);
	    });
	    alert(errorMessages.join("\n"));
	  } else {
	    alert("action can't be performed");
	  }
	}

	exports.Secretary = Secretary;

}((this.BX.Mail = this.BX.Mail || {}),BX));
//# sourceMappingURL=secretary.bundle.js.map
