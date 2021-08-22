this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Provider = this.BX.Messenger.Provider || {};
(function (exports,ui_vue_vuex,im_lib_logger,main_core_events,im_const,pull_client) {
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

	      im_lib_logger.Logger.warn('handleMessageAdd', params);

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      var collection = this.store.state.messages.collection[params.chatId];

	      if (!collection) {
	        collection = [];
	      } //search for message with message id from params


	      var message = collection.find(function (element) {
	        if (params.message.templateId && element.id === params.message.templateId) {
	          return true;
	        }

	        return element.id === params.message.id;
	      }); //stop if it's message with 'push' (pseudo push message in mobile)

	      if (message && params.message.push) {
	        return false;
	      }

	      if (params.chat && params.chat[params.chatId]) {
	        var existingChat = this.store.getters['dialogues/getByChatId'](params.chatId); //add new chat if there is no one

	        if (!existingChat) {
	          var chatToAdd = Object.assign({}, params.chat[params.chatId], {
	            dialogId: params.dialogId
	          });
	          this.store.dispatch('dialogues/set', chatToAdd);
	        } //otherwise - update it
	        else {
	            this.store.dispatch('dialogues/update', {
	              dialogId: params.dialogId,
	              fields: params.chat[params.chatId]
	            });
	          }
	      }

	      var recentItem = this.store.getters['recent/get'](params.dialogId); //add recent item if there is no one

	      if (!recentItem) {
	        var newRecentItem = this.prepareRecentItem(params);
	        this.store.dispatch('recent/set', [newRecentItem]);
	      } //otherwise - update it
	      else {
	          this.store.dispatch('recent/update', {
	            id: params.dialogId,
	            fields: {
	              lines: params.lines || {
	                id: 0
	              },
	              message: {
	                id: params.message.id,
	                text: params.message.text,
	                date: params.message.date,
	                senderId: params.message.senderId
	              },
	              counter: params.counter
	            }
	          });
	        } //set users


	      if (params.users) {
	        this.store.dispatch('users/set', ui_vue_vuex.VuexBuilderModel.convertToArray(params.users));
	      } //set files


	      if (params.files) {
	        var files = this.controller.application.prepareFilesBeforeSave(ui_vue_vuex.VuexBuilderModel.convertToArray(params.files));
	        files.forEach(function (file) {
	          if (files.length === 1 && params.message.templateFileId && _this.store.state.files.index[params.chatId] && _this.store.state.files.index[params.chatId][params.message.templateFileId]) {
	            _this.store.dispatch('files/update', {
	              id: params.message.templateFileId,
	              chatId: params.chatId,
	              fields: file
	            }).then(function () {
	              main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	                chatId: params.chatId,
	                cancelIfScrollChange: true
	              });
	            });
	          } else {
	            _this.store.dispatch('files/set', file);
	          }
	        });
	      } //if we already have message - update it and scrollToBottom


	      if (message) {
	        im_lib_logger.Logger.warn('New message pull handler: we already have this message', params.message);
	        this.store.dispatch('messages/update', {
	          id: message.id,
	          chatId: message.chatId,
	          fields: babelHelpers.objectSpread({}, params.message, {
	            sending: false,
	            error: false
	          })
	        }).then(function () {
	          if (!params.message.push) {
	            main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	              chatId: message.chatId,
	              cancelIfScrollChange: params.message.senderId !== _this.controller.application.getUserId()
	            });
	          }
	        });
	      } //if we dont have message and we have all pages - add new message and send newMessage event (handles scroll stuff)
	      //we dont do anything if we dont have message and there are unloaded messages
	      else if (this.controller.application.isUnreadMessagesLoaded()) {
	          im_lib_logger.Logger.warn('New message pull handler: we dont have this message', params.message);
	          this.store.dispatch('messages/setAfter', babelHelpers.objectSpread({}, params.message, {
	            unread: true
	          })).then(function () {
	            if (!params.message.push) {
	              main_core_events.EventEmitter.emit(im_const.EventType.dialog.newMessage, {
	                chatId: params.message.chatId,
	                messageId: params.message.id
	              });
	            }
	          });
	        } //stop writing event


	      this.controller.application.stopOpponentWriting({
	        dialogId: params.dialogId,
	        userId: params.message.senderId
	      }); //if we sent message and there are no unloaded unread pages - read all messages on server and client, set counter to 0
	      //TODO: to think about it during new chat development

	      if (params.message.senderId === this.controller.application.getUserId() && this.controller.application.isUnreadMessagesLoaded()) {
	        if (this.store.state.dialogues.collection[params.dialogId] && this.store.state.dialogues.collection[params.dialogId].counter !== 0) {
	          this.controller.restClient.callMethod('im.dialog.read', {
	            dialog_id: params.dialogId
	          }).then(function () {
	            _this.store.dispatch('messages/readMessages', {
	              chatId: params.chatId
	            }).then(function (result) {
	              main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	                chatId: params.chatId,
	                cancelIfScrollChange: false
	              });

	              _this.store.dispatch('dialogues/update', {
	                dialogId: params.dialogId,
	                fields: {
	                  counter: 0
	                }
	              });
	            });
	          });
	        }
	      } //increase the counter if message is not ours
	      else if (params.message.senderId !== this.controller.application.getUserId()) {
	          this.store.dispatch('dialogues/increaseCounter', {
	            dialogId: params.dialogId,
	            count: 1
	          });
	        } //set new lastMessageId (used for pagination)


	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          lastMessageId: params.message.id
	        }
	      }); //increase total message count

	      this.store.dispatch('dialogues/increaseMessageCounter', {
	        dialogId: params.dialogId,
	        count: 1
	      });
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
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	          chatId: params.chatId,
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
	    key: "handleChatManagers",
	    value: function handleChatManagers(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          managerList: params.list
	        }
	      });
	    }
	  }, {
	    key: "handleChatUpdateParams",
	    value: function handleChatUpdateParams(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: params.params
	      });
	    }
	  }, {
	    key: "handleChatUserAdd",
	    value: function handleChatUserAdd(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          userCounter: params.userCount
	        }
	      });
	    }
	  }, {
	    key: "handleChatUserLeave",
	    value: function handleChatUserLeave(params, extra) {
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          userCounter: params.userCount
	        }
	      });
	    }
	  }, {
	    key: "handleMessageParamsUpdate",
	    value: function handleMessageParamsUpdate(params, extra) {
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
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	          chatId: params.chatId,
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
	      var _this2 = this;

	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

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
	      var recentItem = this.store.getters['recent/get'](params.dialogId);

	      if (recentItem) {
	        var message = recentItem.element.message;
	        this.store.dispatch('recent/update', {
	          id: params.dialogId,
	          fields: {
	            counter: params.counter,
	            message: babelHelpers.objectSpread({}, message, {
	              status: 'delivered'
	            })
	          }
	        });
	      }
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
	      if (this.skipExecute(params, extra)) {
	        return false;
	      }

	      this.store.dispatch('files/set', this.controller.application.prepareFilesBeforeSave(ui_vue_vuex.VuexBuilderModel.convertToArray({
	        file: params.fileParams
	      }))).then(function () {
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
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
	    key: "handleChatMuteNotify",
	    value: function handleChatMuteNotify(params, extra) {
	      var existingChat = this.store.getters['dialogues/get'](params.dialogId);

	      if (!existingChat) {
	        return false;
	      }

	      var existingMuteList = existingChat.muteList;
	      var newMuteList = [];
	      var currentUser = this.store.state.application.common.userId;

	      if (params.mute) {
	        newMuteList = [].concat(babelHelpers.toConsumableArray(existingMuteList), [currentUser]);
	      } else {
	        newMuteList = existingMuteList.filter(function (element) {
	          return element !== currentUser;
	        });
	      }

	      this.store.dispatch('dialogues/update', {
	        dialogId: params.dialogId,
	        fields: {
	          muteList: newMuteList
	        }
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
	  }, {
	    key: "prepareRecentItem",
	    value: function prepareRecentItem(params) {
	      var type = 'user';

	      if (params.dialogId.toString().startsWith('chat')) {
	        type = 'chat';
	      }

	      params.dialogId.toString().startsWith('chat');
	      var title = type === 'chat' ? params.chat[params.chatId].name : params.users[params.dialogId].name;
	      var chat = params.chat[params.chatId] ? params.chat[params.chatId] : {};

	      if (!params.users) {
	        params.users = {};
	      }

	      var user = params.users[params.dialogId] ? params.users[params.dialogId] : {};
	      var userId = type === 'user' ? params.dialogId : 0;
	      return {
	        id: params.dialogId,
	        type: type,
	        title: title,
	        counter: params.counter,
	        chatId: params.chatId,
	        chat: chat,
	        user: user,
	        userId: userId,
	        message: {
	          id: params.message.id,
	          text: params.message.text,
	          date: params.message.date,
	          senderId: params.message.senderId
	        }
	      };
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
	      this.store.commit('conference/common', {
	        userCount: params.userCount
	      });
	      this.store.dispatch('users/set', users);
	      this.store.dispatch('conference/setUsers', {
	        users: users.map(function (user) {
	          return user.id;
	        })
	      });
	    }
	  }, {
	    key: "handleChatUserLeave",
	    value: function handleChatUserLeave(params) {
	      if (params.userId === this.controller.getUserId() && params.dialogId === this.store.state.application.dialog.dialogId) {
	        this.application.kickFromCall();
	      }

	      this.store.commit('conference/common', {
	        userCount: params.userCount
	      });
	      this.store.dispatch('conference/removeUsers', {
	        users: [params.userId]
	      });
	    }
	  }, {
	    key: "handleCallUserNameUpdate",
	    value: function handleCallUserNameUpdate(params) {
	      var currentUser = this.store.getters['users/get'](params.userId);

	      if (!currentUser) {
	        this.store.dispatch('users/set', {
	          id: params.userId,
	          lastActivityDate: new Date()
	        });
	      }

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
	        this.store.dispatch('dialogues/update', {
	          dialogId: params.dialogId,
	          fields: {
	            public: {
	              code: params.newCode,
	              link: params.newLink
	            }
	          }
	        });
	        this.application.changeVideoconfUrl(params.newLink);
	      }
	    }
	  }, {
	    key: "handleMessageChat",
	    value: function handleMessageChat(params) {
	      var rightPanelMode = this.store.state.conference.common.rightPanelMode;

	      if (params.chatId === this.application.getChatId() && rightPanelMode !== im_const.ConferenceRightPanelMode.chat && rightPanelMode !== im_const.ConferenceRightPanelMode.split && params.message.senderId !== this.controller.getUserId() && !this.store.state.conference.common.error) {
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
	  }, {
	    key: "handleChatRename",
	    value: function handleChatRename(params) {
	      if (params.chatId !== this.application.getChatId()) {
	        return false;
	      }

	      this.store.dispatch('conference/setConferenceTitle', {
	        conferenceTitle: params.name
	      });
	    }
	  }, {
	    key: "handleConferenceUpdate",
	    value: function handleConferenceUpdate(params) {
	      if (params.chatId !== this.application.getChatId()) {
	        return false;
	      }

	      if (params.isBroadcast !== '') {
	        this.store.dispatch('conference/setBroadcastMode', {
	          broadcastMode: params.isBroadcast
	        });
	      }

	      if (params.presenters.length > 0) {
	        this.store.dispatch('conference/setPresenters', {
	          presenters: params.presenters,
	          replace: true
	        });
	      }
	    }
	  }]);
	  return ImCallPullHandler;
	}();

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var ImNotificationsPullHandler = /*#__PURE__*/function () {
	  babelHelpers.createClass(ImNotificationsPullHandler, null, [{
	    key: "create",
	    value: function create() {
	      var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	      return new this(params);
	    }
	  }]);

	  function ImNotificationsPullHandler() {
	    var params = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, ImNotificationsPullHandler);

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

	  babelHelpers.createClass(ImNotificationsPullHandler, [{
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
	    key: "handleNotifyAdd",
	    value: function handleNotifyAdd(params, extra) {
	      if (extra.server_time_ago > 30 || params.onlyFlash === true) {
	        return false;
	      }

	      var user = this.store.getters['users/get'](params.userId);

	      if (!user) {
	        var users = [];
	        users.push({
	          id: params.userId,
	          avatar: params.userAvatar,
	          color: params.userColor,
	          name: params.userName
	        });
	        this.store.dispatch('users/set', users);
	      }

	      this.store.dispatch('notifications/add', {
	        data: params
	      });
	      this.store.dispatch('notifications/setCounter', {
	        unreadTotal: params.counter
	      });
	      this.store.dispatch('recent/update', {
	        id: "notify",
	        fields: {
	          message: {
	            id: params.id,
	            text: params.text,
	            date: params.date
	          },
	          counter: params.counter
	        }
	      });
	    }
	  }, {
	    key: "handleNotifyConfirm",
	    value: function handleNotifyConfirm(params, extra) {
	      if (extra.server_time_ago > 30) {
	        return false;
	      }

	      this.store.dispatch('notifications/delete', {
	        id: params.id
	      });
	      this.store.dispatch('notifications/setCounter', {
	        unreadTotal: params.counter
	      });
	      this.updateRecentListOnDelete(params.counter);
	    }
	  }, {
	    key: "handleNotifyRead",
	    value: function handleNotifyRead(params, extra) {
	      var _this = this;

	      if (extra.server_time_ago > 30) {
	        return false;
	      }

	      params.list.forEach(function (id) {
	        _this.store.dispatch('notifications/read', {
	          ids: [id],
	          action: true
	        });
	      });
	      this.store.dispatch('notifications/setCounter', {
	        unreadTotal: params.counter
	      });
	      this.store.dispatch('recent/update', {
	        id: "notify",
	        fields: {
	          counter: params.counter
	        }
	      });
	    }
	  }, {
	    key: "handleNotifyUnread",
	    value: function handleNotifyUnread(params, extra) {
	      var _this2 = this;

	      if (extra.server_time_ago > 30) {
	        return false;
	      }

	      params.list.forEach(function (id) {
	        _this2.store.dispatch('notifications/read', {
	          ids: [id],
	          action: false
	        });
	      });
	      this.store.dispatch('notifications/setCounter', {
	        unreadTotal: params.counter
	      });
	      this.store.dispatch('recent/update', {
	        id: "notify",
	        fields: {
	          counter: params.counter
	        }
	      });
	    }
	  }, {
	    key: "handleNotifyDelete",
	    value: function handleNotifyDelete(params, extra) {
	      var _this3 = this;

	      if (extra.server_time_ago > 30) {
	        return false;
	      }

	      var idsToDelete = Object.keys(params.id).map(function (id) {
	        return parseInt(id, 10);
	      });
	      idsToDelete.forEach(function (id) {
	        _this3.store.dispatch('notifications/delete', {
	          id: id
	        });
	      });
	      this.updateRecentListOnDelete(params.counter);
	      this.store.dispatch('notifications/setCounter', {
	        unreadTotal: params.counter
	      });
	    }
	  }, {
	    key: "updateRecentListOnDelete",
	    value: function updateRecentListOnDelete(counterValue) {
	      var message;
	      var latestNotification = this.getLatest();

	      if (latestNotification !== null) {
	        message = {
	          id: latestNotification.id,
	          text: latestNotification.text,
	          date: latestNotification.date
	        };
	      } else {
	        var notificationChat = this.store.getters['recent/get']('notify');

	        if (notificationChat === false) {
	          return;
	        }

	        message = notificationChat.element.message;
	        message.text = this.controller.localize['IM_NOTIFICATIONS_DELETED_ITEM_STUB'];
	      }

	      this.store.dispatch('recent/update', {
	        id: "notify",
	        fields: {
	          message: message,
	          counter: counterValue
	        }
	      });
	    }
	  }, {
	    key: "getLatest",
	    value: function getLatest() {
	      var latestNotification = {
	        id: 0
	      };

	      var _iterator = _createForOfIteratorHelper(this.store.state.notifications.collection),
	          _step;

	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var notification = _step.value;

	          if (notification.id > latestNotification.id) {
	            latestNotification = notification;
	          }
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }

	      if (latestNotification.id === 0) {
	        return null;
	      }

	      return latestNotification;
	    }
	  }]);
	  return ImNotificationsPullHandler;
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
	exports.ImNotificationsPullHandler = ImNotificationsPullHandler;

}((this.BX.Messenger.Provider.Pull = this.BX.Messenger.Provider.Pull || {}),BX,BX.Messenger.Lib,BX.Event,BX.Messenger.Const,BX));
//# sourceMappingURL=registry.bundle.js.map
