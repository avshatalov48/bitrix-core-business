(function (exports,im_component_message_body,im_model,ui_vue) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Message Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */

	var _MessageType = Object.freeze({
	  self: 'self',
	  opponent: 'opponent',
	  system: 'system'
	});

	ui_vue.Vue.component('bx-messenger-message', {
	  /**
	   * @emits 'clickByUserName' {user: object, event: MouseEvent}
	   * @emits 'clickByMessageMenu' {message: object, event: MouseEvent}
	   * @emits 'clickByMessageRetry' {message: object, event: MouseEvent}
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
	    enableEmotions: {
	      default: true
	    },
	    enableDateActions: {
	      default: true
	    },
	    enableCreateContent: {
	      default: true
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
	    referenceContentClassName: {
	      default: ''
	    },
	    referenceContentBodyClassName: {
	      default: ''
	    },
	    message: {
	      type: Object,
	      default: im_model.MessagesModel.create().getElementStore
	    }
	  },
	  data: function data() {
	    return {
	      componentBodyId: 'bx-messenger-message-body'
	    };
	  },
	  methods: {
	    clickByUserName: function clickByUserName(event) {
	      this.$emit('clickByUserName', event);
	    },
	    clickByMessageMenu: function clickByMessageMenu(message, event) {
	      this.$emit('clickByMessageMenu', {
	        message: message,
	        event: event
	      });
	    },
	    clickByMessageRetry: function clickByMessageRetry(message, event) {
	      this.$emit('clickByMessageRetry', {
	        message: message,
	        event: event
	      });
	    }
	  },
	  computed: {
	    MessageType: function MessageType() {
	      return _MessageType;
	    },
	    type: function type() {
	      if (this.message.system || this.message.authorId == 0) {
	        return _MessageType.system;
	      } else if (this.message.authorId === -1 || this.message.authorId == this.userId) {
	        return _MessageType.self;
	      } else {
	        return _MessageType.opponent;
	      }
	    },
	    localize: function localize() {
	      var localize = ui_vue.Vue.getFilteredPhrases('IM_MESSENGER_MESSAGE_', this.$root.$bitrixMessages);
	      return Object.freeze(Object.assign({}, localize, {
	        'IM_MESSENGER_MESSAGE_MENU_TITLE': localize.IM_MESSENGER_MESSAGE_MENU_TITLE.replace('#SHORTCUT#', BX.Messenger.Utils.platform.isMac() ? 'CMD' : 'CTRL')
	      }));
	    },
	    userData: function userData() {
	      var user = this.$store.getters['users/get'](this.message.authorId);
	      return user ? user : this.$store.getters['users/getBlank']();
	    },
	    filesData: function filesData() {
	      var files = this.$store.getters['files/getObject'](this.chatId);
	      return files ? files : {};
	    },
	    isEdited: function isEdited() {
	      return this.message.params.IS_EDITED == 'Y';
	    },
	    isDeleted: function isDeleted() {
	      return this.message.params.IS_DELETED == 'Y';
	    }
	  },
	  template: "\n\t\t<div :class=\"['bx-im-message', {\n\t\t\t'bx-im-message-without-menu': !showMenu,\n\t\t\t'bx-im-message-without-avatar': !showAvatar,\n\t\t\t'bx-im-message-type-system': type == MessageType.system,\n\t\t\t'bx-im-message-type-self': type == MessageType.self,\n\t\t\t'bx-im-message-type-opponent': type == MessageType.opponent,\n\t\t\t'bx-im-message-status-error': message.error,\n\t\t\t'bx-im-message-status-unread': message.unread,\n\t\t\t'bx-im-message-status-blink': message.blink,\n\t\t\t'bx-im-message-status-edited': isEdited,\n\t\t\t'bx-im-message-status-deleted': isDeleted,\n\t\t}]\">\n\t\t\t<template v-if=\"type == MessageType.opponent\">\n\t\t\t\t<div v-if=\"showAvatar\" class=\"bx-im-message-avatar\" @click=\"clickByUserName(userData, $event)\">\n\t\t\t\t\t<div :class=\"['bx-im-message-avatar-image', {\n\t\t\t\t\t\t\t'bx-im-message-avatar-image-default': !userData.avatar\n\t\t\t\t\t\t}]\"\n\t\t\t\t\t\t:style=\"{\n\t\t\t\t\t\t\tbackgroundColor: !userData.avatar? userData.color: '', \n\t\t\t\t\t\t\tbackgroundImage: userData.avatar? 'url('+userData.avatar+')': ''\n\t\t\t\t\t\t}\" \n\t\t\t\t\t\t:title=\"userData.name\"\n\t\t\t\t\t></div>\t\n\t\t\t\t</div>\n\t\t\t</template>\n\t\t\t<div class=\"bx-im-message-box\">\n\t\t\t\t<component :is=\"componentBodyId\"\n\t\t\t\t\t:userId=\"userId\" \n\t\t\t\t\t:dialogId=\"dialogId\"\n\t\t\t\t\t:chatId=\"chatId\"\n\t\t\t\t\t:messageType=\"type\"\n\t\t\t\t\t:message=\"message\"\n\t\t\t\t\t:user=\"userData\"\n\t\t\t\t\t:files=\"filesData\"\n\t\t\t\t\t:showAvatar=\"showAvatar\"\n\t\t\t\t\t:showName=\"showName\"\n\t\t\t\t\t:enableEmotions=\"enableEmotions\"\n\t\t\t\t\t:referenceContentBodyClassName=\"referenceContentBodyClassName\"\n\t\t\t\t\t@clickByUserName=\"clickByUserName\"\n\t\t\t\t/>\n\t\t\t</div>\t\n\t\t\t<template v-if=\"type == MessageType.self\">\n\t\t\t\t<div class=\"bx-im-message-box-status\">\n\t\t\t\t\t<transition name=\"bx-im-message-sending\">\n\t\t\t\t\t\t<template v-if=\"message.sending\">\n\t\t\t\t\t\t\t<div class=\"bx-im-message-sending\"></div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</transition>\n\t\t\t\t\t<transition name=\"bx-im-message-status-retry\">\n\t\t\t\t\t\t<template v-if=\"!message.sending && message.error\">\n\t\t\t\t\t\t\t<div class=\"bx-im-message-status-retry\" :title=\"localize.IM_MESSENGER_MESSAGE_RETRY_TITLE\" @click=\"clickByMessageRetry(message, $event)\">\n\t\t\t\t\t\t\t\t<span class=\"bx-im-message-retry-icon\"></span>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</template>\n\t\t\t\t\t</transition>\n\t\t\t\t\t<template v-if=\"showMenu && !message.sending && !message.error\">\n\t\t\t\t\t\t<div class=\"bx-im-message-status-menu\" :title=\"localize.IM_MESSENGER_MESSAGE_MENU_TITLE\" @click=\"clickByMessageMenu(message, $event)\">\n\t\t\t\t\t\t\t<span class=\"bx-im-message-menu-icon\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</template> \n\t\t\t\t</div>\n\t\t\t</template> \n\t\t\t<template v-else-if=\"showMenu\">\n\t\t\t\t<div class=\"bx-im-message-menu\" :title=\"localize.IM_MESSENGER_MESSAGE_MENU_TITLE\" @click=\"clickByMessageMenu(message, $event)\">\n\t\t\t\t\t<span class=\"bx-im-message-menu-icon\"></span>\n\t\t\t\t</div>\n\t\t\t</template> \n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),window,BX.Messenger.Model,BX));
//# sourceMappingURL=message.bundle.js.map
