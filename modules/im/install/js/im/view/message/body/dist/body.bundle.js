(function (exports,ui_designTokens,im_view_element_media,im_view_element_attach,im_view_element_keyboard,im_view_element_chatteaser,ui_vue_components_reaction,ui_vue,ui_vue_vuex,im_model,im_const,im_lib_utils,main_core,main_core_events) {
	'use strict';

	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var BX = window.BX;
	var _ContentType = Object.freeze({
	  "default": 'default',
	  progress: 'progress',
	  image: 'image',
	  audio: 'audio',
	  video: 'video',
	  richLink: 'richLink'
	});
	ui_vue.BitrixVue.component('bx-im-view-message-body', {
	  /**
	   * @emits EventType.dialog.clickOnChatTeaser {message: object, event: MouseEvent}
	   * @emits EventType.dialog.clickOnKeyboardButton {message: object, action: string, params: Object}
	   * @emits EventType.dialog.setMessageReaction {message: object, reaction: object}
	   * @emits EventType.dialog.openMessageReactionList {message: object, values: object}
	   * @emits EventType.dialog.clickOnUserName {user: object, event: MouseEvent}
	   */
	  props: {
	    userId: {
	      "default": 0
	    },
	    dialogId: {
	      "default": '0'
	    },
	    chatId: {
	      "default": 0
	    },
	    messageType: {
	      "default": im_const.MessageType.self
	    },
	    message: {
	      type: Object,
	      "default": im_model.MessagesModel.create().getElementState
	    },
	    enableReactions: {
	      "default": true
	    },
	    showName: {
	      "default": true
	    },
	    showAvatar: {
	      "default": true
	    },
	    referenceContentBodyClassName: {
	      "default": ''
	    },
	    referenceContentNameClassName: {
	      "default": ''
	    }
	  },
	  created: function created() {
	    this.dateFormatFunction = null;
	    this.cacheFormatDate = {};
	  },
	  methods: {
	    clickByUserName: function clickByUserName(event) {
	      if (this.showAvatar && im_lib_utils.Utils.platform.isMobile()) {
	        return false;
	      }
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnUserName, event);
	    },
	    clickByChatTeaser: function clickByChatTeaser(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnChatTeaser, {
	        message: event.message,
	        event: event.event
	      });
	    },
	    clickByKeyboardButton: function clickByKeyboardButton(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.clickOnKeyboardButton, _objectSpread({
	        message: event.message
	      }, event.event));
	    },
	    setReaction: function setReaction(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.setMessageReaction, event);
	    },
	    openReactionList: function openReactionList(event) {
	      main_core_events.EventEmitter.emit(im_const.EventType.dialog.openMessageReactionList, event);
	    },
	    formatDate: function formatDate(date) {
	      var id = date.toJSON().slice(0, 10);
	      if (this.cacheFormatDate[id]) {
	        return this.cacheFormatDate[id];
	      }
	      var dateFormat = im_lib_utils.Utils.date.getFormatType(BX.Messenger.Const.DateFormat.message, this.$Bitrix.Loc.getMessages());
	      this.cacheFormatDate[id] = this._getDateFormat().format(dateFormat, date);
	      return this.cacheFormatDate[id];
	    },
	    _getDateFormat: function _getDateFormat() {
	      var _this = this;
	      if (this.dateFormatFunction) {
	        return this.dateFormatFunction;
	      }
	      this.dateFormatFunction = Object.create(BX.Main.Date);
	      this.dateFormatFunction._getMessage = function (phrase) {
	        return _this.$Bitrix.Loc.getMessage(phrase);
	      };
	      return this.dateFormatFunction;
	    },
	    isDesktop: function isDesktop() {
	      return im_lib_utils.Utils.platform.isBitrixDesktop();
	    },
	    getDesktopVersion: function getDesktopVersion() {
	      return im_lib_utils.Utils.platform.getDesktopVersion();
	    },
	    isMobile: function isMobile() {
	      return im_lib_utils.Utils.platform.isBitrixMobile();
	    }
	  },
	  computed: _objectSpread({
	    MessageType: function MessageType() {
	      return im_const.MessageType;
	    },
	    ContentType: function ContentType() {
	      return _ContentType;
	    },
	    contentType: function contentType() {
	      if (this.filesData.length > 0) {
	        var onlyImage = false;
	        var onlyVideo = false;
	        var onlyAudio = false;
	        var inProgress = false;
	        var _iterator = _createForOfIteratorHelper(this.filesData),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var file = _step.value;
	            if (file.progress < 0) {
	              inProgress = true;
	              break;
	            } else if (file.type === 'audio') {
	              if (onlyVideo || onlyImage) {
	                onlyImage = false;
	                onlyVideo = false;
	                break;
	              }
	              onlyAudio = true;
	            } else if (file.type === 'image' && file.image) {
	              if (onlyVideo || onlyAudio) {
	                onlyAudio = false;
	                onlyVideo = false;
	                break;
	              }
	              onlyImage = true;
	            } else if (file.type === 'video') {
	              if (onlyImage || onlyAudio) {
	                onlyAudio = false;
	                onlyImage = false;
	                break;
	              }
	              onlyVideo = true;
	            } else {
	              onlyAudio = false;
	              onlyImage = false;
	              onlyVideo = false;
	              break;
	            }
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	        if (inProgress) {
	          return _ContentType.progress;
	        } else if (onlyImage) {
	          return _ContentType.image;
	        } else if (onlyAudio) {
	          return _ContentType.audio;
	        } else if (onlyVideo) {
	          return _ContentType.video;
	        }
	      }
	      return _ContentType["default"];
	    },
	    formattedDate: function formattedDate() {
	      return this.formatDate(this.message.date);
	    },
	    messageText: function messageText() {
	      if (this.isDeleted) {
	        return this.$Bitrix.Loc.getMessage('IM_MESSENGER_MESSAGE_DELETED');
	      }
	      var message = this.message.textConverted ? this.message.textConverted : im_lib_utils.Utils.text.decode(this.message.text);
	      var messageParams = this.message.params;
	      if (typeof messageParams.LINK_ACTIVE !== 'undefined' && messageParams.LINK_ACTIVE.length > 0 && !messageParams.LINK_ACTIVE.includes(this.userId)) {
	        message = message.replace(/<a.*?href="([^"]*)".*?>(.*?)<\/a>/gi, '$2');
	      }
	      return message;
	    },
	    messageAttach: function messageAttach() {
	      return this.message.params.ATTACH;
	    },
	    messageReactions: function messageReactions() {
	      return this.message.params.REACTION || {};
	    },
	    isEdited: function isEdited() {
	      return this.message.params.IS_EDITED === 'Y';
	    },
	    isDeleted: function isDeleted() {
	      return this.message.params.IS_DELETED === 'Y';
	    },
	    chatColor: function chatColor() {
	      return this.dialog.type !== im_const.DialogType["private"] ? this.dialog.color : this.user.color;
	    },
	    dialog: function dialog() {
	      var dialog = this.$store.getters['dialogues/get'](this.dialogId);
	      return dialog ? dialog : this.$store.getters['dialogues/getBlank']();
	    },
	    user: function user() {
	      return this.$store.getters['users/get'](this.message.authorId, true);
	    },
	    filesData: function filesData() {
	      var _this2 = this;
	      var files = [];
	      if (!this.message.params.FILE_ID || this.message.params.FILE_ID.length <= 0) {
	        return files;
	      }
	      this.message.params.FILE_ID.forEach(function (fileId) {
	        if (!fileId) {
	          return false;
	        }
	        var file = _this2.$store.getters['files/get'](_this2.chatId, fileId, true);
	        if (!file) {
	          _this2.$store.commit('files/set', {
	            data: [_this2.$store.getters['files/getBlank']({
	              id: fileId,
	              chatId: _this2.chatId
	            })]
	          });
	          file = _this2.$store.getters['files/get'](_this2.chatId, fileId, true);
	        }
	        if (file) {
	          files.push(file);
	        }
	      });
	      return files;
	    },
	    keyboardButtons: function keyboardButtons() {
	      var result = false;
	      if (!this.message.params.KEYBOARD || this.message.params.KEYBOARD === 'N') {
	        return result;
	      }
	      return this.message.params.KEYBOARD;
	    },
	    chatTeaser: function chatTeaser() {
	      if (typeof this.message.params.CHAT_ID === 'undefined' || typeof this.message.params.CHAT_LAST_DATE === 'undefined' || typeof this.message.params.CHAT_MESSAGE === 'undefined') {
	        return false;
	      }
	      return {
	        messageCounter: this.message.params.CHAT_MESSAGE,
	        messageLastDate: this.message.params.CHAT_LAST_DATE,
	        languageId: this.application.common.languageId
	      };
	    },
	    userName: function userName() {
	      if (this.message.params.NAME) {
	        return main_core.Text.decode(this.message.params.NAME);
	      }
	      if (!this.showAvatar) {
	        return this.user.name;
	      } else {
	        return this.user.firstName ? this.user.firstName : this.user.name;
	      }
	    },
	    userColor: function userColor() {
	      if (this.user.extranet) {
	        return "#CA7B00";
	      }
	      return this.user.color;
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  // language=Vue
	  template: "\n\t\t<div class=\"bx-im-message-content-wrap\">\n\t\t\t<template v-if=\"contentType == ContentType.default || contentType == ContentType.audio || contentType == ContentType.progress || (contentType !== ContentType.image && isDesktop() && getDesktopVersion() < 47)\">\n\t\t\t\t<div class=\"bx-im-message-content\">\n\t\t\t\t\t<span class=\"bx-im-message-content-box\">\n\t\t\t\t\t\t<div class=\"bx-im-message-content-name-wrap\">\n\t\t\t\t\t\t\t<template v-if=\"showName && user.extranet && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t\t<div class=\"bx-im-message-extranet-icon\"></div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-if=\"showName && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-name', referenceContentNameClassName]\" :style=\"{color: userColor}\" @click=\"clickByUserName({user: user, event: $event})\">{{userName}}</div>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body', referenceContentBodyClassName]\">\n\t\t\t\t\t\t\t<template v-if=\"(contentType == ContentType.audio) && (!isDesktop() || (isDesktop() && getDesktopVersion() > 43))\">\n\t\t\t\t\t\t\t\t<bx-im-view-element-file-audio v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<bx-im-view-element-file v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body-wrap', {\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-with-text': messageText.length > 0,\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-without-text': messageText.length <= 0,\n\t\t\t\t\t\t\t}]\">\n\t\t\t\t\t\t\t\t<template v-if=\"messageText\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-text\" v-html=\"messageText\"></span>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-for=\"(config, id) in messageAttach\">\n\t\t\t\t\t\t\t\t\t<bx-im-view-element-attach :baseColor=\"chatColor\" :config=\"config\" :key=\"id\"/>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-params\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-date\">{{formattedDate}}</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div v-if=\"!message.push && enableReactions && message.authorId\" class=\"bx-im-message-content-reaction\">\n\t\t\t\t\t\t<bx-reaction :id=\"'message'+message.id\" :values=\"messageReactions\" :userId=\"userId\" :openList=\"false\" @set=\"setReaction({message: message, reaction: $event})\" @list=\"openReactionList({message: message, values: $event.values})\"/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"contentType == ContentType.richLink\">\n\t\t\t\t<!-- richLink type markup -->\n\t\t\t</template>\n\t\t\t<template v-else-if=\"contentType == ContentType.image || contentType == ContentType.video\">\n\t\t\t\t<div class=\"bx-im-message-content bx-im-message-content-fit\">\n\t\t\t\t\t<span class=\"bx-im-message-content-box\">\n\t\t\t\t\t\t<template v-if=\"showName && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-name', referenceContentNameClassName]\" :style=\"{color: user.color}\" @click=\"clickByUserName({user: user, event: $event})\">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body', referenceContentBodyClassName]\">\n\t\t\t\t\t\t\t<template v-if=\"contentType == ContentType.image\">\n\t\t\t\t\t\t\t\t<bx-im-view-element-file-image v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else-if=\"contentType == ContentType.video\">\n\t\t\t\t\t\t\t\t<bx-im-view-element-file-video v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body-wrap', {\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-with-text': messageText.length > 0,\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-without-text': messageText.length <= 0,\n\t\t\t\t\t\t\t}]\">\n\t\t\t\t\t\t\t\t<template v-if=\"messageText\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-text\" v-html=\"messageText\"></span>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-params\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-date\">{{formattedDate}}</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div v-if=\"!message.push && enableReactions && message.authorId\" class=\"bx-im-message-content-reaction\">\n\t\t\t\t\t\t<bx-reaction :id=\"'message'+message.id\" :values=\"messageReactions\" :userId=\"userId\" :openList=\"false\" @set=\"setReaction({message: message, reaction: $event})\" @list=\"openReactionList({message: message, values: $event.values})\"/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"keyboardButtons\">\n\t\t\t\t<bx-im-view-element-keyboard :buttons=\"keyboardButtons\" :messageId=\"message.id\" :userId=\"userId\" :dialogId=\"dialogId\" @click=\"clickByKeyboardButton({message: message, event: $event})\"/>\n\t\t\t</template>\n\t\t\t<template v-if=\"chatTeaser\">\n\t\t\t\t<bx-im-view-element-chat-teaser :messageCounter=\"chatTeaser.messageCounter\" :messageLastDate=\"chatTeaser.messageLastDate\" :languageId=\"chatTeaser.languageId\" @click=\"clickByChatTeaser({message: message, event: $event})\"/>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,window,window,window,window,window,BX,BX,BX.Messenger.Model,BX.Messenger.Const,BX.Messenger.Lib,BX,BX.Event));
//# sourceMappingURL=body.bundle.js.map
