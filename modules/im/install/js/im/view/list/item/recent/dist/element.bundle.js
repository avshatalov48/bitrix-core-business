(function (exports,ui_vue,im_lib_utils) {
	'use strict';

	ui_vue.Vue.cloneComponent('bx-im-view-list-item-recent', 'bx-list-element', {
	  props: ['rawListItem', 'itemTypes'],
	  methods: {
	    formatDate: function formatDate(date) {
	      date = new Date(date);
	      var currentDate = new Date();
	      var weekAgo = currentDate.getTime() - 7 * 24 * 60 * 60 * 1000;
	      var weekDays = ['vs', 'pn', 'vt', 'sr', 'ch', 'py', 'sb'];

	      if (date.getFullYear() === currentDate.getFullYear() && date.getMonth() === currentDate.getMonth() && date.getDate() === currentDate.getDate()) {
	        return im_lib_utils.Utils.date.format(date, 'H:i');
	      } else if (date.getTime() > weekAgo) {
	        return weekDays[date.getDay()];
	      } else if (date.getFullYear() === currentDate.getFullYear()) {
	        return im_lib_utils.Utils.date.format(date, 'd.m');
	      } else {
	        return im_lib_utils.Utils.date.format(date, 'd.m.Y');
	      }
	    }
	  },
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
	        subtitle: {
	          value: this.rawListItem.message.text
	        },
	        avatar: {
	          url: this.avatarUrl,
	          bottomRightIcon: this.avatarBottomRightIcon
	        },
	        message: this.rawListItem.message,
	        date: {
	          value: this.formatDate(this.rawListItem.message.date)
	        },
	        counter: {
	          value: this.rawListItem.counter,
	          leftIcon: this.rawListItem.pinned ? 'pinned' : ''
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
	        if (this.userData.isMobileOnline) {
	          return 'mobile-online';
	        } else if (this.userData.isOnline && (this.userData.status === 'away' || this.userData.status === 'dnd')) {
	          return this.userData.status;
	        } else if (this.userData.isOnline) {
	          return 'online';
	        } else {
	          return 'offline';
	        }
	      }
	    },
	    titleLeftIcon: function titleLeftIcon() {
	      if (this.isUser) {
	        if (this.userData.isAbsent) {
	          return 'absent';
	        } else if (this.userData.isBirthday) {
	          return 'birthday';
	        } else {
	          return '';
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
	  }
	});

}((this.window = this.window || {}),BX,BX.Messenger));
//# sourceMappingURL=element.bundle.js.map
