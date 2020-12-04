(function (exports,im_component_message_body,im_model,ui_vue,im_const,im_utils,im_tools_animation) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Message Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-messenger-message', {
	  /**
	   * @emits 'clickByUserName' {user: object, event: MouseEvent}
	   * @emits 'clickByUploadCancel' {file: object, event: MouseEvent}
	   * @emits 'clickByKeyboardButton' {message: object, action: string, params: Object}
	   * @emits 'clickByChatTeaser' {message: object, event: MouseEvent}
	   * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	   * @emits 'clickByMessageRetry' {message: object, event: MouseEvent}
	   * @emits 'setMessageReaction' {message: object, reaction: object}
	   * @emits 'openMessageReactionList' {message: object, values: object}
	   * @emits 'dragMessage' {result: boolean, event: MouseEvent}
	   * @emits 'quoteMessage' {message: object}
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
	    enableReactions: {
	      default: true
	    },
	    enableDateActions: {
	      default: true
	    },
	    enableCreateContent: {
	      default: true
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
	    showAvatar: {
	      default: true
	    },
	    showMenu: {
	      default: true
	    },
	    showName: {
	      default: true
	    },
	    showLargeFont: {
	      default: true
	    },
	    capturedMoveEvent: {
	      default: null
	    },
	    referenceContentClassName: {
	      default: ''
	    },
	    referenceContentBodyClassName: {
	      default: ''
	    },
	    referenceContentNameClassName: {
	      default: ''
	    },
	    dialog: {
	      type: Object,
	      default: im_model.DialoguesModel.create().getElementState
	    },
	    message: {
	      type: Object,
	      default: im_model.MessagesModel.create().getElementState
	    }
	  },
	  data: function data() {
	    return {
	      componentBodyId: 'bx-messenger-message-body',
	      drag: false,
	      dragWidth: 0,
	      dragPosition: 0,
	      dragIconShowLeft: false,
	      dragIconShowRight: false
	    };
	  },
	  created: function created() {
	    this.dragStartPositionX = 0;
	    this.dragStartPositionY = 0;
	    this.dragMovePositionX = 0;
	    this.dragMovePositionY = 0;
	  },
	  beforeDestroy: function beforeDestroy() {
	    clearTimeout(this.dragStartTimeout1);
	    clearTimeout(this.dragStartTimeout2);

	    if (this.dragBackAnimation) {
	      im_tools_animation.Animation.cancel(this.dragBackAnimation);
	    }
	  },
	  methods: {
	    clickByAvatar: function clickByAvatar(event) {
	      this.$emit('clickByUserName', event);
	    },
	    clickByUserName: function clickByUserName(event) {
	      if (this.showAvatar && im_utils.Utils.platform.isMobile()) {
	        return false;
	      }

	      this.$emit('clickByUserName', event);
	    },
	    clickByUploadCancel: function clickByUploadCancel(event) {
	      this.$emit('clickByUploadCancel', event);
	    },
	    clickByKeyboardButton: function clickByKeyboardButton(event) {
	      this.$emit('clickByKeyboardButton', event);
	    },
	    clickByChatTeaser: function clickByChatTeaser(event) {
	      this.$emit('clickByChatTeaser', event);
	    },
	    clickByMessageMenu: function clickByMessageMenu(event) {
	      this.$emit('clickByMessageMenu', event);
	    },
	    clickByMessageRetry: function clickByMessageRetry(event) {
	      this.$emit('clickByMessageRetry', event);
	    },
	    setMessageReaction: function setMessageReaction(event) {
	      this.$emit('setMessageReaction', event);
	    },
	    openMessageReactionList: function openMessageReactionList(event) {
	      this.$emit('openMessageReactionList', event);
	    },
	    gestureRouter: function gestureRouter(eventName, event) {
	      this.gestureQuote(eventName, event);
	      this.gestureMenu(eventName, event);
	    },
	    gestureMenu: function gestureMenu(eventName, event) {
	      var _this = this;

	      if (!this.enableGestureMenu) {
	        return;
	      }

	      if (eventName === 'touchstart') {
	        this.gestureMenuStarted = true;
	        this.gestureMenuPreventTouchEnd = false;

	        if (event.target.tagName === "A") {
	          return false;
	        }

	        this.gestureMenuStartPosition = {
	          x: event.changedTouches[0].clientX,
	          y: event.changedTouches[0].clientY
	        };
	        this.gestureMenuTimeout = setTimeout(function () {
	          _this.gestureMenuPreventTouchEnd = true;

	          _this.$emit('clickByMessageMenu', {
	            message: _this.message,
	            event: event
	          });
	        }, 500);
	      } else if (eventName === 'touchmove') {
	        if (!this.gestureMenuStarted) {
	          return false;
	        }

	        if (Math.abs(this.gestureMenuStartPosition.x - event.changedTouches[0].clientX) >= 10 || Math.abs(this.gestureMenuStartPosition.y - event.changedTouches[0].clientY) >= 10) {
	          this.gestureMenuStarted = false;
	          clearTimeout(this.gestureMenuTimeout);
	        }
	      } else if (eventName === 'touchend') {
	        if (!this.gestureMenuStarted) {
	          return false;
	        }

	        this.gestureMenuStarted = false;
	        clearTimeout(this.gestureMenuTimeout);

	        if (this.gestureMenuPreventTouchEnd) {
	          event.preventDefault();
	        }
	      }
	    },
	    gestureQuote: function gestureQuote(eventName, event) {
	      var _this2 = this;

	      var target = im_utils.Utils.browser.findParent(event.target, 'bx-im-message') || event.target;

	      if (!this.enableGestureQuote || im_utils.Utils.platform.isAndroid()) {
	        return;
	      }

	      var fromRight = this.enableGestureQuoteFromRight;
	      var layerX = target.getBoundingClientRect().left + event.layerX;
	      var layerY = target.getBoundingClientRect().top + event.layerY;

	      if (eventName === 'touchstart') {
	        this.dragCheck = true;
	        this.dragStartInitialX = target.getBoundingClientRect().left;
	        this.dragStartInitialY = target.getBoundingClientRect().top;
	        this.dragStartPositionX = layerX;
	        this.dragStartPositionY = layerY;
	        this.dragMovePositionX = null;
	        this.dragMovePositionY = null;
	        clearTimeout(this.dragStartTimeout1);
	        clearTimeout(this.dragStartTimeout2);
	        this.dragStartTimeout1 = setTimeout(function () {
	          if (_this2.dragMovePositionX !== null) {
	            if (Math.abs(_this2.dragStartPositionY - _this2.dragMovePositionY) >= 10) {
	              _this2.dragCheck = false;
	            }
	          }
	        }, 29);
	        this.dragStartTimeout2 = setTimeout(function () {
	          _this2.dragCheck = false;

	          if (Math.abs(_this2.dragStartPositionY - _this2.dragMovePositionY) >= 10) {
	            return;
	          }

	          if (_this2.dragMovePositionX === null) {
	            return;
	          } else if (fromRight && _this2.dragStartPositionX - _this2.dragMovePositionX < 9) {
	            return;
	          } else if (!fromRight && _this2.dragStartPositionX - _this2.dragMovePositionX > 9) {
	            return;
	          }

	          im_tools_animation.Animation.cancel(_this2.dragBackAnimation);
	          _this2.drag = true;

	          _this2.$emit('dragMessage', {
	            result: _this2.drag,
	            event: event
	          });

	          _this2.dragWidth = _this2.$refs.body.offsetWidth;
	        }, 80);
	      } else if (eventName === 'touchmove') {
	        if (this.drag || !this.dragCheck) {
	          return false;
	        }

	        this.dragMovePositionX = layerX;
	        this.dragMovePositionY = layerY;
	      } else if (eventName === 'touchend') {
	        clearTimeout(this.dragStartTimeout1);
	        clearTimeout(this.dragStartTimeout2);
	        this.dragCheck = false;

	        if (!this.drag) {
	          this.dragIconShowLeft = false;
	          this.dragIconShowRight = false;
	          return;
	        }

	        im_tools_animation.Animation.cancel(this.dragBackAnimation);
	        this.drag = false;
	        this.$emit('dragMessage', {
	          result: this.drag,
	          event: event
	        });

	        if (this.enableGestureQuoteFromRight && this.dragIconShowRight && this.dragPosition !== 0 || !this.enableGestureQuoteFromRight && this.dragIconShowLeft && this.dragPosition !== this.dragStartInitialX) {
	          if (im_utils.Utils.platform.isBitrixMobile()) {
	            setTimeout(function () {
	              return app.exec("callVibration");
	            }, 200);
	          }

	          this.$emit('quoteMessage', {
	            message: this.message
	          });
	        }

	        this.dragIconShowLeft = false;
	        this.dragIconShowRight = false;
	        this.dragBackAnimation = im_tools_animation.Animation.start({
	          start: this.dragPosition,
	          end: this.dragStartInitialX,
	          increment: 20,
	          duration: 300,
	          element: this,
	          elementProperty: 'dragPosition',
	          callback: function callback() {
	            _this2.dragLayerPosition = undefined;
	            _this2.dragWidth = 0;
	            _this2.dragPosition = 0;
	          }
	        });
	      }
	    }
	  },
	  watch: {
	    capturedMoveEvent: function capturedMoveEvent(event) {
	      if (!this.drag || !event) {
	        return;
	      }

	      var target = im_utils.Utils.browser.findParent(event.target, 'bx-im-message') || event.target;
	      var layerX = target.getBoundingClientRect().left + event.layerX;

	      if (typeof this.dragLayerPosition === 'undefined') {
	        this.dragLayerPosition = layerX;
	      }

	      var movementX = this.dragLayerPosition - layerX;
	      this.dragLayerPosition = layerX;
	      this.dragPosition = this.dragPosition - movementX;

	      if (this.enableGestureQuoteFromRight) {
	        var dragPositionMax = (this.showAvatar ? 30 : 0) + 45;
	        var dragPositionIcon = this.showAvatar ? 30 : 30;

	        if (this.dragPosition < -dragPositionMax) {
	          this.dragPosition = -dragPositionMax;
	        } else if (this.dragPosition < -dragPositionIcon) {
	          if (!this.dragIconShowRight) {
	            this.dragIconShowRight = true;
	          }
	        } else if (this.dragPosition >= 0) {
	          this.dragPosition = 0;
	        }
	      } else {
	        var _dragPositionMax = 60;
	        var _dragPositionIcon = 40;

	        if (this.dragPosition <= this.dragStartInitialX) {
	          this.dragPosition = this.dragStartInitialX;
	        } else if (this.dragPosition >= _dragPositionMax) {
	          this.dragPosition = _dragPositionMax;
	        } else if (this.dragPosition >= _dragPositionIcon) {
	          if (!this.dragIconShowLeft) {
	            this.dragIconShowLeft = true;
	          }
	        }
	      }
	    }
	  },
	  computed: {
	    MessageType: function MessageType() {
	      return im_const.MessageType;
	    },
	    type: function type() {
	      if (this.message.system || this.message.authorId == 0) {
	        return im_const.MessageType.system;
	      } else if (this.message.authorId === -1 || this.message.authorId == this.userId) {
	        return im_const.MessageType.self;
	      } else {
	        return im_const.MessageType.opponent;
	      }
	    },
	    localize: function localize() {
	      var localize = ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_MESSAGE_', this.$root.$bitrixMessages);
	      return Object.freeze(Object.assign({}, localize, {
	        'IM_MESSENGER_MESSAGE_MENU_TITLE': localize.IM_MESSENGER_MESSAGE_MENU_TITLE.replace('#SHORTCUT#', im_utils.Utils.platform.isMac() ? 'CMD' : 'CTRL')
	      }));
	    },
	    userData: function userData() {
	      return this.$store.getters['users/get'](this.message.authorId, true);
	    },
	    userAvatar: function userAvatar() {
	      if (this.message.params.AVATAR) {
	        return "url('".concat(this.message.params.AVATAR, "')");
	      }

	      if (this.userData.avatar) {
	        return "url('".concat(this.userData.avatar, "')");
	      }

	      return '';
	    },
	    filesData: function filesData() {
	      var files = this.$store.getters['files/getList'](this.chatId);
	      return files ? files : {};
	    },
	    isEdited: function isEdited() {
	      return this.message.params.IS_EDITED === 'Y';
	    },
	    isDeleted: function isDeleted() {
	      return this.message.params.IS_DELETED === 'Y';
	    },
	    isLargeFont: function isLargeFont() {
	      return this.showLargeFont && this.message.params.LARGE_FONT === 'Y';
	    }
	  },
	  template: "\n\t\t<div :class=\"['bx-im-message', {\n\t\t\t\t'bx-im-message-without-menu': !showMenu,\n\t\t\t\t'bx-im-message-without-avatar': !showAvatar,\n\t\t\t\t'bx-im-message-type-system': type === MessageType.system,\n\t\t\t\t'bx-im-message-type-self': type === MessageType.self,\n\t\t\t\t'bx-im-message-type-other': type !== MessageType.self,\n\t\t\t\t'bx-im-message-type-opponent': type === MessageType.opponent,\n\t\t\t\t'bx-im-message-status-error': message.error,\n\t\t\t\t'bx-im-message-status-unread': message.unread,\n\t\t\t\t'bx-im-message-status-blink': message.blink,\n\t\t\t\t'bx-im-message-status-edited': isEdited,\n\t\t\t\t'bx-im-message-status-deleted': isDeleted,\n\t\t\t\t'bx-im-message-large-font': isLargeFont,\n\t\t\t}]\" \n\t\t\t@touchstart=\"gestureRouter('touchstart', $event)\"\n\t\t\t@touchmove=\"gestureRouter('touchmove', $event)\"\n\t\t\t@touchend=\"gestureRouter('touchend', $event)\"\n\t\t\tref=\"body\"\n\t\t\t:style=\"{\n\t\t\t\twidth: dragWidth > 0? dragWidth+'px': '', \n\t\t\t\tmarginLeft: (enableGestureQuoteFromRight && dragPosition < 0) || (!enableGestureQuoteFromRight && dragPosition > 0)? dragPosition+'px': '',\n\t\t\t}\"\n\t\t>\n\t\t\t<template v-if=\"type === MessageType.self\">\n\t\t\t\t<template v-if=\"dragIconShowRight\">\n\t\t\t\t\t<div class=\"bx-im-message-reply bx-im-message-reply-right\">\n\t\t\t\t\t\t<div class=\"bx-im-message-reply-icon\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template> \n\t\t\t\t<div class=\"bx-im-message-box\">\n\t\t\t\t\t<component :is=\"componentBodyId\"\n\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t\t:messageType=\"type\"\n\t\t\t\t\t\t:dialog=\"dialog\"\n\t\t\t\t\t\t:message=\"message\"\n\t\t\t\t\t\t:user=\"userData\"\n\t\t\t\t\t\t:files=\"filesData\"\n\t\t\t\t\t\t:showAvatar=\"showAvatar\"\n\t\t\t\t\t\t:showName=\"showName\"\n\t\t\t\t\t\t:enableReactions=\"enableReactions\"\n\t\t\t\t\t\t:referenceContentBodyClassName=\"referenceContentBodyClassName\"\n\t\t\t\t\t\t:referenceContentNameClassName=\"referenceContentNameClassName\"\n\t\t\t\t\t\t@clickByUserName=\"clickByUserName\"\n\t\t\t\t\t\t@clickByUploadCancel=\"clickByUploadCancel\"\n\t\t\t\t\t\t@clickByKeyboardButton=\"clickByKeyboardButton\"\n\t\t\t\t\t\t@clickByChatTeaser=\"clickByChatTeaser\"\n\t\t\t\t\t\t@setReaction=\"setMessageReaction\"\n\t\t\t\t\t\t@openReactionList=\"openMessageReactionList\"\n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"bx-im-message-box-status\">\n\t\t\t\t\t<template v-if=\"message.sending\">\n\t\t\t\t\t\t<div class=\"bx-im-message-sending\"></div>\n\t\t\t\t\t</template>\n\t\t\t\t\t<transition name=\"bx-im-message-status-retry\">\n\t\t\t\t\t\t<template v-if=\"!message.sending && message.error && message.retry\">\n\t\t\t\t\t\t\t<div class=\"bx-im-message-status-retry\" :title=\"localize.IM_MESSENGER_MESSAGE_RETRY_TITLE\" @click=\"clickByMessageRetry({message: message, event: $event})\">\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-retry-icon\"></span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</transition>\n\t\t\t\t\t<template v-if=\"showMenu && !message.sending && !message.error\">\n\t\t\t\t\t\t<div class=\"bx-im-message-status-menu\" :title=\"localize.IM_MESSENGER_MESSAGE_MENU_TITLE\" @click=\"clickByMessageMenu({message: message, event: $event})\">\n\t\t\t\t\t\t\t<span class=\"bx-im-message-menu-icon\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template> \n\t\t\t\t</div>\n\t\t\t\t<template v-if=\"dragIconShowLeft\">\n\t\t\t\t\t<div class=\"bx-im-message-reply bx-im-message-reply-left\">\n\t\t\t\t\t\t<div class=\"bx-im-message-reply-icon\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template> \n\t\t\t</template>\n\t\t\t<template v-else-if=\"type !== MessageType.self\">\n\t\t\t\t<template v-if=\"dragIconShowLeft\">\n\t\t\t\t\t<div class=\"bx-im-message-reply bx-im-message-reply-left\">\n\t\t\t\t\t\t<div class=\"bx-im-message-reply-icon\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template> \n\t\t\t\t<template v-if=\"type === MessageType.opponent\">\n\t\t\t\t\t<div v-if=\"showAvatar\" class=\"bx-im-message-avatar\" @click=\"clickByAvatar({user: userData, event: $event})\">\n\t\t\t\t\t\t<div :class=\"['bx-im-message-avatar-image', {\n\t\t\t\t\t\t\t\t'bx-im-message-avatar-image-default': !userData.avatar\n\t\t\t\t\t\t\t}]\"\n\t\t\t\t\t\t\t:style=\"{\n\t\t\t\t\t\t\t\tbackgroundColor: !userData.avatar? userData.color: '', \n\t\t\t\t\t\t\t\tbackgroundImage: userAvatar\n\t\t\t\t\t\t\t}\" \n\t\t\t\t\t\t\t:title=\"userData.name\"\n\t\t\t\t\t\t></div>\t\n\t\t\t\t\t</div>\n\t\t\t\t</template>\n\t\t\t\t<div class=\"bx-im-message-box\">\n\t\t\t\t\t<component :is=\"componentBodyId\"\n\t\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t\t:messageType=\"type\"\n\t\t\t\t\t\t:message=\"message\"\n\t\t\t\t\t\t:user=\"userData\"\n\t\t\t\t\t\t:files=\"filesData\"\n\t\t\t\t\t\t:showAvatar=\"showAvatar\"\n\t\t\t\t\t\t:showName=\"showName\"\n\t\t\t\t\t\t:enableReactions=\"enableReactions\"\n\t\t\t\t\t\t:referenceContentBodyClassName=\"referenceContentBodyClassName\"\n\t\t\t\t\t\t:referenceContentNameClassName=\"referenceContentNameClassName\"\n\t\t\t\t\t\t@clickByUserName=\"clickByUserName\"\n\t\t\t\t\t\t@clickByUploadCancel=\"clickByUploadCancel\"\n\t\t\t\t\t\t@clickByKeyboardButton=\"clickByKeyboardButton\"\n\t\t\t\t\t\t@clickByChatTeaser=\"clickByChatTeaser\"\n\t\t\t\t\t\t@setReaction=\"setMessageReaction\"\n\t\t\t\t\t\t@openReactionList=\"openMessageReactionList\"\n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t\t<div v-if=\"showMenu\"  class=\"bx-im-message-menu\" :title=\"localize.IM_MESSENGER_MESSAGE_MENU_TITLE\" @click=\"clickByMessageMenu({message: message, event: $event})\">\n\t\t\t\t\t<span class=\"bx-im-message-menu-icon\"></span>\n\t\t\t\t</div>\t\n\t\t\t\t<template v-if=\"dragIconShowRight\">\n\t\t\t\t\t<div class=\"bx-im-message-reply bx-im-message-reply-right\">\n\t\t\t\t\t\t<div class=\"bx-im-message-reply-icon\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</template> \n\t\t\t</template>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),window,BX.Messenger.Model,BX,BX.Messenger.Const,BX.Messenger,BX.Messenger));
//# sourceMappingURL=message.bundle.js.map
