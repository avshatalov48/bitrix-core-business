/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports,ui_vue_vuex,im_const,im_lib_logger,main_core_events) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Base Rest Answer Handler
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var BaseRestHandler = /*#__PURE__*/function () {
	  babelHelpers.createClass(BaseRestHandler, null, [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(params);
	    }
	  }]);
	  function BaseRestHandler() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BaseRestHandler);
	    if (babelHelpers["typeof"](params.controller) === 'object' && params.controller) {
	      this.controller = params.controller;
	    }
	    if (babelHelpers["typeof"](params.store) === 'object' && params.store) {
	      this.store = params.store;
	    }
	  }
	  babelHelpers.createClass(BaseRestHandler, [{
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
	  return BaseRestHandler;
	}();

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var CoreRestHandler = /*#__PURE__*/function (_BaseRestHandler) {
	  babelHelpers.inherits(CoreRestHandler, _BaseRestHandler);
	  function CoreRestHandler() {
	    babelHelpers.classCallCheck(this, CoreRestHandler);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(CoreRestHandler).apply(this, arguments));
	  }
	  babelHelpers.createClass(CoreRestHandler, [{
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
	      this.store.dispatch('files/setBefore', this.controller.application.prepareFilesBeforeSave(data.files));
	      // this.store.dispatch('messages/setBefore', data.messages);
	    }
	  }, {
	    key: "handleImDialogMessagesGetInitSuccess",
	    value: function handleImDialogMessagesGetInitSuccess(data) {
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(data.files));
	      //handling messagesSet for empty chat
	      if (data.messages.length === 0 && data.chat_id) {
	        im_lib_logger.Logger.warn('setting messagesSet for empty chat', data.chat_id);
	        setTimeout(function () {
	          main_core_events.EventEmitter.emit(im_const.EventType.dialog.messagesSet, {
	            chatId: data.chat_id
	          });
	        }, 100);
	      } else {
	        this.store.dispatch('messages/set', data.messages.reverse());
	      }
	    }
	  }, {
	    key: "handleImDialogMessagesGetUnreadSuccess",
	    value: function handleImDialogMessagesGetUnreadSuccess(data) {
	      this.store.dispatch('users/set', data.users);
	      this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(data.files));
	      // this.store.dispatch('messages/setAfter', data.messages);
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
	  }, {
	    key: "handleImRecentListSuccess",
	    value: function handleImRecentListSuccess(result, message) {
	      im_lib_logger.Logger.warn('Provider.Rest.handleImRecentGetSuccess', result);
	      var users = [];
	      var dialogues = [];
	      var recent = [];
	      result.items.forEach(function (item) {
	        var userId = 0;
	        var chatId = 0;
	        if (item.user && item.user.id > 0) {
	          userId = item.user.id;
	          users.push(item.user);
	        }
	        if (item.chat) {
	          chatId = item.chat.id;
	          dialogues.push(Object.assign(item.chat, {
	            dialogId: item.id
	          }));
	        } else {
	          dialogues.push(Object.assign({}, {
	            dialogId: item.id
	          }));
	        }
	        recent.push(_objectSpread(_objectSpread({}, item), {}, {
	          avatar: item.avatar.url,
	          color: item.avatar.color,
	          userId: userId,
	          chatId: chatId
	        }));
	      });
	      this.store.dispatch('users/set', users);
	      this.store.dispatch('dialogues/set', dialogues);
	      this.store.dispatch('recent/set', recent);
	    }
	  }]);
	  return CoreRestHandler;
	}(BaseRestHandler);

	/**
	 * Bitrix Im
	 * Dialog Rest answers (Rest Answer Handler)
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2023 Bitrix
	 */
	var DialogRestHandler = /*#__PURE__*/function (_BaseRestHandler) {
	  babelHelpers.inherits(DialogRestHandler, _BaseRestHandler);
	  function DialogRestHandler(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, DialogRestHandler);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DialogRestHandler).call(this, params));
	    _this.application = params.application;
	    return _this;
	  }
	  babelHelpers.createClass(DialogRestHandler, [{
	    key: "handleImChatGetSuccess",
	    value: function handleImChatGetSuccess(data) {
	      this.store.commit('application/set', {
	        dialog: {
	          chatId: data.id,
	          dialogId: data.dialog_id,
	          diskFolderId: data.disk_folder_id
	        }
	      });
	    }
	  }, {
	    key: "handleImCallGetCallLimitsSuccess",
	    value: function handleImCallGetCallLimitsSuccess(data) {
	      this.store.commit('application/set', {
	        call: {
	          serverEnabled: data.callServerEnabled,
	          maxParticipants: data.maxParticipants
	        }
	      });
	    }
	  }, {
	    key: "handleImChatGetError",
	    value: function handleImChatGetError(error) {
	      if (error.ex.error === 'ACCESS_ERROR') {
	        im_lib_logger.Logger.error('MobileRestAnswerHandler.handleImChatGetError: ACCESS_ERROR');
	        //	app.closeController();
	      }
	    }
	  }, {
	    key: "handleImDialogMessagesGetInitSuccess",
	    value: function handleImDialogMessagesGetInitSuccess(data) {
	      // EventEmitter.emit(EventType.dialog.readVisibleMessages, {chatId: this.controller.application.getChatId()});
	    }
	  }, {
	    key: "handleImMessageAddSuccess",
	    value: function handleImMessageAddSuccess(messageId, message) {
	      console.warn('im.message.add success in dialog handler');
	      // this.application.messagesQueue = this.context.messagesQueue.filter(el => el.id !== message.id);
	    }
	  }, {
	    key: "handleImMessageAddError",
	    value: function handleImMessageAddError(error, message) {
	      // this.application.messagesQueue = this.context.messagesQueue.filter(el => el.id !== message.id);
	    }
	  }, {
	    key: "handleImDiskFileCommitSuccess",
	    value: function handleImDiskFileCommitSuccess(result, message) {
	      // this.application.messagesQueue = this.context.messagesQueue.filter(el => el.id !== message.id);
	    }
	  }]);
	  return DialogRestHandler;
	}(BaseRestHandler);

	/**
	 * Bitrix Messenger
	 * Bundle rest answer handlers
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	exports.BaseRestHandler = BaseRestHandler;
	exports.CoreRestHandler = CoreRestHandler;
	exports.DialogRestHandler = DialogRestHandler;

}((this.BX.Messenger.Provider.Rest = this.BX.Messenger.Provider.Rest || {}),BX,BX.Messenger.Const,BX.Messenger.Lib,BX.Event));
//# sourceMappingURL=registry.bundle.js.map
