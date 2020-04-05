this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports,im_const,ui_vue_vuex) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Base Rest Answer Handler
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var BaseRestAnswerHandler =
	/*#__PURE__*/
	function () {
	  babelHelpers.createClass(BaseRestAnswerHandler, null, [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(params);
	    }
	  }]);

	  function BaseRestAnswerHandler() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseRestAnswerHandler);

	    if (babelHelpers.typeof(params.controller) === 'object' && params.controller) {
	      this.controller = params.controller;
	    }

	    if (babelHelpers.typeof(params.store) === 'object' && params.store) {
	      this.store = params.store;
	    }
	  }

	  babelHelpers.createClass(BaseRestAnswerHandler, [{
	    key: "execute",
	    value: function execute(command, result) {
	      var extra = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	      command = 'handle' + command.split('.').map(function (element) {
	        return element.charAt(0).toUpperCase() + element.slice(1);
	      }).join('');

	      if (result.error()) {
	        if (typeof this[command + 'Error'] === 'function') {
	          return this[command + 'Error'](result.error(), extra);
	        }
	      } else {
	        if (typeof this[command + 'Success'] === 'function') {
	          return this[command + 'Success'](result.data(), extra);
	        }
	      }

	      return typeof this[command] === 'function' ? this[command](result, extra) : null;
	    }
	  }]);
	  return BaseRestAnswerHandler;
	}();

	/**
	 * Bitrix Messenger
	 * Im rest answers (Rest Answer Handler)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var ImRestAnswerHandler =
	/*#__PURE__*/
	function (_BaseRestAnswerHandle) {
	  babelHelpers.inherits(ImRestAnswerHandler, _BaseRestAnswerHandle);

	  function ImRestAnswerHandler() {
	    babelHelpers.classCallCheck(this, ImRestAnswerHandler);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ImRestAnswerHandler).apply(this, arguments));
	  }

	  babelHelpers.createClass(ImRestAnswerHandler, [{
	    key: "handleImUserListGetSuccess",
	    value: function handleImUserListGetSuccess(data) {
	      this.store.dispatch('users/set', ui_vue_vuex.VuexBuilderModel.convertToArray(data));
	    }
	  }, {
	    key: "handleImUserGetSuccess",
	    value: function handleImUserGetSuccess(data) {
	      this.store.dispatch('users/set', [data]);
	    }
	  }, {
	    key: "handleImChatGetSuccess",
	    value: function handleImChatGetSuccess(data) {
	      this.store.dispatch('dialogues/set', data);
	    }
	  }, {
	    key: "handleImDialogMessagesGetSuccess",
	    value: function handleImDialogMessagesGetSuccess(data) {
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/setBefore', this.controller.prepareFilesBeforeSave(data.files));
	      this.store.dispatch('messages/setBefore', data.messages);
	    }
	  }, {
	    key: "handleImDialogMessagesGetInitSuccess",
	    value: function handleImDialogMessagesGetInitSuccess(data) {
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(data.files));
	      this.store.dispatch('messages/set', data.messages.reverse());
	    }
	  }, {
	    key: "handleImDialogMessagesGetUnreadSuccess",
	    value: function handleImDialogMessagesGetUnreadSuccess(data) {
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(data.files));
	      this.store.dispatch('messages/setAfter', data.messages);
	    }
	  }, {
	    key: "handleImDiskFolderGetSuccess",
	    value: function handleImDiskFolderGetSuccess(data) {
	      this.store.commit('application/set', {
	        dialog: {
	          diskFolderId: data.ID
	        }
	      });
	    }
	  }, {
	    key: "handleImMessageAddSuccess",
	    value: function handleImMessageAddSuccess(messageId, message) {
	      var _this = this;

	      this.store.dispatch('messages/update', {
	        id: message.id,
	        chatId: message.chatId,
	        fields: {
	          id: messageId,
	          sending: false,
	          error: false
	        }
	      }).then(function () {
	        _this.store.dispatch('messages/actionFinish', {
	          id: messageId,
	          chatId: message.chatId
	        });
	      });
	    }
	  }, {
	    key: "handleImMessageAddError",
	    value: function handleImMessageAddError(error, message) {
	      this.store.dispatch('messages/actionError', {
	        id: message.id,
	        chatId: message.chatId
	      });
	    }
	  }, {
	    key: "handleImDiskFileCommitSuccess",
	    value: function handleImDiskFileCommitSuccess(result, message) {
	      var _this2 = this;

	      this.store.dispatch('messages/update', {
	        id: message.id,
	        chatId: message.chatId,
	        fields: {
	          id: result['MESSAGE_ID'],
	          sending: false,
	          error: false
	        }
	      }).then(function () {
	        _this2.store.dispatch('messages/actionFinish', {
	          id: result['MESSAGE_ID'],
	          chatId: message.chatId
	        });
	      });
	    }
	  }, {
	    key: "handleImDiskFileCommitError",
	    value: function handleImDiskFileCommitError(error, message) {
	      this.store.dispatch('files/update', {
	        chatId: message.chatId,
	        id: message.file.id,
	        fields: {
	          status: im_const.FileStatus.error,
	          progress: 0
	        }
	      });
	      this.store.dispatch('messages/actionError', {
	        id: message.id,
	        chatId: message.chatId,
	        retry: false
	      });
	    }
	  }]);
	  return ImRestAnswerHandler;
	}(BaseRestAnswerHandler);

	/**
	 * Bitrix Messenger
	 * Bundle rest answer handlers
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	exports.ImRestAnswerHandler = ImRestAnswerHandler;
	exports.BaseRestAnswerHandler = BaseRestAnswerHandler;

}((this.BX.Messenger.Provider.Pull = this.BX.Messenger.Provider.Pull || {}),BX.Messenger.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
