this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_tools_timer,im_const,im_utils) {
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
	    this.templateEngine = null;
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
	    key: "setTemplateEngine",
	    value: function setTemplateEngine(template) {
	      this.templateEngine = template;
	    }
	  }, {
	    key: "setRestClient",
	    value: function setRestClient(client) {
	      this.restClient = client;
	    }
	  }, {
	    key: "setStore",
	    value: function setStore(store) {
	      this.store = store;
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.store.state.application.common.siteId;
	    }
	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      return this.store.state.application.common.userId;
	    }
	  }, {
	    key: "getLanguageId",
	    value: function getLanguageId() {
	      return this.store.state.application.common.languageId;
	    }
	  }, {
	    key: "getCurrentUser",
	    value: function getCurrentUser() {
	      return this.store.getters['users/get'](this.store.state.application.common.userId, true);
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId() {
	      return this.store.state.application.dialog.chatId;
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.store.state.application.dialog.dialogId;
	    }
	  }, {
	    key: "getDialogData",
	    value: function getDialogData() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();

	      if (this.store.state.dialogues.collection[dialogId]) {
	        return this.store.state.dialogues.collection[dialogId];
	      }

	      return this.store.getters['dialogues/getBlank']();
	    }
	  }, {
	    key: "getDialogCrmData",
	    value: function getDialogCrmData() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();
	      var result = {
	        enabled: false,
	        entityType: im_const.DialogCrmType.none,
	        entityId: 0
	      };
	      var dialogData = this.getDialogData(dialogId);

	      if (dialogData.type === im_const.DialogType.call) {
	        if (dialogData.entityData1 && typeof dialogData.entityData1 === 'string') {
	          var _dialogData$entityDat = dialogData.entityData1.split('|'),
	              _dialogData$entityDat2 = babelHelpers.slicedToArray(_dialogData$entityDat, 3),
	              enabled = _dialogData$entityDat2[0],
	              entityType = _dialogData$entityDat2[1],
	              entityId = _dialogData$entityDat2[2];

	          if (enabled) {
	            entityType = entityType ? entityType.toString().toLowerCase() : im_const.DialogCrmType.none;
	            result = {
	              enabled: enabled,
	              entityType: entityType,
	              entityId: entityId
	            };
	          }
	        }
	      } else if (dialogData.type === im_const.DialogType.crm) {
	        var _dialogData$entityId$ = dialogData.entityId.split('|'),
	            _dialogData$entityId$2 = babelHelpers.slicedToArray(_dialogData$entityId$, 2),
	            _entityType = _dialogData$entityId$2[0],
	            _entityId = _dialogData$entityId$2[1];

	        _entityType = _entityType ? _entityType.toString().toLowerCase() : im_const.DialogCrmType.none;
	        result = {
	          enabled: true,
	          entityType: _entityType,
	          entityId: _entityId
	        };
	      }

	      return result;
	    }
	  }, {
	    key: "getDialogIdByChatId",
	    value: function getDialogIdByChatId(chatId) {
	      if (this.getDialogId() === 'chat' + chatId) {
	        return this.getDialogId();
	      }

	      var dialog = this.store.getters['dialogues/getByChatId'](chatId);

	      if (!dialog) {
	        return 0;
	      }

	      return dialog.dialogId;
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
	    key: "emit",
	    value: function emit(eventName) {
	      var params = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      this.templateEngine.$emit(eventName, params);
	      return true;
	    }
	  }, {
	    key: "listen",
	    value: function listen(eventName, callback) {
	      if (typeof callback !== 'function') {
	        return false;
	      }

	      this.templateEngine.$on(eventName, callback);
	      return true;
	    }
	  }, {
	    key: "getReadedList",
	    value: function getReadedList() {
	      var dialog = this.store.state.dialogues.collection[this.getDialogId()];

	      if (!dialog) {
	        return [];
	      }

	      return dialog.readedList;
	    }
	  }, {
	    key: "muteDialog",
	    value: function muteDialog() {
	      var _this = this;

	      var action = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var dialogId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.getDialogId();

	      if (im_utils.Utils.dialog.isEmptyDialogId(dialogId)) {
	        return false;
	      }

	      if (action === null) {
	        action = !this.isDialogMuted();
	      }

	      this.timer.start('muteDialog', dialogId, .3, function (id) {
	        _this.restClient.callMethod(im_const.RestMethod.imChatMute, {
	          'DIALOG_ID': dialogId,
	          'ACTION': action ? 'Y' : 'N'
	        });
	      });
	      var muteList = [];

	      if (action) {
	        muteList = this.getDialogData().muteList;
	        muteList.push(this.getUserId());
	      } else {
	        muteList = this.getDialogData().muteList.filter(function (userId) {
	          return userId !== _this.getUserId();
	        });
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: dialogId,
	        fields: {
	          muteList: muteList
	        }
	      });
	      return true;
	    }
	  }, {
	    key: "isDialogMuted",
	    value: function isDialogMuted() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();
	      return this.getDialogData().muteList.includes(this.getUserId());
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
	    key: "showSmiles",
	    value: function showSmiles() {
	      this.store.dispatch('application/showSmiles');
	    }
	  }, {
	    key: "hideSmiles",
	    value: function hideSmiles() {
	      this.store.dispatch('application/hideSmiles');
	    }
	  }, {
	    key: "startOpponentWriting",
	    value: function startOpponentWriting(params) {
	      var _this2 = this;

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

	        _this2.store.dispatch('dialogues/updateWriting', {
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
	      var _this3 = this;

	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();

	      if (im_utils.Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId)) {
	        return false;
	      }

	      this.timer.start('writes', dialogId, 28);
	      this.timer.start('writesSend', dialogId, 5, function (id) {
	        _this3.restClient.callMethod(im_const.RestMethod.imDialogWriting, {
	          'DIALOG_ID': dialogId
	        }).catch(function () {
	          _this3.timer.stop('writes', dialogId);
	        });
	      });
	    }
	  }, {
	    key: "stopWriting",
	    value: function stopWriting() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();
	      this.timer.stop('writes', dialogId, true);
	      this.timer.stop('writesSend', dialogId, true);
	    }
	  }, {
	    key: "joinParentChat",
	    value: function joinParentChat(messageId, dialogId) {
	      var _this4 = this;

	      return new Promise(function (resolve, reject) {
	        if (!messageId || !dialogId) {
	          return reject();
	        }

	        if (typeof _this4.tempJoinChat === 'undefined') {
	          _this4.tempJoinChat = {};
	        } else if (_this4.tempJoinChat['wait']) {
	          return reject();
	        }

	        _this4.tempJoinChat['wait'] = true;

	        _this4.restClient.callMethod(im_const.RestMethod.imChatParentJoin, {
	          'DIALOG_ID': dialogId,
	          'MESSAGE_ID': messageId
	        }).then(function () {
	          _this4.tempJoinChat['wait'] = false;
	          _this4.tempJoinChat[dialogId] = true;
	          return resolve(dialogId);
	        }).catch(function () {
	          _this4.tempJoinChat['wait'] = false;
	          return reject();
	        });
	      });
	    }
	  }, {
	    key: "setTextareaMessage",
	    value: function setTextareaMessage(params) {
	      var _params$message = params.message,
	          message = _params$message === void 0 ? '' : _params$message,
	          _params$dialogId = params.dialogId,
	          dialogId = _params$dialogId === void 0 ? this.getDialogId() : _params$dialogId;
	      this.store.dispatch('dialogues/update', {
	        dialogId: dialogId,
	        fields: {
	          textareaMessage: message
	        }
	      });
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
	    key: "reactMessage",
	    value: function reactMessage(messageId) {
	      var action = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'auto';
	      this.restClient.callMethod(im_const.RestMethod.imMessageLike, {
	        'MESSAGE_ID': messageId,
	        'ACTION': action === 'auto' ? 'auto' : action === 'set' ? 'plus' : 'minus'
	      });
	    }
	  }, {
	    key: "readMessage",
	    value: function readMessage() {
	      var _this5 = this;

	      var messageId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var skipAjax = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      var chatId = this.getChatId();

	      if (typeof this.messageLastReadId[chatId] === 'undefined') {
	        this.messageLastReadId[chatId] = null;
	      }

	      if (typeof this.messageReadQueue[chatId] === 'undefined') {
	        this.messageReadQueue[chatId] = [];
	      }

	      if (messageId) {
	        this.messageReadQueue[chatId].push(parseInt(messageId));
	      }

	      this.timer.stop('readMessage', chatId, true);
	      this.timer.stop('readMessageServer', chatId, true);

	      if (force) {
	        return this.readMessageExecute(chatId, skipAjax);
	      }

	      return new Promise(function (resolve, reject) {
	        _this5.timer.start('readMessage', chatId, .1, function (chatId, params) {
	          return _this5.readMessageExecute(chatId, skipAjax).then(function (result) {
	            return resolve(result);
	          });
	        });
	      });
	    }
	  }, {
	    key: "readMessageExecute",
	    value: function readMessageExecute(chatId) {
	      var _this6 = this;

	      var skipAjax = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      return new Promise(function (resolve, reject) {
	        if (_this6.messageReadQueue[chatId]) {
	          _this6.messageReadQueue[chatId] = _this6.messageReadQueue[chatId].filter(function (elementId) {
	            if (!_this6.messageLastReadId[chatId]) {
	              _this6.messageLastReadId[chatId] = elementId;
	            } else if (_this6.messageLastReadId[chatId] < elementId) {
	              _this6.messageLastReadId[chatId] = elementId;
	            }
	          });
	        }

	        var dialogId = _this6.getDialogIdByChatId(chatId);

	        var lastId = _this6.messageLastReadId[chatId] || 0;

	        if (lastId <= 0) {
	          resolve({
	            dialogId: dialogId,
	            lastId: 0
	          });
	          return true;
	        }

	        _this6.store.dispatch('messages/readMessages', {
	          chatId: chatId,
	          readId: lastId
	        }).then(function (result) {
	          _this6.store.dispatch('dialogues/decreaseCounter', {
	            dialogId: dialogId,
	            count: result.count
	          });

	          if (_this6.getChatId() === chatId && _this6.store.getters['dialogues/canSaveChat']) {
	            var dialog = _this6.store.getters['dialogues/get'](dialogId);

	            if (dialog.counter <= 0) {
	              _this6.store.commit('application/clearDialogExtraCount');
	            }
	          }

	          if (skipAjax) {
	            resolve({
	              dialogId: dialogId,
	              lastId: lastId
	            });
	          } else {
	            _this6.timer.start('readMessageServer', chatId, .5, function () {
	              _this6.restClient.callMethod(im_const.RestMethod.imDialogRead, {
	                'DIALOG_ID': dialogId,
	                'MESSAGE_ID': lastId
	              }).then(function () {
	                return resolve({
	                  dialogId: dialogId,
	                  lastId: lastId
	                });
	              }).catch(function () {
	                return resolve({
	                  dialogId: dialogId,
	                  lastId: lastId
	                });
	              });
	            });
	          }
	        }).catch(function () {
	          resolve();
	        });
	      });
	    }
	  }, {
	    key: "unreadMessage",
	    value: function unreadMessage() {
	      var _this7 = this;

	      var messageId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var skipAjax = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var chatId = this.getChatId();

	      if (typeof this.messageLastReadId[chatId] === 'undefined') {
	        this.messageLastReadId[chatId] = null;
	      }

	      if (typeof this.messageReadQueue[chatId] === 'undefined') {
	        this.messageReadQueue[chatId] = [];
	      }

	      if (messageId) {
	        this.messageReadQueue[chatId] = this.messageReadQueue[chatId].filter(function (id) {
	          return id < messageId;
	        });
	      }

	      this.timer.stop('readMessage', chatId, true);
	      this.timer.stop('readMessageServer', chatId, true);
	      this.messageLastReadId[chatId] = messageId;
	      this.store.dispatch('messages/unreadMessages', {
	        chatId: chatId,
	        unreadId: this.messageLastReadId[chatId]
	      }).then(function (result) {
	        var dialogId = _this7.getDialogIdByChatId(chatId);

	        _this7.store.dispatch('dialogues/update', {
	          dialogId: dialogId,
	          fields: {
	            unreadId: messageId
	          }
	        });

	        _this7.store.dispatch('dialogues/increaseCounter', {
	          dialogId: dialogId,
	          count: result.count
	        });

	        if (!skipAjax) {
	          _this7.restClient.callMethod(im_const.RestMethod.imDialogUnread, {
	            'DIALOG_ID': dialogId,
	            'MESSAGE_ID': _this7.messageLastReadId[chatId]
	          });
	        }
	      }).catch(function () {});
	    }
	  }, {
	    key: "shareMessage",
	    value: function shareMessage(messageId, type) {
	      this.restClient.callMethod(im_const.RestMethod.imMessageShare, {
	        'DIALOG_ID': this.getDialogId(),
	        'MESSAGE_ID': messageId,
	        'TYPE': type
	      });
	      return true;
	    }
	  }]);
	  return ApplicationController;
	}();

	exports.ApplicationController = ApplicationController;

}((this.BX.Messenger.Controller = this.BX.Messenger.Controller || {}),BX.Messenger,BX.Messenger.Const,BX.Messenger));
//# sourceMappingURL=registry.bundle.js.map
