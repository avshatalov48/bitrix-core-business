this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_tools_timer,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Application controller
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var ApplicationController =
	/*#__PURE__*/
	function () {
	  function ApplicationController() {
	    babelHelpers.classCallCheck(this, ApplicationController);
	    this.store = null;
	    this.restClient = null;
	    this.timer = new im_tools_timer.Timer();

	    this._prepareFilesBeforeSave = function (params) {
	      return params;
	    };

	    this.defaultMessageLimit = 20;
	    this.requestMessageLimit = this.getDefaultMessageLimit();
	    this.messageLastReadId = {};
	    this.messageReadQueue = {};
	  }

	  babelHelpers.createClass(ApplicationController, [{
	    key: "setRestClient",
	    value: function setRestClient(client) {
	      this.restClient = client;
	    }
	  }, {
	    key: "setVuexStore",
	    value: function setVuexStore(store) {
	      this.store = store;
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.store.state.application.common.siteId;
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId() {
	      return this.store.state.application.dialog.chatId;
	    }
	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      return this.store.state.application.common.userId;
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.store.state.application.dialog.dialogId;
	    }
	  }, {
	    key: "getDialogIdByChatId",
	    value: function getDialogIdByChatId(chatId) // TODO error with work user dialog id (not chat)
	    {
	      return 'chat' + chatId;
	    }
	  }, {
	    key: "getDiskFolderId",
	    value: function getDiskFolderId() {
	      return this.store.state.application.dialog.diskFolderId;
	    }
	  }, {
	    key: "getMessageLimit",
	    value: function getMessageLimit() {
	      return this.store.state.application.dialog.messageLimit;
	    }
	  }, {
	    key: "getDefaultMessageLimit",
	    value: function getDefaultMessageLimit() {
	      return this.defaultMessageLimit;
	    }
	  }, {
	    key: "getRequestMessageLimit",
	    value: function getRequestMessageLimit() {
	      return this.requestMessageLimit;
	    }
	  }, {
	    key: "isUnreadMessagesLoaded",
	    value: function isUnreadMessagesLoaded() {
	      var dialog = this.store.state.dialogues.collection[this.getDialogId()];

	      if (!dialog) {
	        return true;
	      }

	      if (dialog.unreadLastId <= 0) {
	        return true;
	      }

	      var collection = this.store.state.messages.collection[this.getChatId()];

	      if (!collection || collection.length <= 0) {
	        return true;
	      }

	      var lastElementId = 0;

	      for (var index = collection.length - 1; index >= 0; index--) {
	        var lastElement = collection[index];

	        if (typeof lastElement.id === "number") {
	          lastElementId = lastElement.id;
	          break;
	        }
	      }

	      return lastElementId >= dialog.unreadLastId;
	    }
	  }, {
	    key: "prepareFilesBeforeSave",
	    value: function prepareFilesBeforeSave(files) {
	      return this._prepareFilesBeforeSave(files);
	    }
	  }, {
	    key: "setPrepareFilesBeforeSaveFunction",
	    value: function setPrepareFilesBeforeSaveFunction(func) {
	      this._prepareFilesBeforeSave = func.bind(this);
	    }
	  }, {
	    key: "startOpponentWriting",
	    value: function startOpponentWriting(params) {
	      var _this = this;

	      var dialogId = params.dialogId,
	          userId = params.userId,
	          userName = params.userName;
	      this.store.dispatch('dialogues/updateWriting', {
	        dialogId: dialogId,
	        userId: userId,
	        userName: userName,
	        action: true
	      });
	      this.timer.start('writingEnd', dialogId + '|' + userId, 35, function (id, params) {
	        var dialogId = params.dialogId,
	            userId = params.userId;

	        _this.store.dispatch('dialogues/updateWriting', {
	          dialogId: dialogId,
	          userId: userId,
	          action: false
	        });
	      }, {
	        dialogId: dialogId,
	        userId: userId
	      });
	      return true;
	    }
	  }, {
	    key: "stopOpponentWriting",
	    value: function stopOpponentWriting() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      var dialogId = params.dialogId,
	          userId = params.userId,
	          userName = params.userName;
	      this.timer.stop('writingStart', dialogId + '|' + userId, true);
	      this.timer.stop('writingEnd', dialogId + '|' + userId);
	      return true;
	    }
	  }, {
	    key: "startWriting",
	    value: function startWriting() {
	      var _this2 = this;

	      if (!this.getChatId() || this.timer.has('writes')) {
	        return false;
	      }

	      this.timer.start('writes', null, 28);
	      this.timer.start('writesSend', null, 5, function (id) {
	        _this2.restClient.callMethod(im_const.RestMethod.imChatSendTyping, {
	          'CHAT_ID': _this2.getChatId()
	        }).catch(function () {
	          _this2.timer.stop('writes', _this2.getChatId());
	        });
	      });
	    }
	  }, {
	    key: "stopWriting",
	    value: function stopWriting() {
	      this.timer.stop('writes');
	      this.timer.stop('writesSend');
	    }
	  }, {
	    key: "setSendingMessageFlag",
	    value: function setSendingMessageFlag(messageId) {
	      this.store.dispatch('messages/actionStart', {
	        id: messageId,
	        chatId: this.getChatId()
	      });
	    }
	  }, {
	    key: "readMessage",
	    value: function readMessage() {
	      var _this3 = this;

	      var messageId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var chatId = this.getChatId();

	      if (typeof this.messageLastReadId[chatId] == 'undefined') {
	        this.messageLastReadId[chatId] = null;
	      }

	      if (typeof this.messageReadQueue[chatId] == 'undefined') {
	        this.messageReadQueue[chatId] = [];
	      }

	      if (messageId) {
	        this.messageReadQueue[chatId].push(parseInt(messageId));
	      }

	      this.timer.start('readMessage', chatId, .1, function (chatId, params) {
	        _this3.messageReadQueue[chatId] = _this3.messageReadQueue[chatId].filter(function (elementId) {
	          if (!_this3.messageLastReadId[chatId]) {
	            _this3.messageLastReadId[chatId] = elementId;
	          } else if (_this3.messageLastReadId[chatId] < elementId) {
	            _this3.messageLastReadId[chatId] = elementId;
	          }

	          return false;
	        });

	        if (_this3.messageLastReadId[chatId] <= 0) {
	          return false;
	        }

	        _this3.store.dispatch('messages/readMessages', {
	          chatId: chatId,
	          readId: _this3.messageLastReadId[chatId]
	        }).then(function (result) {
	          _this3.store.dispatch('dialogues/decreaseCounter', {
	            dialogId: _this3.getDialogIdByChatId(chatId),
	            count: result.count
	          });
	        });

	        _this3.timer.start('readMessageServer', chatId, .5, function (chatId, params) {
	          _this3.restClient.callMethod(im_const.RestMethod.imDialogRead, {
	            'DIALOG_ID': _this3.getDialogIdByChatId(chatId),
	            'MESSAGE_ID': _this3.messageLastReadId[chatId]
	          }); // TODO catch set message to unread status

	        });
	      });
	    }
	  }]);
	  return ApplicationController;
	}();

	exports.ApplicationController = ApplicationController;

}((this.BX.Messenger.Controller = this.BX.Messenger.Controller || {}),BX.Messenger,BX.Messenger.Const));
//# sourceMappingURL=registry.bundle.js.map
