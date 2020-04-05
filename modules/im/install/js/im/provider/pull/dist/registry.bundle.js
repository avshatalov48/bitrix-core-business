this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports,pull_client,ui_vue_vuex,im_const) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Im pull commands (Pull Command Handler)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var ImPullCommandHandler =
	/*#__PURE__*/
	function () {
	  babelHelpers.createClass(ImPullCommandHandler, null, [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(params);
	    }
	  }]);

	  function ImPullCommandHandler() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImPullCommandHandler);

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

	  babelHelpers.createClass(ImPullCommandHandler, [{
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
	          console.info('Pull: command skipped while loading messages', params);
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

	      if (params.users) {
	        this.store.dispatch('users/set', ui_vue_vuex.VuexBuilderModel.convertToArray(params.users));
	      }

	      if (params.files) {
	        var files = ui_vue_vuex.VuexBuilderModel.convertToArray(params.files);
	        files.forEach(function (file) {
	          file = _this.controller.prepareFilesBeforeSave(file);

	          if (files.length === 1 && params.message.templateFileId && _this.store.state.files.index[params.chatId] && _this.store.state.files.index[params.chatId][params.message.templateFileId]) {
	            _this.store.dispatch('files/update', {
	              id: params.message.templateFileId,
	              chatId: params.chatId,
	              fields: file
	            }).then(function () {
	              _this.controller.emit(im_const.EventType.dialog.scrollToBottom, {
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
	          _this.controller.emit(im_const.EventType.dialog.scrollToBottom, {
	            cancelIfScrollChange: params.message.senderId !== _this.controller.getUserId()
	          });
	        });
	      } else if (this.controller.isUnreadMessagesLoaded()) {
	        if (this.controller.getChatId() === params.chatId) {
	          this.store.commit('application/increaseDialogExtraCount');
	        }

	        this.store.dispatch('messages/setAfter', babelHelpers.objectSpread({
	          push: false
	        }, params.message, {
	          unread: true
	        }));
	      }

	      this.controller.stopOpponentWriting({
	        dialogId: params.dialogId,
	        userId: params.message.senderId
	      });

	      if (params.message.senderId === this.controller.getUserId()) {
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

	      this.controller.stopOpponentWriting({
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
	        _this2.controller.emit(im_const.EventType.dialog.scrollToBottom, {
	          cancelIfScrollChange: true
	        });
	      });
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
	      this.controller.stopOpponentWriting({
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
	        _this3.controller.emit(im_const.EventType.dialog.scrollToBottom, {
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

	      this.controller.startOpponentWriting(params);
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

	      this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(ui_vue_vuex.VuexBuilderModel.convertToArray({
	        file: params.fileParams
	      }))).then(function () {
	        _this5.controller.emit(im_const.EventType.dialog.scrollToBottom, {
	          cancelIfScrollChange: true
	        });
	      });
	    }
	  }]);
	  return ImPullCommandHandler;
	}();

	/**
	 * Bitrix Messenger
	 * Bundle pull command handlers
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	exports.ImPullCommandHandler = ImPullCommandHandler;

}((this.BX.Messenger.Provider.Pull = this.BX.Messenger.Provider.Pull || {}),BX,BX,BX.Messenger.Const));
//# sourceMappingURL=registry.bundle.js.map
