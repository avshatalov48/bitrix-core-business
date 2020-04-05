this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports,pull_client,ui_vue_vuex) {
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
	    key: "handleMessageChat",
	    value: function handleMessageChat(params) {
	      var _this = this;

	      if (params.chat && params.chat[params.chatId]) {
	        this.store.dispatch('dialogues/update', {
	          dialogId: 'chat' + params.chatId,
	          fields: params.chat[params.chatId]
	        });
	      }

	      if (params.users) {
	        this.store.dispatch('users/set', ui_vue_vuex.VuexBuilderModel.convertToArray(params.users));
	      }

	      if (params.files) {
	        this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(ui_vue_vuex.VuexBuilderModel.convertToArray(params.files)));
	      }

	      var collection = this.store.state.messages.collection[params.chatId];

	      if (!collection) {
	        collection = [];
	      }

	      var update = false;

	      if (params.message.tempId && collection.length > 0) {
	        for (var index = collection.length - 1; index >= 0; index--) {
	          if (collection[index].id == params.message.tempId) {
	            update = true;
	            break;
	          }
	        }
	      }

	      if (update) {
	        this.store.dispatch('messages/update', {
	          id: params.message.tempId,
	          chatId: params.message.chatId,
	          fields: params.message
	        });
	      } else if (this.controller.isUnreadMessagesLoaded()) {
	        var unreadCountInCollection = 0;

	        if (collection.length > 0) {
	          collection.forEach(function (element) {
	            return element.unread ? unreadCountInCollection++ : 0;
	          });
	        }

	        if (unreadCountInCollection > 0) {
	          this.store.commit('application/set', {
	            dialog: {
	              messageLimit: this.controller.getRequestMessageLimit() + unreadCountInCollection
	            }
	          });
	        } else if (this.controller.getMessageLimit() != this.controller.getRequestMessageLimit()) {
	          this.store.commit('application/set', {
	            dialog: {
	              messageLimit: this.controller.getRequestMessageLimit()
	            }
	          });
	        }

	        this.store.dispatch('messages/set', babelHelpers.objectSpread({}, params.message, {
	          unread: true
	        }));
	      }

	      this.controller.stopOpponentWriting({
	        dialogId: 'chat' + params.message.chatId,
	        userId: params.message.senderId
	      });

	      if (params.message.senderId == this.controller.getUserId()) {
	        this.store.dispatch('messages/readMessages', {
	          chatId: params.message.chatId
	        }).then(function (result) {
	          _this.store.dispatch('dialogues/update', {
	            dialogId: 'chat' + params.message.chatId,
	            fields: {
	              counter: 0
	            }
	          });
	        });
	      } else {
	        this.store.dispatch('dialogues/increaseCounter', {
	          dialogId: 'chat' + params.message.chatId,
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
	    key: "handleMessageDeleteComplete",
	    value: function handleMessageDeleteComplete(params) {
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
	    key: "handleMessageParamsUpdate",
	    value: function handleMessageParamsUpdate(params) {
	      this.store.dispatch('messages/update', {
	        id: params.id,
	        chatId: params.chatId,
	        fields: {
	          params: params.params
	        }
	      });
	    }
	  }, {
	    key: "handleStartWriting",
	    value: function handleStartWriting(params) {
	      this.controller.startOpponentWriting(params);
	    }
	  }, {
	    key: "handleReadMessageChat",
	    value: function handleReadMessageChat(params) {
	      var _this2 = this;

	      this.store.dispatch('messages/readMessages', {
	        chatId: params.chatId,
	        readId: params.lastId
	      }).then(function (result) {
	        _this2.store.dispatch('dialogues/update', {
	          dialogId: params.dialogId,
	          fields: {
	            counter: params.counter
	          }
	        });
	      });
	    }
	  }, {
	    key: "execMessageUpdateOrDelete",
	    value: function execMessageUpdateOrDelete(params, extra, command) {
	      this.store.dispatch('messages/update', {
	        id: params.id,
	        chatId: params.chatId,
	        fields: {
	          text: command == "messageUpdate" ? params.text : '',
	          textOriginal: command == "messageUpdate" ? params.textOriginal : '',
	          params: params.params,
	          blink: true
	        }
	      });
	      this.controller.stopOpponentWriting({
	        dialogId: params.dialogId,
	        userId: params.senderId
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

}((this.BX.Messenger.Provider.Pull = this.BX.Messenger.Provider.Pull || {}),BX,BX));
//# sourceMappingURL=registry.bundle.js.map
