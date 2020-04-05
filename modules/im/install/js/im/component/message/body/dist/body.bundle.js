(function (exports,ui_vue,im_model) {
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

	var _MessageType = Object.freeze({
	  self: 'self',
	  opponent: 'opponent',
	  system: 'system'
	});

	var _ContentType = Object.freeze({
	  default: 'default',
	  image: 'image',
	  video: 'video',
	  richLink: 'richLink'
	});

	ui_vue.Vue.component('bx-messenger-message-body', {
	  /**
	   * @emits 'clickByUserName' {user: object, event: MouseEvent}
	   */
	  props: {
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: 0
	    },
	    chatId: {
	      default: 0
	    },
	    messageType: {
	      default: _MessageType.self
	    },
	    message: {
	      type: Object,
	      default: im_model.MessagesModel.create().getElementStore
	    },
	    user: {
	      type: Object,
	      default: im_model.UsersModel.create().getElementStore
	    },
	    files: {
	      type: Object,
	      default: {}
	    },
	    enableEmotions: {
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
	    }
	  },
	  created: function created() {
	    this.dateFormatFunction = null;
	    this.cacheFormatDate = {};
	  },
	  methods: {
	    clickByUserName: function clickByUserName(user, event) {
	      this.$emit('clickByUserName', {
	        user: user,
	        event: event
	      });
	    },
	    formatDate: function formatDate(date) {
	      var id = date.toJSON().slice(0, 10);

	      if (this.cacheFormatDate[id]) {
	        return this.cacheFormatDate[id];
	      }

	      var dateFormat = BX.Messenger.Utils.getDateFormatType(BX.Messenger.Const.DateFormat.message, this.$root.$bitrixMessages);
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
	  computed: {
	    MessageType: function MessageType() {
	      return _MessageType;
	    },
	    ContentType: function ContentType() {
	      return _ContentType;
	    },
	    contentType: function contentType() {
	      if (this.filesData.length > 0) {
	        var onlyImage = false;
	        var onlyVideo = false;
	        var _iteratorNormalCompletion = true;
	        var _didIteratorError = false;
	        var _iteratorError = undefined;

	        try {
	          for (var _iterator = this.filesData[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
	            var file = _step.value;

	            if (file.type == 'image') {
	              if (onlyVideo) {
	                onlyVideo = false;
	                break;
	              }

	              onlyImage = true;
	            } else {
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

	        if (onlyImage) {
	          return _ContentType.image;
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
	    isEdited: function isEdited() {
	      return this.message.params.IS_EDITED == 'Y';
	    },
	    isDeleted: function isDeleted() {
	      return this.message.params.IS_DELETED == 'Y';
	    },
	    filesData: function filesData() {
	      var _this2 = this;

	      var files = [];

	      if (!this.message.params.FILE_ID || this.message.params.FILE_ID.length <= 0) {
	        return files;
	      }

	      this.message.params.FILE_ID.forEach(function (fileId) {
	        if (_this2.files[fileId]) {
	          files.push(_this2.files[fileId]);
	        }
	      });
	      return files;
	    }
	  },
	  template: "\n\t\t<div class=\"bx-im-message-content-wrap\">\n\t\t\t<template v-if=\"contentType == ContentType.default\">\n\t\t\t\t<div class=\"bx-im-message-content\">\n\t\t\t\t\t<span class=\"bx-im-message-content-box\">\n\t\t\t\t\t\t<template v-if=\"showName && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t<div class=\"bx-im-message-content-name\" :style=\"{color: user.color}\" @click=\"clickByUserName(user, $event)\">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body', referenceContentBodyClassName]\">\n\t\t\t\t\t\t\t<template v-for=\"file in filesData\">\n\t\t\t\t\t\t\t\t<bx-messenger-element-file :file=\"file\"/>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body-wrap', {\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-with-text': messageText.length > 0,\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-without-text': messageText.length <= 0,\n\t\t\t\t\t\t\t}]\">\n\t\t\t\t\t\t\t\t<template v-if=\"messageText\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-text\" v-html=\"messageText\"></span>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-params\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-date\">{{formattedDate}}</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t\t<!-- keyboard -->\n\t\t\t</template>\n\t\t\t<template v-else-if=\"contentType == ContentType.richLink\">\n\t\t\t\t<!-- richLink type markup -->\n\t\t\t</template>\n\t\t\t<template v-else-if=\"contentType == ContentType.image || contentType == ContentType.video\">\n\t\t\t\t<div class=\"bx-im-message-content bx-im-message-content-fit\">\n\t\t\t\t\t<span class=\"bx-im-message-content-box\">\n\t\t\t\t\t\t<template v-if=\"showName && messageType == MessageType.opponent\">\n\t\t\t\t\t\t\t<div class=\"bx-im-message-content-name\" :style=\"{color: user.color}\" @click=\"clickByUserName(user, $event)\">{{!showAvatar? user.name: (user.firstName? user.firstName: user.name)}}</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body', referenceContentBodyClassName]\">\n\t\t\t\t\t\t\t<template v-if=\"contentType == ContentType.image\">\n\t\t\t\t\t\t\t\t<template v-for=\"file in filesData\">\n\t\t\t\t\t\t\t\t\t<bx-messenger-element-file-image :file=\"file\"/>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<template v-else-if=\"contentType == ContentType.video\">\n\t\t\t\t\t\t\t\t<template v-for=\"file in filesData\">\n\t\t\t\t\t\t\t\t\t<bx-messenger-element-file-video :file=\"file\"/>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t<div :class=\"['bx-im-message-content-body-wrap', {\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-with-text': messageText.length > 0,\n\t\t\t\t\t\t\t\t'bx-im-message-content-body-without-text': messageText.length <= 0,\n\t\t\t\t\t\t\t}]\">\n\t\t\t\t\t\t\t\t<template v-if=\"messageText\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-text\" v-html=\"messageText\"></span>\n\t\t\t\t\t\t\t\t</template>\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-params\">\n\t\t\t\t\t\t\t\t\t<span class=\"bx-im-message-content-date\">{{formattedDate}}</span>\n\t\t\t\t\t\t\t\t</span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t\t<!-- keyboard -->\n\t\t\t</template>\n\t\t</div>\n\t"
	});
	/*
		<span class="bx-messenger-content-item-like bx-messenger-content-like-digit-off">
			<span>&nbsp;</span>
			<span class="bx-messenger-content-like-digit"></span>
			<span data-messageid="28571160" class="bx-messenger-content-like-button">{{localize.IM_MESSENGER_MESSAGE_LIKE}}</span>
		</span>
	 */

}((this.window = this.window || {}),BX,BX.Messenger.Model));
//# sourceMappingURL=body.bundle.js.map
