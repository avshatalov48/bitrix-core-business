this.BX = this.BX || {};
(function (exports,pull_client,rest_client,ui_vue_vuex,im_model,im_provider_pull,im_provider_rest,im_lib_timer,im_const,im_lib_utils,ui_vue,im_lib_logger) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Application controller
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var ApplicationController = /*#__PURE__*/function () {
	  function ApplicationController() {
	    babelHelpers.classCallCheck(this, ApplicationController);
	    this.controller = null;
	    this.timer = new im_lib_timer.Timer();
	    this._prepareFilesBeforeSave = function (params) {
	      return params;
	    };
	    this.defaultMessageLimit = 50;
	    this.requestMessageLimit = this.getDefaultMessageLimit();
	    this.messageLastReadId = {};
	    this.messageReadQueue = {};
	  }
	  babelHelpers.createClass(ApplicationController, [{
	    key: "setCoreController",
	    value: function setCoreController(controller) {
	      this.controller = controller;
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.controller.getStore().state.application.common.siteId;
	    }
	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      return this.controller.getStore().state.application.common.userId;
	    }
	  }, {
	    key: "getLanguageId",
	    value: function getLanguageId() {
	      return this.controller.getStore().state.application.common.languageId;
	    }
	  }, {
	    key: "getCurrentUser",
	    value: function getCurrentUser() {
	      return this.controller.getStore().getters['users/get'](this.controller.getStore().state.application.common.userId, true);
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId() {
	      return this.controller.getStore().state.application.dialog.chatId;
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.controller.getStore().state.application.dialog.dialogId;
	    }
	  }, {
	    key: "getData",
	    value: function getData() {
	      return this.controller.getStore().state.application;
	    }
	  }, {
	    key: "getDialogData",
	    value: function getDialogData() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();
	      if (this.controller.getStore().state.dialogues.collection[dialogId]) {
	        return this.controller.getStore().state.dialogues.collection[dialogId];
	      }
	      return this.controller.getStore().getters['dialogues/getBlank']();
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
	      var dialog = this.controller.getStore().getters['dialogues/getByChatId'](chatId);
	      if (!dialog) {
	        return 0;
	      }
	      return dialog.dialogId;
	    }
	  }, {
	    key: "getDiskFolderId",
	    value: function getDiskFolderId() {
	      return this.controller.getStore().state.application.dialog.diskFolderId;
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
	    key: "muteDialog",
	    value: function muteDialog() {
	      var _this = this;
	      var action = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var dialogId = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.getDialogId();
	      if (im_lib_utils.Utils.dialog.isEmptyDialogId(dialogId)) {
	        return false;
	      }
	      if (action === null) {
	        action = !this.isDialogMuted();
	      }
	      this.timer.start('muteDialog', dialogId, .3, function (id) {
	        _this.controller.restClient.callMethod(im_const.RestMethod.imChatMute, {
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
	      this.controller.getStore().dispatch('dialogues/update', {
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
	      var dialog = this.controller.getStore().state.dialogues.collection[this.getDialogId()];
	      if (!dialog) {
	        return true;
	      }
	      if (dialog.lastMessageId <= 0) {
	        return true;
	      }
	      var collection = this.controller.getStore().state.messages.collection[this.getChatId()];
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
	      return lastElementId >= dialog.lastMessageId;
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
	      this.controller.getStore().dispatch('dialogues/updateWriting', {
	        dialogId: dialogId,
	        userId: userId,
	        userName: userName,
	        action: true
	      });
	      this.timer.start('writingEnd', dialogId + '|' + userId, 35, function (id, params) {
	        var dialogId = params.dialogId,
	          userId = params.userId;
	        _this2.controller.getStore().dispatch('dialogues/updateWriting', {
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
	      if (im_lib_utils.Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId)) {
	        return false;
	      }
	      this.timer.start('writes', dialogId, 28);
	      this.timer.start('writesSend', dialogId, 5, function (id) {
	        _this3.controller.restClient.callMethod(im_const.RestMethod.imDialogWriting, {
	          'DIALOG_ID': dialogId
	        })["catch"](function () {
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
	        _this4.controller.restClient.callMethod(im_const.RestMethod.imChatParentJoin, {
	          'DIALOG_ID': dialogId,
	          'MESSAGE_ID': messageId
	        }).then(function () {
	          _this4.tempJoinChat['wait'] = false;
	          _this4.tempJoinChat[dialogId] = true;
	          return resolve(dialogId);
	        })["catch"](function () {
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
	      this.controller.getStore().dispatch('dialogues/update', {
	        dialogId: dialogId,
	        fields: {
	          textareaMessage: message
	        }
	      });
	    }
	  }, {
	    key: "setSendingMessageFlag",
	    value: function setSendingMessageFlag(messageId) {
	      this.controller.getStore().dispatch('messages/actionStart', {
	        id: messageId,
	        chatId: this.getChatId()
	      });
	    }
	  }, {
	    key: "reactMessage",
	    value: function reactMessage(messageId) {
	      var action = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'auto';
	      this.controller.restClient.callMethod(im_const.RestMethod.imMessageLike, {
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
	        _this6.controller.getStore().dispatch('messages/readMessages', {
	          chatId: chatId,
	          readId: lastId
	        }).then(function (result) {
	          _this6.controller.getStore().dispatch('dialogues/decreaseCounter', {
	            dialogId: dialogId,
	            count: result.count
	          });
	          if (_this6.getChatId() === chatId && _this6.controller.getStore().getters['dialogues/canSaveChat']) {
	            var dialog = _this6.controller.getStore().getters['dialogues/get'](dialogId);
	            if (dialog.counter <= 0) {
	              _this6.controller.getStore().commit('application/clearDialogExtraCount');
	            }
	          }
	          if (skipAjax) {
	            resolve({
	              dialogId: dialogId,
	              lastId: lastId
	            });
	          } else {
	            _this6.timer.start('readMessageServer', chatId, .5, function () {
	              _this6.controller.restClient.callMethod(im_const.RestMethod.imDialogRead, {
	                'DIALOG_ID': dialogId,
	                'MESSAGE_ID': lastId
	              }).then(function () {
	                return resolve({
	                  dialogId: dialogId,
	                  lastId: lastId
	                });
	              })["catch"](function () {
	                return resolve({
	                  dialogId: dialogId,
	                  lastId: lastId
	                });
	              });
	            });
	          }
	        })["catch"](function () {
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
	      this.controller.getStore().dispatch('messages/unreadMessages', {
	        chatId: chatId,
	        unreadId: this.messageLastReadId[chatId]
	      }).then(function (result) {
	        var dialogId = _this7.getDialogIdByChatId(chatId);
	        _this7.controller.getStore().dispatch('dialogues/update', {
	          dialogId: dialogId,
	          fields: {
	            unreadId: messageId
	          }
	        });
	        _this7.controller.getStore().dispatch('dialogues/increaseCounter', {
	          dialogId: dialogId,
	          count: result.count
	        });
	        if (!skipAjax) {
	          _this7.controller.restClient.callMethod(im_const.RestMethod.imDialogUnread, {
	            'DIALOG_ID': dialogId,
	            'MESSAGE_ID': _this7.messageLastReadId[chatId]
	          });
	        }
	      })["catch"](function () {});
	    }
	  }, {
	    key: "shareMessage",
	    value: function shareMessage(messageId, type) {
	      this.controller.restClient.callMethod(im_const.RestMethod.imMessageShare, {
	        'DIALOG_ID': this.getDialogId(),
	        'MESSAGE_ID': messageId,
	        'TYPE': type
	      });
	      return true;
	    }
	  }, {
	    key: "replyToUser",
	    value: function replyToUser(userId, user) {
	      return true;
	    }
	  }, {
	    key: "openMessageReactionList",
	    value: function openMessageReactionList(messageId, values) {
	      return true;
	    }
	  }, {
	    key: "emit",
	    value: function emit(eventName) {
	      var _Vue$event;
	      for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        args[_key - 1] = arguments[_key];
	      }
	      (_Vue$event = ui_vue.Vue.event).$emit.apply(_Vue$event, [eventName].concat(args));
	    }
	  }, {
	    key: "listen",
	    value: function listen(event, callback) {
	      ui_vue.Vue.event.$on(event, callback);
	    }
	  }]);
	  return ApplicationController;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var Controller = /*#__PURE__*/function () {
	  /* region 01. Initialize and store data */

	  function Controller() {
	    var _this = this;
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Controller);
	    this.inited = false;
	    this.initPromise = new Promise(function (resolve, reject) {
	      _this.initPromiseResolver = resolve;
	    });
	    this.offline = false;
	    this.restAnswerHandler = [];
	    this.vuexAdditionalModel = [];
	    this.store = null;
	    this.storeBuilder = null;
	    this.init().then(function () {
	      return _this.prepareParams(params);
	    }).then(function () {
	      return _this.initController();
	    }).then(function () {
	      return _this.initLocalStorage();
	    }).then(function () {
	      return _this.initStorage();
	    }).then(function () {
	      return _this.initRestClient();
	    }).then(function () {
	      return _this.initPullClient();
	    }).then(function () {
	      return _this.initEnvironment();
	    }).then(function () {
	      return _this.initComplete();
	    })["catch"](function (error) {
	      im_lib_logger.Logger.error('error initializing core controller', error);
	    });
	  }
	  babelHelpers.createClass(Controller, [{
	    key: "init",
	    value: function init() {
	      return Promise.resolve();
	    }
	  }, {
	    key: "prepareParams",
	    value: function prepareParams(params) {
	      var _this2 = this;
	      if (typeof params.localize !== 'undefined') {
	        this.localize = params.localize;
	      } else {
	        if (typeof BX !== 'undefined') {
	          this.localize = _objectSpread({}, BX.message);
	        } else {
	          this.localize = {};
	        }
	      }
	      if (typeof params.host !== 'undefined') {
	        this.host = params.host;
	      } else {
	        this.host = location.origin;
	      }
	      if (typeof params.userId !== 'undefined') {
	        var parsedUserId = parseInt(params.userId);
	        if (!isNaN(parsedUserId)) {
	          this.userId = parsedUserId;
	        } else {
	          this.userId = 0;
	        }
	      } else {
	        var userId = this.getLocalize('USER_ID');
	        this.userId = userId ? parseInt(userId) : 0;
	      }
	      if (typeof params.siteId !== 'undefined') {
	        if (typeof params.siteId === 'string' && params.siteId !== '') {
	          this.siteId = params.siteId;
	        } else {
	          this.siteId = 's1';
	        }
	      } else {
	        this.siteId = this.getLocalize('SITE_ID') || 's1';
	      }
	      if (typeof params.siteDir !== 'undefined') {
	        if (typeof params.siteDir === 'string' && params.siteDir !== '') {
	          this.siteDir = params.siteDir;
	        } else {
	          this.siteDir = 's1';
	        }
	      } else {
	        this.siteDir = this.getLocalize('SITE_DIR') || 's1';
	      }
	      if (typeof params.languageId !== 'undefined') {
	        if (typeof params.languageId === 'string' && params.languageId !== '') {
	          this.languageId = params.languageId;
	        } else {
	          this.languageId = 'en';
	        }
	      } else {
	        this.languageId = this.getLocalize('LANGUAGE_ID') || 'en';
	      }
	      this.pullInstance = pull_client.PullClient;
	      this.pullClient = pull_client.PULL;
	      if (typeof params.pull !== 'undefined') {
	        if (typeof params.pull.instance !== 'undefined') {
	          this.pullInstance = params.pull.instance;
	        }
	        if (typeof params.pull.client !== 'undefined') {
	          this.pullClient = params.pull.client;
	        }
	      }
	      this.restInstance = rest_client.RestClient;
	      this.restClient = rest_client.rest;
	      if (typeof params.rest !== 'undefined') {
	        if (typeof params.rest.instance !== 'undefined') {
	          this.restInstance = params.rest.instance;
	        }
	        if (typeof params.rest.client !== 'undefined') {
	          this.restClient = params.rest.client;
	        }
	      }
	      this.vuexBuilder = {
	        database: false,
	        databaseName: 'desktop/im',
	        databaseType: ui_vue_vuex.VuexBuilder.DatabaseType.indexedDb
	      };
	      if (typeof params.vuexBuilder !== 'undefined') {
	        if (typeof params.vuexBuilder.database !== 'undefined') {
	          this.vuexBuilder.database = params.vuexBuilder.database;
	        }
	        if (typeof params.vuexBuilder.databaseName !== 'undefined') {
	          this.vuexBuilder.databaseName = params.vuexBuilder.databaseName;
	        }
	        if (typeof params.vuexBuilder.databaseType !== 'undefined') {
	          this.vuexBuilder.databaseType = params.vuexBuilder.databaseType;
	        }
	        if (typeof params.vuexBuilder.models !== 'undefined') {
	          params.vuexBuilder.models.forEach(function (model) {
	            _this2.addVuexModel(model);
	          });
	        }
	      }
	      return Promise.resolve();
	    }
	  }, {
	    key: "initController",
	    value: function initController() {
	      this.application = new ApplicationController();
	      this.application.setCoreController(this);
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initLocalStorage",
	    value: function initLocalStorage() {
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initStorage",
	    value: function initStorage() {
	      var _this3 = this;
	      var applicationVariables = {
	        common: {
	          host: this.getHost(),
	          userId: this.getUserId(),
	          siteId: this.getSiteId(),
	          languageId: this.getLanguageId()
	        },
	        dialog: {
	          messageLimit: this.application.getDefaultMessageLimit(),
	          enableReadMessages: true
	        },
	        device: {
	          type: im_lib_utils.Utils.device.isMobile() ? im_const.DeviceType.mobile : im_const.DeviceType.desktop,
	          orientation: im_lib_utils.Utils.device.getOrientation()
	        }
	      };
	      var builder = new ui_vue_vuex.VuexBuilder().addModel(im_model.ApplicationModel.create().useDatabase(false).setVariables(applicationVariables)).addModel(im_model.MessagesModel.create().useDatabase(this.vuexBuilder.database).setVariables({
	        host: this.getHost()
	      })).addModel(im_model.DialoguesModel.create().useDatabase(this.vuexBuilder.database).setVariables({
	        host: this.getHost()
	      })).addModel(im_model.FilesModel.create().useDatabase(this.vuexBuilder.database).setVariables({
	        host: this.getHost(),
	        "default": {
	          name: 'File is deleted'
	        }
	      })).addModel(im_model.UsersModel.create().useDatabase(this.vuexBuilder.database).setVariables({
	        host: this.getHost(),
	        "default": {
	          name: 'Anonymous'
	        }
	      })).addModel(im_model.RecentModel.create().useDatabase(false).setVariables({
	        host: this.getHost()
	      })).addModel(im_model.NotificationsModel.create().useDatabase(false).setVariables({
	        host: this.getHost()
	      }));
	      this.vuexAdditionalModel.forEach(function (model) {
	        builder.addModel(model);
	      });
	      builder.setDatabaseConfig({
	        name: this.vuexBuilder.databaseName,
	        type: this.vuexBuilder.databaseType,
	        siteId: this.getSiteId(),
	        userId: this.getUserId()
	      });
	      return builder.build().then(function (result) {
	        _this3.store = result.store;
	        _this3.storeBuilder = result.builder;
	        return new Promise(function (resolve, reject) {
	          return resolve();
	        });
	      });
	    }
	  }, {
	    key: "initRestClient",
	    value: function initRestClient(result) {
	      this.addRestAnswerHandler(im_provider_rest.CoreRestHandler.create({
	        store: this.store,
	        controller: this
	      }));
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initPullClient",
	    value: function initPullClient() {
	      if (!this.pullClient) {
	        return false;
	      }
	      this.pullClient.subscribe(this.pullBaseHandler = new im_provider_pull.ImBasePullHandler({
	        store: this.store,
	        controller: this
	      }));
	      this.pullClient.subscribe({
	        type: this.pullInstance.SubscriptionType.Status,
	        callback: this.eventStatusInteraction.bind(this)
	      });
	      this.pullClient.subscribe({
	        type: this.pullInstance.SubscriptionType.Online,
	        callback: this.eventOnlineInteraction.bind(this)
	      });
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initEnvironment",
	    value: function initEnvironment(result) {
	      var _this4 = this;
	      window.addEventListener('orientationchange', function () {
	        if (!_this4.store) {
	          return;
	        }
	        _this4.store.commit('application/set', {
	          device: {
	            orientation: im_lib_utils.Utils.device.getOrientation()
	          }
	        });
	        if (_this4.store.state.application.device.type === im_const.DeviceType.mobile && _this4.store.state.application.device.orientation === im_const.DeviceOrientation.horizontal) {
	          document.activeElement.blur();
	        }
	      });
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    }
	  }, {
	    key: "initComplete",
	    value: function initComplete() {
	      this.inited = true;
	      this.initPromiseResolver(this);
	    }
	    /* endregion 01. Initialize and store data */
	    /* region 02. Push & Pull */
	  }, {
	    key: "eventStatusInteraction",
	    value: function eventStatusInteraction(data) {
	      if (data.status === this.pullInstance.PullStatus.Online) {
	        this.offline = false;

	        //this.pullBaseHandler.option.skip = true;
	        // this.getDialogUnread().then(() => {
	        // 	this.pullBaseHandler.option.skip = false;
	        // 	this.processSendMessages();
	        // 	this.emit(EventType.dialog.sendReadMessages);
	        // }).catch(() => {
	        // 	this.pullBaseHandler.option.skip = false;
	        // 	this.processSendMessages();
	        // });
	      } else if (data.status === this.pullInstance.PullStatus.Offline) {
	        this.offline = true;
	      }
	    }
	  }, {
	    key: "eventOnlineInteraction",
	    value: function eventOnlineInteraction(data) {
	      if (data.command === 'list' || data.command === 'userStatus') {
	        for (var userId in data.params.users) {
	          if (!data.params.users.hasOwnProperty(userId)) {
	            continue;
	          }
	          this.store.dispatch('users/update', {
	            id: data.params.users[userId].id,
	            fields: data.params.users[userId]
	          });
	        }
	      }
	    }
	    /* endregion 02. Push & Pull */
	    /* region 03. Rest */
	  }, {
	    key: "executeRestAnswer",
	    value: function executeRestAnswer(command, result, extra) {
	      im_lib_logger.Logger.warn('Core.controller.executeRestAnswer', command, result, extra);
	      this.restAnswerHandler.forEach(function (handler) {
	        handler.execute(command, result, extra);
	      });
	    }
	    /* endregion 03. Rest */
	    /* region 04. Template engine */
	  }, {
	    key: "createVue",
	    value: function createVue(application) {
	      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      var controller = this;
	      var beforeCreateFunction = function beforeCreateFunction() {};
	      if (config.beforeCreate) {
	        beforeCreateFunction = config.beforeCreate;
	      }
	      var destroyedFunction = function destroyedFunction() {};
	      if (config.destroyed) {
	        destroyedFunction = config.destroyed;
	      }
	      var createdFunction = function createdFunction() {};
	      if (config.created) {
	        createdFunction = config.created;
	      }
	      var initConfig = {
	        store: this.store,
	        beforeCreate: function beforeCreate() {
	          this.$bitrix.Data.set('controller', controller);
	          this.$bitrix.Application.set(application);
	          this.$bitrix.Loc.setMessage(controller.localize);
	          if (controller.restClient) {
	            this.$bitrix.RestClient.set(controller.restClient);
	          }
	          if (controller.pullClient) {
	            this.$bitrix.PullClient.set(controller.pullClient);
	          }
	          beforeCreateFunction.bind(this)();
	        },
	        created: function created() {
	          createdFunction.bind(this)();
	        },
	        destroyed: function destroyed() {
	          destroyedFunction.bind(this)();
	        }
	      };
	      if (config.el) {
	        initConfig.el = config.el;
	      }
	      if (config.template) {
	        initConfig.template = config.template;
	      }
	      if (config.computed) {
	        initConfig.computed = config.computed;
	      }
	      if (config.data) {
	        initConfig.data = config.data;
	      }
	      var initConfigCreatedFunction = initConfig.created;
	      return new Promise(function (resolve, reject) {
	        initConfig.created = function () {
	          initConfigCreatedFunction.bind(this)();
	          resolve(this);
	        };
	        ui_vue.BitrixVue.createApp(initConfig);
	      });
	    }
	    /* endregion 04. Template engine */
	    /* region 05. Core methods */
	  }, {
	    key: "getHost",
	    value: function getHost() {
	      return this.host;
	    }
	  }, {
	    key: "setHost",
	    value: function setHost(host) {
	      this.host = host;
	      this.store.commit('application/set', {
	        common: {
	          host: host
	        }
	      });
	    }
	  }, {
	    key: "getUserId",
	    value: function getUserId() {
	      return this.userId;
	    }
	  }, {
	    key: "setUserId",
	    value: function setUserId(userId) {
	      var parsedUserId = parseInt(userId);
	      if (!isNaN(parsedUserId)) {
	        this.userId = parsedUserId;
	      } else {
	        this.userId = 0;
	      }
	      this.store.commit('application/set', {
	        common: {
	          userId: userId
	        }
	      });
	    }
	  }, {
	    key: "getSiteId",
	    value: function getSiteId() {
	      return this.siteId;
	    }
	  }, {
	    key: "setSiteId",
	    value: function setSiteId(siteId) {
	      if (typeof siteId === 'string' && siteId !== '') {
	        this.siteId = siteId;
	      } else {
	        this.siteId = 's1';
	      }
	      this.store.commit('application/set', {
	        common: {
	          siteId: this.siteId
	        }
	      });
	    }
	  }, {
	    key: "getLanguageId",
	    value: function getLanguageId() {
	      return this.languageId;
	    }
	  }, {
	    key: "setLanguageId",
	    value: function setLanguageId(languageId) {
	      if (typeof languageId === 'string' && languageId !== '') {
	        this.languageId = languageId;
	      } else {
	        this.languageId = 'en';
	      }
	      this.store.commit('application/set', {
	        common: {
	          languageId: this.languageId
	        }
	      });
	    }
	  }, {
	    key: "getStore",
	    value: function getStore() {
	      return this.store;
	    }
	  }, {
	    key: "getStoreBuilder",
	    value: function getStoreBuilder() {
	      return this.storeBuilder;
	    }
	  }, {
	    key: "addRestAnswerHandler",
	    value: function addRestAnswerHandler(handler) {
	      this.restAnswerHandler.push(handler);
	    }
	  }, {
	    key: "addVuexModel",
	    value: function addVuexModel(model) {
	      this.vuexAdditionalModel.push(model);
	    }
	  }, {
	    key: "isOnline",
	    value: function isOnline() {
	      return !this.offline;
	    }
	  }, {
	    key: "ready",
	    value: function ready() {
	      if (this.inited) {
	        return Promise.resolve(this);
	      }
	      return this.initPromise;
	    }
	    /* endregion 05. Methods */
	    /* region 06. Interaction and utils */
	  }, {
	    key: "setError",
	    value: function setError() {
	      var code = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var description = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
	      im_lib_logger.Logger.error("Messenger.Application.error: ".concat(code, " (").concat(description, ")"));
	      var localizeDescription = '';
	      if (code.endsWith('LOCALIZED')) {
	        localizeDescription = description;
	      }
	      this.store.commit('application/set', {
	        error: {
	          active: true,
	          code: code,
	          description: localizeDescription
	        }
	      });
	    }
	  }, {
	    key: "clearError",
	    value: function clearError() {
	      this.store.commit('application/set', {
	        error: {
	          active: false,
	          code: '',
	          description: ''
	        }
	      });
	    }
	  }, {
	    key: "addLocalize",
	    value: function addLocalize(phrases) {
	      if (babelHelpers["typeof"](phrases) !== "object" || !phrases) {
	        return false;
	      }
	      for (var name in phrases) {
	        if (phrases.hasOwnProperty(name)) {
	          this.localize[name] = phrases[name];
	        }
	      }
	      return true;
	    }
	  }, {
	    key: "getLocalize",
	    value: function getLocalize(name) {
	      var phrase = '';
	      if (typeof name === 'undefined') {
	        return this.localize;
	      } else if (typeof this.localize[name.toString()] === 'undefined') {
	        im_lib_logger.Logger.warn("Controller.Core.getLocalize: message with code '".concat(name.toString(), "' is undefined."));
	        //Logger.trace();
	      } else {
	        phrase = this.localize[name];
	      }
	      return phrase;
	    } /* endregion 06. Interaction and utils */
	  }]);
	  return Controller;
	}();

	exports.Controller = Controller;

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX,BX.Messenger.Model,BX.Messenger.Provider.Pull,BX.Messenger.Provider.Rest,BX.Messenger.Lib,BX.Messenger.Const,BX.Messenger.Lib,BX,BX.Messenger.Lib));
//# sourceMappingURL=controller.bundle.js.map
