this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports,ui_vue_vuex,im_lib_logger,pull_client,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Im base pull commands (Pull Command Handler)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var ImBasePullHandler = /*#__PURE__*/function () {
	  babelHelpers.createClass(ImBasePullHandler, null, [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(params);
	    }
	  }]);

	  function ImBasePullHandler() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImBasePullHandler);

	    if (babelHelpers.typeof(params.controller) === 'object' && params.controller) {
	      this.controller = params.controller;
	    }

	    if (babelHelpers.typeof(params.store) === 'object' && params.store) {
	      this.store = params.store;
	    }

	    this.option = babelHelpers.typeof(params.store) === 'object' && params.store ? params.store : {};

	    if (!(babelHelpers.typeof(this.option.handlingDialog) === 'object' && this.option.handlingDialog && this.option.handlingDialog.chatId && this.option.handlingDialog.dialogId)) {
	      this.option.handlingDialog = false;
	    }
	  }

	  babelHelpers.createClass(ImBasePullHandler, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'im';
	    }
	  }, {
	    key: "getSubscriptionType",
	    value: function getSubscriptionType() {
	      return pull_client.PullClient.SubscriptionType.Server;
	    }
	  }, {
	    key: "skipExecute",
	    value: function skipExecute(params) {
	      var extra = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

	      if (!extra.optionImportant) {
	        if (this.option.skip) {
	          im_lib_logger.Logger.info('Pull: command skipped while loading messages', params);
	          return true;
	        }

	        if (!this.option.handlingDialog) {
	          return false;
	        }
	      }

	      if (typeof params.chatId !== 'undefined' || typeof params.dialogId !== 'undefined') {
	        if (typeof params.chatId !== 'undefined' && parseInt(params.chatId) === parseInt(this.option.handlingDialog.chatId)) {
	          return false;
	        }

	        if (typeof params.dialogId !== 'undefined' && params.dialogId.toString() === this.option.handlingDialog.dialogId.toString()) {
	          return false;
	        }

	        return true;
	      }

	      return false;
	    }
	  }, {
	    key: "handleMessage",
	    value: function handleMessage(params, extra) {
	      this.handleMessageAdd(params, extra);
	    }
	  }, {
	    key: "handleMessageChat",
	    value: function handleMessageChat(params, extra) {
	      this.handleMessageAdd(params, extra);
	    }
	  }, {
	    key: "handleMessageAdd",
	    value: function handleMessageAdd(params, extra) {
	      var _this = this;

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      if (params.chat && params.chat[params.chatId]) {
	        this.store.dispatch('dialogues/update', {
	          dialogId: params.dialogId,
	          fields: params.chat[params.chatId]
	        });
	      }

	      this.store.dispatch('recent/update', {
	        id: params.dialogId,
	        fields: {
	          message: {
	            id: params.message.id,
	            text: params.message.text,
	            date: params.message.date
	          },
	          counter: params.counter
	        }
	      });

	      if (params.users) {
	        this.store.dispatch('users/set', ui_vue_vuex.VuexBuilderModel.convertToArray(params.users));
	      }

	      if (params.files) {
	        var files = this.controller.application.prepareFilesBeforeSave(ui_vue_vuex.VuexBuilderModel.convertToArray(params.files));
	        files.forEach(function (file) {
	          if (files.length === 1 && params.message.templateFileId && _this.store.state.files.index[params.chatId] && _this.store.state.files.index[params.chatId][params.message.templateFileId]) {
	            _this.store.dispatch('files/update', {
	              id: params.message.templateFileId,
	              chatId: params.chatId,
	              fields: file
	            }).then(function () {
	              _this.controller.application.emit(im_const.EventType.dialog.scrollToBottom, {
	                cancelIfScrollChange: true
	              });
	            });
	          } else {
	            _this.store.dispatch('files/set', file);
	          }
	        });
	      }

	      var collection = this.store.state.messages.collection[params.chatId];

	      if (!collection) {
	        collection = [];
	      }

	      var update = false;

	      if (params.message.templateId && collection.length > 0) {
	        for (var index = collection.length - 1; index >= 0; index--) {
	          if (collection[index].id === params.message.templateId) {
	            update = true;
	            break;
	          }
	        }
	      }

	      if (update) {
	        this.store.dispatch('messages/update', {
	          id: params.message.templateId,
	          chatId: params.chatId,
	          fields: babelHelpers.objectSpread({
	            push: false
	          }, params.message, {
	            sending: false,
	            error: false
	          })
	        }).then(function () {
	          _this.controller.application.emit(im_const.EventType.dialog.scrollToBottom, {
	            cancelIfScrollChange: params.message.senderId !== _this.controller.application.getUserId()
	          });
	        });
	      } else if (this.controller.application.isUnreadMessagesLoaded()) {
	        if (this.controller.application.getChatId() === params.chatId) {
	          this.store.commit('application/increaseDialogExtraCount');
	        }

	        this.store.dispatch('messages/setAfter', babelHelpers.objectSpread({
	          push: false
	        }, params.message, {
	          unread: true
	        }));
	      }

	      this.controller.application.stopOpponentWriting({
	        dialogId: params.dialogId,
	        userId: params.message.senderId
	      });

	      if (params.message.senderId === this.controller.application.getUserId()) {
	        this.store.dispatch('messages/readMessages', {
	          chatId: params.chatId
	        }).then(function (result) {
	          _this.store.dispatch('dialogues/update', {
	            dialogId: params.dialogId,
	            fields: {
	              counter: 0
	            }
	          });
	        });
	      } else {
	        this.store.dispatch('dialogues/increaseCounter', {
	          dialogId: params.dialogId,
	          count: 1
	        });
	      }
	    }
	  }, {
	    key: "handleMessageUpdate",
	    value: function handleMessageUpdate(params, extra, command) {
	      this.execMessageUpdateOrDelete(params, extra, command);
	    }
	  }, {
	    key: "handleMessageDelete",
	    value: function handleMessageDelete(params, extra, command) {
	      this.execMessageUpdateOrDelete(params, extra, command);
	    }
	  }, {
	    key: "execMessageUpdateOrDelete",
	    value: function execMessageUpdateOrDelete(params, extra, command) {
	      var _this2 = this;

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.controller.application.stopOpponentWriting({
	        dialogId: params.dialogId,
	        userId: params.senderId
	      });
	      this.store.dispatch('messages/update', {
	        id: params.id,
	        chatId: params.chatId,
	        fields: {
	          text: command === "messageUpdate" ? params.text : '',
	          textOriginal: command === "messageUpdate" ? params.textOriginal : '',
	          params: params.params,
	          blink: true
	        }
	      }).then(function () {
	        _this2.controller.application.emit(im_const.EventType.dialog.scrollToBottom, {
	          cancelIfScrollChange: true
	        });
	      });
	      var recentItem = this.store.getters['recent/get'](params.dialogId);

	      if (command === 'messageUpdate' && recentItem.element && recentItem.element.message.id === params.id) {
	        this.store.dispatch('recent/update', {
	          id: params.dialogId,
	          fields: {
	            message: {
	              id: params.id,
	              text: params.text,
	              date: recentItem.element.message.date
	            }
	          }
	        });
	      }

	      if (command === 'messageDelete' && recentItem.element && recentItem.element.message.id === params.id) {
	        this.store.dispatch('recent/update', {
	          id: params.dialogId,
	          fields: {
	            message: {
	              id: params.id,
	              text: 'Message deleted',
	              date: recentItem.element.message.date
	            }
	          }
	        });
	      }
	    }
	  }, {
	    key: "handleMessageDeleteComplete",
	    value: function handleMessageDeleteComplete(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('messages/delete', {
	        id: params.id,
	        chatId: params.chatId
	      });
	      this.controller.application.stopOpponentWriting({
	        dialogId: params.dialogId,
	        userId: params.senderId,
	        action: false
	      });
	    }
	  }, {
	    key: "handleMessageLike",
	    value: function handleMessageLike(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('messages/update', {
	        id: params.id,
	        chatId: params.chatId,
	        fields: {
	          params: {
	            LIKE: params.users
	          }
	        }
	      });
	    }
	  }, {
	    key: "handleChatOwner",
	    value: function handleChatOwner(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          ownerId: params.userId
	        }
	      });
	    }
	  }, {
	    key: "handleMessageParamsUpdate",
	    value: function handleMessageParamsUpdate(params, extra) {
	      var _this3 = this;

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('messages/update', {
	        id: params.id,
	        chatId: params.chatId,
	        fields: {
	          params: params.params
	        }
	      }).then(function () {
	        _this3.controller.application.emit(im_const.EventType.dialog.scrollToBottom, {
	          cancelIfScrollChange: true
	        });
	      });
	    }
	  }, {
	    key: "handleStartWriting",
	    value: function handleStartWriting(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.controller.application.startOpponentWriting(params);
	    }
	  }, {
	    key: "handleReadMessage",
	    value: function handleReadMessage(params, extra) {
	      var _this4 = this;

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('messages/readMessages', {
	        chatId: params.chatId,
	        readId: params.lastId
	      }).then(function (result) {
	        _this4.store.dispatch('dialogues/update', {
	          dialogId: params.dialogId,
	          fields: {
	            counter: params.counter
	          }
	        });
	      });
	      this.store.dispatch('recent/update', {
	        id: params.dialogId,
	        fields: {
	          counter: params.counter
	        }
	      });
	    }
	  }, {
	    key: "handleReadMessageChat",
	    value: function handleReadMessageChat(params, extra) {
	      this.handleReadMessage(params, extra);
	    }
	  }, {
	    key: "handleReadMessageOpponent",
	    value: function handleReadMessageOpponent(params, extra) {
	      this.execReadMessageOpponent(params, extra);
	    }
	  }, {
	    key: "handleReadMessageChatOpponent",
	    value: function handleReadMessageChatOpponent(params, extra) {
	      this.execReadMessageOpponent(params, extra);
	    }
	  }, {
	    key: "execReadMessageOpponent",
	    value: function execReadMessageOpponent(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/updateReaded', {
	        dialogId: params.dialogId,
	        userId: params.userId,
	        userName: params.userName,
	        messageId: params.lastId,
	        date: params.date,
	        action: true
	      });
	    }
	  }, {
	    key: "handleUnreadMessageOpponent",
	    value: function handleUnreadMessageOpponent(params, extra) {
	      this.execUnreadMessageOpponent(params, extra);
	    }
	  }, {
	    key: "handleUnreadMessageChatOpponent",
	    value: function handleUnreadMessageChatOpponent(params, extra) {
	      this.execUnreadMessageOpponent(params, extra);
	    }
	  }, {
	    key: "execUnreadMessageOpponent",
	    value: function execUnreadMessageOpponent(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/updateReaded', {
	        dialogId: params.dialogId,
	        userId: params.userId,
	        action: false
	      });
	    }
	  }, {
	    key: "handleFileUpload",
	    value: function handleFileUpload(params, extra) {
	      var _this5 = this;

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(ui_vue_vuex.VuexBuilderModel.convertToArray({
	        file: params.fileParams
	      }))).then(function () {
	        _this5.controller.application.emit(im_const.EventType.dialog.scrollToBottom, {
	          cancelIfScrollChange: true
	        });
	      });
	    }
	  }, {
	    key: "handleChatPin",
	    value: function handleChatPin(params, extra) {
	      this.store.dispatch('recent/pin', {
	        id: params.dialogId,
	        action: params.active
	      });
	    }
	  }, {
	    key: "handleChatHide",
	    value: function handleChatHide(params, extra) {
	      this.store.dispatch('recent/delete', {
	        id: params.dialogId
	      });
	    }
	  }, {
	    key: "handleReadNotifyList",
	    value: function handleReadNotifyList(params, extra) {
	      this.store.dispatch('recent/update', {
	        id: 'notify',
	        fields: {
	          counter: params.counter
	        }
	      });
	    }
	  }, {
	    key: "handleUserInvite",
	    value: function handleUserInvite(params, extra) {
	      if (!params.invited) {
	        this.store.dispatch('users/update', {
	          id: params.userId,
	          fields: params.user
	        });
	      }
	    }
	  }]);
	  return ImBasePullHandler;
	}();

	/**
	 * Bitrix Messenger
	 * Im call pull commands (Pull Command Handler)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2020 Bitrix
	 */
	var ImCallPullHandler = /*#__PURE__*/function () {
	  babelHelpers.createClass(ImCallPullHandler, null, [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(params);
	    }
	  }]);

	  function ImCallPullHandler() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImCallPullHandler);

	    if (babelHelpers.typeof(params.application) === 'object' && params.application) {
	      this.application = params.application;
	    }

	    if (babelHelpers.typeof(params.controller) === 'object' && params.controller) {
	      this.controller = params.controller;
	    }

	    if (babelHelpers.typeof(params.store) === 'object' && params.store) {
	      this.store = params.store;
	    }

	    this.option = babelHelpers.typeof(params.store) === 'object' && params.store ? params.store : {};
	  }

	  babelHelpers.createClass(ImCallPullHandler, [{
	    key: "getModuleId",
	    value: function getModuleId() {
	      return 'im';
	    }
	  }, {
	    key: "getSubscriptionType",
	    value: function getSubscriptionType() {
	      return pull_client.PullClient.SubscriptionType.Server;
	    }
	  }, {
	    key: "handleChatUserAdd",
	    value: function handleChatUserAdd(params) {
	      var users = Object.values(params.users).map(function (user) {
	        return babelHelpers.objectSpread({}, user, {
	          lastActivityDate: new Date()
	        });
	      });
	      this.store.commit('callApplication/common', {
	        userCount: params.userCount
	      });
	      this.store.commit('users/set', users);
	    }
	  }, {
	    key: "handleChatUserLeave",
	    value: function handleChatUserLeave(params) {
	      if (params.userId === this.controller.getUserId() && params.dialogId === this.store.state.application.dialog.dialogId) {
	        this.application.kickFromCall();
	      }

	      this.store.commit('callApplication/common', {
	        userCount: params.userCount
	      });
	    }
	  }, {
	    key: "handleCallUserNameUpdate",
	    value: function handleCallUserNameUpdate(params) {
	      this.store.dispatch('users/update', {
	        id: params.userId,
	        fields: {
	          name: params.name,
	          lastActivityDate: new Date()
	        }
	      });
	    }
	  }, {
	    key: "handleVideoconfShareUpdate",
	    value: function handleVideoconfShareUpdate(params) {
	      if (params.dialogId === this.store.state.application.dialog.dialogId) {
	        this.application.changeVideoconfUrl(params.newLink);
	      }
	    }
	  }, {
	    key: "handleMessageChat",
	    value: function handleMessageChat(params) {
	      if (params.chatId === this.application.getChatId() && !this.store.state.callApplication.common.showChat && params.message.senderId !== this.controller.getUserId()) {
	        var text = '';

	        if (params.message.senderId === 0 || params.message.system === 'Y') {
	          text = params.message.text;
	        } else {
	          var userName = params.users[params.message.senderId].name;

	          if (params.message.text === '' && Object.keys(params.files).length > 0) {
	            text = "".concat(userName, ": ").concat(this.controller.localize['BX_IM_COMPONENT_CALL_FILE']);
	          } else if (params.message.text !== '') {
	            text = "".concat(userName, ": ").concat(params.message.text);
	          }
	        }

	        this.application.sendNewMessageNotify(text);
	      }
	    }
	  }]);
	  return ImCallPullHandler;
	}();

	/**
	 * Bitrix Messenger
	 * Bundle pull command handlers
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	exports.ImBasePullHandler = ImBasePullHandler;
	exports.ImCallPullHandler = ImCallPullHandler;

}((this.BX.Messenger.Provider.Pull = this.BX.Messenger.Provider.Pull || {}),BX,BX.Messenger.Lib,BX,BX.Messenger.Const));
//# sourceMappingURL=registry.bundle.js.map
