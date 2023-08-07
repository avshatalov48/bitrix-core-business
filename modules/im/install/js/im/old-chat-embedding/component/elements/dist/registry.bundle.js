/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.Embedding = this.BX.Messenger.Embedding || {};
(function (exports,im_oldChatEmbedding_lib_utils,ui_fonts_opensans,ui_vue3,im_oldChatEmbedding_const) {
	'use strict';

	const AvatarSize = {
	  XS: 'XS',
	  S: 'S',
	  M: 'M',
	  L: 'L',
	  XL: 'XL',
	  XXL: 'XXL'
	};
	const Avatar = {
	  name: 'Avatar',
	  props: {
	    dialogId: {
	      type: String,
	      default: '0'
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    withStatus: {
	      type: Boolean,
	      default: true
	    },
	    withCounter: {
	      type: Boolean,
	      default: false
	    },
	    withBirthday: {
	      type: Boolean,
	      default: false
	    },
	    withTyping: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_oldChatEmbedding_const.DialogType.user;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.bot;
	      }
	      return false;
	    },
	    isActiveInvitation() {
	      const recentItem = this.$store.getters['recent/get'](this.dialogId);
	      if (!recentItem) {
	        return false;
	      }
	      return recentItem.invitation.isActive;
	    },
	    chatAvatarStyle() {
	      return {
	        backgroundImage: `url('${this.dialog.avatar}')`
	      };
	    },
	    avatarText() {
	      if (![im_oldChatEmbedding_const.DialogType.user, im_oldChatEmbedding_const.DialogType.open, im_oldChatEmbedding_const.DialogType.chat].includes(this.dialog.type)) {
	        return '';
	      }
	      return im_oldChatEmbedding_lib_utils.Utils.text.getFirstLetters(this.dialog.name);
	    },
	    chatTypeIconClasses() {
	      const classes = [];
	      if (im_oldChatEmbedding_const.DialogType[this.dialog.type]) {
	        classes.push(`bx-im-component-avatar-icon-${this.dialog.type}`);
	      } else {
	        classes.push('bx-im-component-avatar-icon-default');
	      }
	      return classes;
	    },
	    userStatusIcon() {
	      if (!this.isUser || this.isBot || this.user.id === this.currentUserId) {
	        return '';
	      }
	      const status = this.$store.getters['users/getStatus'](this.dialogId);
	      if (status) {
	        return status;
	      }
	      return '';
	    },
	    isSomeoneTyping() {
	      return Object.keys(this.dialog.writingList).length > 0;
	    },
	    formattedCounter() {
	      return this.dialog.counter > 99 ? '99+' : this.dialog.counter;
	    },
	    isChatMuted() {
	      if (this.isUser) {
	        return false;
	      }
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === this.currentUserId;
	      });
	      return !!isMuted;
	    },
	    showBirthdays() {
	      return this.$store.getters['recent/getOption'](im_oldChatEmbedding_const.RecentSettings.showBirthday);
	    },
	    currentUserId() {
	      return this.$store.state.application.common.userId;
	    }
	  },
	  template: `
		<div :title="dialog.name" :class="'bx-im-component-avatar-size-' + size.toLowerCase()" class="bx-im-component-avatar-wrap">
			<div v-if="isActiveInvitation" class="bx-im-component-avatar-content bx-im-component-avatar-invitation"></div>
			<div v-else-if="dialog.avatar" :style="chatAvatarStyle" class="bx-im-component-avatar-content bx-im-component-avatar-image"></div>
			<div v-else-if="withAvatarLetters && avatarText" :style="{backgroundColor: dialog.color}" class="bx-im-component-avatar-content bx-im-component-avatar-text">
				{{ avatarText }}
			</div>
			<div v-else :style="{backgroundColor: dialog.color}" :class="chatTypeIconClasses" class="bx-im-component-avatar-content bx-im-component-avatar-icon"></div>
			<div v-if="withTyping && isSomeoneTyping" class="bx-im-component-avatar-user-status-icon bx-im-component-avatar-user-status-icon-typing"></div>
			<div v-else-if="withBirthday && isUser && showBirthdays && user.isBirthday" class="bx-im-component-avatar-user-status-icon bx-im-component-avatar-user-status-icon-birthday"></div>
			<div v-else-if="withStatus && userStatusIcon" :class="'bx-im-component-avatar-user-status-icon bx-im-component-avatar-user-status-icon-' + userStatusIcon"></div>
			<div v-if="withCounter && dialog.counter > 0" :class="{'bx-im-component-avatar-counter-muted': isChatMuted}" class="bx-im-component-avatar-counter">
				{{ formattedCounter }}
			</div>
		</div>
	`
	};

	const DialogSpecialType = {
	  bot: 'bot',
	  extranet: 'extranet',
	  network: 'network',
	  support24: 'support24'
	};
	const TitleIcons = {
	  absent: 'absent',
	  birthday: 'birthday'
	};
	const ChatTitle = {
	  name: 'ChatTitle',
	  props: {
	    dialogId: {
	      type: String,
	      default: '0'
	    },
	    withMute: {
	      type: Boolean,
	      default: false
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    botType() {
	      if (!this.isUser) {
	        return '';
	      }
	      return this.$store.getters['users/getBotType'](this.dialogId);
	    },
	    isUser() {
	      return this.dialog.type === im_oldChatEmbedding_const.DialogType.user;
	    },
	    isSelfChat() {
	      return this.isUser && this.user.id === this.currentUserId;
	    },
	    dialogSpecialType() {
	      if (!this.isUser) {
	        if (this.isExtranet) {
	          return DialogSpecialType.extranet;
	        } else if ([im_oldChatEmbedding_const.DialogType.support24Notifier, im_oldChatEmbedding_const.DialogType.support24Question].includes(this.dialog.type)) {
	          return DialogSpecialType.support24;
	        }
	        return '';
	      }
	      if (this.isBot) {
	        return this.botType;
	      } else if (this.isExtranet) {
	        return DialogSpecialType.extranet;
	      } else if (this.isNetwork) {
	        return DialogSpecialType.network;
	      }
	      return '';
	    },
	    leftIcon() {
	      if (this.dialogSpecialType) {
	        return this.dialogSpecialType;
	      }
	      if (!this.isUser) {
	        return '';
	      }
	      if (this.showBirthdays && this.user.isBirthday) {
	        return TitleIcons.birthday;
	      } else if (this.user.isAbsent) {
	        return TitleIcons.absent;
	      }
	      return '';
	    },
	    color() {
	      return this.dialogSpecialType;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.bot;
	      }
	      return false;
	    },
	    isExtranet() {
	      if (this.isUser) {
	        return this.user.extranet;
	      }
	      return this.dialog.extranet;
	    },
	    isNetwork() {
	      if (this.isUser) {
	        return this.user.network;
	      }
	      return false;
	    },
	    isChatMuted() {
	      if (this.isUser) {
	        return false;
	      }
	      const isMuted = this.dialog.muteList.find(element => {
	        return element === this.currentUserId;
	      });
	      return !!isMuted;
	    },
	    currentUserId() {
	      return this.$store.state.application.common.userId;
	    },
	    tooltipText() {
	      if (this.isSelfChat) {
	        return `${this.dialog.name} (${this.loc('IM_RECENT_CHAT_SELF')})`;
	      }
	      return this.dialog.name;
	    },
	    showBirthdays() {
	      return this.$store.getters['recent/getOption'](im_oldChatEmbedding_const.RecentSettings.showBirthday);
	    },
	    isDarkTheme() {
	      return this.$store.state.application.options.darkTheme;
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-component-chat-title-wrap">
			<div v-if="leftIcon" :class="'bx-im-component-chat-name-left-icon bx-im-component-chat-name-left-icon-' + leftIcon"></div>
			<span :class="'bx-im-component-chat-name-text-' + color" :title="tooltipText" class="bx-im-component-chat-name-text" >
				{{ dialog.name }}
				<strong v-if="isSelfChat">
					<span class="bx-im-component-chat-name-text-self">({{ loc('IM_RECENT_CHAT_SELF') }})</span>
				</strong>
			</span>
			<div v-if="withMute && isChatMuted" class="bx-im-component-chat-name-muted-icon"></div>
		</div>
	`
	};

	const RecentLoadingState = {
	  name: 'RecentLoadingState',
	  props: {
	    compactMode: {
	      type: Boolean,
	      default: false
	    },
	    itemsToShow: {
	      type: Number,
	      default: 50
	    }
	  },
	  methods: {
	    isThreeLineVersion() {
	      return Math.random() < 0.5;
	    }
	  },
	  template: `
		<div v-if="!compactMode" class="bx-im-component-recent-loading-state">
			<div v-for="index in itemsToShow" class="bx-im-component-recent-loading-state-item">
				<div class="bx-im-component-recent-loading-state-avatar-wrap">
					<div class="bx-im-component-recent-loading-state-avatar-placeholder"></div>
				</div>
				<div class="bx-im-component-recent-loading-state-content">
					<div class="bx-im-component-recent-loading-state-line bx-im-component-recent-loading-state-line-long"></div>
					<div class="bx-im-component-recent-loading-state-line bx-im-component-recent-loading-state-line-short"></div>
					<div v-if="isThreeLineVersion()" class="bx-im-component-recent-loading-state-line bx-im-component-recent-loading-state-line-short"></div>
				</div>
			</div>
		</div>
		<div v-if="compactMode" class="bx-im-component-recent-loading-state bx-im-component-recent-loading-state-compact">
			<div v-for="index in itemsToShow" class="bx-im-component-recent-loading-state-item">
				<div class="bx-im-component-recent-loading-state-avatar-wrap">
					<div class="bx-im-component-recent-loading-state-avatar-placeholder"></div>
				</div>
			</div>
		</div>
	`
	};

	exports.Avatar = Avatar;
	exports.AvatarSize = AvatarSize;
	exports.ChatTitle = ChatTitle;
	exports.RecentLoadingState = RecentLoadingState;

}((this.BX.Messenger.Embedding.ComponentLegacy = this.BX.Messenger.Embedding.ComponentLegacy || {}),BX.Messenger.Embedding.Lib,BX,BX.Vue3,BX.Messenger.Embedding.Const));
//# sourceMappingURL=registry.bundle.js.map
