this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,ui_vue_vuex,ui_vue,im_lib_timer,im_lib_clipboard,im_lib_utils,main_core_events,im_const,im_lib_uploader,im_lib_logger) {
	'use strict';

	/**
	 * @notice you need to provide this.userId and this.dialogId
	 */

	var DialogCore = {
	  data: function data() {
	    return {
	      dialogState: im_const.DialogState.loading
	    };
	  },
	  created: function created() {
	    this.timer = new im_lib_timer.Timer();
	  },
	  methods: {
	    getController: function getController() {
	      return this.$Bitrix.Data.get('controller');
	    },
	    getApplicationController: function getApplicationController() {
	      return this.getController().application;
	    },
	    getApplication: function getApplication() {
	      return this.$Bitrix.Application.get();
	    },
	    getRestClient: function getRestClient() {
	      return this.$Bitrix.RestClient.get();
	    },
	    getCurrentUser: function getCurrentUser() {
	      return this.$store.getters['users/get'](this.application.common.userId, true);
	    },
	    executeRestAnswer: function executeRestAnswer(method, queryResult, extra) {
	      this.getController().executeRestAnswer(method, queryResult, extra);
	    },
	    isUnreadMessagesLoaded: function isUnreadMessagesLoaded() {
	      if (!this.dialog) {
	        return true;
	      }

	      if (this.dialog.lastMessageId <= 0) {
	        return true;
	      }

	      if (!this.messageCollection || this.messageCollection.length <= 0) {
	        return true;
	      }

	      var lastElementId = 0;

	      for (var index = this.messageCollection.length - 1; index >= 0; index--) {
	        var lastElement = this.messageCollection[index];

	        if (typeof lastElement.id === "number") {
	          lastElementId = lastElement.id;
	          break;
	        }
	      }

	      return lastElementId >= this.dialog.lastMessageId;
	    },
	    //methods used in several mixins
	    openDialog: function openDialog() {//TODO
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.application.dialog.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    chatId: function chatId() {
	      // if (this.dialog)
	      // {
	      // 	return this.dialog.chatId;
	      // }
	      if (this.application) {
	        return this.application.dialog.chatId;
	      }
	    },
	    // userId()
	    // {
	    // 	return this.application.common.userId;
	    // },
	    diskFolderId: function diskFolderId() {
	      return this.application.dialog.diskFolderId;
	    },
	    messageCollection: function messageCollection() {
	      return this.$store.getters['messages/get'](this.application.dialog.chatId);
	    },
	    isDialogShowingMessages: function isDialogShowingMessages() {
	      var messagesNotEmpty = this.messageCollection && this.messageCollection.length > 0;

	      if (messagesNotEmpty) {
	        this.dialogState = im_const.DialogState.show;
	      } else if (this.dialog && this.dialog.init) {
	        this.dialogState = im_const.DialogState.empty;
	      } else {
	        this.dialogState = im_const.DialogState.loading;
	      }

	      return messagesNotEmpty;
	    },
	    isDarkBackground: function isDarkBackground() {
	      return this.application.options.darkBackground;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  }), {
	    localize: function localize() {
	      return ui_vue.BitrixVue.getFilteredPhrases(['IM_DIALOG_', 'IM_UTILS_', 'IM_MESSENGER_DIALOG_', 'IM_QUOTE_'], this);
	    }
	  })
	};

	function _createForOfIteratorHelper(o, allowArrayLike) { var it; if (typeof Symbol === "undefined" || o[Symbol.iterator] == null) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = o[Symbol.iterator](); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it.return != null) it.return(); } finally { if (didErr) throw err; } } }; }

	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }
	var DialogReadMessages = {
	  data: function data() {
	    return {
	      lastMessageToRead: null,
	      messagesToRead: []
	    };
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.readMessage, this.onReadMessage);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.readMessage, this.onReadMessage);
	  },
	  methods: {
	    onReadMessage: function onReadMessage(_ref) {
	      var event = _ref.data;
	      this.readMessage(event.id).then(function () {
	        return im_lib_logger.Logger.log('Read message complete');
	      }).catch(function () {
	        return im_lib_logger.Logger.error('Read message failed');
	      });
	    },
	    readMessage: function readMessage() {
	      var _this = this;

	      var messageId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
	      var force = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var skipAjax = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;

	      if (messageId) {
	        this.messagesToRead.push(parseInt(messageId));
	      }

	      this.timer.stop('readMessage', this.chatId, true);
	      this.timer.stop('readMessageServer', this.chatId, true);

	      if (force) {
	        return this.readMessageRequest(skipAjax);
	      }

	      return new Promise(function (resolve, reject) {
	        _this.timer.start('readMessage', _this.chatId, .1, function () {
	          _this.readMessageRequest(skipAjax).then(function (result) {
	            return resolve(result);
	          }).catch(reject);
	        });
	      });
	    },
	    readMessageRequest: function readMessageRequest() {
	      var _this2 = this;

	      var skipAjax = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      return new Promise(function (resolve, reject) {
	        //get max message id from queue
	        var _iterator = _createForOfIteratorHelper(_this2.messagesToRead),
	            _step;

	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var messageId = _step.value;

	            if (!_this2.lastMessageToRead) {
	              _this2.lastMessageToRead = messageId;
	            } else if (_this2.lastMessageToRead < messageId) {
	              _this2.lastMessageToRead = messageId;
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }

	        _this2.messagesToRead = [];
	        var lastId = _this2.lastMessageToRead || 0;

	        if (lastId <= 0) {
	          return resolve({
	            lastId: 0
	          });
	        } //read messages on front


	        _this2.$store.dispatch('messages/readMessages', {
	          chatId: _this2.chatId,
	          readId: lastId
	        }).then(function (result) {
	          //decrease counter
	          return _this2.$store.dispatch('dialogues/decreaseCounter', {
	            dialogId: _this2.dialogId,
	            count: result.count
	          });
	        }).then(function () {
	          if (skipAjax) {
	            return resolve({
	              lastId: lastId
	            });
	          } //read messages on server in .5s


	          _this2.timer.start('readMessageServer', _this2.chatId, .5, function () {
	            _this2.getRestClient().callMethod(im_const.RestMethod.imDialogRead, {
	              'DIALOG_ID': _this2.dialogId,
	              'MESSAGE_ID': lastId
	            }).then(function () {
	              return resolve({
	                lastId: lastId
	              });
	            }).catch(reject);
	          });
	        }).catch(reject);
	      });
	    }
	  }
	};

	var DialogQuoteMessage = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.quoteMessage, this.onQuoteMessage);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.quotePanelClose, this.onQuotePanelClose);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.quoteMessage, this.onQuoteMessage);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.quotePanelClose, this.onQuotePanelClose);
	  },
	  methods: {
	    onQuoteMessage: function onQuoteMessage(_ref) {
	      var event = _ref.data;
	      this.quoteMessage({
	        id: event.message.id
	      });
	    },
	    onQuotePanelClose: function onQuotePanelClose() {
	      this.quoteMessageClear();
	    },
	    quoteMessage: function quoteMessage(_ref2) {
	      var id = _ref2.id;
	      this.$store.dispatch('dialogues/update', {
	        dialogId: this.dialogId,
	        fields: {
	          quoteId: id
	        }
	      });
	    },
	    quoteMessageClear: function quoteMessageClear() {
	      this.$store.dispatch('dialogues/update', {
	        dialogId: this.dialogId,
	        fields: {
	          quoteId: 0
	        }
	      });
	    }
	  }
	};

	/**
	 * @notice needs TextareaCore mixin
	 */

	var DialogClickOnCommand = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnCommand, this.onClickOnCommand);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnCommand, this.onClickOnCommand);
	  },
	  methods: {
	    onClickOnCommand: function onClickOnCommand(_ref) {
	      var event = _ref.data;

	      if (event.type === 'put') {
	        this.insertText({
	          text: event.value + ' '
	        });
	      } else if (event.type === 'send') {
	        this.addMessageOnClient(event.value);
	      } else {
	        im_lib_logger.Logger.warn('Unprocessed command', event);
	      }
	    }
	  }
	};

	var DialogClickOnMention = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnMention, this.onClickOnMention);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnMention, this.onClickOnMention);
	  },
	  methods: {
	    onClickOnMention: function onClickOnMention(_ref) {
	      var event = _ref.data;

	      if (event.type === 'USER') {
	        this.openProfile(event.value);
	      } else if (event.type === 'CHAT') {
	        this.openDialog(event.value);
	      } else if (event.type === 'CALL') {
	        this.openPhoneMenu(event.value);
	      }
	    },
	    openProfile: function openProfile() {//TODO
	    },
	    openPhoneMenu: function openPhoneMenu() {//TODO
	    }
	  }
	};

	var DialogClickOnUserName = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnUserName, this.onClickOnUserName);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnUserName, this.onClickOnUserName);
	  },
	  methods: {
	    onClickOnUserName: function onClickOnUserName(_ref) {
	      var event = _ref.data;
	      this.replyToUser(event.user.id, event.user);
	    },
	    replyToUser: function replyToUser() {//TODO
	    }
	  }
	};

	var DialogClickOnMessageMenu = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnMessageMenu, this.onClickOnMessageMenu);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnMessageMenu, this.onClickOnMessageMenu);
	  },
	  methods: {
	    onClickOnMessageMenu: function onClickOnMessageMenu(_ref) {
	      var event = _ref.data;
	      this.openMessageMenu(event.message);
	    },
	    openMessageMenu: function openMessageMenu() {//TODO
	    }
	  }
	};

	var DialogClickOnMessageRetry = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetry);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetry);
	  },
	  methods: {
	    onClickOnMessageRetry: function onClickOnMessageRetry(_ref) {
	      var event = _ref.data;
	      this.retrySendMessage(event.message);
	    },
	    retrySendMessage: function retrySendMessage() {//TODO
	    }
	  }
	};

	var DialogClickOnUploadCancel = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancel);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancel);
	  },
	  methods: {
	    onClickOnUploadCancel: function onClickOnUploadCancel(_ref) {
	      var event = _ref.data;
	      this.cancelUploadFile(event.file.id);
	    },
	    cancelUploadFile: function cancelUploadFile() {//TODO
	    }
	  }
	};

	var DialogClickOnReadList = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnReadList, this.onClickOnReadList);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnReadList, this.onClickOnReadList);
	  },
	  methods: {
	    onClickOnReadList: function onClickOnReadList(_ref) {
	      var event = _ref.data;
	      this.openReadList(event.list);
	    },
	    openReadList: function openReadList() {//TODO
	    }
	  }
	};

	var DialogSetMessageReaction = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.setMessageReaction, this.onSetMessageReaction);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.setMessageReaction, this.onSetMessageReaction);
	  },
	  methods: {
	    onSetMessageReaction: function onSetMessageReaction(_ref) {
	      var event = _ref.data;
	      this.reactMessage(event.message.id, event.reaction);
	    },
	    reactMessage: function reactMessage(messageId) {
	      var action = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'auto';
	      this.getRestClient().callMethod(im_const.RestMethod.imMessageLike, {
	        'MESSAGE_ID': messageId,
	        'ACTION': action === 'auto' ? 'auto' : action === 'set' ? 'plus' : 'minus'
	      });
	    }
	  }
	};

	var DialogOpenMessageReactionList = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.openMessageReactionList, this.onOpenMessageReactionList);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.openMessageReactionList, this.onOpenMessageReactionList);
	  },
	  methods: {
	    onOpenMessageReactionList: function onOpenMessageReactionList(_ref) {
	      var event = _ref.data;
	      this.openMessageReactionList(event.message.id, event.values);
	    },
	    openMessageReactionList: function openMessageReactionList() {//TODO
	    }
	  }
	};

	/**
	 * @notice needs TextareaCore mixin
	 */

	var DialogClickOnKeyboardButton = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardButton);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardButton);
	  },
	  methods: {
	    onClickOnKeyboardButton: function onClickOnKeyboardButton(_ref) {
	      var _this = this;

	      var event = _ref.data;

	      if (event.action === 'ACTION') {
	        var _event$params = event.params,
	            dialogId = _event$params.dialogId,
	            messageId = _event$params.messageId,
	            botId = _event$params.botId,
	            action = _event$params.action,
	            value = _event$params.value;

	        if (action === 'SEND') {
	          this.addMessageOnClient(value);
	          setTimeout(function () {
	            main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	              chatId: _this.chatId,
	              duration: 300,
	              cancelIfScrollChange: false
	            });
	          }, 300);
	        } else if (action === 'PUT') {
	          this.insertText({
	            text: value + ' '
	          });
	        } else if (action === 'CALL') ; else if (action === 'COPY') {
	          im_lib_clipboard.Clipboard.copy(value);
	          BX.UI.Notification.Center.notify({
	            content: this.localize['IM_DIALOG_CLIPBOARD_COPY_SUCCESS'],
	            autoHideDelay: 4000
	          });
	        }

	        return true;
	      }

	      if (event.action === 'COMMAND') {
	        var _event$params2 = event.params,
	            _dialogId = _event$params2.dialogId,
	            _messageId = _event$params2.messageId,
	            _botId = _event$params2.botId,
	            command = _event$params2.command,
	            params = _event$params2.params;
	        this.getRestClient().callMethod(im_const.RestMethod.imMessageCommand, {
	          'MESSAGE_ID': _messageId,
	          'DIALOG_ID': _dialogId,
	          'BOT_ID': _botId,
	          'COMMAND': command,
	          'COMMAND_PARAMS': params
	        });
	        return true;
	      }

	      return false;
	    }
	  }
	};

	var DialogClickOnChatTeaser = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnChatTeaser, this.onClickOnChatTeaser);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnChatTeaser, this.onClickOnChatTeaser);
	  },
	  methods: {
	    onClickOnChatTeaser: function onClickOnChatTeaser(_ref) {
	      var _this = this;

	      var event = _ref.data;
	      this.joinParentChat(event.message.id, 'chat' + event.message.params.CHAT_ID).then(function (dialogId) {
	        _this.openDialog(dialogId);
	      }).catch(function () {});
	      return true;
	    },
	    joinParentChat: function joinParentChat(messageId, dialogId) {
	      var _this2 = this;

	      return new Promise(function (resolve, reject) {
	        if (!messageId || !dialogId) {
	          return reject();
	        }

	        if (typeof _this2.tempJoinChat === 'undefined') {
	          _this2.tempJoinChat = {};
	        } else if (_this2.tempJoinChat['wait']) {
	          return reject();
	        }

	        _this2.tempJoinChat['wait'] = true;

	        _this2.getRestClient().callMethod(im_const.RestMethod.imChatParentJoin, {
	          'DIALOG_ID': dialogId,
	          'MESSAGE_ID': messageId
	        }).then(function () {
	          _this2.tempJoinChat['wait'] = false;
	          _this2.tempJoinChat[dialogId] = true;
	          return resolve(dialogId);
	        }).catch(function () {
	          _this2.tempJoinChat['wait'] = false;
	          return reject();
	        });
	      });
	    }
	  }
	};

	var DialogClickOnDialog = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnDialog, this.onClickOnDialog);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnDialog, this.onClickOnDialog);
	  },
	  methods: {
	    onClickOnDialog: function onClickOnDialog(_ref) {
	      var event = _ref.data;
	      return true;
	    }
	  }
	};

	/**
	 * @notice needs DialogCore mixin
	 */

	var TextareaCore = {
	  data: function data() {
	    return {
	      messagesToSend: []
	    };
	  },
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.sendMessage, this.onSendMessage);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.startWriting, this.onTextareaStartWriting);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.appButtonClick, this.onTextareaAppButtonClick);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.focus, this.onTextareaFocus);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.blur, this.onTextareaBlur);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.keyUp, this.onTextareaKeyUp);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.edit, this.onTextareaEdit);
	  },
	  beforeDestroy: function beforeDestroy() {
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.sendMessage, this.onSendMessage);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.startWriting, this.onTextareaStartWriting);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.appButtonClick, this.onTextareaAppButtonClick);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.focus, this.onTextareaFocus);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.blur, this.onTextareaBlur);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.keyUp, this.onTextareaKeyUp);
	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.edit, this.onTextareaEdit);
	  },
	  methods: {
	    //handlers
	    onSendMessage: function onSendMessage(_ref) {
	      var event = _ref.data;

	      if (!event.text) {
	        return false;
	      }

	      this.addMessageOnClient(event.text);
	    },
	    onTextareaStartWriting: function onTextareaStartWriting(_ref2) {
	      var event = _ref2.data;
	      this.startWriting();
	    },
	    onTextareaAppButtonClick: function onTextareaAppButtonClick(_ref3) {//TODO

	      var event = _ref3.data;
	    },
	    onTextareaFocus: function onTextareaFocus(_ref4) {//TODO

	      var event = _ref4.data;
	    },
	    onTextareaBlur: function onTextareaBlur(_ref5) {//TODO

	      var event = _ref5.data;
	    },
	    onTextareaKeyUp: function onTextareaKeyUp(_ref6) {//TODO

	      var event = _ref6.data;
	    },
	    onTextareaEdit: function onTextareaEdit(_ref7) {//TODO

	      var event = _ref7.data;
	    },
	    //actions
	    addMessageOnClient: function addMessageOnClient() {
	      var _this = this;

	      var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var file = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;

	      if (!text && !file) {
	        return false;
	      }

	      var quoteId = this.$store.getters['dialogues/getQuoteId'](this.dialogId);

	      if (quoteId) {
	        var quoteMessage = this.$store.getters['messages/getMessage'](this.chatId, quoteId);

	        if (quoteMessage) {
	          var user = null;

	          if (quoteMessage.authorId) {
	            user = this.$store.getters['users/get'](quoteMessage.authorId);
	          }

	          var files = this.$store.getters['files/getList'](this.chatId);
	          var message = [];
	          message.push('-'.repeat(54));
	          message.push((user && user.name ? user.name : this.localize['IM_QUOTE_PANEL_DEFAULT_TITLE']) + ' [' + im_lib_utils.Utils.date.format(quoteMessage.date, null, this.localize) + ']');
	          message.push(im_lib_utils.Utils.text.quote(quoteMessage.text, quoteMessage.params, files, this.localize));
	          message.push('-'.repeat(54));
	          message.push(text);
	          text = message.join("\n");
	          this.quoteMessageClear();
	        }
	      }

	      if (!this.isUnreadMessagesLoaded()) {
	        this.addMessageOnServer({
	          id: 0,
	          chatId: this.chatId,
	          dialogId: this.dialogId,
	          text: text,
	          file: file
	        });
	        this.processMessagesToSendQueue();
	        return true;
	      }

	      var params = {};

	      if (file) {
	        params.FILE_ID = [file.id];
	      }

	      this.$store.dispatch('messages/add', {
	        chatId: this.chatId,
	        authorId: this.userId,
	        text: text,
	        params: params,
	        sending: !file
	      }).then(function (messageId) {
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	          chatId: _this.chatId,
	          cancelIfScrollChange: true
	        });

	        _this.messagesToSend.push({
	          id: messageId,
	          chatId: _this.chatId,
	          dialogId: _this.dialogId,
	          text: text,
	          file: file,
	          sending: false
	        });

	        _this.processMessagesToSendQueue();
	      });
	      return true;
	    },
	    processMessagesToSendQueue: function processMessagesToSendQueue() {
	      var _this2 = this;

	      if (!this.diskFolderId) {
	        this.requestDiskFolderId().then(function () {
	          _this2.processMessagesToSendQueue();
	        }).catch(function (error) {
	          im_lib_logger.Logger.warn('processMessagesToSendQueue error', error);
	          return false;
	        });
	        return false;
	      }

	      this.messagesToSend.filter(function (element) {
	        return !element.sending;
	      }).forEach(function (element) {
	        element.sending = true;

	        if (element.file) {
	          _this2.addMessageWithFile(element);
	        } else {
	          _this2.addMessageOnServer(element);
	        }
	      });
	      return true;
	    },
	    addMessageOnServer: function addMessageOnServer(element) {
	      var _this3 = this;

	      this.stopWriting();
	      var quoteId = this.$store.getters['dialogues/getQuoteId'](this.dialogId);

	      if (quoteId) {
	        var quoteMessage = this.$store.getters['messages/getMessage'](this.chatId, quoteId);

	        if (quoteMessage) {
	          var user = this.$store.getters['users/get'](quoteMessage.authorId);
	          var newMessage = [];
	          newMessage.push("------------------------------------------------------");
	          newMessage.push(user.name ? user.name : this.localize['IM_QUOTE_PANEL_DEFAULT_TITLE']);
	          newMessage.push(quoteMessage.text);
	          newMessage.push('------------------------------------------------------');
	          newMessage.push(element.text);
	          element.text = newMessage.join("\n");
	          this.quoteMessageClear();
	        }
	      }

	      this.getRestClient().callMethod(im_const.RestMethod.imMessageAdd, {
	        'TEMPLATE_ID': element.id,
	        'DIALOG_ID': element.dialogId,
	        'MESSAGE': element.text
	      }, null, null).then(function (response) {
	        _this3.$store.dispatch('messages/update', {
	          id: element.id,
	          chatId: element.chatId,
	          fields: {
	            id: response.data(),
	            sending: false,
	            error: false
	          }
	        }).then(function () {
	          _this3.$store.dispatch('messages/actionFinish', {
	            id: response.data(),
	            chatId: element.chatId
	          });
	        });
	      }).catch(function (error) {
	        im_lib_logger.Logger.warn('Error during adding message');
	      });
	      return true;
	    },
	    //writing
	    stopWriting: function stopWriting() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.dialogId;
	      this.timer.stop('writes', dialogId, true);
	      this.timer.stop('writesSend', dialogId, true);
	    },
	    startWriting: function startWriting() {
	      var _this4 = this;

	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.dialogId;

	      if (im_lib_utils.Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId)) {
	        return false;
	      }

	      this.timer.start('writes', dialogId, 28);
	      this.timer.start('writesSend', dialogId, 5, function () {
	        _this4.getRestClient().callMethod(im_const.RestMethod.imDialogWriting, {
	          'DIALOG_ID': dialogId
	        }).catch(function () {
	          _this4.timer.stop('writes', dialogId);
	        });
	      });
	    },
	    insertText: function insertText(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.insertText, event);
	    },
	    requestDiskFolderId: function requestDiskFolderId() {
	      var _this5 = this;

	      if (this.requestDiskFolderPromise) {
	        return this.requestDiskFolderPromise;
	      }

	      this.requestDiskFolderPromise = new Promise(function (resolve, reject) {
	        if (_this5.flagRequestDiskFolderIdSended || _this5.diskFolderId) {
	          _this5.flagRequestDiskFolderIdSended = false;
	          resolve();
	          return true;
	        }

	        _this5.flagRequestDiskFolderIdSended = true;

	        _this5.getRestClient().callMethod(im_const.RestMethod.imDiskFolderGet, {
	          chat_id: _this5.chatId
	        }).then(function (response) {
	          _this5.flagRequestDiskFolderIdSended = false;

	          _this5.executeRestAnswer(im_const.RestMethodHandler.imDiskFolderGet, response);

	          resolve();
	        }).catch(function (error) {
	          _this5.flagRequestDiskFolderIdSended = false;

	          _this5.executeRestAnswer(im_const.RestMethodHandler.imDiskFolderGet, error);

	          reject();
	        });
	      });
	      return this.requestDiskFolderPromise;
	    }
	  }
	};

	/**
	 * @notice creates uploader instance when dialog is inited (dialog.init in model)
	 * @notice define actionUploadChunk and actionCommitFile fields for custom upload methods (e.g. videoconference)
	 * @notice redefine addMessageWithFile for custom headers (e.g. videoconference)
	 */

	var TextareaUploadFile = {
	  created: function created() {
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.fileSelected, this.onTextareaFileSelected);
	  },
	  beforeDestroy: function beforeDestroy() {
	    if (this.uploader) {
	      this.uploader.unsubscribeAll();
	    }

	    main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.fileSelected, this.onTextareaFileSelected);
	  },
	  computed: {
	    dialogInited: function dialogInited() {
	      if (!this.dialog) {
	        return false;
	      }

	      return this.dialog.init;
	    }
	  },
	  watch: {
	    dialogInited: function dialogInited(newValue) {
	      if (newValue === true) {
	        this.initUploader();
	      }
	    }
	  },
	  methods: {
	    onTextareaFileSelected: function onTextareaFileSelected(_ref) {
	      var event = _ref.data;
	      var fileInput = event && event.fileChangeEvent && event.fileChangeEvent.target.files.length > 0 ? event.fileChangeEvent : '';

	      if (!fileInput) {
	        return false;
	      }

	      this.uploadFile(fileInput);
	    },
	    addMessageWithFile: function addMessageWithFile(message) {
	      this.stopWriting();
	      this.uploader.addTask({
	        taskId: message.file.id,
	        fileData: message.file.source.file,
	        fileName: message.file.source.file.name,
	        generateUniqueName: true,
	        diskFolderId: this.diskFolderId,
	        previewBlob: message.file.previewBlob
	      });
	    },
	    //uploader
	    uploadFile: function uploadFile(event) {
	      if (!event) {
	        return false;
	      }

	      this.uploader.addFilesFromEvent(event);
	    },
	    initUploader: function initUploader() {
	      var _this = this;

	      this.uploader = new im_lib_uploader.Uploader({
	        generatePreview: true,
	        sender: {
	          actionUploadChunk: this.actionUploadChunk,
	          actionCommitFile: this.actionCommitFile
	        }
	      });
	      this.uploader.subscribe('onStartUpload', function (event) {
	        var eventData = event.getData();
	        im_lib_logger.Logger.log('Uploader: onStartUpload', eventData);

	        _this.$store.dispatch('files/update', {
	          chatId: _this.chatId,
	          id: eventData.id,
	          fields: {
	            status: im_const.FileStatus.upload,
	            progress: 0
	          }
	        });
	      });
	      this.uploader.subscribe('onProgress', function (event) {
	        var eventData = event.getData();
	        im_lib_logger.Logger.log('Uploader: onProgress', eventData);

	        _this.$store.dispatch('files/update', {
	          chatId: _this.chatId,
	          id: eventData.id,
	          fields: {
	            status: im_const.FileStatus.upload,
	            progress: eventData.progress === 100 ? 99 : eventData.progress
	          }
	        });
	      });
	      this.uploader.subscribe('onSelectFile', function (event) {
	        var eventData = event.getData();
	        var file = eventData.file;
	        im_lib_logger.Logger.log('Uploader: onSelectFile', eventData);
	        var fileType = 'file';

	        if (file.type.toString().startsWith('image')) {
	          fileType = 'image';
	        } else if (file.type.toString().startsWith('video')) {
	          fileType = 'video';
	        }

	        _this.$store.dispatch('files/add', {
	          chatId: _this.chatId,
	          authorId: _this.userId,
	          name: file.name,
	          type: fileType,
	          extension: file.name.split('.').splice(-1)[0],
	          size: file.size,
	          image: !eventData.previewData ? false : {
	            width: eventData.previewDataWidth,
	            height: eventData.previewDataHeight
	          },
	          status: im_const.FileStatus.wait,
	          progress: 0,
	          authorName: _this.getCurrentUser().name,
	          urlPreview: eventData.previewData ? URL.createObjectURL(eventData.previewData) : ""
	        }).then(function (fileId) {
	          _this.addMessageOnClient('', {
	            id: fileId,
	            source: eventData,
	            previewBlob: eventData.previewData
	          });
	        });
	      });
	      this.uploader.subscribe('onComplete', function (event) {
	        var eventData = event.getData();
	        im_lib_logger.Logger.log('Uploader: onComplete', eventData);

	        _this.$store.dispatch('files/update', {
	          chatId: _this.chatId,
	          id: eventData.id,
	          fields: {
	            status: im_const.FileStatus.wait,
	            progress: 100
	          }
	        });

	        var message = _this.messagesToSend.find(function (message) {
	          if (message.file) {
	            return message.file.id === eventData.id;
	          }

	          return false;
	        });

	        var fileType = _this.$store.getters['files/get'](_this.chatId, message.file.id, true).type;

	        _this.fileCommit({
	          chatId: _this.chatId,
	          uploadId: eventData.result.data.file.id,
	          messageText: message.text,
	          messageId: message.id,
	          fileId: message.file.id,
	          fileType: fileType
	        }, message);
	      });
	      this.uploader.subscribe('onUploadFileError', function (event) {
	        var eventData = event.getData();
	        im_lib_logger.Logger.log('Uploader: onUploadFileError', eventData);

	        var message = _this.messagesToSend.find(function (message) {
	          if (message.file) {
	            return message.file.id === eventData.id;
	          }

	          return false;
	        });

	        _this.fileError(_this.chatId, message.file.id, message.id);
	      });
	      this.uploader.subscribe('onCreateFileError', function (event) {
	        var eventData = event.getData();
	        im_lib_logger.Logger.log('Uploader: onCreateFileError', eventData);

	        var message = _this.messagesToSend.find(function (message) {
	          if (message.file) {
	            return message.file.id === eventData.id;
	          }

	          return false;
	        });

	        _this.fileError(_this.chatId, message.file.id, message.id);
	      });
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    },
	    fileCommit: function fileCommit(params, message) {
	      var _this2 = this;

	      this.getRestClient().callMethod(im_const.RestMethod.imDiskFileCommit, {
	        chat_id: params.chatId,
	        upload_id: params.uploadId,
	        message: params.messageText,
	        template_id: params.messageId,
	        file_template_id: params.fileId
	      }, null, null).then(function (response) {
	        _this2.executeRestAnswer(im_const.RestMethodHandler.imDiskFileCommit, response, message);
	      }).catch(function (error) {
	        _this2.executeRestAnswer(im_const.RestMethodHandler.imDiskFileCommit, error, message);
	      });
	      return true;
	    },
	    fileError: function fileError(chatId, fileId) {
	      var messageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	      this.$store.dispatch('files/update', {
	        chatId: chatId,
	        id: fileId,
	        fields: {
	          status: im_const.FileStatus.error,
	          progress: 0
	        }
	      });

	      if (messageId) {
	        this.$store.dispatch('messages/actionError', {
	          chatId: chatId,
	          id: messageId,
	          retry: false
	        });
	      }
	    }
	  }
	};

	exports.DialogCore = DialogCore;
	exports.DialogReadMessages = DialogReadMessages;
	exports.DialogQuoteMessage = DialogQuoteMessage;
	exports.DialogClickOnCommand = DialogClickOnCommand;
	exports.DialogClickOnMention = DialogClickOnMention;
	exports.DialogClickOnUserName = DialogClickOnUserName;
	exports.DialogClickOnMessageMenu = DialogClickOnMessageMenu;
	exports.DialogClickOnMessageRetry = DialogClickOnMessageRetry;
	exports.DialogClickOnUploadCancel = DialogClickOnUploadCancel;
	exports.DialogClickOnReadList = DialogClickOnReadList;
	exports.DialogSetMessageReaction = DialogSetMessageReaction;
	exports.DialogOpenMessageReactionList = DialogOpenMessageReactionList;
	exports.DialogClickOnKeyboardButton = DialogClickOnKeyboardButton;
	exports.DialogClickOnChatTeaser = DialogClickOnChatTeaser;
	exports.DialogClickOnDialog = DialogClickOnDialog;
	exports.TextareaCore = TextareaCore;
	exports.TextareaUploadFile = TextareaUploadFile;

}((this.BX.Messenger.Mixin = this.BX.Messenger.Mixin || {}),BX,BX,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Event,BX.Messenger.Const,BX.Messenger.Lib,BX.Messenger.Lib));
//# sourceMappingURL=registry.bundle.js.map
