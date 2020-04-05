this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports) {
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
	    key: "handleImChatGetSuccess",
	    value: function handleImChatGetSuccess(data) {
	      this.store.dispatch('dialogues/set', data);
	    }
	  }, {
	    key: "handleImDialogMessagesGetSuccess",
	    value: function handleImDialogMessagesGetSuccess(data) {
	      this.store.dispatch('messages/setBefore', data.messages);
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/setBefore', this.controller.prepareFilesBeforeSave(data.files));
	    }
	  }, {
	    key: "handleImDialogMessagesUnreadSuccess",
	    value: function handleImDialogMessagesUnreadSuccess(data) {
	      this.store.dispatch('messages/set', data.messages);
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/set', this.controller.prepareFilesBeforeSave(data.files));
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
	      if (typeof messageId === "number") {
	        this.store.dispatch('messages/update', {
	          id: message.id,
	          chatId: this.controller.getChatId(),
	          fields: {
	            id: messageId,
	            sending: false,
	            error: false
	          }
	        });
	        this.store.dispatch('messages/actionFinish', {
	          id: messageId,
	          chatId: this.controller.getChatId()
	        });
	      } else {
	        this.store.dispatch('messages/actionError', {
	          id: message.id,
	          chatId: this.controller.getChatId()
	        });
	      }
	    }
	  }, {
	    key: "handleImMessageAddError",
	    value: function handleImMessageAddError(error, message) {
	      this.store.dispatch('messages/actionError', {
	        id: message.id,
	        chatId: this.controller.getChatId()
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

}((this.BX.Messenger.Provider.Pull = this.BX.Messenger.Provider.Pull || {})));
//# sourceMappingURL=registry.bundle.js.map
