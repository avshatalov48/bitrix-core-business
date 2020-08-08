this.BX = this.BX || {};
(function (exports,ui_vue,ui_vue_vuex,im_lib_logger,im_const,im_lib_utils) {
	'use strict';

	/**
	 * Bitrix im dialog mobile
	 * Dialog vue component
	 *
	 * @package bitrix
	 * @subpackage mobile
	 * @copyright 2001-2019 Bitrix
	 */
	/**
	 * @notice Do not mutate or clone this component! It is under development.
	 */

	ui_vue.Vue.component('bx-im-component-dialog', {
	  props: {
	    chatId: {
	      default: 0
	    },
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: 0
	    },
	    enableGestureQuote: {
	      default: true
	    },
	    enableGestureQuoteFromRight: {
	      default: true
	    },
	    enableGestureMenu: {
	      default: false
	    },
	    showMessageUserName: {
	      default: true
	    },
	    showMessageAvatar: {
	      default: true
	    },
	    listenEventScrollToBottom: {
	      default: ''
	    },
	    listenEventRequestHistory: {
	      default: ''
	    },
	    listenEventRequestUnread: {
	      default: ''
	    },
	    listenEventSendReadMessages: {
	      default: ''
	    }
	  },
	  data: function data() {
	    return {
	      dialogState: 'loading',
	      dialogDiskFolderId: 0,
	      dialogChatId: 0,
	      scrollToBottomEvent: this.listenEventScrollToBottom,
	      requestHistoryEvent: this.listenEventRequestHistory,
	      requestUnreadEvent: this.listenEventRequestUnread,
	      sendReadMessagesEvent: this.listenEventSendReadMessages
	    };
	  },
	  created: function created() {
	    if (!this.listenEventScrollToBottom) {
	      this.scrollToBottomEvent = im_const.EventType.dialog.scrollToBottom;
	    }

	    if (!this.listenEventRequestHistory) {
	      this.requestHistoryEvent = im_const.EventType.dialog.requestHistoryResult;
	    }

	    if (!this.listenEventRequestUnread) {
	      this.requestUnreadEvent = im_const.EventType.dialog.requestUnreadResult;
	    }

	    if (!this.listenEventSendReadMessages) {
	      this.sendReadMessagesEvent = im_const.EventType.dialog.sendReadMessages;
	    }

	    this.requestData();
	  },
	  watch: {
	    dialogId: function dialogId() {
	      this.requestData();
	    }
	  },
	  computed: babelHelpers.objectSpread({
	    EventType: function EventType() {
	      return im_const.EventType;
	    },
	    localize: function localize() {
	      return Object.assign({}, ui_vue.Vue.getFilteredPhrases('MOBILE_CHAT_', this.$root.$bitrixMessages), ui_vue.Vue.getFilteredPhrases('IM_UTILS_', this.$root.$bitrixMessages));
	    },
	    widgetClassName: function widgetClassName(state) {
	      var className = ['bx-mobilechat-wrapper'];

	      if (this.showMessageDialog) {
	        className.push('bx-mobilechat-chat-start');
	      }

	      return className.join(' ');
	    },
	    quotePanelData: function quotePanelData() {
	      var result = {
	        id: 0,
	        title: '',
	        description: '',
	        color: ''
	      };

	      if (!this.showMessageDialog || !this.dialog.quoteId) {
	        return result;
	      }

	      var message = this.$store.getters['messages/getMessage'](this.dialog.chatId, this.dialog.quoteId);

	      if (!message) {
	        return result;
	      }

	      var user = this.$store.getters['users/get'](message.authorId);
	      var files = this.$store.getters['files/getList'](this.dialog.chatId);
	      return {
	        id: this.dialog.quoteId,
	        title: message.params.NAME ? message.params.NAME : user ? user.name : '',
	        color: user ? user.color : '',
	        description: im_lib_utils.Utils.text.purify(message.text, message.params, files, this.localize)
	      };
	    },
	    isDialog: function isDialog() {
	      return im_lib_utils.Utils.dialog.isChatId(this.dialog.dialogId);
	    },
	    isGestureQuoteSupported: function isGestureQuoteSupported() {
	      return false;
	    },
	    isDarkBackground: function isDarkBackground() {
	      return this.application.options.darkBackground;
	    },
	    showMessageDialog: function showMessageDialog() {
	      var result = this.messageCollection && this.messageCollection.length > 0;

	      if (result) {
	        this.dialogState = 'show';
	      } else if (this.dialog && this.dialog.init) {
	        this.dialogState = 'empty';
	      } else {
	        this.dialogState = 'loading';
	      }

	      return result;
	    },
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.application.dialog.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    messageCollection: function messageCollection() {
	      return this.$store.getters['messages/get'](this.application.dialog.chatId);
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  methods: {
	    requestData: function requestData() {
	      var _query,
	          _this = this;

	      console.log('4. requestData'); //this.requestDataSend = true;

	      var query = (_query = {}, babelHelpers.defineProperty(_query, im_const.RestMethodHandler.mobileBrowserConstGet, [im_const.RestMethod.mobileBrowserConstGet, {}]), babelHelpers.defineProperty(_query, im_const.RestMethodHandler.imChatGet, [im_const.RestMethod.imChatGet, {
	        dialog_id: this.dialogId
	      }]), babelHelpers.defineProperty(_query, im_const.RestMethodHandler.imDialogMessagesGetInit, [im_const.RestMethod.imDialogMessagesGet, {
	        dialog_id: this.dialogId,
	        limit: this.$root.$bitrixController.application.getRequestMessageLimit(),
	        convert_text: 'Y'
	      }]), _query);

	      if (im_lib_utils.Utils.dialog.isChatId(this.dialogId)) {
	        query[im_const.RestMethodHandler.imUserGet] = [im_const.RestMethod.imUserGet, {}];
	      } else {
	        query[im_const.RestMethodHandler.imUserListGet] = [im_const.RestMethod.imUserListGet, {
	          id: [this.userId, this.dialogId]
	        }];
	      }

	      this.$root.$bitrixController.restClient.callBatch(query, function (response) {
	        if (!response) {
	          //this.requestDataSend = false;
	          //this.setError('EMPTY_RESPONSE', 'Server returned an empty response.');
	          return false;
	        }

	        var constGet = response[im_const.RestMethodHandler.mobileBrowserConstGet];

	        if (constGet.error()) ; else {
	          _this.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.mobileBrowserConstGet, constGet);
	        }

	        var userGet = response[im_const.RestMethodHandler.imUserGet];

	        if (userGet && !userGet.error()) {
	          _this.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imUserGet, userGet);
	        }

	        var userListGet = response[im_const.RestMethodHandler.imUserListGet];

	        if (userListGet && !userListGet.error()) {
	          _this.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imUserListGet, userListGet);
	        }

	        var chatGetResult = response[im_const.RestMethodHandler.imChatGet];

	        if (!chatGetResult.error()) {
	          _this.dialogChatId = chatGetResult.data().id;
	          _this.dialogDiskFolderId = chatGetResult.data().disk_folder_id;
	        } // TODO imChatGet


	        _this.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imChatGet, chatGetResult);

	        var dialogMessagesGetResult = response[im_const.RestMethodHandler.imDialogMessagesGetInit];

	        if (dialogMessagesGetResult.error()) ; else {
	          //this.timer.stop('data', 'load', true);
	          // this.$root.$bitrixController.getStore().dispatch('dialogues/saveDialog', {
	          // 	dialogId: this.$root.$bitrixController.application.getDialogId(),
	          // 	chatId: this.$root.$bitrixController.application.getChatId(),
	          // });
	          if (_this.$root.$bitrixController.pullCommandHandler) ;

	          _this.$root.$bitrixController.getStore().dispatch('application/set', {
	            dialog: {
	              enableReadMessages: true
	            }
	          }).then(function () {
	            _this.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGetInit, dialogMessagesGetResult);
	          }); //this.processSendMessages();

	        } //this.requestDataSend = false;

	      }, false, false, im_lib_utils.Utils.getLogTrackingParams({
	        name: 'im.dialog',
	        dialog: this.$root.$bitrixController.application.getDialogData()
	      }));
	      return new Promise(function (resolve, reject) {
	        return resolve();
	      });
	    },
	    getDialogHistory: function getDialogHistory(lastId) {
	      var _this2 = this;

	      var limit = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.$root.$bitrixController.application.getRequestMessageLimit();
	      this.$root.$bitrixController.restClient.callMethod(im_const.RestMethod.imDialogMessagesGet, {
	        'CHAT_ID': this.dialogChatId,
	        'LAST_ID': lastId,
	        'LIMIT': limit,
	        'CONVERT_TEXT': 'Y'
	      }).then(function (result) {
	        _this2.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGet, result);

	        _this2.$root.$emit(im_const.EventType.dialog.requestHistoryResult, {
	          count: result.data().messages.length
	        });
	      }).catch(function (result) {
	        _this2.$root.$emit(im_const.EventType.dialog.requestHistoryResult, {
	          error: result.error().ex
	        });
	      });
	    },
	    getDialogUnread: function getDialogUnread(lastId) {
	      var _this3 = this;

	      var limit = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : this.$root.$bitrixController.application.getRequestMessageLimit();

	      if (this.promiseGetDialogUnreadWait) {
	        return this.promiseGetDialogUnread;
	      }

	      this.promiseGetDialogUnread = new BX.Promise();
	      this.promiseGetDialogUnreadWait = true;

	      if (!lastId) {
	        lastId = this.$root.$bitrixController.getStore().getters['messages/getLastId'](this.dialogChatId);
	      }

	      if (!lastId) {
	        this.$root.$emit(im_const.EventType.dialog.requestUnreadResult, {
	          error: {
	            error: 'LAST_ID_EMPTY',
	            error_description: 'LastId is empty.'
	          }
	        });
	        this.promiseGetDialogUnread.reject();
	        this.promiseGetDialogUnreadWait = false;
	        return this.promiseGetDialogUnread;
	      }

	      this.$root.$bitrixController.application.readMessage(lastId, true, true).then(function () {
	        var _query2;

	        // this.timer.start('data', 'load', .5, () => {
	        // 	console.warn("ChatDialog.requestData: slow connection show progress icon");
	        // 	app.titleAction("setParams", {useProgress: true, useLetterImage: false});
	        // });
	        var query = (_query2 = {}, babelHelpers.defineProperty(_query2, im_const.RestMethodHandler.imDialogRead, [im_const.RestMethod.imDialogRead, {
	          dialog_id: _this3.dialogId,
	          message_id: lastId
	        }]), babelHelpers.defineProperty(_query2, im_const.RestMethodHandler.imChatGet, [im_const.RestMethod.imChatGet, {
	          dialog_id: _this3.dialogId
	        }]), babelHelpers.defineProperty(_query2, im_const.RestMethodHandler.imDialogMessagesGetUnread, [im_const.RestMethod.imDialogMessagesGet, {
	          chat_id: _this3.dialogChatId,
	          first_id: lastId,
	          limit: limit,
	          convert_text: 'Y'
	        }]), _query2);

	        _this3.$root.$bitrixController.restClient.callBatch(query, function (response) {
	          if (!response) {
	            _this3.$root.$emit(im_const.EventType.dialog.requestUnreadResult, {
	              error: {
	                error: 'EMPTY_RESPONSE',
	                error_description: 'Server returned an empty response.'
	              }
	            });

	            _this3.promiseGetDialogUnread.reject();

	            _this3.promiseGetDialogUnreadWait = false;
	            return false;
	          }

	          var chatGetResult = response[im_const.RestMethodHandler.imChatGet];

	          if (!chatGetResult.error()) {
	            _this3.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imChatGet, chatGetResult);
	          }

	          var dialogMessageUnread = response[im_const.RestMethodHandler.imDialogMessagesGetUnread];

	          if (dialogMessageUnread.error()) {
	            _this3.$root.$emit(im_const.EventType.dialog.requestUnreadResult, {
	              error: dialogMessageUnread.error().ex
	            });
	          } else {
	            _this3.$root.$bitrixController.executeRestAnswer(im_const.RestMethodHandler.imDialogMessagesGetUnread, dialogMessageUnread);

	            _this3.$root.$emit(im_const.EventType.dialog.requestUnreadResult, {
	              firstMessageId: dialogMessageUnread.data().messages.length > 0 ? dialogMessageUnread.data().messages[0].id : 0,
	              count: dialogMessageUnread.data().messages.length
	            }); //app.titleAction("setParams", {useProgress: false, useLetterImage: true});
	            //this.timer.stop('data', 'load', true);

	          }

	          _this3.promiseGetDialogUnread.fulfill(response);

	          _this3.promiseGetDialogUnreadWait = false;
	        }, false, false, im_lib_utils.Utils.getLogTrackingParams({
	          name: im_const.RestMethodHandler.imDialogMessagesGetUnread,
	          dialog: _this3.$root.$bitrixController.application.getDialogData()
	        }));
	      });
	      return this.promiseGetDialogUnread;
	    },
	    logEvent: function logEvent(name) {
	      for (var _len = arguments.length, params = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
	        params[_key - 1] = arguments[_key];
	      }

	      im_lib_logger.Logger.info.apply(im_lib_logger.Logger, [name].concat(params));
	    },
	    onDialogRequestHistory: function onDialogRequestHistory(event) {
	      this.getDialogHistory(event.lastId);
	    },
	    onDialogRequestUnread: function onDialogRequestUnread(event) {
	      this.getDialogUnread(event.lastId);
	    },
	    onDialogMessageClickByUserName: function onDialogMessageClickByUserName(event) {
	      this.$root.$bitrixController.application.replyToUser(event.user.id, event.user);
	    },
	    onDialogMessageClickByUploadCancel: function onDialogMessageClickByUploadCancel(event) {
	      this.$root.$bitrixController.application.cancelUploadFile(event.file.id);
	    },
	    onDialogMessageClickByCommand: function onDialogMessageClickByCommand(event) {
	      if (event.type === 'put') {
	        this.$root.$bitrixController.application.insertText({
	          text: event.value + ' '
	        });
	      } else if (event.type === 'send') {
	        this.$root.$bitrixController.application.addMessage(event.value);
	      } else {
	        im_lib_logger.Logger.warn('Unprocessed command', event);
	      }
	    },
	    onDialogMessageClickByMention: function onDialogMessageClickByMention(event) {
	      if (event.type === 'USER') {
	        this.$root.$bitrixController.application.openProfile(event.value);
	      } else if (event.type === 'CHAT') {
	        this.$root.$bitrixController.application.openDialog(event.value);
	      } else if (event.type === 'CALL') {
	        this.$root.$bitrixController.application.openPhoneMenu(event.value);
	      }
	    },
	    onDialogMessageMenuClick: function onDialogMessageMenuClick(event) {
	      im_lib_logger.Logger.warn('Message menu:', event);
	      this.$root.$bitrixController.application.openMessageMenu(event.message);
	    },
	    onDialogMessageRetryClick: function onDialogMessageRetryClick(event) {
	      im_lib_logger.Logger.warn('Message retry:', event);
	      this.$root.$bitrixController.application.retrySendMessage(event.message);
	    },
	    onDialogReadMessage: function onDialogReadMessage(event) {
	      this.$root.$bitrixController.application.readMessage(event.id);
	    },
	    onDialogReadedListClick: function onDialogReadedListClick(event) {
	      this.$root.$bitrixController.application.openReadedList(event.list);
	    },
	    onDialogQuoteMessage: function onDialogQuoteMessage(event) {
	      this.$root.$bitrixController.application.quoteMessage(event.message.id);
	    },
	    onDialogMessageReactionSet: function onDialogMessageReactionSet(event) {
	      this.$root.$bitrixController.application.reactMessage(event.message.id, event.reaction);
	    },
	    onDialogMessageReactionListOpen: function onDialogMessageReactionListOpen(event) {
	      this.$root.$bitrixController.application.openMessageReactionList(event.message.id, event.values);
	    },
	    onDialogMessageClickByKeyboardButton: function onDialogMessageClickByKeyboardButton(event) {
	      this.$root.$bitrixController.application.execMessageKeyboardCommand(event);
	    },
	    onDialogMessageClickByChatTeaser: function onDialogMessageClickByChatTeaser(event) {
	      this.$root.$bitrixController.application.execMessageOpenChatTeaser(event);
	    },
	    onDialogClick: function onDialogClick(event) {},
	    onQuotePanelClose: function onQuotePanelClose() {
	      this.$root.$bitrixController.quoteMessageClear();
	    }
	  },
	  template: "\n\t\t<div :class=\"widgetClassName\">\n\t\t\t<div :class=\"['bx-mobilechat-box', {'bx-mobilechat-box-dark-background': isDarkBackground}]\">\n\t\t\t\t<template v-if=\"application.error.active\">\n\t\t\t\t\t<div class=\"bx-mobilechat-body\">\n\t\t\t\t\t\t<div class=\"bx-mobilechat-warning-window\">\n\t\t\t\t\t\t\t<div class=\"bx-mobilechat-warning-icon\"></div>\n\t\t\t\t\t\t\t<template v-if=\"application.error.description\"> \n\t\t\t\t\t\t\t\t<div class=\"bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg\" v-html=\"application.error.description\"></div>\n\t\t\t\t\t\t\t</template> \n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<div class=\"bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-warning-msg\">{{localize.MOBILE_CHAT_ERROR_TITLE}}</div>\n\t\t\t\t\t\t\t\t<div class=\"bx-mobilechat-help-title bx-mobilechat-help-title-sm bx-mobilechat-warning-msg\">{{localize.MOBILE_CHAT_ERROR_DESC}}</div>\n\t\t\t\t\t\t\t</template> \n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\t\t\t\n\t\t\t\t<template v-else>\n\t\t\t\t\t<div :class=\"['bx-mobilechat-body', {'bx-mobilechat-body-with-message': dialogState == 'show'}]\" key=\"with-message\">\n\t\t\t\t\t\t<template v-if=\"dialogState == 'loading'\">\n\t\t\t\t\t\t\t<div class=\"bx-mobilechat-loading-window\">\n\t\t\t\t\t\t\t\t<svg class=\"bx-mobilechat-loading-circular\" viewBox=\"25 25 50 50\">\n\t\t\t\t\t\t\t\t\t<circle class=\"bx-mobilechat-loading-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t\t\t\t\t<circle class=\"bx-mobilechat-loading-inner-path\" cx=\"50\" cy=\"50\" r=\"20\" fill=\"none\" stroke-miterlimit=\"10\"/>\n\t\t\t\t\t\t\t\t</svg>\n\t\t\t\t\t\t\t\t<h3 class=\"bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-loading-msg\">{{localize.MOBILE_CHAT_LOADING}}</h3>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else-if=\"dialogState == 'empty'\">\n\t\t\t\t\t\t\t<div class=\"bx-mobilechat-loading-window\">\n\t\t\t\t\t\t\t\t<h3 class=\"bx-mobilechat-help-title bx-mobilechat-help-title-md bx-mobilechat-loading-msg\">{{localize.MOBILE_CHAT_EMPTY}}</h3>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t<div class=\"bx-mobilechat-dialog\">\n\t\t\t\t\t\t\t\t<bx-im-view-dialog\n\t\t\t\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t\t\t\t:chatId=\"dialogChatId\"\n\t\t\t\t\t\t\t\t\t:messageLimit=\"application.dialog.messageLimit\"\n\t\t\t\t\t\t\t\t\t:messageExtraCount=\"application.dialog.messageExtraCount\"\n\t\t\t\t\t\t\t\t\t:enableReadMessages=\"application.dialog.enableReadMessages\"\n\t\t\t\t\t\t\t\t\t:enableReactions=\"true\"\n\t\t\t\t\t\t\t\t\t:enableDateActions=\"false\"\n\t\t\t\t\t\t\t\t\t:enableCreateContent=\"false\"\n\t\t\t\t\t\t\t\t\t:enableGestureQuote=\"enableGestureQuote\"\n\t\t\t\t\t\t\t\t\t:enableGestureQuoteFromRight=\"enableGestureQuoteFromRight\"\n\t\t\t\t\t\t\t\t\t:enableGestureMenu=\"enableGestureMenu\"\n\t\t\t\t\t\t\t\t\t:showMessageUserName=\"showMessageUserName\"\n\t\t\t\t\t\t\t\t\t:showMessageAvatar=\"showMessageAvatar\"\n\t\t\t\t\t\t\t\t\t:showMessageMenu=\"false\"\n\t\t\t\t\t\t\t\t\t:listenEventScrollToBottom=\"scrollToBottomEvent\"\n\t\t\t\t\t\t\t\t\t:listenEventRequestHistory=\"listenEventRequestHistory\"\n\t\t\t\t\t\t\t\t\t:listenEventRequestUnread=\"listenEventRequestUnread\"\n\t\t\t\t\t\t\t\t\t:listenEventSendReadMessages=\"listenEventSendReadMessages\"\n\t\t\t\t\t\t\t\t\t@readMessage=\"onDialogReadMessage\"\n\t\t\t\t\t\t\t\t\t@quoteMessage=\"onDialogQuoteMessage\"\n\t\t\t\t\t\t\t\t\t@requestHistory=\"onDialogRequestHistory\"\n\t\t\t\t\t\t\t\t\t@requestUnread=\"onDialogRequestUnread\"\n\t\t\t\t\t\t\t\t\t@clickByCommand=\"onDialogMessageClickByCommand\"\n\t\t\t\t\t\t\t\t\t@clickByMention=\"onDialogMessageClickByMention\"\n\t\t\t\t\t\t\t\t\t@clickByUserName=\"onDialogMessageClickByUserName\"\n\t\t\t\t\t\t\t\t\t@clickByMessageMenu=\"onDialogMessageMenuClick\"\n\t\t\t\t\t\t\t\t\t@clickByMessageRetry=\"onDialogMessageRetryClick\"\n\t\t\t\t\t\t\t\t\t@clickByUploadCancel=\"onDialogMessageClickByUploadCancel\"\n\t\t\t\t\t\t\t\t\t@clickByReadedList=\"onDialogReadedListClick\"\n\t\t\t\t\t\t\t\t\t@setMessageReaction=\"onDialogMessageReactionSet\"\n\t\t\t\t\t\t\t\t\t@openMessageReactionList=\"onDialogMessageReactionListOpen\"\n\t\t\t\t\t\t\t\t\t@clickByKeyboardButton=\"onDialogMessageClickByKeyboardButton\"\n\t\t\t\t\t\t\t\t\t@clickByChatTeaser=\"onDialogMessageClickByChatTeaser\"\n\t\t\t\t\t\t\t\t\t@click=\"onDialogClick\"\n\t\t\t\t\t\t\t\t />\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t\t<bx-im-view-quote-panel :id=\"quotePanelData.id\" :title=\"quotePanelData.title\" :description=\"quotePanelData.description\" :color=\"quotePanelData.color\" @close=\"onQuotePanelClose\"/>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.BX.Messenger = this.BX.Messenger || {}),BX,BX,BX.Messenger.Lib,BX.Messenger.Const,BX.Messenger.Lib));
//# sourceMappingURL=dialog.bundle.js.map
