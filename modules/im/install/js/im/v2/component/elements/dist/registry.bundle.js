this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_fonts_opensans,im_v2_lib_parser,im_v2_provider_service,im_v2_application_core,im_v2_lib_user,ui_loader,im_public,im_v2_model,im_v2_lib_logger,main_popup,dropdown_css,main_core_events,im_v2_lib_utils,im_v2_lib_progressbar,ui_icons_disk,ui_vue3_directives_lazyload,ui_vue3_components_audioplayer,im_v2_const,ui_vue3,ui_vue3_components_socialvideo,main_core) {
	'use strict';

	const UserStatusSize = {
	  S: 'S',
	  M: 'M',
	  L: 'L',
	  XL: 'XL',
	  XXL: 'XXL'
	};

	// @vue/component
	const UserStatus = {
	  name: 'UserStatus',
	  props: {
	    status: {
	      type: String,
	      required: true,
	      validator(value) {
	        return Object.values(im_v2_const.UserStatus).includes(value);
	      }
	    },
	    size: {
	      type: String,
	      default: UserStatusSize.M,
	      validator(value) {
	        return Object.values(UserStatusSize).includes(value);
	      }
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    containerClasses() {
	      return [`--size-${this.size.toLowerCase()}`, `--${this.status}`];
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-user-status__container bx-im-user-status__scope"></div>
	`
	};

	const AvatarSize = Object.freeze({
	  XS: 'XS',
	  S: 'S',
	  M: 'M',
	  L: 'L',
	  XL: 'XL',
	  XXL: 'XXL',
	  XXXL: 'XXXL'
	});

	// @vue/component
	const Avatar = {
	  name: 'MessengerAvatar',
	  components: {
	    UserStatus
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
	    withStatus: {
	      type: Boolean,
	      default: true
	    },
	    withSpecialTypes: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      imageLoadError: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.bot;
	      }
	      return false;
	    },
	    isSpecialType() {
	      const commonTypes = [im_v2_const.DialogType.user, im_v2_const.DialogType.chat, im_v2_const.DialogType.open];
	      return !commonTypes.includes(this.dialog.type);
	    },
	    containerClasses() {
	      const classes = [`--size-${this.size.toLowerCase()}`];
	      if (this.withSpecialTypes && this.isSpecialType) {
	        classes.push('--special');
	      }
	      const typeClass = im_v2_const.DialogType[this.dialog.type] ? `--${this.dialog.type}` : '--default';
	      classes.push(typeClass);
	      return classes;
	    },
	    backgroundColorStyle() {
	      return {
	        backgroundColor: this.dialog.color
	      };
	    },
	    avatarText() {
	      if (this.isSpecialType || !this.isEnoughSizeForText) {
	        return '';
	      }
	      return im_v2_lib_utils.Utils.text.getFirstLetters(this.dialog.name);
	    },
	    userStatusIcon() {
	      if (!this.isUser || this.isBot || this.user.id === im_v2_application_core.Core.getUserId() || !this.isEnoughSizeForStatus) {
	        return '';
	      }
	      const status = this.$store.getters['users/getStatus'](this.dialogId);
	      if (status && status !== im_v2_const.UserStatus.online) {
	        return status;
	      }
	      return '';
	    },
	    userStatusSize() {
	      // avatar size: status size
	      const sizesMap = {
	        [AvatarSize.M]: UserStatusSize.S,
	        [AvatarSize.L]: UserStatusSize.M,
	        [AvatarSize.XL]: UserStatusSize.L,
	        [AvatarSize.XXL]: UserStatusSize.XL,
	        [AvatarSize.XXXL]: UserStatusSize.XXL
	      };
	      return sizesMap[this.size];
	    },
	    isEnoughSizeForText() {
	      const avatarSizesWithText = [AvatarSize.L, AvatarSize.XL, AvatarSize.XXL, AvatarSize.XXXL];
	      return avatarSizesWithText.includes(this.size.toUpperCase());
	    },
	    isEnoughSizeForStatus() {
	      const avatarSizesWithText = [AvatarSize.M, AvatarSize.L, AvatarSize.XL, AvatarSize.XXL, AvatarSize.XXXL];
	      return avatarSizesWithText.includes(this.size.toUpperCase());
	    },
	    avatarUrl() {
	      return this.dialog.avatar;
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
		<div :title="dialog.name" :class="containerClasses" class="bx-im-avatar__scope bx-im-avatar__container">
			<!-- Avatar -->
			<template v-if="hasImage">
				<img :src="avatarUrl" :alt="dialog.name" class="bx-im-avatar__content --image" @error="onImageLoadError"/>
				<div v-if="withSpecialTypes && isSpecialType" :style="backgroundColorStyle" class="bx-im-avatar__special-type_icon"></div>
			</template>
			<div v-else-if="withAvatarLetters && avatarText" :style="backgroundColorStyle" class="bx-im-avatar__content --text">
				{{ avatarText }}
			</div>
			<div v-else :style="backgroundColorStyle" class="bx-im-avatar__content bx-im-avatar__icon"></div>
			<!-- Status icons -->
			<div v-if="withStatus && userStatusIcon" class="bx-im-avatar__status-icon">
				<UserStatus :status="userStatusIcon" :size="userStatusSize" />
			</div>
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
	      type: [Number, String],
	      default: 0
	    },
	    text: {
	      type: String,
	      default: ''
	    },
	    showItsYou: {
	      type: Boolean,
	      default: true
	    },
	    withLeftIcon: {
	      type: Boolean,
	      default: true
	    },
	    withColor: {
	      type: Boolean,
	      default: false
	    },
	    withMute: {
	      type: Boolean,
	      default: false
	    },
	    onlyFirstName: {
	      type: Boolean,
	      default: false
	    },
	    twoLine: {
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
	      return this.dialog.type === im_v2_const.DialogType.user;
	    },
	    isSelfChat() {
	      return this.isUser && this.user.id === im_v2_application_core.Core.getUserId();
	    },
	    containerClasses() {
	      const classes = [];
	      if (this.twoLine) {
	        classes.push('--twoline');
	      }
	      return classes;
	    },
	    dialogName() {
	      if (!this.dialogId && this.text) {
	        return this.text;
	      }
	      if (this.isUser) {
	        if (this.onlyFirstName) {
	          return this.user.firstName;
	        }
	        return this.user.name;
	      }
	      return this.dialog.name;
	    },
	    dialogSpecialType() {
	      if (!this.isUser) {
	        if (this.isExtranet) {
	          return DialogSpecialType.extranet;
	        } else if ([im_v2_const.DialogType.support24Notifier, im_v2_const.DialogType.support24Question].includes(this.dialog.type)) {
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
	      if (!this.withLeftIcon) {
	        return '';
	      }
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
	      if (!this.withColor || this.specialColor) {
	        return '';
	      }
	      return this.dialog.color;
	    },
	    specialColor() {
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
	        return element === im_v2_application_core.Core.getUserId();
	      });
	      return !!isMuted;
	    },
	    tooltipText() {
	      if (this.isSelfChat && this.showItsYou) {
	        return `${this.dialog.name} (${this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_SELF')})`;
	      }
	      return this.dialog.name;
	    },
	    showBirthdays() {
	      return this.$store.getters['application/settings/get'](im_v2_const.Settings.recent.showBirthday);
	    }
	  },
	  template: `
		<div :class="containerClasses" class="bx-im-chat-title__scope bx-im-chat-title__container">
			<span class="bx-im-chat-title__content">
				<span v-if="leftIcon" :class="'--' + leftIcon" class="bx-im-chat-title__icon"></span>
				<span
					:class="[specialColor? '--' + specialColor : '']"
					:style="{color: color}"
					:title="tooltipText"
					class="bx-im-chat-title__text"
				>
					{{ dialogName }}
				</span>
				<strong v-if="isSelfChat && showItsYou">
					<span class="bx-im-chat-title__text --self">({{ $Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_SELF') }})</span>
				</strong>
				<div v-if="withMute && isChatMuted" class="bx-im-chat-title__muted-icon"></div>
			</span>
		</div>
	`
	};

	const ButtonSize = {
	  S: 'S',
	  M: 'M',
	  L: 'L',
	  XL: 'XL',
	  XXL: 'XXL'
	};
	const ButtonColor = {
	  Primary: 'primary',
	  PrimaryLight: 'primary-light',
	  Success: 'success',
	  Danger: 'danger',
	  LightBorder: 'light-border',
	  DangerBorder: 'danger-border',
	  PrimaryBorder: 'primary-border',
	  Link: 'link'
	};
	const ButtonIcon = {
	  Plus: 'plus',
	  Link: 'link',
	  Call: 'call',
	  EndCall: 'end-call',
	  AddUser: 'add-user'
	};
	// @vue/component
	const Button = {
	  name: 'MessengerButton',
	  props: {
	    size: {
	      type: String,
	      required: true
	    },
	    text: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    icon: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    color: {
	      type: String,
	      required: false,
	      default: ButtonColor.Primary
	    },
	    customColorScheme: {
	      type: Object,
	      required: false,
	      default: () => {
	        return {
	          borderColor: '',
	          backgroundColor: '',
	          iconColor: '',
	          textColor: '',
	          hoverColor: ''
	        };
	      }
	    },
	    isRounded: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    isDisabled: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    isLoading: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    isUppercase: {
	      type: Boolean,
	      required: false,
	      default: true
	    }
	  },
	  emits: ['click'],
	  computed: {
	    buttonStyles() {
	      const result = {};
	      if (this.hasCustomColorScheme) {
	        result['borderColor'] = this.customColorScheme.borderColor;
	        result['backgroundColor'] = this.customColorScheme.backgroundColor;
	        result['color'] = this.customColorScheme.textColor;
	        result['--im-button__background-color_hover'] = this.customColorScheme.hoverColor;
	      }
	      return result;
	    },
	    buttonClasses() {
	      const classes = [`--size-${this.size.toLowerCase()}`];
	      if (!this.hasCustomColorScheme) {
	        classes.push(`--color-${this.color.toLowerCase()}`);
	      }
	      if (this.isRounded) {
	        classes.push('--rounded');
	      }
	      if (this.isDisabled) {
	        classes.push('--disabled');
	      }
	      if (this.isLoading) {
	        classes.push('--loading');
	      }
	      if (this.isUppercase && this.size !== ButtonSize.S) {
	        classes.push('--uppercase');
	      }
	      if (this.text === '') {
	        classes.push('--no-text');
	      }
	      return classes;
	    },
	    iconStyles() {
	      const result = {};
	      if (this.hasCustomColorScheme) {
	        result['backgroundColor'] = this.customColorScheme.iconColor;
	      }
	      return result;
	    },
	    iconClasses() {
	      const classes = [`--${this.icon}`];
	      if (this.hasCustomColorScheme) {
	        classes.push('--custom-color');
	      }
	      return classes;
	    },
	    hasCustomColorScheme() {
	      return main_core.Type.isStringFilled(this.customColorScheme.borderColor) && main_core.Type.isStringFilled(this.customColorScheme.iconColor) && main_core.Type.isStringFilled(this.customColorScheme.textColor) && main_core.Type.isStringFilled(this.customColorScheme.hoverColor);
	    }
	  },
	  methods: {
	    onClick(event) {
	      if (this.isDisabled || this.isLoading) {
	        return;
	      }
	      this.$emit('click', event);
	    }
	  },
	  template: `
		<button
			:class="buttonClasses"
			:style="buttonStyles"
			@click.stop="onClick"
			class="bx-im-button__scope bx-im-button__container"
		>
			<span v-if="icon" :style="iconStyles" :class="iconClasses" class="bx-im-button__icon"></span>
			<span class="bx-im-button__text">{{ text }}</span>
		</button>
	`
	};

	const POPUP_CONTAINER_PREFIX = '#popup-window-content-';

	// @vue/component
	const MessengerPopup = {
	  name: 'MessengerPopup',
	  props: {
	    id: {
	      type: String,
	      required: true
	    },
	    config: {
	      type: Object,
	      required: false,
	      default: function () {
	        return {};
	      }
	    }
	  },
	  emits: ['close'],
	  computed: {
	    popupContainer() {
	      return `${POPUP_CONTAINER_PREFIX}${this.id}`;
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn(`Popup: ${this.id} created`);
	    this.instance = this.getPopupInstance();
	    this.instance.show();
	  },
	  mounted() {
	    this.instance.adjustPosition({
	      forceBindPosition: true,
	      position: this.getPopupConfig().bindOptions.position
	    });
	  },
	  beforeUnmount() {
	    if (!this.instance) {
	      return;
	    }
	    this.closePopup();
	  },
	  methods: {
	    getPopupInstance() {
	      if (!this.instance) {
	        var _PopupManager$getPopu;
	        (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(this.id)) == null ? void 0 : _PopupManager$getPopu.destroy();
	        this.instance = new main_popup.Popup(this.getPopupConfig());
	      }
	      return this.instance;
	    },
	    getDefaultConfig() {
	      return {
	        id: this.id,
	        bindOptions: {
	          position: 'bottom'
	        },
	        offsetTop: 0,
	        offsetLeft: 0,
	        className: 'bx-im-messenger__scope',
	        cacheable: false,
	        closeIcon: false,
	        autoHide: true,
	        closeByEsc: true,
	        animation: 'fading',
	        events: {
	          onPopupClose: this.closePopup.bind(this)
	        }
	      };
	    },
	    getPopupConfig() {
	      var _this$config$offsetTo, _this$config$bindOpti;
	      const defaultConfig = this.getDefaultConfig();
	      const modifiedOptions = {};
	      const defaultClassName = defaultConfig.className;
	      if (this.config.className) {
	        modifiedOptions['className'] = `${defaultClassName} ${this.config.className}`;
	      }
	      const offsetTop = (_this$config$offsetTo = this.config.offsetTop) != null ? _this$config$offsetTo : defaultConfig.offsetTop;
	      // adjust for default popup margin for shadow
	      if (((_this$config$bindOpti = this.config.bindOptions) == null ? void 0 : _this$config$bindOpti.position) === 'top' && main_core.Type.isNumber(this.config.offsetTop)) {
	        modifiedOptions['offsetTop'] = offsetTop - 10;
	      }
	      return {
	        ...defaultConfig,
	        ...this.config,
	        ...modifiedOptions
	      };
	    },
	    closePopup() {
	      im_v2_lib_logger.Logger.warn(`Popup: ${this.id} closing`);
	      this.$emit('close');
	      this.instance.destroy();
	      this.instance = null;
	    },
	    enableAutoHide() {
	      this.getPopupInstance().setAutoHide(true);
	    },
	    disableAutoHide() {
	      this.getPopupInstance().setAutoHide(false);
	    },
	    adjustPosition() {
	      this.getPopupInstance().adjustPosition({
	        forceBindPosition: true,
	        position: this.getPopupConfig().bindOptions.position
	      });
	    }
	  },
	  template: `
		<Teleport :to="popupContainer">
			<slot
				:adjustPosition="adjustPosition"
				:enableAutoHide="enableAutoHide"
				:disableAutoHide="disableAutoHide"
			></slot>
		</Teleport>
	`
	};

	const MenuItemIcon = {
	  chat: 'chat',
	  channel: 'channel',
	  conference: 'conference',
	  disk: 'disk',
	  upload: 'upload',
	  task: 'task',
	  meeting: 'meeting',
	  summary: 'summary',
	  vote: 'vote'
	};

	// @vue/component
	const MenuItem = {
	  name: 'MenuItem',
	  props: {
	    icon: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    title: {
	      type: String,
	      required: true
	    },
	    subtitle: {
	      type: String,
	      required: false,
	      default: ''
	    },
	    disabled: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    counter: {
	      type: Number,
	      required: false,
	      default: 0
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    formattedCounter() {
	      if (this.counter === 0) {
	        return '';
	      }
	      return this.counter > 99 ? '99+' : `${this.counter}`;
	    }
	  },
	  template: `
		<div class="bx-im-menu-item__container" :class="{'--disabled': disabled}">
			<div class="bx-im-menu-item__content" :class="{'--with-icon': !!icon}">
				<div v-if="icon" class="bx-im-menu_item__icon" :class="'--' + icon"></div>
				<div class="bx-im-menu-item__text-content" :class="{'--with-subtitle': !!subtitle}">
					<div class="bx-im-menu-item__title">
						<div class="bx-im-menu-item__title_text">{{ title }}</div>
						<div v-if="counter" class="bx-im-menu-item__title_counter">{{ formattedCounter }}</div>
					</div>
					<div v-if="subtitle" :title="subtitle" class="bx-im-menu-item__subtitle">{{ subtitle }}</div>
				</div>
			</div>
		</div>
	`
	};

	const ID_PREFIX = 'im-v2-menu';

	// @vue/component
	const MessengerMenu = {
	  name: 'MessengerMenu',
	  components: {
	    MessengerPopup
	  },
	  props: {
	    config: {
	      type: Object,
	      required: true
	    },
	    className: {
	      type: String,
	      required: false,
	      default: ''
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      id: ''
	    };
	  },
	  created() {
	    var _this$config$id;
	    this.id = (_this$config$id = this.config.id) != null ? _this$config$id : `${ID_PREFIX}-${im_v2_lib_utils.Utils.text.getUuidV4()}`;
	  },
	  template: `
		<MessengerPopup
			:config="config"
			@close="$emit('close')"
			:id="id"
		>
			<div class="bx-im-menu__container" :class="className">
				<slot name="header"></slot>
				<slot></slot>
				<slot name="footer"></slot>
			</div>
		</MessengerPopup>
	`
	};

	// @vue/component
	const AttachDelimiter = {
	  name: 'AttachDelimiter',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    styles() {
	      var _this$internalConfig$;
	      const result = {
	        backgroundColor: (_this$internalConfig$ = this.internalConfig.DELIMITER.COLOR) != null ? _this$internalConfig$ : this.color
	      };
	      if (this.internalConfig.DELIMITER.SIZE) {
	        result.width = `${this.internalConfig.DELIMITER.SIZE}px`;
	      }
	      return result;
	    }
	  },
	  template: `
		<div class="bx-im-attach-delimiter__container" :style="styles"></div>
	`
	};

	const AttachFileItem = {
	  name: 'AttachFileItem',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    fileName() {
	      return this.internalConfig.NAME;
	    },
	    fileSize() {
	      return this.internalConfig.SIZE;
	    },
	    link() {
	      return this.internalConfig.LINK;
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 70;
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.fileName, NAME_MAX_LENGTH);
	    },
	    formattedFileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.fileSize);
	    },
	    iconClasses() {
	      return ['ui-icon', `ui-icon-file-${this.fileIcon}`];
	    },
	    fileIcon() {
	      return im_v2_lib_utils.Utils.file.getIconTypeByFilename(this.fileName);
	    }
	  },
	  methods: {
	    openLink() {
	      if (!this.link) {
	        return false;
	      }
	      window.open(this.link, '_blank');
	    }
	  },
	  template: `
		<div @click="openLink" class="bx-im-attach-file__container">
			<div class="bx-im-attach-file__item">
				<div class="bx-im-attach-file__icon">
					<div :class="iconClasses"><i></i></div>
				</div>
				<div class="bx-im-attach-file__block">
					<div class="bx-im-attach-file__name" :title="fileName">
						{{ fileShortName }}
					</div>
					<div class="bx-im-attach-file__size">
						{{ formattedFileSize }}
					</div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const AttachFile = {
	  name: 'AttachFile',
	  components: {
	    AttachFileItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-file__container">
			<AttachFileItem
				v-for="(fileItem, index) in internalConfig.FILE"
				:config="fileItem"
				:key="index"
			/>
		</div>
	`
	};

	const AttachGridItemDisplayType = {
	  block: 'block',
	  line: 'line',
	  row: 'row'
	};
	const DisplayType = AttachGridItemDisplayType;

	// @vue/component
	const AttachGridItem = {
	  name: 'AttachGridItem',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    DisplayType: () => DisplayType,
	    internalConfig() {
	      return this.config;
	    },
	    display() {
	      return this.internalConfig.DISPLAY.toLowerCase();
	    },
	    width() {
	      if (!this.value || !this.internalConfig.WIDTH) {
	        return '';
	      }
	      return `${this.internalConfig.WIDTH}px`;
	    },
	    value() {
	      if (!this.internalConfig.VALUE) {
	        return '';
	      }
	      return im_v2_lib_parser.Parser.decodeText(this.internalConfig.VALUE);
	    },
	    color() {
	      return this.internalConfig.COLOR || '';
	    },
	    name() {
	      return this.internalConfig.NAME;
	    },
	    link() {
	      return this.internalConfig.LINK;
	    }
	  },
	  template: `
		<div v-if="display === DisplayType.block" :style="{width}" class="bx-im-attach-grid__item --block">
			<div class="bx-im-attach-grid__name">{{ name }}</div>
			<div v-if="link" class="bx-im-attach-grid__value --link">
				<a :href="link" target="_blank" :style="{color}" v-html="value"></a>
			</div>
			<div v-else v-html="value" :style="{color}" class="bx-im-attach-grid__value"></div>
		</div>
		<div v-if="display === DisplayType.line" :style="{width}" class="bx-im-attach-grid__item --line">
			<div class="bx-im-attach-grid__name">{{ name }}</div>
			<div v-if="link" :style="{color}" class="bx-im-attach-grid__value --link">
				<a :href="link" target="_blank" v-html="value"></a>
			</div>
			<div v-else class="bx-im-attach-grid__value" :style="{color}" v-html="value"></div>
		</div>
		<div v-if="display === DisplayType.row" class="bx-im-attach-grid__item --row">
			<table>
				<tbody>
					<tr>
						<td v-if="name" :colspan="value? 1: 2" :style="{width}" class="bx-im-attach-grid__name">
							{{ name }}
						</td>
						<td
							v-if="value && link"
							:colspan="name? 1: 2"
							:style="{color}"
							class="bx-im-attach-grid__value --link"
						>
							<a :href="link" target="_blank" v-html="value"></a>
						</td>
						<td
							v-if="value && !link"
							:colspan="name? 1: 2"
							:style="{color}"
							v-html="value"
							class="bx-im-attach-grid__value"
						>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	`
	};

	// @vue/component
	const AttachGrid = {
	  name: 'AttachGrid',
	  components: {
	    AttachGridItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-grid__container">
			<AttachGridItem
				v-for="(gridItem, index) in internalConfig.GRID"
				:config="gridItem"
				:key="index"
			/>
		</div>
	`
	};

	const AttachHtml = {
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    html() {
	      return im_v2_lib_parser.Parser.decodeHtml(this.internalConfig.HTML);
	    }
	  },
	  template: `
		<div class="bx-im-element-attach-type-html" v-html="html"></div>
	`
	};

	const MAX_IMAGE_SIZE = 250;

	// @vue/component
	const AttachImageItem = {
	  name: 'AttachImageItem',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    width() {
	      return this.internalConfig.WIDTH;
	    },
	    height() {
	      return this.internalConfig.HEIGHT;
	    },
	    link() {
	      return this.internalConfig.LINK;
	    },
	    name() {
	      return this.internalConfig.NAME;
	    },
	    preview() {
	      return this.internalConfig.PREVIEW;
	    },
	    source() {
	      var _this$preview;
	      return (_this$preview = this.preview) != null ? _this$preview : this.link;
	    },
	    imageSize() {
	      if (!this.width && !this.height) {
	        return {};
	      }
	      const aspectRatio = this.width > MAX_IMAGE_SIZE ? MAX_IMAGE_SIZE / this.width : 1;
	      const sizes = {
	        width: this.width * aspectRatio,
	        height: this.height * aspectRatio
	      };
	      return {
	        width: `${sizes.width}px`,
	        height: `${sizes.height}px`,
	        backgroundSize: sizes.width < 100 || sizes.height < 100 ? 'contain' : 'initial'
	      };
	    }
	  },
	  methods: {
	    open() {
	      if (!this.link) {
	        return false;
	      }
	      window.open(this.link, '_blank');
	    },
	    lazyLoadCallback(event) {
	      const {
	        element
	      } = event;
	      if (!main_core.Dom.style(element, 'width')) {
	        main_core.Dom.style(element, 'width', `${element.offsetWidth}px`);
	      }
	      if (!main_core.Dom.style(element, 'height')) {
	        main_core.Dom.style(element, 'height', `${element.offsetHeight}px`);
	      }
	    }
	  },
	  template: `
		<div class="bx-im-attach-image__item" @click="open">
			<img
				v-lazyload="{callback: lazyLoadCallback}"
				:data-lazyload-src="source"
				:style="imageSize"
				:title="name"
				:alt="name"
				class="bx-im-attach-image__source"
			/>
		</div>
	`
	};

	const AttachImage = {
	  name: 'AttachImage',
	  components: {
	    AttachImageItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-image__container">
			<AttachImageItem v-for="(image, index) in internalConfig.IMAGE" :config="image" :key="index" />
		</div>
	`
	};

	// @vue/component
	const AttachLinkItem = {
	  name: 'AttachLinkItem',
	  components: {
	    AttachImage
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    link() {
	      return this.internalConfig.LINK;
	    },
	    name() {
	      var _this$internalConfig$;
	      return (_this$internalConfig$ = this.internalConfig.NAME) != null ? _this$internalConfig$ : this.link;
	    },
	    description() {
	      return this.internalConfig.DESC;
	    },
	    html() {
	      const content = this.internalConfig.HTML || this.description;
	      return im_v2_lib_parser.Parser.decodeText(content);
	    },
	    preview() {
	      return this.internalConfig.PREVIEW;
	    },
	    imageConfig() {
	      return {
	        IMAGE: [{
	          NAME: this.internalConfig.NAME,
	          PREVIEW: this.internalConfig.PREVIEW,
	          WIDTH: this.internalConfig.WIDTH,
	          HEIGHT: this.internalConfig.HEIGHT
	        }]
	      };
	    }
	  },
	  template: `
		<div class="bx-im-attach-link__item">
			<a v-if="link" :href="link" target="_blank" class="bx-im-attach-link__link">
				{{ name }}
			</a>
			<span v-else class="bx-im-attach-link__name">
				{{ name }}
			</span>
			<div v-if="internalConfig.HTML || description" class="bx-im-attach-link__desc" v-html="html"></div>
			<div v-if="preview" class="bx-im-attach-link__image">
				<AttachImage :config="imageConfig" :color="color" />
			</div>
		</div>
	`
	};

	// @vue/component
	const AttachLink = {
	  name: 'AttachLink',
	  components: {
	    AttachLinkItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-link__container">
			<AttachLinkItem v-for="(link, index) in internalConfig.LINK" :config="link" :key="index" />
		</div>
	`
	};

	// @vue/component
	const AttachMessage = {
	  name: 'AttachMessage',
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    message() {
	      return im_v2_lib_parser.Parser.decodeText(this.internalConfig.MESSAGE);
	    }
	  },
	  template: `
		<div class="bx-im-attach-message__container" v-html="message"></div>
	`
	};

	// @vue/component
	const AttachRichItem = {
	  name: 'AttachRichItem',
	  components: {
	    AttachImage
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    link() {
	      return this.internalConfig.LINK;
	    },
	    name() {
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(this.internalConfig.NAME);
	    },
	    description() {
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(this.internalConfig.DESC);
	    },
	    html() {
	      return this.internalConfig.HTML;
	    },
	    preview() {
	      return this.internalConfig.PREVIEW;
	    },
	    imageConfig() {
	      return {
	        IMAGE: [{
	          NAME: this.name,
	          PREVIEW: this.preview
	        }]
	      };
	    }
	  },
	  methods: {
	    openLink() {
	      if (!this.link) {
	        return false;
	      }
	      window.open(this.link, '_blank');
	    }
	  },
	  template: `
		<div class="bx-im-attach-rich__item">
			<div v-if="preview" class="bx-im-attach-rich__image">
				<AttachImage :config="imageConfig" :color="color" />
			</div>
			<div class="bx-im-attach-rich__block">
				<div class="bx-im-attach-rich__name" @click="openLink">{{ name }}</div>
				<div v-if="html || description" class="bx-im-attach-rich__desc">{{ html || description }}</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const AttachRich = {
	  components: {
	    AttachRichItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-rich__container">
			<AttachRichItem v-for="(rich, index) in internalConfig.RICH_LINK" :config="rich" :color="color" :key="index" />
		</div>
	`
	};

	const AVATAR_TYPE = {
	  user: 'user',
	  chat: 'chat',
	  bot: 'bot'
	};

	// @vue/component
	const AttachUserItem = {
	  name: 'AttachUserItem',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    name() {
	      return this.internalConfig.NAME;
	    },
	    avatar() {
	      return this.internalConfig.AVATAR;
	    },
	    avatarType() {
	      return this.internalConfig.AVATAR_TYPE;
	    },
	    link() {
	      return this.internalConfig.LINK;
	    },
	    avatarTypeClass() {
	      if (this.avatar) {
	        return '';
	      }
	      let avatarType = AVATAR_TYPE.user;
	      if (this.avatarType === AVATAR_TYPE.chat) {
	        avatarType = AVATAR_TYPE.chat;
	      } else if (this.avatarType === AVATAR_TYPE.bot) {
	        avatarType = AVATAR_TYPE.bot;
	      }
	      return `--${avatarType}`;
	    },
	    avatarTypeStyle() {
	      return {
	        backgroundColor: !this.avatar ? this.color : ''
	      };
	    }
	  },
	  template: `
		<div class="bx-im-attach-user__item">
			<div class="bx-im-attach-user__avatar" :class="avatarTypeClass" :style="avatarTypeStyle">
				<img v-if="avatar" v-lazyload :data-lazyload-src="avatar" class="bx-im-attach-user__source" alt="name" />
			</div>
			<a v-if="link" :href="link" class="bx-im-attach-user__name" target="_blank">
				{{ name }}
			</a>
			<span class="bx-im-attach-user__name" v-else>
				{{ name }}
			</span>
		</div>
	`
	};

	// @vue/component
	const AttachUser = {
	  name: 'AttachUser',
	  components: {
	    AttachUserItem
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-user__container">
			<AttachUserItem v-for="(user, index) in internalConfig.USER" :config="user" :color="color" :key="index" />
		</div>
	`
	};

	const PropertyToComponentMap = {
	  [im_v2_const.AttachType.Delimiter]: AttachDelimiter,
	  [im_v2_const.AttachType.File]: AttachFile,
	  [im_v2_const.AttachType.Grid]: AttachGrid,
	  [im_v2_const.AttachType.Html]: AttachHtml,
	  [im_v2_const.AttachType.Image]: AttachImage,
	  [im_v2_const.AttachType.Link]: AttachLink,
	  [im_v2_const.AttachType.Message]: AttachMessage,
	  [im_v2_const.AttachType.Rich]: AttachRich,
	  [im_v2_const.AttachType.User]: AttachUser
	};

	// @vue/component
	const Attach = {
	  name: 'MessengerAttach',
	  components: {
	    AttachDelimiter,
	    AttachFile,
	    AttachGrid,
	    AttachHtml,
	    AttachImage,
	    AttachLink,
	    AttachMessage,
	    AttachRich,
	    AttachUser
	  },
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    baseColor: {
	      type: String,
	      default: im_v2_const.Color.base
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    blocks() {
	      return this.internalConfig.BLOCKS;
	    },
	    color() {
	      if (!this.internalConfig.COLOR) {
	        return this.baseColor;
	      }
	      if (this.internalConfig.COLOR === im_v2_const.Color.transparent) {
	        return '';
	      }
	      return this.internalConfig.COLOR;
	    }
	  },
	  methods: {
	    getComponentForBlock(block) {
	      const [blockType] = Object.keys(block);
	      if (!PropertyToComponentMap[blockType]) {
	        return '';
	      }
	      return PropertyToComponentMap[blockType];
	    }
	  },
	  template: `
		<div class="bx-im-attach__container bx-im-attach__scope">
			<div v-if="color" class="bx-im-attach__border" :style="{borderColor: color}"></div>
			<div class="bx-im-attach__content">
				<component
					v-for="(block, index) in blocks"
					:is="getComponentForBlock(block)"
					:config="block"
					:color="color"
					:key="index"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const ChatInfoContent = {
	  components: {
	    Avatar,
	    ChatTitle,
	    Button
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      hasError: false,
	      isLoading: false
	    };
	  },
	  computed: {
	    ButtonColor: () => ButtonColor,
	    ButtonSize: () => ButtonSize,
	    dialog() {
	      return this.$store.getters['dialogues/get'](this.dialogId);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    isUser() {
	      var _this$dialog;
	      return ((_this$dialog = this.dialog) == null ? void 0 : _this$dialog.type) === im_v2_const.DialogType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    chatType() {
	      if (this.isUser) {
	        return this.$store.getters['users/getPosition'](this.dialogId);
	      }
	      return this.$Bitrix.Loc.getMessage('IM_LIST_RECENT_CHAT_TYPE_GROUP_V2');
	    },
	    openChatButtonText() {
	      if (this.isChat) {
	        return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_OPEN_CHAT');
	      }
	      return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_WRITE_A_MESSAGE');
	    },
	    userProfileLink() {
	      return im_v2_lib_utils.Utils.user.getProfileLink(this.dialogId);
	    }
	  },
	  created() {
	    this.chatService = new im_v2_provider_service.ChatService();
	    if (!this.dialog) {
	      this.loadChat();
	    }
	  },
	  methods: {
	    loadChat() {
	      this.isLoading = true;
	      this.chatService.loadChat(this.dialogId).then(() => {
	        this.isLoading = false;
	      }).catch(error => {
	        this.isLoading = false;
	        this.hasError = true;
	        console.error(error);
	      });
	    },
	    onOpenChat() {
	      im_public.Messenger.openChat(this.dialogId);
	    },
	    onClickVideoCall() {
	      im_public.Messenger.startVideoCall(this.dialogId);
	    }
	  },
	  template: `
		<div class="bx-im-chat-info-content__container">
			<template v-if="!isLoading && !hasError">
				<div class="bx-im-chat-info-content__detail-info-container">
					<div class="bx-im-chat-info-content__avatar-container">
						<Avatar :dialogId="dialogId" size="XL"/>
					</div>
					<div class="bx-im-chat-info-content__title-container">
						<ChatTitle v-if="isChat" :dialogId="dialogId" />
						<a v-else :href="userProfileLink" target="_blank">
							<ChatTitle :dialogId="dialogId" />
						</a>
						<div class="bx-im-chat-info-content__chat-description_text">
							{{ chatType }}
						</div>
					</div>
				</div>
				<div class="bx-im-chat-info-content__buttons-container">
					<Button
						:size="ButtonSize.M"
						:color="ButtonColor.PrimaryBorder"
						:isRounded="true"
						:text="openChatButtonText"
						:isUppercase="false"
						@click="onOpenChat"
					/>
					<Button
						v-if="isUser"
						:size="ButtonSize.M"
						:color="ButtonColor.PrimaryBorder"
						:isRounded="true"
						:isUppercase="false"
						:text="$Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_VIDEOCALL')"
						@click="onClickVideoCall"
					/>
				</div>
			</template>
			<template v-else-if="isLoading">
				<div class="bx-im-chat-info-content__loader-container">
					<div class="bx-im-chat-info-content__loader_icon"></div>
				</div>
			</template>
			<template v-else-if="hasError">
				<div class="bx-im-chat-info-content__error-container">
					{{ $Bitrix.Loc.getMessage('IM_ELEMENTS_CHAT_INFO_POPUP_NO_ACCESS') }}
				</div>
			</template>
		</div>
	`
	};

	const POPUP_ID = 'im-chat-info-popup';

	// @vue/component
	const ChatInfoPopup = {
	  name: 'ChatInfoPopup',
	  components: {
	    MessengerPopup,
	    ChatInfoContent
	  },
	  props: {
	    showPopup: {
	      type: Boolean,
	      required: true
	    },
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        minWidth: 313,
	        height: 134,
	        bindElement: this.bindElement,
	        targetContainer: document.body,
	        offsetTop: 0,
	        padding: 16,
	        angle: true
	      };
	    }
	  },
	  template: `
		<MessengerPopup
			v-if="showPopup" 
			:config="config"
			@close="$emit('close')"
			:id="POPUP_ID"
		>
			<ChatInfoContent :dialogId="dialogId"/>
		</MessengerPopup>
	`
	};

	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class UserListService {
	  constructor() {
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  loadUsers(userIds) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imUserListGet, {
	      ID: userIds
	    }).then(response => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager].setUsersToModel(Object.values(response.data()));
	    });
	  }
	}

	const LOADER_SIZE = 'xs';
	const LOADER_TYPE = 'BULLET';

	// @vue/component
	const Loader = {
	  name: 'MessengerLoader',
	  mounted() {
	    this.loader = new ui_loader.Loader({
	      target: this.$refs['messenger-loader'],
	      type: LOADER_TYPE,
	      size: LOADER_SIZE
	    });
	    this.loader.render();
	    this.loader.show();
	  },
	  beforeUnmount() {
	    this.loader.hide();
	    this.loader = null;
	  },
	  template: `
		<div class="bx-im-elements-loader__container" ref="messenger-loader"></div>
	`
	};

	// @vue/component
	const UserItem = {
	  name: 'UserItem',
	  components: {
	    Avatar,
	    ChatTitle
	  },
	  props: {
	    userId: {
	      type: Number,
	      required: true
	    }
	  },
	  computed: {
	    AvatarSize: () => AvatarSize,
	    user() {
	      return this.$store.getters['users/get'](this.userId, true);
	    },
	    userDialogId() {
	      return this.userId.toString();
	    },
	    userStatusClasses() {
	      if (this.user.bot) {
	        return [];
	      }
	      const status = this.$store.getters['users/getStatus'](this.userId);
	      if (status === '') {
	        return [];
	      }
	      return ['bx-im-user-list-content__avatar-status', `--${status}`];
	    }
	  },
	  methods: {
	    onUserClick() {
	      im_public.Messenger.openChat(this.userDialogId);
	    }
	  },
	  template: `
		<div class="bx-im-user-list-content__user-container" @click="onUserClick">
			<div class="bx-im-user-list-content__avatar-container" :class="userStatusClasses">
				<Avatar :size="AvatarSize.XS" :dialogId="userDialogId" />
			</div>
			<ChatTitle class="bx-im-user-list-content__chat-title-container" :dialogId="userDialogId" />
		</div>
	`
	};

	// @vue/component
	const UserListContent = {
	  components: {
	    UserItem,
	    Loader
	  },
	  props: {
	    userIds: {
	      type: Array,
	      required: true
	    },
	    adjustPopupFunction: {
	      type: Function,
	      required: true
	    },
	    loading: {
	      type: Boolean,
	      required: false,
	      default: false
	    }
	  },
	  data() {
	    return {
	      hasError: false,
	      isLoadingUsers: false
	    };
	  },
	  computed: {
	    isLoading() {
	      return this.loading || this.isLoadingUsers;
	    }
	  },
	  watch: {
	    userIds() {
	      this.$nextTick(() => {
	        this.adjustPopupFunction();
	      });
	    }
	  },
	  created() {
	    if (this.needUserRequest()) {
	      this.requestUserData();
	    }
	  },
	  methods: {
	    getUserListService() {
	      if (!this.userListService) {
	        this.userListService = new UserListService();
	      }
	      return this.userListService;
	    },
	    getUser(userId) {
	      return this.$store.getters['users/get'](userId);
	    },
	    needUserRequest() {
	      return this.userIds.some(userId => !this.getUser(userId));
	    },
	    requestUserData() {
	      this.isLoadingUsers = true;
	      this.getUserListService().loadUsers(this.userIds).then(() => {
	        this.isLoadingUsers = false;
	      }).catch(error => {
	        console.error(error);
	        this.hasError = true;
	        this.isLoadingUsers = false;
	      });
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-user-list-content__container bx-im-user-list-content__scope">
			<template v-if="!isLoading && !hasError">
				<UserItem v-for="userId in userIds" :userId="userId" />
			</template>
			<div v-else-if="isLoading" class="bx-im-user-list-content__loader-container">
				<Loader />
			</div>
			<div v-else-if="hasError">
				{{ loc('IM_ELEMENTS_CHAT_INFO_POPUP_NO_ACCESS') }}
			</div>
		</div>
	`
	};

	const POPUP_ID$1 = 'im-user-list-popup';

	// @vue/component
	const UserListPopup = {
	  name: 'UserListPopup',
	  components: {
	    MessengerPopup,
	    UserListContent
	  },
	  props: {
	    showPopup: {
	      type: Boolean,
	      required: true
	    },
	    id: {
	      type: String,
	      required: false,
	      default: POPUP_ID$1
	    },
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    userIds: {
	      type: Array,
	      required: true
	    },
	    withAngle: {
	      type: Boolean,
	      required: false,
	      default: true
	    },
	    loading: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    forceTop: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    offsetLeft: {
	      type: Number,
	      required: false,
	      default: 0
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID$1,
	    config() {
	      const config = {
	        bindElement: this.bindElement,
	        targetContainer: document.body,
	        offsetTop: 4,
	        offsetLeft: this.offsetLeft,
	        padding: 0,
	        angle: this.withAngle
	      };
	      if (this.forceTop) {
	        config.bindOptions = {
	          position: 'top'
	        };
	      }
	      return config;
	    }
	  },
	  template: `
		<MessengerPopup
			v-if="showPopup"
			v-slot="{adjustPosition}"
			:config="config"
			@close="$emit('close')"
			:id="id"
		>
			<UserListContent :userIds="userIds" :loading="loading" :adjustPopupFunction="adjustPosition"/>
		</MessengerPopup>
	`
	};

	/**
	 * Bitrix Messenger
	 * Attach element Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	const ButtonType = Object.freeze({
	  newline: 'NEWLINE',
	  button: 'BUTTON'
	});
	const Keyboard = {
	  /*
	   * @emits 'click' {action: string, params: Object}
	   */
	  props: {
	    buttons: {
	      type: Array,
	      default: () => []
	    },
	    messageId: {
	      default: 0
	    },
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: 0
	    }
	  },
	  data: function () {
	    return {
	      isMobile: im_v2_lib_utils.Utils.platform.isMobile(),
	      isBlocked: false,
	      localButtons: []
	    };
	  },
	  created() {
	    this.localButtons = this.prepareButtons(this.buttons);
	  },
	  watch: {
	    buttons() {
	      clearTimeout(this.recoverStateButton);
	      this.isBlocked = false;
	      this.localButtons = this.prepareButtons(this.buttons);
	    }
	  },
	  methods: {
	    click(button) {
	      if (this.isBlocked) {
	        return false;
	      }
	      if (button.DISABLED && button.DISABLED === 'Y') {
	        return false;
	      }
	      if (button.ACTION && button.ACTION_VALUE.toString()) {
	        this.$emit('click', {
	          action: 'ACTION',
	          params: {
	            dialogId: this.dialogId,
	            messageId: this.messageId,
	            botId: button.BOT_ID,
	            action: button.ACTION,
	            value: button.ACTION_VALUE
	          }
	        });
	      } else if (button.FUNCTION) {
	        const execFunction = button.FUNCTION.toString().replace('#MESSAGE_ID#', this.messageId).replace('#DIALOG_ID#', this.dialogId).replace('#USER_ID#', this.userId);
	        eval(execFunction);
	      } else if (button.APP_ID) {
	        im_v2_lib_logger.Logger.warn('Messenger keyboard: open app is not implemented.');
	      } else if (button.LINK) {
	        if (im_v2_lib_utils.Utils.platform.isBitrixMobile()) {
	          app.openNewPage(button.LINK);
	        } else {
	          window.open(button.LINK, '_blank');
	        }
	      } else if (button.WAIT !== 'Y') {
	        if (button.BLOCK === 'Y') {
	          this.isBlocked = true;
	        }
	        button.WAIT = 'Y';
	        this.$emit('click', {
	          action: 'COMMAND',
	          params: {
	            dialogId: this.dialogId,
	            messageId: this.messageId,
	            botId: button.BOT_ID,
	            command: button.COMMAND,
	            params: button.COMMAND_PARAMS
	          }
	        });
	        this.recoverStateButton = setTimeout(() => {
	          this.isBlocked = false;
	          button.WAIT = 'N';
	        }, 10000);
	      }
	      return true;
	    },
	    getStyles(button) {
	      const styles = {};
	      if (button.WIDTH) {
	        styles['width'] = `${button.WIDTH}px`;
	      } else if (button.DISPLAY === 'BLOCK') {
	        styles['width'] = '225px';
	      }
	      if (button.BG_COLOR) {
	        styles['backgroundColor'] = button.BG_COLOR;
	      }
	      if (button.TEXT_COLOR) {
	        styles['color'] = button.TEXT_COLOR;
	      }
	      return styles;
	    },
	    prepareButtons(buttons) {
	      return buttons.filter(button => {
	        if (!button.CONTEXT) {
	          return true;
	        }
	        if (im_v2_lib_utils.Utils.platform.isBitrixMobile() && button.CONTEXT === 'DESKTOP') {
	          return false;
	        }
	        if (!im_v2_lib_utils.Utils.platform.isBitrixMobile() && button.CONTEXT === 'MOBILE') {
	          return false;
	        }

	        // TODO activate this buttons
	        if (!im_v2_lib_utils.Utils.platform.isBitrixMobile() && (button.ACTION === 'DIALOG' || button.ACTION === 'CALL')) {
	          return false;
	        }
	        return true;
	      });
	    }
	  },
	  computed: {
	    ButtonType: () => ButtonType
	  },
	  template: `
		<div :class="['bx-im-element-keyboard', {'bx-im-element-keyboard-mobile': isMobile}]">
			<template v-for="(button, index) in localButtons">
				<div v-if="button.TYPE === ButtonType.newline" class="bx-im-element-keyboard-button-separator"></div>
				<span v-else-if="button.TYPE === ButtonType.button" :class="[
					'bx-im-element-keyboard-button', 
					'bx-im-element-keyboard-button-'+button.DISPLAY.toLowerCase(), 
					{
						'bx-im-element-keyboard-button-disabled': isBlocked || button.DISABLED === 'Y',
						'bx-im-element-keyboard-button-progress': button.WAIT === 'Y',
					}
				]" @click="click(button)">
					<span class="bx-im-element-keyboard-button-text" :style="getStyles(button)">{{button.TEXT}}</span>
				</span>
			</template>
		</div>
	`
	};

	// @vue/component
	const Dropdown = {
	  name: 'ChatDropdown',
	  props: {
	    items: {
	      type: Object,
	      required: true
	    },
	    id: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['itemChange'],
	  data() {
	    return {
	      selectedElement: '',
	      menuOpened: false
	    };
	  },
	  computed: {
	    formattedItems() {
	      const map = {};
	      this.items.forEach(item => {
	        map[item.value] = item;
	      });
	      return map;
	    },
	    defaultItem() {
	      return this.items.find(item => {
	        return item.default === true;
	      });
	    }
	  },
	  created() {
	    this.menuInstance = null;
	    if (this.defaultItem) {
	      this.selectedElement = this.defaultItem.value;
	    }
	    this.$emit('itemChange', this.selectedElement);
	  },
	  methods: {
	    toggleMenu() {
	      if (!this.menuInstance) {
	        this.menuInstance = this.getMenuInstance();
	      }
	      if (this.menuOpened) {
	        this.menuInstance.close();
	        return;
	      }
	      this.menuInstance.show();
	      const width = this.$refs['container'].clientWidth;
	      this.menuInstance.getPopupWindow().setWidth(width);
	      this.menuOpened = true;
	    },
	    getMenuInstance() {
	      return main_popup.MenuManager.create({
	        id: this.id,
	        bindOptions: {
	          forceBindPosition: true,
	          position: 'bottom'
	        },
	        targetContainer: document.body,
	        bindElement: this.$refs['container'],
	        items: this.getMenuItems(),
	        events: {
	          onClose: () => {
	            this.menuOpened = false;
	          }
	        }
	      });
	    },
	    getMenuItems() {
	      return Object.values(this.formattedItems).map(item => {
	        return {
	          text: item.text,
	          onclick: () => {
	            this.selectedElement = item.value;
	            this.$emit('itemChange', item.value);
	            this.menuInstance.close();
	          }
	        };
	      });
	    }
	  },
	  template: `
		<div class="bx-im-dropdown__container bx-im-dropdown__scope">
			<div @click="toggleMenu" class="ui-ctl ui-ctl-xl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown" ref="container">
				<div class="ui-ctl-after ui-ctl-icon-angle"></div>
				<div class="ui-ctl-element">{{ formattedItems[selectedElement].text }}</div>
			</div>
		</div>
	`
	};

	const SpinnerSize = Object.freeze({
	  S: 'S',
	  L: 'L'
	});

	// @vue/component
	const Spinner = {
	  name: 'MessengerSpinner',
	  props: {
	    size: {
	      type: String,
	      default: SpinnerSize.S
	    }
	  },
	  computed: {
	    sizeClassName() {
	      return `--size-${this.size.toLowerCase()}`;
	    }
	  },
	  template: `
		<div class="bx-im-elements-spinner__container">
			<div class="bx-im-elements-spinner__spinner" :class="sizeClassName"></div>
		</div>
	`
	};

	const ToggleSize = {
	  S: 'S',
	  M: 'M'
	};

	// @vue/component
	const Toggle = {
	  name: 'ToggleControl',
	  props: {
	    size: {
	      type: String,
	      required: true
	    },
	    isEnabled: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['change'],
	  data() {
	    return {};
	  },
	  computed: {
	    containerClasses() {
	      const classes = [`--size-${this.size.toLowerCase()}`];
	      if (!this.isEnabled) {
	        classes.push('--off');
	      }
	      return classes;
	    }
	  },
	  methods: {
	    toggle() {
	      this.$emit('change', !this.isEnabled);
	    }
	  },
	  template: `
		<div @click="toggle" :class="containerClasses" class="bx-im-toggle__container bx-im-toggle__scope">
			<span class="bx-im-toggle__cursor"></span>
			<span class="bx-im-toggle__enabled"></span>
			<span class="bx-im-toggle__disabled"></span>
		</div>
	`
	};

	// @vue/component
	const File = {
	  name: 'FileComponent',
	  props: {
	    item: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    file() {
	      return this.item;
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 70;
	      return im_v2_lib_utils.Utils.file.getShortFileName(this.file.name, NAME_MAX_LENGTH);
	    },
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    iconClass() {
	      const iconType = im_v2_lib_utils.Utils.file.getIconTypeByFilename(this.file.name);
	      return `ui-icon-file-${iconType}`;
	    },
	    isImage() {
	      return this.file.type !== im_v2_const.FileType.image;
	    },
	    isVideo() {
	      return this.file.type !== im_v2_const.FileType.video;
	    },
	    canBeOpenedWithViewer() {
	      var _BX$UI;
	      return this.file.viewerAttrs && ((_BX$UI = BX.UI) == null ? void 0 : _BX$UI.Viewer);
	    },
	    viewerAttributes() {
	      return im_v2_lib_utils.Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
	    }
	  },
	  watch: {
	    'file.status'() {
	      this.getProgressBarManager().update();
	    },
	    'file.progress'() {
	      this.getProgressBarManager().update();
	    }
	  },
	  mounted() {
	    this.initProgressBar();
	  },
	  beforeUnmount() {
	    this.removeProgressBar();
	  },
	  methods: {
	    download() {
	      var _this$file$urlDownloa;
	      if (this.file.progress !== 100 || this.canBeOpenedWithViewer) {
	        return;
	      }
	      const url = (_this$file$urlDownloa = this.file.urlDownload) != null ? _this$file$urlDownloa : this.file.urlShow;
	      window.open(url, '_blank');
	    },
	    initProgressBar() {
	      if (this.file.progress === 100) {
	        return;
	      }
	      let blurElement;
	      if (this.file.progress < 0 || !this.isImage && !this.isVideo) {
	        blurElement = false;
	      }
	      this.progressBarManager = new im_v2_lib_progressbar.ProgressBarManager({
	        container: this.$refs['container'],
	        uploadState: this.file,
	        customConfig: {
	          blurElement
	        }
	      });
	      this.progressBarManager.subscribe(im_v2_lib_progressbar.ProgressBarManager.event.cancel, () => {
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.uploader.cancel, {
	          taskId: this.file.id
	        });
	      });
	      this.progressBarManager.subscribe(im_v2_lib_progressbar.ProgressBarManager.event.destroy, () => {
	        if (this.progressBar) {
	          this.progressBar = null;
	        }
	      });
	      this.progressBarManager.start();
	    },
	    removeProgressBar() {
	      if (!this.progressBarManager) {
	        return;
	      }
	      this.progressBarManager.destroy();
	    },
	    getProgressBarManager() {
	      return this.progressBarManager;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div @click="download" v-bind="viewerAttributes" class="bx-im-media-file__container bx-im-media-file__scope" ref="container">
			<div class="bx-im-media-file__icon">
				<div :class="iconClass" class="ui-icon"><i></i></div>
			</div>
			<div class="bx-im-media-file__right">
				<div :title="file.name" class="bx-im-element-file-name bx-im-media-file__name">
					{{ fileShortName }}
				</div>
				<div class="bx-im-element-file-size bx-im-media-file__size">{{ fileSize }}</div>
			</div>
		</div>
	`
	};

	const MAX_IMAGE_SIZE$1 = 250;

	// @vue/component
	const Image = ui_vue3.BitrixVue.cloneComponent(File, {
	  name: 'ImageComponent',
	  directives: {
	    lazyload: ui_vue3_directives_lazyload.lazyload
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    imageSize() {
	      const aspectRatio = this.file.width > MAX_IMAGE_SIZE$1 ? MAX_IMAGE_SIZE$1 / this.file.width : 1;
	      const sizes = {
	        width: this.file.width * aspectRatio,
	        height: this.file.height * aspectRatio
	      };
	      return {
	        width: `${sizes.width}px`,
	        height: `${sizes.height}px`,
	        backgroundSize: sizes.width < 100 || sizes.height < 100 ? 'contain' : 'initial'
	      };
	    }
	  },
	  template: `
		<div class="bx-im-media-image__container" @click="download" ref="container">
			<img
				v-lazyload
				data-lazyload-dont-hide
				:data-lazyload-src="file.urlPreview"
				v-bind="viewerAttributes"
				:title="loc('IM_ELEMENTS_MEDIA_IMAGE_TITLE', {'#NAME#': file.name, '#SIZE#': file.size})"
				:style="imageSize"
				class="bx-im-media-image__source"
			/>
		</div>
	`
	});

	// @vue/component
	const Audio = {
	  name: 'AudioComponent',
	  components: {
	    AudioPlayer: ui_vue3_components_audioplayer.AudioPlayer
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    messageType: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    file() {
	      return this.item;
	    },
	    playerBackgroundType() {
	      return this.messageType === im_v2_const.MessageType.self ? 'dark' : 'light';
	    }
	  },
	  template: `
		<div class="bx-im-media-audio__container">
			<AudioPlayer :id="file.id" :src="file.urlShow" :background="playerBackgroundType" />
		</div>
	`
	};

	const VIDEO_SIZE_TO_AUTOPLAY = 5000000;

	// @vue/component
	const Video = ui_vue3.BitrixVue.cloneComponent(Image, {
	  name: 'VideoComponent',
	  components: {
	    SocialVideo: ui_vue3_components_socialvideo.SocialVideo
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    autoplay() {
	      return this.file.size < VIDEO_SIZE_TO_AUTOPLAY;
	    }
	  },
	  template: `
		<div @click="download" class="bx-im-media-video__container" ref="container">
			<SocialVideo
				v-bind="viewerAttributes"
				:id="file.id"
				:src="file.urlShow"
				:preview="file.urlPreview"
				:containerStyle="{height: '162px'}"
				:elementStyle="imageSize"
				:autoplay="autoplay"
				:showControls="!file.viewerAttrs"
			/>
		</div>
	`
	});

	const ARROW_CONTROL_SIZE = 50;
	const TabsColorScheme = Object.freeze({
	  white: 'white',
	  gray: 'gray'
	});

	// @vue/component
	const MessengerTabs = {
	  name: 'MessengerTabs',
	  props: {
	    colorScheme: {
	      type: String,
	      required: true,
	      default: TabsColorScheme.white,
	      validator: value => Object.values(TabsColorScheme).includes(value.toLowerCase())
	    },
	    tabs: {
	      type: Array,
	      default: () => []
	    }
	  },
	  data() {
	    return {
	      hasLeftControl: false,
	      hasRightControl: false,
	      currentElementIndex: 0,
	      highlightOffsetLeft: 0,
	      highlightWidth: 0
	    };
	  },
	  computed: {
	    highlightStyle() {
	      return {
	        left: `${this.highlightOffsetLeft}px`,
	        width: `${this.highlightWidth}px`
	      };
	    },
	    colorSchemeClass() {
	      return this.colorScheme === TabsColorScheme.white ? '--white' : '--gray';
	    }
	  },
	  watch: {
	    currentElementIndex(newIndex) {
	      this.updateHighlightPosition(newIndex);
	      this.$emit('tabSelect', this.tabs[newIndex]);
	      this.scrollToElement(newIndex);
	    }
	  },
	  mounted() {
	    if (this.$refs.tabs.scrollWidth > this.$refs.tabs.offsetWidth) {
	      this.hasRightControl = true;
	    }
	    this.updateHighlightPosition(this.currentElementIndex);
	  },
	  methods: {
	    getElementNodeByIndex(index) {
	      return [...this.$refs.tabs.children].filter(node => !main_core.Dom.hasClass(node, 'bx-im-elements-tabs__highlight'))[index];
	    },
	    updateHighlightPosition(index) {
	      const element = this.getElementNodeByIndex(index);
	      this.highlightOffsetLeft = element.offsetLeft;
	      this.highlightWidth = element.offsetWidth;
	    },
	    scrollToElement(elementIndex) {
	      const element = this.getElementNodeByIndex(elementIndex);
	      this.$refs.tabs.scroll({
	        left: element.offsetLeft - ARROW_CONTROL_SIZE,
	        behavior: 'smooth'
	      });
	    },
	    onTabClick(event) {
	      this.currentElementIndex = event.index;
	    },
	    isSelectedTab(index) {
	      return index === this.currentElementIndex;
	    },
	    onLeftClick() {
	      if (this.currentElementIndex <= 0) {
	        return;
	      }
	      this.currentElementIndex--;
	    },
	    onRightClick() {
	      if (this.currentElementIndex >= this.tabs.length - 1) {
	        return;
	      }
	      this.currentElementIndex++;
	    },
	    updateControlsVisibility() {
	      this.hasRightControl = this.$refs.tabs.scrollWidth > this.$refs.tabs.scrollLeft + this.$refs.tabs.clientWidth;
	      this.hasLeftControl = this.$refs.tabs.scrollLeft > 0;
	    }
	  },
	  template: `
		<div class="bx-im-elements-tabs__container bx-im-elements-tabs__scope" :class="colorSchemeClass">
			<div v-if="hasLeftControl" @click.stop="onLeftClick" class="bx-im-elements-tabs__control --left">
				<div class="bx-im-elements-tabs__forward-icon"></div>
			</div>
			<div v-if="hasRightControl" @click.stop="onRightClick" class="bx-im-elements-tabs__control --right">
				<div class="bx-im-elements-tabs__forward-icon"></div>
			</div>
			<div class="bx-im-elements-tabs__elements" ref="tabs" @scroll.passive="updateControlsVisibility">
				<div class="bx-im-elements-tabs__highlight" :style="highlightStyle"></div>
				<div
					v-for="(tab, index) in tabs"
					:key="tab.id"
					class="bx-im-elements-tabs__item"
					:class="[isSelectedTab(index) ? '--selected' : '']"
					@click="onTabClick({index: index})"
					:title="tab.title"
				>
					<div class="bx-im-elements-tabs__item-title">{{ tab.title }}</div>
				</div>
			</div>
		</div>
	`
	};

	exports.Avatar = Avatar;
	exports.AvatarSize = AvatarSize;
	exports.RecentLoadingState = RecentLoadingState;
	exports.ChatTitle = ChatTitle;
	exports.Button = Button;
	exports.ButtonColor = ButtonColor;
	exports.ButtonSize = ButtonSize;
	exports.ButtonIcon = ButtonIcon;
	exports.MessengerPopup = MessengerPopup;
	exports.MessengerMenu = MessengerMenu;
	exports.MenuItem = MenuItem;
	exports.MenuItemIcon = MenuItemIcon;
	exports.Attach = Attach;
	exports.ChatInfoPopup = ChatInfoPopup;
	exports.UserListPopup = UserListPopup;
	exports.Keyboard = Keyboard;
	exports.UserStatus = UserStatus;
	exports.UserStatusSize = UserStatusSize;
	exports.Dropdown = Dropdown;
	exports.Loader = Loader;
	exports.Spinner = Spinner;
	exports.SpinnerSize = SpinnerSize;
	exports.Toggle = Toggle;
	exports.ToggleSize = ToggleSize;
	exports.File = File;
	exports.Image = Image;
	exports.Audio = Audio;
	exports.Video = Video;
	exports.MessengerTabs = MessengerTabs;
	exports.TabsColorScheme = TabsColorScheme;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.UI,BX.Messenger.v2.Lib,BX.Messenger.v2.Model,BX.Messenger.v2.Lib,BX.Main,BX,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Vue3.Directives,BX.Vue3.Components,BX.Messenger.v2.Const,BX.Vue3,BX.Vue3.Components,BX));
//# sourceMappingURL=registry.bundle.js.map
