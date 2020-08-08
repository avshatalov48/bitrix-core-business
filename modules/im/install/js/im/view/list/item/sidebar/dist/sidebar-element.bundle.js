(function (exports,ui_vue,im_lib_utils) {
	'use strict';

	ui_vue.Vue.cloneComponent('bx-im-view-list-item-sidebar', 'bx-list-element', {
	  props: ['rawListItem', 'itemTypes'],
	  methods: {},
	  computed: {
	    listItem: function listItem() {
	      return {
	        id: this.rawListItem.id,
	        template: this.rawListItem.template,
	        type: this.rawListItem.chatType,
	        sectionCode: this.rawListItem.sectionCode,
	        title: {
	          leftIcon: this.titleLeftIcon,
	          value: this.titleValue
	        },
	        avatar: {
	          url: this.avatarUrl,
	          topRightIcon: this.rawListItem.counter,
	          bottomRightIcon: this.avatarBottomRightIcon
	        },
	        notification: false
	      };
	    },
	    listItemStyle: function listItemStyle() {
	      if (this.rawListItem.sectionCode === 'pinned') {
	        return {
	          backgroundColor: '#f7f7f7'
	        };
	      }

	      return {};
	    },
	    imageStyle: function imageStyle() {
	      return {
	        backgroundColor: this.listItem.avatar.url ? '' : this.imageColor
	      };
	    },
	    imageClass: function imageClass() {
	      return 'bx-vue-list-item-image';
	    },
	    imageColor: function imageColor() {
	      if (this.isUser && this.userData) {
	        return this.userData.color;
	      }

	      if (this.isChat && this.dialogData) {
	        return this.dialogData.color;
	      }

	      if (this.isNotificationChat) {
	        return this.rawListItem.color;
	      }
	    },
	    avatarUrl: function avatarUrl() {
	      if (this.isUser && this.userData) {
	        return this.userData.avatar;
	      }

	      if (this.isChat && this.dialogData) {
	        return this.dialogData.avatar;
	      }
	    },
	    avatarBottomRightIcon: function avatarBottomRightIcon() {
	      if (this.isUser) {
	        if (this.userData.isAbsent) {
	          return 'absent';
	        } else if (this.userData.isBirthday) {
	          return 'birthday';
	        } else if (this.userData.isMobileOnline) {
	          return 'mobile-online';
	        } else if (this.userData.isOnline && (this.userData.status === 'away' || this.userData.status === 'dnd')) {
	          return this.userData.status;
	        } else if (this.userData.isOnline) {
	          return 'online';
	        } else {
	          return 'offline';
	        }
	      }

	      if (this.isChat) {
	        return this.rawListItem.chatType;
	      }
	    },
	    titleValue: function titleValue() {
	      if (this.isUser && this.userData) {
	        return this.userData.name;
	      }

	      if (this.isChat && this.dialogData) {
	        return this.dialogData.name;
	      }

	      if (this.isNotificationChat) {
	        return this.rawListItem.title;
	      }
	    },
	    userData: function userData() {
	      return this.$root.$store.getters['users/get'](this.rawListItem.userId);
	    },
	    dialogData: function dialogData() {
	      return this.$root.$store.getters['dialogues/getByChatId'](this.rawListItem.chatId);
	    },
	    isChat: function isChat() {
	      return ['chat', 'open'].includes(this.rawListItem.chatType);
	    },
	    isUser: function isUser() {
	      return this.rawListItem.chatType === 'user';
	    },
	    isNotificationChat: function isNotificationChat() {
	      return this.rawListItem.chatType === 'notification';
	    }
	  },
	  template: "\n\t<div class=\"bx-vue-list-item\" :style=\"listItemStyle\">\n\t\t<template v-if=\"listItem.template !== itemTypes.placeholder\">\n\t\t\t<div v-if=\"listItem.avatar\" class=\"bx-vue-list-item-image-wrap\">\n\t\t\t\t<img v-if=\"listItem.avatar.url\" :src=\"listItem.avatar.url\" :style=\"imageStyle\" :class=\"imageClass\" alt=\"\">\n\t\t\t\t<div v-else-if=\"!listItem.avatar.url\" :style=\"imageStyle\" class=\"bx-vue-list-item-image-text\">{{ avatarText }}</div>\t\n\t\t\t\t<div v-if=\"listItem.avatar.topLeftIcon\" :class=\"'bx-vue-list-icon-avatar-top-left bx-vue-list-icon-avatar-top-left-' + listItem.avatar.topLeftIcon\"></div>\n\t\t\t\t<div v-if=\"listItem.avatar.topRightIcon\" class=\"bx-vue-list-icon-avatar-top-right\">{{ listItem.avatar.topRightIcon }}</div>\n\t\t\t\t<div v-if=\"listItem.avatar.bottomRightIcon\" :class=\"'bx-vue-list-icon-avatar-bottom-right bx-vue-list-avatar-bottom-right-' + listItem.avatar.bottomRightIcon\"></div>\n\t\t\t</div>\n\t\t</template>\n\t\t<template v-else-if=\"listItem.template === itemTypes.placeholder\">\n\t\t\t<div class=\"bx-vue-list-item-image-wrap\"><img src=\"https://www.ischool.berkeley.edu/sites/default/files/default_images/avatar.jpeg\" alt=\"\" class=\"bx-vue-list-item-image\"></div>\n\t\t</template>\n\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX.Messenger));
//# sourceMappingURL=sidebar-element.bundle.js.map
