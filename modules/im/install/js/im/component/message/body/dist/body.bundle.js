(function (exports,im_component_element_media,im_component_element_attach,im_component_element_keyboard,im_component_element_chatteaser,ui_vue_components_reaction,ui_vue,ui_vue_vuex,im_model,im_const,im_utils) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Message Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	var BX = window.BX;

	var _ContentType = Object.freeze({
	  default: 'default',
	  progress: 'progress',
	  image: 'image',
	  audio: 'audio',
	  video: 'video',
	  richLink: 'richLink'
	});

	ui_vue.Vue.component('bx-messenger-message-body', {
	  /**
	   * @emits 'clickByUserName' {user: object, event: MouseEvent}
	   * @emits 'clickByUploadCancel' {file: object, event: MouseEvent}
	   * @emits 'clickByChatTeaser' {params: object, event: MouseEvent}
	   * @emits 'clickByKeyboardButton' {message: object, action: string, params: Object}
	   * @emits 'setReaction' {message: object, reaction: object}
	   * @emits 'openReactionList' {message: object, values: object}
	   */
	  props: {
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: '0'
	    },
	    chatId: {
	      default: 0
	    },
	    messageType: {
	      default: im_const.MessageType.self
	    },
	    message: {
	      type: Object,
	      default: im_model.MessagesModel.create().getElementState
	    },
	    user: {
	      type: Object,
	      default: im_model.UsersModel.create().getElementState
	    },
	    dialog: {
	      type: Object,
	      default: im_model.DialoguesModel.create().getElementState
	    },
	    files: {
	      type: Object,
	      default: {}
	    },
	    enableReactions: {
	      default: true
	    },
	    showName: {
	      default: true
	    },
	    showAvatar: {
	      default: true
	    },
	    referenceContentBodyClassName: {
	      default: ''
	    },
	    referenceContentNameClassName: {
	      default: ''
	    }
	  },
	  created: function created() {
	    this.dateFormatFunction = null;
	    this.cacheFormatDate = {};
	  },
	  methods: {
	    clickByUserName: function clickByUserName(event) {
	      this.$emit('clickByUserName', event);
	    },
	    clickByUploadCancel: function clickByUploadCancel(event) {
	      this.$emit('clickByUploadCancel', event);
	    },
	    clickByChatTeaser: function clickByChatTeaser(event) {
	      this.$emit('clickByChatTeaser', {
	        message: event.message,
	        event: event
	      });
	    },
	    clickByKeyboardButton: function clickByKeyboardButton(event) {
	      this.$emit('clickByKeyboardButton', babelHelpers.objectSpread({
	        message: event.message
	      }, event.event));
	    },
	    setReaction: function setReaction(event) {
	      this.$emit('setReaction', event);
	    },
	    openReactionList: function openReactionList(event) {
	      this.$emit('openReactionList', event);
	    },
	    formatDate: function formatDate(date) {
	      var id = date.toJSON().slice(0, 10);

	      if (this.cacheFormatDate[id]) {
	        return this.cacheFormatDate[id];
	      }

	      var dateFormat = im_utils.Utils.date.getFormatType(BX.Messenger.Const.DateFormat.message, this.$root.$bitrixMessages);
	      this.cacheFormatDate[id] = this._getDateFormat().format(dateFormat, date);
	      return this.cacheFormatDate[id];
	    },
	    _getDateFormat: function _getDateFormat() {
	      var _this = this;

	      if (this.dateFormatFunction) {
	        return this.dateFormatFunction;
	      }

	      this.dateFormatFunction = Object.create(BX.Main.Date);

	      if (this.$root.$bitrixMessages) {
	        this.dateFormatFunction._getMessage = function (phrase) {
	          return _this.$root.$bitrixMessages[phrase];
	        };
	      }

	      return this.dateFormatFunction;
	    }
	  },
	  computed: babelHelpers.objectSpread({
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
	        var _iteratorNormalCompletion = true;
	        var _didIteratorError = false;
	        var _iteratorError = undefined;

	        try {
	          for (var _iterator = this.filesData[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
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
	          _didIteratorError = true;
	          _iteratorError = err;
	        } finally {
	          try {
	            if (!_iteratorNormalCompletion && _iterator.return != null) {
	              _iterator.return();
	            }
	          } finally {
	            if (_didIteratorError) {
	              throw _iteratorError;
	            }
	          }
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

	      return _ContentType.default;
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_MESSAGE_', this.$root.$bitrixMessages);
	    },
	    formattedDate: function formattedDate() {
	      return this.formatDate(this.message.date);
	    },
	    messageText: function messageText() {
	      if (this.isDeleted) {
	        return this.localize.IM_MESSENGER_MESSAGE_DELETED;
	      }

	      return this.message.textConverted;
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
	      return this.dialog.type !== im_const.DialogType.private ? this.dialog.color : this.user.color;
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
	        return this.message.params.NAME;
	      }

	      if (!this.showAvatar) {
	        return this.user.name;
	      } else {
	        return this.user.firstName ? this.user.firstName : this.user.name;
	      }
	    }
	  }, ui_vue_vuex.Vuex.mapState({
	    application: function application(state) {
	      return state.application;
	    }
	  })),
	  template: "\n\t\t<div class=\"bx-im-message-content-wrap\">\n\t\t\t<template v-if=\"contentType == ContentType.default || contentType == ContentType.audio || contentType == ContentType.progress\">\n\t\t\t\t<div class=\"bx-im-message-content\">\n\t\t\t\t\t<span class=\"bx-im-message-content-box\">\n\t\t\t\t\t\t<template v-if=\"showName && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-name', referenceContentNameClassName]\" :style=\"{color: user.color}\" @click=\"clickByUserName({user: user, event: $event})\">{{userName}}</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body', referenceContentBodyClassName]\">\n\t\t\t\t\t\t\t<template v-if=\"contentType == ContentType.audio\">\n\t\t\t\t\t\t\t\t<bx-messenger-element-file-audio v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\" @uploadCancel=\"clickByUploadCancel\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else>\n\t\t\t\t\t\t\t\t<bx-messenger-element-file v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\" @uploadCancel=\"clickByUploadCancel\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body-wrap', {\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-with-text': messageText.length > 0,\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-without-text': messageText.length <= 0,\n\t\t\t\t\t\t\t}]\">\n\t\t\t\t\t\t\t\t<template v-if=\"messageText\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-text\" v-html=\"messageText\"></span>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<template v-for=\"(config, id) in messageAttach\">\n\t\t\t\t\t\t\t\t\t<bx-messenger-element-attach :baseColor=\"chatColor\" :config=\"config\" :key=\"id\"/>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-params\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-date\">{{formattedDate}}</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div v-if=\"!message.push && enableReactions && message.authorId\" class=\"bx-im-message-content-reaction\">\n\t\t\t\t\t\t<bx-reaction :values=\"messageReactions\" :userId=\"userId\" :openList=\"false\" @set=\"setReaction({message: message, reaction: $event})\" @list=\"openReactionList({message: message, values: $event.values})\"/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-else-if=\"contentType == ContentType.richLink\">\n\t\t\t\t<!-- richLink type markup -->\n\t\t\t</template>\n\t\t\t<template v-else-if=\"contentType == ContentType.image || contentType == ContentType.video\">\n\t\t\t\t<div class=\"bx-im-message-content bx-im-message-content-fit\">\n\t\t\t\t\t<span class=\"bx-im-message-content-box\">\n\t\t\t\t\t\t<template v-if=\"showName && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-name', referenceContentNameClassName]\" :style=\"{color: user.color}\" @click=\"clickByUserName({user: user, event: $event})\">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body', referenceContentBodyClassName]\">\n\t\t\t\t\t\t\t<template v-if=\"contentType == ContentType.image\">\n\t\t\t\t\t\t\t\t<bx-messenger-element-file-image v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\" @uploadCancel=\"clickByUploadCancel\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else-if=\"contentType == ContentType.video\">\n\t\t\t\t\t\t\t\t<bx-messenger-element-file-video v-for=\"file in filesData\" :messageType=\"messageType\" :file=\"file\" :key=\"file.templateId\" @uploadCancel=\"clickByUploadCancel\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body-wrap', {\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-with-text': messageText.length > 0,\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-without-text': messageText.length <= 0,\n\t\t\t\t\t\t\t}]\">\n\t\t\t\t\t\t\t\t<template v-if=\"messageText\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-text\" v-html=\"messageText\"></span>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-params\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-date\">{{formattedDate}}</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</span>\n\t\t\t\t\t<div v-if=\"!message.push && enableReactions && message.authorId\" class=\"bx-im-message-content-reaction\">\n\t\t\t\t\t\t<bx-reaction :values=\"messageReactions\" :userId=\"userId\" :openList=\"false\" @set=\"setReaction({message: message, reaction: $event})\" @list=\"openReactionList({message: message, values: $event.values})\"/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<template v-if=\"keyboardButtons\">\n\t\t\t\t<bx-messenger-element-keyboard :buttons=\"keyboardButtons\" :messageId=\"message.id\" :userId=\"userId\" :dialogId=\"dialogId\" @click=\"clickByKeyboardButton({message: message, event: $event})\"/>\n\t\t\t</template>\n\t\t\t<template v-if=\"chatTeaser\">\n\t\t\t\t<bx-messenger-element-chat-teaser :messageCounter=\"chatTeaser.messageCounter\" :messageLastDate=\"chatTeaser.messageLastDate\" :languageId=\"chatTeaser.languageId\" @click=\"clickByChatTeaser({message: message, event: $event})\"/>\n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),window,window,window,window,window,BX,BX,BX.Messenger.Model,BX.Messenger.Const,BX.Messenger));
//# sourceMappingURL=body.bundle.js.map
