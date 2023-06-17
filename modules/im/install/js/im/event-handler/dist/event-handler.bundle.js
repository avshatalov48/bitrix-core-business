this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
(function (exports,im_lib_clipboard,im_lib_timer,im_lib_uploader,im_lib_utils,main_core_events,im_const,im_lib_logger,main_core) {
	'use strict';

	var SendMessageHandler = /*#__PURE__*/function () {
	  function SendMessageHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, SendMessageHandler);
	    babelHelpers.defineProperty(this, "messagesToSend", []);
	    babelHelpers.defineProperty(this, "store", null);
	    babelHelpers.defineProperty(this, "restClient", null);
	    babelHelpers.defineProperty(this, "loc", null);
	    this.controller = $Bitrix.Data.get('controller');
	    this.store = this.controller.store;
	    this.restClient = $Bitrix.RestClient.get();
	    this.loc = $Bitrix.Loc.messages;
	    this.onSendMessageHandler = this.onSendMessage.bind(this);
	    this.onClickOnMessageRetryHandler = this.onClickOnMessageRetry.bind(this);
	    this.onClickOnCommandHandler = this.onClickOnCommand.bind(this);
	    this.onClickOnKeyboardHandler = this.onClickOnKeyboard.bind(this);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.sendMessage, this.onSendMessageHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetryHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnCommand, this.onClickOnCommandHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardHandler);
	  }
	  babelHelpers.createClass(SendMessageHandler, [{
	    key: "onSendMessage",
	    value: function onSendMessage(_ref) {
	      var data = _ref.data;
	      if (!data.text && !data.file) {
	        return false;
	      }
	      this.sendMessage(data.text, data.file);
	    } //endregion events
	    // entry point for sending message
	  }, {
	    key: "sendMessage",
	    value: function sendMessage() {
	      var _this = this;
	      var text = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
	      var file = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      if (!text && !file) {
	        return false;
	      }

	      // quote handling
	      var quoteId = this.store.getters['dialogues/getQuoteId'](this.getDialogId());
	      if (quoteId) {
	        var quoteMessage = this.store.getters['messages/getMessage'](this.getChatId(), quoteId);
	        if (quoteMessage) {
	          text = this.getMessageTextWithQuote(quoteMessage, text);
	          main_core_events.EventEmitter.emit(im_const.EventType.dialog.quotePanelClose);
	        }
	      }
	      if (!this.controller.application.isUnreadMessagesLoaded()) {
	        // not all messages are loaded, adding message only on server
	        this.sendMessageToServer({
	          id: 0,
	          chatId: this.getChatId(),
	          dialogId: this.getDialogId(),
	          text: text,
	          file: file
	        });
	        this.processQueue();
	        return true;
	      }
	      var params = {};
	      if (file) {
	        params.FILE_ID = [file.id];
	      }
	      this.addMessageToModel({
	        text: text,
	        params: params,
	        sending: !file
	      }).then(function (messageId) {
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	          chatId: _this.getChatId(),
	          cancelIfScrollChange: true
	        });
	        _this.addMessageToQueue({
	          messageId: messageId,
	          text: text,
	          file: file
	        });
	        _this.processQueue();
	      });
	    }
	    /**
	     * Goes through messages queue:
	     * - For messages with file sends event to uploader
	     * - For common messages sends them to server
	     */
	  }, {
	    key: "processQueue",
	    value: function processQueue() {
	      var _this2 = this;
	      this.messagesToSend.filter(function (element) {
	        return !element.sending;
	      }).forEach(function (element) {
	        _this2.deleteFromQueue(element.id);
	        element.sending = true;
	        if (element.file) {
	          main_core_events.EventEmitter.emit(im_const.EventType.textarea.stopWriting);
	          main_core_events.EventEmitter.emit(im_const.EventType.uploader.addMessageWithFile, element);
	        } else {
	          _this2.sendMessageToServer(element);
	        }
	      });
	    }
	  }, {
	    key: "addMessageToModel",
	    value: function addMessageToModel(_ref2) {
	      var text = _ref2.text,
	        params = _ref2.params,
	        sending = _ref2.sending;
	      return this.store.dispatch('messages/add', {
	        chatId: this.getChatId(),
	        authorId: this.getUserId(),
	        text: text,
	        params: params,
	        sending: sending
	      });
	    }
	  }, {
	    key: "addMessageToQueue",
	    value: function addMessageToQueue(_ref3) {
	      var messageId = _ref3.messageId,
	        text = _ref3.text,
	        file = _ref3.file;
	      this.messagesToSend.push({
	        id: messageId,
	        chatId: this.getChatId(),
	        dialogId: this.getDialogId(),
	        text: text,
	        file: file,
	        sending: false
	      });
	    }
	  }, {
	    key: "sendMessageToServer",
	    value: function sendMessageToServer(element) {
	      var _this3 = this;
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.stopWriting);
	      this.restClient.callMethod(im_const.RestMethod.imMessageAdd, {
	        'TEMPLATE_ID': element.id,
	        'DIALOG_ID': element.dialogId,
	        'MESSAGE': element.text
	      }, null, null).then(function (response) {
	        _this3.controller.executeRestAnswer(im_const.RestMethodHandler.imMessageAdd, response, element);
	      })["catch"](function (error) {
	        _this3.controller.executeRestAnswer(im_const.RestMethodHandler.imMessageAdd, error, element);
	        im_lib_logger.Logger.warn('SendMessageHandler: error during adding message', error);
	      });
	    }
	  }, {
	    key: "onClickOnMessageRetry",
	    value: function onClickOnMessageRetry(_ref4) {
	      var event = _ref4.data;
	      this.retrySendMessage(event.message);
	    }
	  }, {
	    key: "retrySendMessage",
	    value: function retrySendMessage(message) {
	      this.addMessageToQueue({
	        messageId: message.id,
	        text: message.text,
	        file: null
	      });
	      this.setSendingMessageFlag(message.id);
	      this.processQueue();
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
	    key: "deleteFromQueue",
	    value: function deleteFromQueue(messageId) {
	      this.messagesToSend = this.messagesToSend.filter(function (element) {
	        return element.id !== messageId;
	      });
	    }
	  }, {
	    key: "onClickOnCommand",
	    value: function onClickOnCommand(_ref5) {
	      var event = _ref5.data;
	      if (event.type === 'put') {
	        this.handlePutAction(event.value);
	      } else if (event.type === 'send') {
	        this.handleSendAction(event.value);
	      } else {
	        im_lib_logger.Logger.warn('SendMessageHandler: Unprocessed command', event);
	      }
	    }
	  }, {
	    key: "onClickOnKeyboard",
	    value: function onClickOnKeyboard(_ref6) {
	      var event = _ref6.data;
	      if (event.action === 'ACTION') {
	        var _event$params = event.params,
	          action = _event$params.action,
	          value = _event$params.value;
	        this.handleKeyboardAction(action, value);
	      }
	      if (event.action === 'COMMAND') {
	        var _event$params2 = event.params,
	          dialogId = _event$params2.dialogId,
	          messageId = _event$params2.messageId,
	          botId = _event$params2.botId,
	          command = _event$params2.command,
	          params = _event$params2.params;
	        this.restClient.callMethod(im_const.RestMethod.imMessageCommand, {
	          'MESSAGE_ID': messageId,
	          'DIALOG_ID': dialogId,
	          'BOT_ID': botId,
	          'COMMAND': command,
	          'COMMAND_PARAMS': params
	        })["catch"](function (error) {
	          return console.error('SendMessageHandler: command processing error', error);
	        });
	      }
	    }
	  }, {
	    key: "handleKeyboardAction",
	    value: function handleKeyboardAction(action, value) {
	      switch (action) {
	        case 'SEND':
	          {
	            this.handleSendAction(value);
	            break;
	          }
	        case 'PUT':
	          {
	            this.handlePutAction(value);
	            break;
	          }
	        case 'CALL':
	          {
	            //this.openPhoneMenu(value);
	            break;
	          }
	        case 'COPY':
	          {
	            im_lib_clipboard.Clipboard.copy(value);
	            BX.UI.Notification.Center.notify({
	              content: this.loc['IM_DIALOG_CLIPBOARD_COPY_SUCCESS'],
	              autoHideDelay: 4000
	            });
	            break;
	          }
	        case 'DIALOG':
	          {
	            //this.openDialog(value);
	            break;
	          }
	        default:
	          {
	            console.error('SendMessageHandler: unknown keyboard action');
	          }
	      }
	    }
	  }, {
	    key: "handlePutAction",
	    value: function handlePutAction(text) {
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.insertText, {
	        text: "".concat(text, " ")
	      });
	    }
	  }, {
	    key: "handleSendAction",
	    value: function handleSendAction(text) {
	      var _this4 = this;
	      this.sendMessage(text);
	      setTimeout(function () {
	        main_core_events.EventEmitter.emit(im_const.EventType.dialog.scrollToBottom, {
	          chatId: _this4.getChatId(),
	          duration: 300,
	          cancelIfScrollChange: false
	        });
	      }, 300);
	    } // region helpers
	  }, {
	    key: "getMessageTextWithQuote",
	    value: function getMessageTextWithQuote(quoteMessage, text) {
	      var user = null;
	      if (quoteMessage.authorId) {
	        user = this.store.getters['users/get'](quoteMessage.authorId);
	      }
	      var files = this.store.getters['files/getList'](this.getChatId());
	      var quoteDelimiter = '-'.repeat(54);
	      var quoteTitle = user && user.name ? user.name : this.loc['IM_QUOTE_PANEL_DEFAULT_TITLE'];
	      var quoteDate = im_lib_utils.Utils.date.format(quoteMessage.date, null, this.loc);
	      var quoteContent = im_lib_utils.Utils.text.quote(quoteMessage.text, quoteMessage.params, files, this.loc);
	      var message = [];
	      message.push(quoteDelimiter);
	      message.push("".concat(quoteTitle, " [").concat(quoteDate, "]"));
	      message.push(quoteContent);
	      message.push(quoteDelimiter);
	      message.push(text);
	      return message.join("\n");
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
	    key: "getUserId",
	    value: function getUserId() {
	      return this.store.state.application.common.userId;
	    } // endregion helpers
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.sendMessage, this.onSendMessageHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnMessageRetry, this.onClickOnMessageRetryHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnCommand, this.onClickOnCommandHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnKeyboardButton, this.onClickOnKeyboardHandler);
	    }
	  }]);
	  return SendMessageHandler;
	}();

	var ReadingHandler = /*#__PURE__*/function () {
	  // {<chatId>: [<messageId>]}

	  function ReadingHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, ReadingHandler);
	    babelHelpers.defineProperty(this, "messagesToRead", {});
	    babelHelpers.defineProperty(this, "timer", null);
	    babelHelpers.defineProperty(this, "store", null);
	    babelHelpers.defineProperty(this, "restClient", null);
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	    this.timer = new im_lib_timer.Timer();
	    this.onReadMessageHandler = this.onReadMessage.bind(this);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.readMessage, this.onReadMessageHandler);
	  }
	  babelHelpers.createClass(ReadingHandler, [{
	    key: "onReadMessage",
	    value: function onReadMessage(_ref) {
	      var _ref$data = _ref.data,
	        _ref$data$id = _ref$data.id,
	        id = _ref$data$id === void 0 ? null : _ref$data$id,
	        _ref$data$skipTimer = _ref$data.skipTimer,
	        skipTimer = _ref$data$skipTimer === void 0 ? false : _ref$data$skipTimer,
	        _ref$data$skipAjax = _ref$data.skipAjax,
	        skipAjax = _ref$data$skipAjax === void 0 ? false : _ref$data$skipAjax;
	      return this.readMessage(id, skipTimer, skipAjax);
	    }
	  }, {
	    key: "readMessage",
	    value: function readMessage(messageId) {
	      var _this = this;
	      var skipTimer = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var skipAjax = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      var chatId = this.getChatId();
	      if (messageId) {
	        if (!this.messagesToRead[chatId]) {
	          this.messagesToRead[chatId] = [];
	        }
	        this.messagesToRead[chatId].push(Number.parseInt(messageId, 10));
	      }
	      this.timer.stop('readMessage', chatId, true);
	      this.timer.stop('readMessageServer', chatId, true);
	      if (skipTimer) {
	        return this.processMessagesToRead(chatId, skipAjax);
	      }
	      return new Promise(function (resolve, reject) {
	        _this.timer.start('readMessage', chatId, 0.1, function () {
	          _this.processMessagesToRead(chatId, skipAjax).then(function (result) {
	            return resolve(result);
	          })["catch"](reject);
	        });
	      });
	    }
	  }, {
	    key: "processMessagesToRead",
	    value: function processMessagesToRead(chatId) {
	      var _this2 = this;
	      var skipAjax = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var lastMessageToRead = this.getMaxMessageIdFromQueue(chatId);
	      delete this.messagesToRead[chatId];
	      if (lastMessageToRead <= 0) {
	        return Promise.resolve();
	      }
	      return new Promise(function (resolve, reject) {
	        _this2.readMessageOnClient(chatId, lastMessageToRead).then(function (readResult) {
	          return _this2.decreaseChatCounter(chatId, readResult.count);
	        }).then(function () {
	          if (skipAjax) {
	            return resolve({
	              chatId: chatId,
	              lastId: lastMessageToRead
	            });
	          }
	          _this2.timer.start('readMessageServer', chatId, 0.5, function () {
	            _this2.readMessageOnServer(chatId, lastMessageToRead).then(function () {
	              resolve({
	                chatId: chatId,
	                lastId: lastMessageToRead
	              });
	            })["catch"](reject);
	          });
	        })["catch"](function (error) {
	          im_lib_logger.Logger.error('Reading messages error', error);
	          reject();
	        });
	      });
	    }
	  }, {
	    key: "getMaxMessageIdFromQueue",
	    value: function getMaxMessageIdFromQueue(chatId) {
	      var maxMessageId = 0;
	      if (!this.messagesToRead[chatId]) {
	        return maxMessageId;
	      }
	      this.messagesToRead[chatId].forEach(function (messageId) {
	        if (maxMessageId < messageId) {
	          maxMessageId = messageId;
	        }
	      });
	      return maxMessageId;
	    }
	  }, {
	    key: "readMessageOnClient",
	    value: function readMessageOnClient(chatId, lastMessageToRead) {
	      return this.store.dispatch('messages/readMessages', {
	        chatId: chatId,
	        readId: lastMessageToRead
	      });
	    }
	  }, {
	    key: "readMessageOnServer",
	    value: function readMessageOnServer(chatId, lastMessageToRead) {
	      return this.restClient.callMethod(im_const.RestMethod.imDialogRead, {
	        'DIALOG_ID': this.getDialogIdByChatId(chatId),
	        'MESSAGE_ID': lastMessageToRead
	      });
	    }
	  }, {
	    key: "decreaseChatCounter",
	    value: function decreaseChatCounter(chatId, counter) {
	      return this.store.dispatch('dialogues/decreaseCounter', {
	        dialogId: this.getDialogIdByChatId(chatId),
	        count: counter
	      });
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId() {
	      return this.store.state.application.dialog.chatId;
	    }
	  }, {
	    key: "getDialogIdByChatId",
	    value: function getDialogIdByChatId(chatId) {
	      var dialog = this.store.getters['dialogues/getByChatId'](chatId);
	      if (!dialog) {
	        return 0;
	      }
	      return dialog.dialogId;
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.store.state.application.dialog.dialogId;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.readMessage, this.onReadMessageHandler);
	    }
	  }]);
	  return ReadingHandler;
	}();

	var ReactionHandler = /*#__PURE__*/function () {
	  function ReactionHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, ReactionHandler);
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	    this.onSetMessageReactionHandler = this.onSetMessageReaction.bind(this);
	    this.onOpenMessageReactionListHandler = this.onOpenMessageReactionList.bind(this);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.setMessageReaction, this.onSetMessageReactionHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.openMessageReactionList, this.onOpenMessageReactionListHandler);
	  }
	  babelHelpers.createClass(ReactionHandler, [{
	    key: "onSetMessageReaction",
	    value: function onSetMessageReaction(_ref) {
	      var data = _ref.data;
	      this.reactToMessage(data.message.id, data.reaction);
	    }
	  }, {
	    key: "onOpenMessageReactionList",
	    value: function onOpenMessageReactionList(_ref2) {
	      var data = _ref2.data;
	      this.openMessageReactionList(data.message.id, data.values);
	    }
	  }, {
	    key: "reactToMessage",
	    value: function reactToMessage(messageId, reaction) {
	      // let type = reaction.type || ReactionHandler.types.like;
	      var action = reaction.action || ReactionHandler.actions.auto;
	      if (action !== ReactionHandler.actions.auto) {
	        action = action === ReactionHandler.actions.set ? ReactionHandler.actions.plus : ReactionHandler.actions.minus;
	      }
	      this.restClient.callMethod(im_const.RestMethod.imMessageLike, {
	        'MESSAGE_ID': messageId,
	        'ACTION': action
	      });
	    }
	  }, {
	    key: "openMessageReactionList",
	    value: function openMessageReactionList(messageId, values) {
	      im_lib_logger.Logger.warn('Message reaction list not implemented yet!', messageId, values);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.setMessageReaction, this.onSetMessageReactionHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.openMessageReactionList, this.onOpenMessageReactionListHandler);
	    }
	  }]);
	  return ReactionHandler;
	}();
	babelHelpers.defineProperty(ReactionHandler, "types", {
	  none: 'none',
	  like: 'like',
	  kiss: 'kiss',
	  laugh: 'laugh',
	  wonder: 'wonder',
	  cry: 'cry',
	  angry: 'angry'
	});
	babelHelpers.defineProperty(ReactionHandler, "actions", {
	  auto: 'auto',
	  plus: 'plus',
	  minus: 'minus',
	  set: 'set'
	});

	var QuoteHandler = /*#__PURE__*/function () {
	  function QuoteHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, QuoteHandler);
	    this.store = $Bitrix.Data.get('controller').store;
	    this.onQuoteMessageHandler = this.onQuoteMessage.bind(this);
	    this.onQuotePanelCloseHandler = this.onQuotePanelClose.bind(this);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.quoteMessage, this.onQuoteMessageHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.quotePanelClose, this.onQuotePanelCloseHandler);
	  }
	  babelHelpers.createClass(QuoteHandler, [{
	    key: "onQuoteMessage",
	    value: function onQuoteMessage(_ref) {
	      var data = _ref.data;
	      this.quoteMessage(data.message.id);
	    }
	  }, {
	    key: "onQuotePanelClose",
	    value: function onQuotePanelClose() {
	      this.clearQuote();
	    }
	  }, {
	    key: "quoteMessage",
	    value: function quoteMessage(messageId) {
	      this.store.dispatch('dialogues/update', {
	        dialogId: this.getDialogId(),
	        fields: {
	          quoteId: messageId
	        }
	      });
	    }
	  }, {
	    key: "clearQuote",
	    value: function clearQuote() {
	      this.store.dispatch('dialogues/update', {
	        dialogId: this.getDialogId(),
	        fields: {
	          quoteId: 0
	        }
	      });
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.store.state.application.dialog.dialogId;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.quoteMessage, this.onQuoteMessageHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.quotePanelClose, this.onQuotePanelCloseHandler);
	    }
	  }]);
	  return QuoteHandler;
	}();

	var TextareaHandler = /*#__PURE__*/function () {
	  function TextareaHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, TextareaHandler);
	    babelHelpers.defineProperty(this, "store", null);
	    babelHelpers.defineProperty(this, "restClient", null);
	    babelHelpers.defineProperty(this, "timer", null);
	    this.store = $Bitrix.Data.get('controller').store;
	    this.restClient = $Bitrix.RestClient.get();
	    this.timer = new im_lib_timer.Timer();
	    this.subscribeToEvents();
	  }

	  // region events
	  babelHelpers.createClass(TextareaHandler, [{
	    key: "subscribeToEvents",
	    value: function subscribeToEvents() {
	      this.onStartWritingHandler = this.onStartWriting.bind(this);
	      this.onStopWritingHandler = this.onStopWriting.bind(this);
	      this.onAppButtonClickHandler = this.onAppButtonClick.bind(this);
	      this.onFocusHandler = this.onFocus.bind(this);
	      this.onBlurHandler = this.onBlur.bind(this);
	      this.onKeyUpHandler = this.onKeyUp.bind(this);
	      this.onEditHandler = this.onEdit.bind(this);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.startWriting, this.onStartWritingHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.stopWriting, this.onStopWritingHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.appButtonClick, this.onAppButtonClickHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.focus, this.onFocusHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.blur, this.onBlurHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.keyUp, this.onKeyUpHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.edit, this.onEditHandler);
	    }
	  }, {
	    key: "onStartWriting",
	    value: function onStartWriting() {
	      this.startWriting();
	    }
	  }, {
	    key: "onStopWriting",
	    value: function onStopWriting() {
	      this.stopWriting();
	    }
	  }, {
	    key: "onAppButtonClick",
	    value: function onAppButtonClick() {
	      //
	    }
	  }, {
	    key: "onFocus",
	    value: function onFocus() {
	      //
	    }
	  }, {
	    key: "onBlur",
	    value: function onBlur() {
	      //
	    }
	  }, {
	    key: "onKeyUp",
	    value: function onKeyUp() {
	      //
	    }
	  }, {
	    key: "onEdit",
	    value: function onEdit() {
	      //
	    } //endregion events
	    // region writing
	  }, {
	    key: "startWriting",
	    value: function startWriting() {
	      var _this = this;
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();
	      if (im_lib_utils.Utils.dialog.isEmptyDialogId(dialogId) || this.timer.has('writes', dialogId)) {
	        return false;
	      }
	      this.timer.start('writes', dialogId, 28);
	      this.timer.start('writesSend', dialogId, 5, function () {
	        _this.restClient.callMethod(im_const.RestMethod.imDialogWriting, {
	          'DIALOG_ID': dialogId
	        })["catch"](function () {
	          _this.timer.stop('writes', dialogId);
	        });
	      });
	    }
	  }, {
	    key: "stopWriting",
	    value: function stopWriting() {
	      var dialogId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.getDialogId();
	      this.timer.stop('writes', dialogId, true);
	      this.timer.stop('writesSend', dialogId, true);
	    } // endregion writing
	    // region helpers
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
	    key: "getUserId",
	    value: function getUserId() {
	      return this.store.state.application.common.userId;
	    }
	  }, {
	    key: "getDiskFolderId",
	    value: function getDiskFolderId() {
	      return this.store.state.application.dialog.diskFolderId;
	    } // endregion helpers
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.startWriting, this.onStartWritingHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.stopWriting, this.onStopWritingHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.appButtonClick, this.onAppButtonClickHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.focus, this.onFocusHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.blur, this.onBlurHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.keyUp, this.onKeyUpHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.edit, this.onEditHandler);
	    }
	  }]);
	  return TextareaHandler;
	}();

	/**
	 * @notice define getActionUploadChunk and getActionCommitFile methods for custom upload methods (e.g. videoconference)
	 * @notice redefine addMessageWithFile for custom headers (e.g. videoconference)
	 */
	var TextareaUploadHandler = /*#__PURE__*/function () {
	  function TextareaUploadHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, TextareaUploadHandler);
	    babelHelpers.defineProperty(this, "controller", null);
	    babelHelpers.defineProperty(this, "restClient", null);
	    babelHelpers.defineProperty(this, "uploader", null);
	    babelHelpers.defineProperty(this, "isRequestingDiskFolderId", false);
	    this.controller = $Bitrix.Data.get('controller');
	    this.restClient = $Bitrix.RestClient.get();
	    this.initUploader();
	    this.onTextareaFileSelectedHandler = this.onTextareaFileSelected.bind(this);
	    this.addMessageWithFileHandler = this.addMessageWithFile.bind(this);
	    this.onClickOnUploadCancelHandler = this.onClickOnUploadCancel.bind(this);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.textarea.fileSelected, this.onTextareaFileSelectedHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.uploader.addMessageWithFile, this.addMessageWithFileHandler);
	    main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancelHandler);
	  }
	  babelHelpers.createClass(TextareaUploadHandler, [{
	    key: "initUploader",
	    value: function initUploader() {
	      this.uploader = new im_lib_uploader.Uploader({
	        generatePreview: true,
	        sender: this.getUploaderSenderOptions()
	      });
	      this.uploader.subscribe('onStartUpload', this.onStartUploadHandler.bind(this));
	      this.uploader.subscribe('onProgress', this.onProgressHandler.bind(this));
	      this.uploader.subscribe('onSelectFile', this.onSelectFileHandler.bind(this));
	      this.uploader.subscribe('onComplete', this.onCompleteHandler.bind(this));
	      this.uploader.subscribe('onUploadFileError', this.onUploadFileErrorHandler.bind(this));
	      this.uploader.subscribe('onCreateFileError', this.onCreateFileErrorHandler.bind(this));
	    }
	  }, {
	    key: "commitFile",
	    value: function commitFile(params, message) {
	      var _this = this;
	      this.restClient.callMethod(im_const.RestMethod.imDiskFileCommit, {
	        chat_id: params.chatId,
	        upload_id: params.uploadId,
	        message: params.messageText,
	        template_id: params.messageId,
	        file_template_id: params.fileId
	      }, null, null).then(function (response) {
	        _this.controller.executeRestAnswer(im_const.RestMethodHandler.imDiskFileCommit, response, message);
	      })["catch"](function (error) {
	        _this.controller.executeRestAnswer(im_const.RestMethodHandler.imDiskFileCommit, error, message);
	      });
	      return true;
	    }
	  }, {
	    key: "setUploadError",
	    value: function setUploadError(chatId, fileId) {
	      var messageId = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 0;
	      this.controller.store.dispatch('files/update', {
	        chatId: chatId,
	        id: fileId,
	        fields: {
	          status: im_const.FileStatus.error,
	          progress: 0
	        }
	      });
	      if (messageId) {
	        this.controller.store.dispatch('messages/actionError', {
	          chatId: chatId,
	          id: messageId,
	          retry: false
	        });
	      }
	    }
	  }, {
	    key: "onTextareaFileSelected",
	    value: function onTextareaFileSelected(_ref) {
	      var event = _ref.data;
	      var fileInput = event && event.fileChangeEvent && event.fileChangeEvent.target.files.length > 0 ? event.fileChangeEvent : '';
	      if (!fileInput) {
	        return false;
	      }
	      this.uploadFile(fileInput);
	    }
	  }, {
	    key: "addMessageWithFile",
	    value: function addMessageWithFile(event) {
	      var _this2 = this;
	      var message = event.getData();
	      if (!this.getDiskFolderId()) {
	        this.requestDiskFolderId(message.chatId).then(function () {
	          _this2.addMessageWithFile(event);
	        })["catch"](function (error) {
	          im_lib_logger.Logger.error('addMessageWithFile error', error);
	          return false;
	        });
	        return false;
	      }
	      this.uploader.addTask({
	        taskId: message.file.id,
	        fileData: message.file.source.file,
	        fileName: message.file.source.file.name,
	        generateUniqueName: true,
	        diskFolderId: this.getDiskFolderId(),
	        previewBlob: message.file.previewBlob
	      });
	    }
	  }, {
	    key: "uploadFile",
	    value: function uploadFile(event) {
	      if (!event) {
	        return false;
	      }
	      this.uploader.addFilesFromEvent(event);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      if (this.uploader) {
	        this.uploader.unsubscribeAll();
	      }
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.textarea.fileSelected, this.onTextareaFileSelectedHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.uploader.addMessageWithFile, this.addMessageWithFileHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnUploadCancel, this.onClickOnUploadCancelHandler);
	    }
	  }, {
	    key: "getChatId",
	    value: function getChatId() {
	      return this.controller.store.state.application.dialog.chatId;
	    }
	  }, {
	    key: "getDialogId",
	    value: function getDialogId() {
	      return this.controller.store.state.application.dialog.dialogId;
	    }
	  }, {
	    key: "getDiskFolderId",
	    value: function getDiskFolderId() {
	      return this.controller.store.state.application.dialog.diskFolderId;
	    }
	  }, {
	    key: "getCurrentUser",
	    value: function getCurrentUser() {
	      return this.controller.store.getters['users/get'](this.controller.store.state.application.common.userId, true);
	    }
	  }, {
	    key: "getMessageByFileId",
	    value: function getMessageByFileId(fileId, eventData) {
	      var chatMessages = this.controller.store.getters['messages/get'](this.getChatId());
	      var messageWithFile = chatMessages.find(function (message) {
	        var _message$params;
	        if (main_core.Type.isArray((_message$params = message.params) === null || _message$params === void 0 ? void 0 : _message$params.FILE_ID)) {
	          return message.params.FILE_ID.includes(fileId);
	        }
	        return false;
	      });
	      if (!messageWithFile) {
	        return;
	      }
	      return {
	        id: messageWithFile.id,
	        chatId: messageWithFile.chatId,
	        dialogId: this.getDialogId(),
	        text: messageWithFile.text,
	        file: {
	          id: fileId,
	          source: eventData,
	          previewBlob: eventData.previewData
	        },
	        sending: true
	      };
	    }
	  }, {
	    key: "requestDiskFolderId",
	    value: function requestDiskFolderId(chatId) {
	      var _this3 = this;
	      return new Promise(function (resolve, reject) {
	        if (_this3.isRequestingDiskFolderId || _this3.getDiskFolderId()) {
	          _this3.isRequestingDiskFolderId = false;
	          resolve();
	          return;
	        }
	        _this3.isRequestingDiskFolderId = true;
	        _this3.restClient.callMethod(im_const.RestMethod.imDiskFolderGet, {
	          chat_id: chatId
	        }).then(function (response) {
	          _this3.isRequestingDiskFolderId = false;
	          _this3.controller.executeRestAnswer(im_const.RestMethodHandler.imDiskFolderGet, response);
	          resolve();
	        })["catch"](function (error) {
	          _this3.isRequestingDiskFolderId = false;
	          _this3.controller.executeRestAnswer(im_const.RestMethodHandler.imDiskFolderGet, error);
	          reject(error);
	        });
	      });
	    } // Uploader handlers
	  }, {
	    key: "onStartUploadHandler",
	    value: function onStartUploadHandler(event) {
	      var eventData = event.getData();
	      im_lib_logger.Logger.log('Uploader: onStartUpload', eventData);
	      this.controller.store.dispatch('files/update', {
	        chatId: this.getChatId(),
	        id: eventData.id,
	        fields: {
	          status: im_const.FileStatus.upload,
	          progress: 0
	        }
	      });
	    }
	  }, {
	    key: "onProgressHandler",
	    value: function onProgressHandler(event) {
	      var eventData = event.getData();
	      im_lib_logger.Logger.log('Uploader: onProgress', eventData);
	      this.controller.store.dispatch('files/update', {
	        chatId: this.getChatId(),
	        id: eventData.id,
	        fields: {
	          status: im_const.FileStatus.upload,
	          progress: eventData.progress === 100 ? 99 : eventData.progress
	        }
	      });
	    }
	  }, {
	    key: "onSelectFileHandler",
	    value: function onSelectFileHandler(event) {
	      var eventData = event.getData();
	      var file = eventData.file;
	      im_lib_logger.Logger.log('Uploader: onSelectFile', eventData);
	      var fileType = 'file';
	      if (file.type.toString().startsWith('image')) {
	        fileType = 'image';
	      } else if (file.type.toString().startsWith('video')) {
	        fileType = 'video';
	      }
	      this.controller.store.dispatch('files/add', {
	        chatId: this.getChatId(),
	        authorId: this.getCurrentUser().id,
	        name: file.name,
	        type: fileType,
	        extension: file.name.split('.').splice(-1)[0],
	        size: file.size,
	        image: !eventData.previewData ? false : {
	          width: eventData.previewDataWidth,
	          height: eventData.previewDataHeight
	        },
	        status: im_const.FileStatus.progress,
	        progress: 0,
	        authorName: this.getCurrentUser().name,
	        urlPreview: eventData.previewData ? URL.createObjectURL(eventData.previewData) : ''
	      }).then(function (fileId) {
	        main_core_events.EventEmitter.emit(im_const.EventType.textarea.sendMessage, {
	          text: '',
	          file: {
	            id: fileId,
	            source: eventData,
	            previewBlob: eventData.previewData
	          }
	        });
	      });
	    }
	  }, {
	    key: "onCompleteHandler",
	    value: function onCompleteHandler(event) {
	      var eventData = event.getData();
	      im_lib_logger.Logger.log('Uploader: onComplete', eventData);
	      this.controller.store.dispatch('files/update', {
	        chatId: this.getChatId(),
	        id: eventData.id,
	        fields: {
	          status: im_const.FileStatus.wait,
	          progress: 100
	        }
	      });
	      var messageWithFile = this.getMessageByFileId(eventData.id, eventData);
	      var fileType = this.controller.store.getters['files/get'](this.getChatId(), messageWithFile.file.id, true).type;
	      this.commitFile({
	        chatId: this.getChatId(),
	        uploadId: eventData.result.data.file.id,
	        messageText: messageWithFile.text,
	        messageId: messageWithFile.id,
	        fileId: messageWithFile.file.id,
	        fileType: fileType
	      }, messageWithFile);
	    }
	  }, {
	    key: "onUploadFileErrorHandler",
	    value: function onUploadFileErrorHandler(event) {
	      var eventData = event.getData();
	      im_lib_logger.Logger.log('Uploader: onUploadFileError', eventData);
	      var messageWithFile = this.getMessageByFileId(eventData.id, eventData);
	      if (messageWithFile) {
	        this.setUploadError(this.getChatId(), messageWithFile.file.id, messageWithFile.id);
	      }
	    }
	  }, {
	    key: "onCreateFileErrorHandler",
	    value: function onCreateFileErrorHandler(event) {
	      var eventData = event.getData();
	      im_lib_logger.Logger.log('Uploader: onCreateFileError', eventData);
	      var messageWithFile = this.getMessageByFileId(eventData.id, eventData);
	      if (messageWithFile) {
	        this.setUploadError(this.getChatId(), messageWithFile.file.id, messageWithFile.id);
	      }
	    }
	  }, {
	    key: "onClickOnUploadCancel",
	    value: function onClickOnUploadCancel(_ref2) {
	      var _this4 = this;
	      var event = _ref2.data;
	      var fileId = event.file.id;
	      var fileData = event.file;
	      var messageWithFile = this.getMessageByFileId(fileId, fileData);
	      if (!messageWithFile) {
	        return;
	      }
	      this.uploader.deleteTask(fileId);
	      this.controller.store.dispatch('messages/delete', {
	        chatId: this.getChatId(),
	        id: messageWithFile.id
	      }).then(function () {
	        _this4.controller.store.dispatch('files/delete', {
	          chatId: _this4.getChatId(),
	          id: messageWithFile.file.id
	        });
	      });
	    }
	  }, {
	    key: "getActionCommitFile",
	    value: function getActionCommitFile() {
	      return null;
	    }
	  }, {
	    key: "getActionUploadChunk",
	    value: function getActionUploadChunk() {
	      return null;
	    }
	  }, {
	    key: "getUploaderSenderOptions",
	    value: function getUploaderSenderOptions() {
	      return {
	        actionUploadChunk: this.getActionUploadChunk(),
	        actionCommitFile: this.getActionCommitFile()
	      };
	    }
	  }]);
	  return TextareaUploadHandler;
	}();

	var TextareaDragHandler = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(TextareaDragHandler, _EventEmitter);
	  function TextareaDragHandler(events) {
	    var _this;
	    babelHelpers.classCallCheck(this, TextareaDragHandler);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextareaDragHandler).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "isDragging", false);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "minimumHeight", 120);
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "maximumHeight", 400);
	    _this.setEventNamespace('BX.IM.TextareaDragHandler');
	    _this.subscribeToEvents(events);
	    if (im_lib_utils.Utils.device.isMobile()) {
	      _this.maximumHeight = 200;
	    }
	    return _this;
	  }
	  babelHelpers.createClass(TextareaDragHandler, [{
	    key: "subscribeToEvents",
	    value: function subscribeToEvents(configEvents) {
	      var _this2 = this;
	      var events = main_core.Type.isObject(configEvents) ? configEvents : {};
	      Object.entries(events).forEach(function (_ref) {
	        var _ref2 = babelHelpers.slicedToArray(_ref, 2),
	          name = _ref2[0],
	          callback = _ref2[1];
	        if (main_core.Type.isFunction(callback)) {
	          _this2.subscribe(name, callback);
	        }
	      });
	    }
	  }, {
	    key: "onStartDrag",
	    value: function onStartDrag(event, currentHeight) {
	      if (this.isDragging) {
	        return;
	      }
	      this.isDragging = true;
	      event = event.changedTouches ? event.changedTouches[0] : event;
	      this.textareaDragCursorStartPoint = event.clientY;
	      this.textareaDragHeightStartPoint = currentHeight;
	      this.addTextareaDragEvents();
	    }
	  }, {
	    key: "onTextareaContinueDrag",
	    value: function onTextareaContinueDrag(event) {
	      if (!this.isDragging) {
	        return;
	      }
	      event = event.changedTouches ? event.changedTouches[0] : event;
	      this.textareaDragCursorControlPoint = event.clientY;
	      var maxPoint = Math.min(this.textareaDragHeightStartPoint + this.textareaDragCursorStartPoint - this.textareaDragCursorControlPoint, this.maximumHeight);
	      var newTextareaHeight = Math.max(maxPoint, this.minimumHeight);
	      this.emit(TextareaDragHandler.events.onHeightChange, {
	        newHeight: newTextareaHeight
	      });
	    }
	  }, {
	    key: "onTextareaStopDrag",
	    value: function onTextareaStopDrag() {
	      if (!this.isDragging) {
	        return;
	      }
	      this.isDragging = false;
	      this.removeTextareaDragEvents();
	      this.emit(TextareaDragHandler.events.onStopDrag);
	    }
	  }, {
	    key: "addTextareaDragEvents",
	    value: function addTextareaDragEvents() {
	      this.onContinueDragHandler = this.onTextareaContinueDrag.bind(this);
	      this.onStopDragHandler = this.onTextareaStopDrag.bind(this);
	      document.addEventListener('mousemove', this.onContinueDragHandler);
	      document.addEventListener('touchmove', this.onContinueDragHandler);
	      document.addEventListener('touchend', this.onStopDragHandler);
	      document.addEventListener('mouseup', this.onStopDragHandler);
	      document.addEventListener('mouseleave', this.onStopDragHandler);
	    }
	  }, {
	    key: "removeTextareaDragEvents",
	    value: function removeTextareaDragEvents() {
	      document.removeEventListener('mousemove', this.onContinueDragHandler);
	      document.removeEventListener('touchmove', this.onContinueDragHandler);
	      document.removeEventListener('touchend', this.onStopDragHandler);
	      document.removeEventListener('mouseup', this.onStopDragHandler);
	      document.removeEventListener('mouseleave', this.onStopDragHandler);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.removeTextareaDragEvents();
	    }
	  }]);
	  return TextareaDragHandler;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(TextareaDragHandler, "events", {
	  onHeightChange: 'onHeightChange',
	  onStopDrag: 'onStopDrag'
	});

	var DialogActionHandler = /*#__PURE__*/function () {
	  function DialogActionHandler($Bitrix) {
	    babelHelpers.classCallCheck(this, DialogActionHandler);
	    babelHelpers.defineProperty(this, "restClient", null);
	    this.restClient = $Bitrix.RestClient.get();
	    this.subscribeToEvents();
	  }
	  babelHelpers.createClass(DialogActionHandler, [{
	    key: "subscribeToEvents",
	    value: function subscribeToEvents() {
	      this.clickOnMentionHandler = this.onClickOnMention.bind(this);
	      this.clickOnUserNameHandler = this.onClickOnUserName.bind(this);
	      this.clickOnMessageMenuHandler = this.onClickOnMessageMenu.bind(this);
	      this.clickOnReadListHandler = this.onClickOnReadList.bind(this);
	      this.clickOnChatTeaserHandler = this.onClickOnChatTeaser.bind(this);
	      this.clickOnDialogHandler = this.onClickOnDialog.bind(this);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnMention, this.clickOnMentionHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnUserName, this.clickOnUserNameHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnMessageMenu, this.clickOnMessageMenuHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnReadList, this.clickOnReadListHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnChatTeaser, this.clickOnChatTeaserHandler);
	      main_core_events.EventEmitter.subscribe(im_const.EventType.dialog.clickOnDialog, this.clickOnDialogHandler);
	    }
	  }, {
	    key: "onClickOnMention",
	    value: function onClickOnMention(_ref) {
	      var event = _ref.data;
	      if (event.type === 'USER') {
	        im_lib_logger.Logger.warn('DialogActionHandler: open user profile', event);
	      } else if (event.type === 'CHAT') {
	        im_lib_logger.Logger.warn('DialogActionHandler: open dialog from mention click', event);
	      } else if (event.type === 'CALL') {
	        im_lib_logger.Logger.warn('DialogActionHandler: open phone menu', event);
	      }
	    }
	  }, {
	    key: "onClickOnUserName",
	    value: function onClickOnUserName(_ref2) {
	      var event = _ref2.data;
	      main_core_events.EventEmitter.emit(im_const.EventType.textarea.insertText, {
	        text: "".concat(event.user.name, ", ")
	      });
	    }
	  }, {
	    key: "onClickOnMessageMenu",
	    value: function onClickOnMessageMenu(_ref3) {
	      var event = _ref3.data;
	      im_lib_logger.Logger.warn('DialogActionHandler: open message menu', event);
	    }
	  }, {
	    key: "onClickOnReadList",
	    value: function onClickOnReadList(_ref4) {
	      var event = _ref4.data;
	      im_lib_logger.Logger.warn('DialogActionHandler: open read list', event);
	    }
	  }, {
	    key: "onClickOnChatTeaser",
	    value: function onClickOnChatTeaser(_ref5) {
	      var event = _ref5.data;
	      this.joinParentChat(event.message.id, "chat".concat(event.message.params.CHAT_ID)).then(function (dialogId) {
	        im_lib_logger.Logger.warn('DialogActionHandler: open dialog from teaser click', dialogId);
	      })["catch"](function (error) {
	        console.error('DialogActionHandler: error joining parent chat', error);
	      });
	    }
	  }, {
	    key: "onClickOnDialog",
	    value: function onClickOnDialog() {
	      im_lib_logger.Logger.warn('DialogActionHandler: click on dialog');
	    }
	  }, {
	    key: "joinParentChat",
	    value: function joinParentChat(messageId, dialogId) {
	      var _this = this;
	      return new Promise(function (resolve, reject) {
	        if (!messageId || !dialogId) {
	          return reject();
	        }

	        // TODO: what is this for
	        if (typeof _this.tempJoinChat === 'undefined') {
	          _this.tempJoinChat = {};
	        } else if (_this.tempJoinChat['wait']) {
	          return reject();
	        }
	        _this.tempJoinChat['wait'] = true;
	        _this.restClient.callMethod(im_const.RestMethod.imChatParentJoin, {
	          'DIALOG_ID': dialogId,
	          'MESSAGE_ID': messageId
	        }).then(function () {
	          _this.tempJoinChat['wait'] = false;
	          _this.tempJoinChat[dialogId] = true;
	          return resolve(dialogId);
	        })["catch"](function () {
	          _this.tempJoinChat['wait'] = false;
	          return reject();
	        });
	      });
	    }
	  }, {
	    key: "unsubscribeEvents",
	    value: function unsubscribeEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnMention, this.clickOnMentionHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnUserName, this.clickOnUserNameHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnMessageMenu, this.clickOnMessageMenuHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnReadList, this.clickOnReadListHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnChatTeaser, this.clickOnChatTeaserHandler);
	      main_core_events.EventEmitter.unsubscribe(im_const.EventType.dialog.clickOnDialog, this.clickOnDialogHandler);
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      this.unsubscribeEvents();
	    }
	  }]);
	  return DialogActionHandler;
	}();

	// fix for compatible with mobile, bug #169468
	var namespace = main_core.Reflection.getClass('BX.Messenger');
	if (namespace) {
	  namespace.ReadingHandler = ReadingHandler;
	  namespace.ReactionHandler = ReactionHandler;
	  namespace.QuoteHandler = QuoteHandler;
	}

	exports.TextareaHandler = TextareaHandler;
	exports.TextareaDragHandler = TextareaDragHandler;
	exports.TextareaUploadHandler = TextareaUploadHandler;
	exports.SendMessageHandler = SendMessageHandler;
	exports.ReadingHandler = ReadingHandler;
	exports.ReactionHandler = ReactionHandler;
	exports.QuoteHandler = QuoteHandler;
	exports.DialogActionHandler = DialogActionHandler;

}((this.BX.Messenger.EventHandler = this.BX.Messenger.EventHandler || {}),BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Messenger.Lib,BX.Event,BX.Messenger.Const,BX.Messenger.Lib,BX));
//# sourceMappingURL=event-handler.bundle.js.map
