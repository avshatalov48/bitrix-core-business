/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_fonts_opensans,im_v2_lib_utils,im_v2_lib_channel,ui_avatar,im_v2_lib_copilot,main_core,im_v2_const) {
	'use strict';

	const AvatarSize = Object.freeze({
	  XXS: 'XXS',
	  XS: 'XS',
	  S: 'S',
	  M: 'M',
	  L: 'L',
	  XL: 'XL',
	  XXL: 'XXL',
	  XXXL: 'XXXL'
	});
	const AvatarSizeMap = Object.freeze({
	  [AvatarSize.XXXL]: 94,
	  [AvatarSize.XXL]: 60,
	  [AvatarSize.XL]: 48,
	  [AvatarSize.L]: 42,
	  [AvatarSize.M]: 32,
	  [AvatarSize.S]: 22,
	  [AvatarSize.XS]: 18,
	  [AvatarSize.XXS]: 14
	});
	const ChatAvatarType = {
	  notes: 'notes'
	};
	const EmptyAvatarType = Object.freeze({
	  default: 'default',
	  squared: 'squared',
	  collab: 'collab'
	});

	// @vue/component
	const Avatar = {
	  name: 'MessengerAvatar',
	  props: {
	    dialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    customSource: {
	      type: String,
	      default: ''
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    },
	    backgroundColor: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      imageLoadError: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isChannel() {
	      return im_v2_lib_channel.ChannelManager.isChannel(this.dialogId);
	    },
	    isSpecialType() {
	      const commonTypes = [im_v2_const.ChatType.user, im_v2_const.ChatType.chat, im_v2_const.ChatType.open, im_v2_const.ChatType.lines, im_v2_const.ChatType.copilot];
	      return !commonTypes.includes(this.dialog.type);
	    },
	    containerTitle() {
	      if (!this.withTooltip) {
	        return '';
	      }
	      return this.dialog.name;
	    },
	    containerClasses() {
	      const classes = [`--size-${this.size.toLowerCase()}`];
	      if (this.withSpecialTypes && this.isSpecialType) {
	        classes.push('--special');
	      }
	      const typeClass = im_v2_const.ChatType[this.dialog.type] ? `--${this.dialog.type}` : '--default';
	      classes.push(typeClass);
	      return classes;
	    },
	    backgroundColorStyle() {
	      if (this.backgroundColor) {
	        return {
	          backgroundColor: this.backgroundColor
	        };
	      }
	      return {
	        backgroundColor: this.dialog.color
	      };
	    },
	    avatarText() {
	      if (!this.showAvatarLetters || !this.isEnoughSizeForText) {
	        return '';
	      }
	      return im_v2_lib_utils.Utils.text.getFirstLetters(this.dialog.name);
	    },
	    showAvatarLetters() {
	      const SPECIAL_TYPES_WITH_LETTERS = [im_v2_const.ChatType.openChannel, im_v2_const.ChatType.channel, im_v2_const.ChatType.taskComments];
	      if (SPECIAL_TYPES_WITH_LETTERS.includes(this.dialog.type)) {
	        return true;
	      }
	      return !this.isSpecialType;
	    },
	    showSpecialTypeIcon() {
	      if (!this.withSpecialTypes || !this.withSpecialTypeIcon || this.isChannel) {
	        return false;
	      }
	      return this.isSpecialType;
	    },
	    isEnoughSizeForText() {
	      const avatarSizesWithText = [AvatarSize.M, AvatarSize.L, AvatarSize.XL, AvatarSize.XXL, AvatarSize.XXXL];
	      return avatarSizesWithText.includes(this.size.toUpperCase());
	    },
	    avatarUrl() {
	      return this.customSource.length > 0 ? this.customSource : this.dialog.avatar;
	    },
	    hasImage() {
	      return this.avatarUrl && !this.imageLoadError;
	    }
	  },
	  watch: {
	    avatarUrl() {
	      this.imageLoadError = false;
	    }
	  },
	  methods: {
	    onImageLoadError() {
	      this.imageLoadError = true;
	    }
	  },
	  template: `
		<div :title="containerTitle" :class="containerClasses" class="bx-im-avatar__scope bx-im-avatar__container">
			<!-- Avatar -->
			<template v-if="hasImage">
				<img :src="avatarUrl" :alt="dialog.name" class="bx-im-avatar__content --image" @error="onImageLoadError" draggable="false"/>
				<div v-if="showSpecialTypeIcon" :style="backgroundColorStyle" class="bx-im-avatar__special-type_icon"></div>
			</template>
			<div v-else-if="withAvatarLetters && avatarText" :style="backgroundColorStyle" class="bx-im-avatar__content --text">
				{{ avatarText }}
			</div>
			<div v-else :style="backgroundColorStyle" class="bx-im-avatar__content bx-im-avatar__icon"></div>
		</div>
	`
	};

	const AvatarType = {
	  extranet: 'extranet',
	  collaber: 'collaber',
	  collab: 'collab',
	  copilot: 'copilot',
	  default: 'default'
	};

	// @vue/component
	const BaseUiAvatar = {
	  props: {
	    type: {
	      type: String,
	      required: true,
	      validator(value) {
	        return Object.values(AvatarType).includes(value);
	      }
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    url: {
	      type: String,
	      default: ''
	    },
	    title: {
	      type: String,
	      default: ''
	    },
	    backgroundColor: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    AvatarSize: () => AvatarSize,
	    calculatedSize() {
	      return AvatarSizeMap[this.size];
	    }
	  },
	  watch: {
	    title() {
	      this.avatar.setTitle(this.title);
	    },
	    url() {
	      this.setAvatarImage();
	    }
	  },
	  created() {
	    const classMap = {
	      extranet: ui_avatar.AvatarRoundExtranet,
	      collaber: ui_avatar.AvatarRoundGuest,
	      collab: ui_avatar.AvatarHexagonGuest,
	      copilot: ui_avatar.AvatarRoundCopilot,
	      default: ui_avatar.AvatarBase
	    };
	    const AvatarClass = classMap[this.type] || classMap.default;
	    this.avatar = new AvatarClass({
	      size: this.calculatedSize,
	      title: this.title
	    });
	    this.setAvatarImage();
	    this.setBackgroundColor();
	  },
	  mounted() {
	    if (this.avatar && this.$refs.avatarContainer) {
	      this.avatar.renderTo(this.$refs.avatarContainer);
	    }
	  },
	  methods: {
	    setAvatarImage() {
	      if (!this.url) {
	        return;
	      }
	      this.avatar.setUserPic(this.url);
	    },
	    setBackgroundColor() {
	      if (!this.backgroundColor) {
	        return;
	      }
	      this.avatar.setBaseColor(this.backgroundColor);
	    }
	  },
	  template: `
		<div class="bx-im-base-ui-avatar__container" ref="avatarContainer"></div>
	`
	};

	// @vue/component
	const CollabChatAvatar = {
	  name: 'CollabChatAvatar',
	  components: {
	    BaseUiAvatar
	  },
	  props: {
	    dialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    customSource: {
	      type: String,
	      default: ''
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    AvatarType: () => AvatarType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogName() {
	      return this.dialog.name;
	    },
	    dialogAvatarUrl() {
	      return this.dialog.avatar;
	    },
	    collabBackgroundColor() {
	      return im_v2_const.Color.collab60;
	    }
	  },
	  template: `
		<BaseUiAvatar
			:type="AvatarType.collab"
			:key="dialogId"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
			:backgroundColor="collabBackgroundColor" 
		/>
	`
	};

	// @vue/component
	const CollaberAvatar = {
	  name: 'CollaberAvatar',
	  components: {
	    BaseUiAvatar
	  },
	  props: {
	    dialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    customSource: {
	      type: String,
	      default: ''
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    AvatarType: () => AvatarType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogName() {
	      return this.dialog.name;
	    },
	    dialogAvatarUrl() {
	      return this.dialog.avatar;
	    },
	    collaberBackgroundColor() {
	      return im_v2_const.Color.collab60;
	    }
	  },
	  template: `
		<BaseUiAvatar
			:type="AvatarType.collaber"
			:key="dialogId"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl"
			:backgroundColor="collaberBackgroundColor" 
		/>
	`
	};

	// @vue/component
	const ExtranetChatAvatar = {
	  name: 'ExtranetChatAvatar',
	  components: {
	    Avatar
	  },
	  props: {
	    dialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    customSource: {
	      type: String,
	      default: ''
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogName() {
	      return this.dialog.name;
	    },
	    dialogAvatarUrl() {
	      return this.dialog.avatar;
	    },
	    extranetBackgroundColor() {
	      return im_v2_const.Color.orange50;
	    }
	  },
	  template: `
		<Avatar
			:dialogId="dialogId"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
			:backgroundColor="extranetBackgroundColor" 
		/>
	`
	};

	// @vue/component
	const ExtranetUserAvatar = {
	  name: 'ExtranetUserAvatar',
	  components: {
	    BaseUiAvatar
	  },
	  props: {
	    dialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    customSource: {
	      type: String,
	      default: ''
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    AvatarType: () => AvatarType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogName() {
	      return this.dialog.name;
	    },
	    dialogAvatarUrl() {
	      return this.dialog.avatar;
	    }
	  },
	  template: `
		<BaseUiAvatar
			:type="AvatarType.extranet"
			:title="dialogName"
			:size="size"
			:url="dialogAvatarUrl"
		/>
	`
	};

	// @vue/component
	const CopilotAvatar = {
	  name: 'CopilotAvatar',
	  components: {
	    BaseUiAvatar
	  },
	  props: {
	    dialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    customSource: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    AvatarType: () => AvatarType,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogName() {
	      return this.dialog.name;
	    },
	    dialogAvatarUrl() {
	      return this.customSource.length > 0 ? this.customSource : this.dialog.avatar;
	    }
	  },
	  template: `
		<BaseUiAvatar
			:type="AvatarType.copilot"
			:title="dialogName" 
			:size="size" 
			:url="dialogAvatarUrl" 
		/>
	`
	};

	// @vue/component
	const NotesAvatar = {
	  name: 'NotesAvatar',
	  inheritAttrs: false,
	  props: {
	    size: {
	      type: String,
	      default: AvatarSize.M
	    }
	  },
	  computed: {
	    sizeStyles() {
	      return {
	        width: `${AvatarSizeMap[this.size]}px`,
	        height: `${AvatarSizeMap[this.size]}px`
	      };
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-notes-avatar__container" :style="sizeStyles" :title="loc('IM_ELEMENTS_CHAT_MY_NOTES')"></div>
	`
	};

	// @vue/component
	const ChatAvatar = {
	  name: 'ChatAvatar',
	  components: {
	    Avatar,
	    CollabAvatar: CollabChatAvatar,
	    CollaberAvatar,
	    ExtranetUserAvatar,
	    NotesAvatar,
	    CopilotAvatar
	  },
	  props: {
	    avatarDialogId: {
	      type: [String, Number],
	      default: 0
	    },
	    contextDialogId: {
	      type: String,
	      required: true
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    },
	    customType: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    isUser() {
	      return this.avatarDialog.type === im_v2_const.ChatType.user;
	    },
	    user() {
	      return this.$store.getters['users/get'](this.avatarDialogId, true);
	    },
	    customAvatarUrl() {
	      if (!this.isCopilot) {
	        return '';
	      }
	      return this.copilotManager.getRoleAvatarUrl({
	        avatarDialogId: this.avatarDialogId,
	        contextDialogId: this.contextDialogId
	      });
	    },
	    avatarDialog() {
	      return this.$store.getters['chats/get'](this.avatarDialogId, true);
	    },
	    isCollabChat() {
	      return this.avatarDialog.type === im_v2_const.ChatType.collab;
	    },
	    isCollaber() {
	      var _this$user;
	      return ((_this$user = this.user) == null ? void 0 : _this$user.type) === im_v2_const.UserType.collaber;
	    },
	    isExtranetChat() {
	      return this.avatarDialog.extranet;
	    },
	    isExtranet() {
	      var _this$user2;
	      return ((_this$user2 = this.user) == null ? void 0 : _this$user2.type) === im_v2_const.UserType.extranet;
	    },
	    isCopilot() {
	      return this.copilotManager.isCopilotChatOrBot(this.avatarDialogId);
	    },
	    avatarComponent() {
	      if (this.customType === ChatAvatarType.notes) {
	        return NotesAvatar;
	      }
	      if (this.isExtranet) {
	        return ExtranetUserAvatar;
	      }
	      if (this.isCollaber) {
	        return CollaberAvatar;
	      }
	      if (this.isCollabChat) {
	        return CollabChatAvatar;
	      }
	      if (this.isCopilot) {
	        return CopilotAvatar;
	      }
	      return this.isExtranetChat ? ExtranetChatAvatar : Avatar;
	    }
	  },
	  created() {
	    this.copilotManager = new im_v2_lib_copilot.CopilotManager();
	  },
	  template: `
		<component
			:is="avatarComponent"
			:dialogId="avatarDialogId"
			:customSource="customAvatarUrl"
			:size="size"
			:withAvatarLetters="withAvatarLetters"
			:withSpecialTypes="withSpecialTypes"
			:withSpecialTypeIcon="withSpecialTypeIcon"
			:withTooltip="withTooltip"
		/>
	`
	};

	// @vue/component
	const MessageAvatar = {
	  name: 'MessageAvatar',
	  components: {
	    Avatar,
	    CollaberAvatar,
	    CopilotAvatar
	  },
	  props: {
	    messageId: {
	      type: [String, Number],
	      default: 0
	    },
	    authorId: {
	      type: [String, Number],
	      default: 0
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    },
	    withAvatarLetters: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypeIcon: {
	      type: Boolean,
	      default: true
	    },
	    withTooltip: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    customAvatarUrl() {
	      const copilotManager = new im_v2_lib_copilot.CopilotManager();
	      if (!copilotManager.isCopilotMessage(this.messageId)) {
	        return '';
	      }
	      return copilotManager.getMessageRoleAvatar(this.messageId);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.authorId, true);
	    },
	    avatarComponent() {
	      var _avatarMap$this$user$;
	      const avatarMap = {
	        [im_v2_const.UserType.extranet]: ExtranetUserAvatar,
	        [im_v2_const.UserType.collaber]: CollaberAvatar,
	        [im_v2_const.UserType.bot]: this.getBotAvatar()
	      };
	      return (_avatarMap$this$user$ = avatarMap[this.user.type]) != null ? _avatarMap$this$user$ : Avatar;
	    }
	  },
	  methods: {
	    getBotAvatar() {
	      const copilotManager = new im_v2_lib_copilot.CopilotManager();
	      return copilotManager.isCopilotChatOrBot(this.authorId) ? CopilotAvatar : Avatar;
	    }
	  },
	  template: `
		<component
			:is="avatarComponent"
			:dialogId="authorId"
			:customSource="customAvatarUrl"
			:size="size"
			:withAvatarLetters="withAvatarLetters"
			:withSpecialTypes="withSpecialTypes"
			:withSpecialTypeIcon="withSpecialTypeIcon"
			:withTooltip="withTooltip"
		/>
	`
	};

	const COLLAB_EMPTY_AVATAR_URL = '/bitrix/js/im/v2/component/elements/avatar/src/components/base/css/images/camera.png';

	// @vue/component
	const EmptyAvatar = {
	  name: 'EmptyAvatar',
	  components: {
	    BaseUiAvatar
	  },
	  props: {
	    url: {
	      type: String,
	      default: ''
	    },
	    title: {
	      type: String,
	      default: ''
	    },
	    type: {
	      type: String,
	      default: EmptyAvatarType.default
	    },
	    size: {
	      type: String,
	      default: AvatarSize.M
	    }
	  },
	  data() {
	    return {
	      imageLoadError: false
	    };
	  },
	  computed: {
	    AvatarSize: () => AvatarSize,
	    AvatarType: () => AvatarType,
	    Color: () => im_v2_const.Color,
	    isSquared() {
	      return this.type === EmptyAvatarType.squared;
	    },
	    isCollabType() {
	      return this.type === EmptyAvatarType.collab;
	    },
	    collabEmptyAvatarUrl() {
	      if (!main_core.Type.isStringFilled(this.url)) {
	        return COLLAB_EMPTY_AVATAR_URL;
	      }
	      return this.url;
	    },
	    containerClasses() {
	      const classes = [`--size-${this.size.toLowerCase()}`];
	      if (this.isSquared) {
	        classes.push('--squared');
	      }
	      return classes;
	    }
	  },
	  template: `
		<BaseUiAvatar
			v-if="isCollabType"
			:type="AvatarType.collab"
			:url="collabEmptyAvatarUrl" 
			:size="size"
			:title="title"
			:backgroundColor="Color.collab10"
		/>
		<div v-else class="bx-im-empty-avatar__container" :class="containerClasses">
			<div v-if="!url" class="bx-im-empty-avatar__avatar --default"></div>
			<img v-else class="bx-im-empty-avatar__avatar --image" :src="url" :alt="title"/>
		</div>
	`
	};

	exports.AvatarSize = AvatarSize;
	exports.ChatAvatarType = ChatAvatarType;
	exports.EmptyAvatarType = EmptyAvatarType;
	exports.ChatAvatar = ChatAvatar;
	exports.MessageAvatar = MessageAvatar;
	exports.EmptyAvatar = EmptyAvatar;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.UI,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Const));
//# sourceMappingURL=registry.bundle.js.map
