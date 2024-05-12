/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_fonts_opensans,ui_icons_disk,im_v2_lib_parser,rest_client,ui_vue3_directives_lazyload,ui_loader,im_v2_model,main_core_events,ui_notification,im_public,im_v2_provider_service,im_v2_lib_phone,main_popup,ui_forms,ui_vue3_components_audioplayer,ui_vue3,im_v2_lib_textHighlighter,im_v2_lib_utils,im_v2_lib_permission,main_core,im_v2_lib_dateFormatter,im_v2_application_core,im_v2_const,im_v2_lib_user,im_v2_lib_logger) {
	'use strict';

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
	  data() {
	    return {
	      imageLoadError: false
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.bot;
	      }
	      return false;
	    },
	    isSpecialType() {
	      const commonTypes = [im_v2_const.ChatType.user, im_v2_const.ChatType.chat, im_v2_const.ChatType.open];
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
	    isEnoughSizeForText() {
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
		<div :title="containerTitle" :class="containerClasses" class="bx-im-avatar__scope bx-im-avatar__container">
			<!-- Avatar -->
			<template v-if="hasImage">
				<img :src="avatarUrl" :alt="dialog.name" class="bx-im-avatar__content --image" @error="onImageLoadError" draggable="false"/>
				<div v-if="withSpecialTypes && withSpecialTypeIcon && isSpecialType" :style="backgroundColorStyle" class="bx-im-avatar__special-type_icon"></div>
			</template>
			<div v-else-if="withAvatarLetters && avatarText" :style="backgroundColorStyle" class="bx-im-avatar__content --text">
				{{ avatarText }}
			</div>
			<div v-else :style="backgroundColorStyle" class="bx-im-avatar__content bx-im-avatar__icon"></div>
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
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    botType() {
	      if (!this.isUser) {
	        return '';
	      }
	      const {
	        type
	      } = this.$store.getters['users/bots/getByUserId'](this.dialogId);
	      return type;
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
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
	      let resultText = this.dialog.name;
	      if (!this.dialogId && this.text) {
	        resultText = this.text;
	      }
	      if (this.isUser) {
	        if (this.onlyFirstName) {
	          resultText = this.user.firstName;
	        }
	        resultText = this.user.name;
	      }
	      return main_core.Text.encode(resultText);
	    },
	    dialogSpecialType() {
	      if (!this.isUser) {
	        if (this.isExtranet) {
	          return DialogSpecialType.extranet;
	        }
	        if ([im_v2_const.ChatType.support24Notifier, im_v2_const.ChatType.support24Question].includes(this.dialog.type)) {
	          return DialogSpecialType.support24;
	        }
	        return '';
	      }
	      if (this.isBot) {
	        return this.botType;
	      }
	      if (this.isExtranet) {
	        return DialogSpecialType.extranet;
	      }
	      if (this.isNetwork) {
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
	      }
	      if (this.user.isAbsent) {
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
	      if (!this.isUser) {
	        return false;
	      }
	      return this.user.bot === true;
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
	      return Boolean(isMuted);
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
					v-html="dialogName"
				></span>
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
	  // 18
	  M: 'M',
	  // 26
	  L: 'L',
	  // 31
	  XL: 'XL',
	  // 39
	  XXL: 'XXL' // 47
	};

	const ButtonColor = {
	  Primary: 'primary',
	  PrimaryLight: 'primary-light',
	  Copilot: 'copilot',
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
	  AddUser: 'add-user',
	  Camera: 'camera'
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
	const POPUP_BORDER_RADIUS = '10px';

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
	      default() {
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
	          onPopupClose: this.closePopup.bind(this),
	          onPopupDestroy: this.closePopup.bind(this)
	        },
	        contentBorderRadius: POPUP_BORDER_RADIUS
	      };
	    },
	    getPopupConfig() {
	      var _this$config$offsetTo, _this$config$bindOpti;
	      const defaultConfig = this.getDefaultConfig();
	      const modifiedOptions = {};
	      const defaultClassName = defaultConfig.className;
	      if (this.config.className) {
	        modifiedOptions.className = `${defaultClassName} ${this.config.className}`;
	      }
	      const offsetTop = (_this$config$offsetTo = this.config.offsetTop) != null ? _this$config$offsetTo : defaultConfig.offsetTop;
	      // adjust for default popup margin for shadow
	      if (((_this$config$bindOpti = this.config.bindOptions) == null ? void 0 : _this$config$bindOpti.position) === 'top' && main_core.Type.isNumber(this.config.offsetTop)) {
	        modifiedOptions.offsetTop = offsetTop - 10;
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
	  file: 'file',
	  task: 'task',
	  meeting: 'meeting',
	  summary: 'summary',
	  vote: 'vote',
	  aiText: 'ai-text',
	  aiImage: 'ai-image'
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
	        backgroundColor: (_this$internalConfig$ = this.internalConfig.delimiter.color) != null ? _this$internalConfig$ : this.color
	      };
	      if (this.internalConfig.delimiter.size) {
	        result.width = `${this.internalConfig.delimiter.size}px`;
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
	      return this.internalConfig.name;
	    },
	    fileSize() {
	      return this.internalConfig.size;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    fileShortName() {
	      const NAME_MAX_LENGTH = 70;
	      const fileName = main_core.Type.isStringFilled(this.fileName) ? this.fileName : this.$Bitrix.Loc.getMessage('IM_ELEMENTS_ATTACH_RICH_FILE_NO_NAME');
	      return im_v2_lib_utils.Utils.file.getShortFileName(fileName, NAME_MAX_LENGTH);
	    },
	    formattedFileSize() {
	      if (!this.fileSize) {
	        return '';
	      }
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
	        return;
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
				v-for="(fileItem, index) in internalConfig.file"
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
	      return this.internalConfig.display.toLowerCase();
	    },
	    width() {
	      if (!this.value || !this.internalConfig.width) {
	        return '';
	      }
	      return `${this.internalConfig.width}px`;
	    },
	    value() {
	      if (!this.internalConfig.value) {
	        return '';
	      }
	      return im_v2_lib_parser.Parser.decodeText(this.internalConfig.value);
	    },
	    color() {
	      return this.internalConfig.color || '';
	    },
	    name() {
	      return this.internalConfig.name;
	    },
	    link() {
	      return this.internalConfig.link;
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
				v-for="(gridItem, index) in internalConfig.grid"
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
	      return im_v2_lib_parser.Parser.decodeHtml(this.internalConfig.html);
	    }
	  },
	  template: `
		<div class="bx-im-element-attach-type-html" v-html="html"></div>
	`
	};

	const MAX_IMAGE_SIZE = 272;

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
	      return this.internalConfig.width || 0;
	    },
	    height() {
	      return this.internalConfig.height || 0;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    name() {
	      return this.internalConfig.name;
	    },
	    preview() {
	      return this.internalConfig.preview;
	    },
	    source() {
	      var _this$preview;
	      return (_this$preview = this.preview) != null ? _this$preview : this.link;
	    },
	    imageSize() {
	      if (this.width === 0 || this.height === 0) {
	        return {};
	      }
	      const sizes = im_v2_lib_utils.Utils.file.resizeToFitMaxSize(this.width, this.height, MAX_IMAGE_SIZE);
	      return {
	        width: `${sizes.width}px`,
	        height: `${sizes.height}px`,
	        'object-fit': sizes.width < 100 || sizes.height < 100 ? 'cover' : 'contain'
	      };
	    },
	    hasWidth() {
	      return Boolean(this.imageSize.width);
	    }
	  },
	  methods: {
	    open() {
	      if (!this.link) {
	        return;
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
		<div class="bx-im-attach-image__item" :class="{'--with-width': hasWidth }" @click="open">
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
		<div class="bx-im-attach-image__container bx-im-attach-image__scope">
			<AttachImageItem v-for="(image, index) in internalConfig.image" :config="image" :key="index" />
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
	      return this.internalConfig.link;
	    },
	    name() {
	      var _this$internalConfig$;
	      return (_this$internalConfig$ = this.internalConfig.name) != null ? _this$internalConfig$ : this.link;
	    },
	    description() {
	      return this.internalConfig.desc;
	    },
	    html() {
	      const content = this.internalConfig.html || this.description;
	      return im_v2_lib_parser.Parser.decodeText(content);
	    },
	    preview() {
	      return this.internalConfig.preview;
	    },
	    imageConfig() {
	      return {
	        image: [{
	          name: this.internalConfig.name,
	          preview: this.internalConfig.preview,
	          width: this.internalConfig.width,
	          height: this.internalConfig.height
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
			<div v-if="internalConfig.html || description" class="bx-im-attach-link__desc" v-html="html"></div>
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
			<AttachLinkItem v-for="(link, index) in internalConfig.link" :config="link" :key="index" />
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
	      return im_v2_lib_parser.Parser.decodeText(this.internalConfig.message);
	    }
	  },
	  template: `
		<div class="bx-im-attach-message__container" v-html="message"></div>
	`
	};

	var _restClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _message = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("message");
	class RichService {
	  constructor(message) {
	    Object.defineProperty(this, _restClient, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _message, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _message)[_message] = message;
	  }
	  deleteRichLink(attachId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/deleteAttach', {
	      messageId: babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id,
	      attachId
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient)[_restClient].callMethod(im_v2_const.RestMethod.imV2ChatMessageDeleteRichUrl, {
	      messageId: babelHelpers.classPrivateFieldLooseBase(this, _message)[_message].id
	    }).catch(error => {
	      console.error('RichService: error deleting rich link', error);
	    });
	  }
	}

	// @vue/component
	const AttachRichItem = {
	  name: 'AttachRichItem',
	  components: {
	    AttachImage
	  },
	  inject: ['message'],
	  props: {
	    config: {
	      type: Object,
	      default: () => {}
	    },
	    color: {
	      type: String,
	      default: im_v2_const.Color.transparent
	    },
	    attachId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    },
	    link() {
	      return this.internalConfig.link;
	    },
	    name() {
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(this.internalConfig.name);
	    },
	    description() {
	      return im_v2_lib_utils.Utils.text.convertHtmlEntities(this.internalConfig.desc);
	    },
	    html() {
	      return this.internalConfig.html;
	    },
	    preview() {
	      return this.internalConfig.preview;
	    },
	    previewSize() {
	      var _this$internalConfig$, _this$internalConfig$2, _this$internalConfig$3, _this$internalConfig$4;
	      return {
	        width: (_this$internalConfig$ = (_this$internalConfig$2 = this.internalConfig.previewSize) == null ? void 0 : _this$internalConfig$2.width) != null ? _this$internalConfig$ : 0,
	        height: (_this$internalConfig$3 = (_this$internalConfig$4 = this.internalConfig.previewSize) == null ? void 0 : _this$internalConfig$4.height) != null ? _this$internalConfig$3 : 0
	      };
	    },
	    imageConfig() {
	      return {
	        image: [{
	          name: this.name,
	          preview: this.preview,
	          width: this.previewSize.width,
	          height: this.previewSize.height
	        }]
	      };
	    },
	    canShowDeleteIcon() {
	      if (!this.message) {
	        return false;
	      }
	      return this.message.authorId === im_v2_application_core.Core.getUserId();
	    },
	    deleteRichLinkTitle() {
	      return this.$Bitrix.Loc.getMessage('IM_ELEMENTS_ATTACH_RICH_LINK_DELETE');
	    },
	    imageStyles() {
	      if (this.previewSize.width === 0 || this.previewSize.height === 0) {
	        return {
	          width: '272px',
	          height: '272px'
	        };
	      }
	      return {};
	    }
	  },
	  methods: {
	    openLink() {
	      if (!this.link) {
	        return;
	      }
	      window.open(this.link, '_blank');
	    },
	    deleteRichLink() {
	      if (!this.message) {
	        return;
	      }
	      new RichService(this.message).deleteRichLink(this.attachId);
	    }
	  },
	  template: `
		<div class="bx-im-attach-rich__scope bx-im-attach-rich__container">
			<div class="bx-im-attach-rich__block">
				<div class="bx-im-attach-rich__name" @click="openLink">{{ name }}</div>
				<div v-if="html || description" class="bx-im-attach-rich__desc">{{ html || description }}</div>
				<button 
					v-if="canShowDeleteIcon" 
					class="bx-im-attach-rich__hide-icon"
					@click="deleteRichLink"
					:title="deleteRichLinkTitle"
				></button>
			</div>
			<div v-if="preview" class="bx-im-attach-rich__image" @click="openLink" :style="imageStyles">
				<AttachImage :config="imageConfig" :color="color" />
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
	    },
	    attachId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    internalConfig() {
	      return this.config;
	    }
	  },
	  template: `
		<div class="bx-im-attach-rich__container">
			<AttachRichItem 
				v-for="(rich, index) in internalConfig.richLink" 
				:config="rich" 
				:color="color" 
				:key="index" 
				:attachId="attachId" 
			/>
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
	      return this.internalConfig.name;
	    },
	    avatar() {
	      return this.internalConfig.avatar;
	    },
	    avatarType() {
	      return this.internalConfig.avatarType;
	    },
	    link() {
	      return this.internalConfig.link;
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
			<AttachUserItem v-for="(user, index) in internalConfig.user" :config="user" :color="color" :key="index" />
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
	      return this.internalConfig.blocks;
	    },
	    color() {
	      if (!this.internalConfig.color) {
	        return this.baseColor;
	      }

	      // todo: in future we should set color for rich link on the backend. Remove after we delete the old chat.
	      if (this.internalConfig.color === im_v2_const.Color.transparent && this.hasRichLink) {
	        return '#2FC6F6';
	      }
	      if (this.internalConfig.color === im_v2_const.Color.transparent) {
	        return '';
	      }
	      return this.internalConfig.color;
	    },
	    hasRichLink() {
	      return this.blocks.some(block => block[im_v2_const.AttachType.Rich]);
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
					:attachId="internalConfig.id.toString()"
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
	      return this.$store.getters['chats/get'](this.dialogId);
	    },
	    user() {
	      return this.$store.getters['users/get'](this.dialogId, true);
	    },
	    isUser() {
	      var _this$dialog;
	      return ((_this$dialog = this.dialog) == null ? void 0 : _this$dialog.type) === im_v2_const.ChatType.user;
	    },
	    isBot() {
	      if (this.isUser) {
	        return this.user.bot;
	      }
	      return false;
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
						v-if="isUser && !isBot"
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

	var _store$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _restClient$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("restClient");
	var _userManager = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("userManager");
	class UserListService {
	  constructor() {
	    Object.defineProperty(this, _store$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _restClient$1, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _userManager, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store$1)[_store$1] = im_v2_application_core.Core.getStore();
	    babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1] = im_v2_application_core.Core.getRestClient();
	    babelHelpers.classPrivateFieldLooseBase(this, _userManager)[_userManager] = new im_v2_lib_user.UserManager();
	  }
	  loadUsers(userIds) {
	    return babelHelpers.classPrivateFieldLooseBase(this, _restClient$1)[_restClient$1].callMethod(im_v2_const.RestMethod.imUserListGet, {
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
	    }
	  },
	  methods: {
	    onUserClick() {
	      im_public.Messenger.openChat(this.userDialogId);
	    }
	  },
	  template: `
		<div class="bx-im-user-list-content__user-container" @click="onUserClick">
			<div class="bx-im-user-list-content__avatar-container">
				<Avatar :size="AvatarSize.XS" :dialogId="userDialogId" />
			</div>
			<ChatTitle class="bx-im-user-list-content__chat-title-container" :dialogId="userDialogId" :showItsYou="false" />
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
	        // eslint-disable-next-line no-console
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

	// @vue/component
	const KeyboardButton = {
	  name: 'KeyboardButton',
	  props: {
	    config: {
	      type: Object,
	      required: true
	    },
	    keyboardBlocked: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['action', 'customCommand', 'blockKeyboard'],
	  data() {
	    return {};
	  },
	  computed: {
	    button() {
	      return this.config;
	    },
	    buttonClasses() {
	      const displayClass = this.button.display === im_v2_const.KeyboardButtonDisplay.block ? '--block' : '--line';
	      const classes = [displayClass];
	      if (this.keyboardBlocked || this.button.disabled) {
	        classes.push('--disabled');
	      }
	      if (this.button.wait) {
	        classes.push('--loading');
	      }
	      return classes;
	    },
	    buttonStyles() {
	      const styles = {};
	      const {
	        width,
	        bgColor,
	        textColor
	      } = this.button;
	      if (width) {
	        styles.width = `${width}px`;
	      }
	      if (bgColor) {
	        styles.backgroundColor = bgColor;
	      }
	      if (textColor) {
	        styles.color = textColor;
	      }
	      return styles;
	    }
	  },
	  methods: {
	    onClick() {
	      if (this.keyboardBlocked || this.button.disabled || this.button.wait) {
	        return;
	      }
	      if (this.button.action && this.button.actionValue) {
	        this.handleAction();
	      } else if (this.button.appId) {
	        im_v2_lib_logger.Logger.warn('Messenger keyboard: open app is not implemented.');
	      } else if (this.button.link) {
	        const preparedLink = main_core.Text.decode(this.button.link);
	        im_v2_lib_utils.Utils.browser.openLink(preparedLink);
	      } else if (this.button.command) {
	        this.handleCustomCommand();
	      }
	    },
	    handleAction() {
	      this.$emit('action', {
	        action: this.button.action,
	        payload: this.button.actionValue
	      });
	    },
	    handleCustomCommand() {
	      if (this.button.block) {
	        this.$emit('blockKeyboard');
	      }
	      this.button.wait = true;
	      this.$emit('customCommand', {
	        botId: this.button.botId,
	        command: this.button.command,
	        payload: this.button.commandParams
	      });
	    }
	  },
	  template: `
		<div
			class="bx-im-keyboard-button__container"
			:class="buttonClasses"
			:style="buttonStyles"
			@click="onClick"
		>
			{{ button.text }}
		</div>
	`
	};

	// @vue/component
	const KeyboardSeparator = {
	  name: 'KeyboardSeparator',
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-keyboard-button__separator"></div>
	`
	};

	var _dialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	var _actionHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actionHandlers");
	var _sendMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMessage");
	var _insertText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("insertText");
	var _startCall = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startCall");
	var _copyText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyText");
	var _openChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openChat");
	class ActionManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _openChat, {
	      value: _openChat2
	    });
	    Object.defineProperty(this, _copyText, {
	      value: _copyText2
	    });
	    Object.defineProperty(this, _startCall, {
	      value: _startCall2
	    });
	    Object.defineProperty(this, _insertText, {
	      value: _insertText2
	    });
	    Object.defineProperty(this, _sendMessage, {
	      value: _sendMessage2
	    });
	    Object.defineProperty(this, _dialogId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _actionHandlers, {
	      writable: true,
	      value: {
	        [im_v2_const.KeyboardButtonAction.send]: babelHelpers.classPrivateFieldLooseBase(this, _sendMessage)[_sendMessage].bind(this),
	        [im_v2_const.KeyboardButtonAction.put]: babelHelpers.classPrivateFieldLooseBase(this, _insertText)[_insertText].bind(this),
	        [im_v2_const.KeyboardButtonAction.call]: babelHelpers.classPrivateFieldLooseBase(this, _startCall)[_startCall].bind(this),
	        [im_v2_const.KeyboardButtonAction.copy]: babelHelpers.classPrivateFieldLooseBase(this, _copyText)[_copyText].bind(this),
	        [im_v2_const.KeyboardButtonAction.dialog]: babelHelpers.classPrivateFieldLooseBase(this, _openChat)[_openChat].bind(this)
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId] = dialogId;
	  }
	  handleAction(event) {
	    const {
	      action,
	      payload
	    } = event;
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _actionHandlers)[_actionHandlers][action]) {
	      // eslint-disable-next-line no-console
	      console.error('Keyboard: action not found');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _actionHandlers)[_actionHandlers][action](payload);
	  }
	}
	function _sendMessage2(payload) {
	  im_v2_provider_service.SendingService.getInstance().sendMessage({
	    text: payload,
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]
	  });
	}
	function _insertText2(payload) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	    text: payload
	  });
	}
	function _startCall2(payload) {
	  im_v2_lib_phone.PhoneManager.getInstance().startCall(payload);
	}
	function _copyText2(payload) {
	  var _BX$clipboard;
	  if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(payload)) {
	    BX.UI.Notification.Center.notify({
	      content: main_core.Loc.getMessage('IM_ELEMENTS_KEYBOARD_BUTTON_ACTION_COPY_SUCCESS')
	    });
	  }
	}
	function _openChat2(payload) {
	  im_public.Messenger.openChat(payload);
	}

	var _messageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageId");
	var _dialogId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	class BotService {
	  constructor(params) {
	    Object.defineProperty(this, _messageId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogId$1, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      messageId,
	      dialogId
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId] = messageId;
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId$1)[_dialogId$1] = dialogId;
	  }
	  sendCommand(event) {
	    const {
	      botId,
	      command,
	      payload
	    } = event;
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imMessageCommand, {
	      MESSAGE_ID: babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId],
	      DIALOG_ID: babelHelpers.classPrivateFieldLooseBase(this, _dialogId$1)[_dialogId$1],
	      BOT_ID: botId,
	      COMMAND: command,
	      COMMAND_PARAMS: payload
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('BotService: error sending command:', error);
	    });
	  }
	}

	const Keyboard = {
	  props: {
	    buttons: {
	      type: Array,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    messageId: {
	      type: [Number, String],
	      required: true
	    }
	  },
	  components: {
	    KeyboardButton,
	    KeyboardSeparator
	  },
	  data() {
	    return {
	      keyboardBlocked: false
	    };
	  },
	  emits: ['click'],
	  watch: {
	    buttons() {
	      this.keyboardBlocked = false;
	    }
	  },
	  computed: {
	    ButtonType: () => im_v2_const.KeyboardButtonType,
	    preparedButtons() {
	      return this.buttons.filter(button => {
	        return button.context !== im_v2_const.KeyboardButtonContext.mobile;
	      });
	    }
	  },
	  methods: {
	    onButtonActionClick(event) {
	      this.getActionManager().handleAction(event);
	    },
	    onButtonCustomCommandClick(event) {
	      this.getBotService().sendCommand(event);
	    },
	    getActionManager() {
	      if (!this.actionManager) {
	        this.actionManager = new ActionManager(this.dialogId);
	      }
	      return this.actionManager;
	    },
	    getBotService() {
	      if (!this.botService) {
	        this.botService = new BotService({
	          messageId: this.messageId,
	          dialogId: this.dialogId
	        });
	      }
	      return this.botService;
	    }
	  },
	  template: `
		<div class="bx-im-keyboard__container">
			<template v-for="button in preparedButtons">
				<KeyboardButton
					v-if="button.type === ButtonType.button"
					:config="button"
					:keyboardBlocked="keyboardBlocked"
					@blockKeyboard="keyboardBlocked = true"
					@action="onButtonActionClick"
					@customCommand="onButtonCustomCommandClick"
				/>
				<KeyboardSeparator v-else-if="button.type === ButtonType.newLine" />
			</template>
		</div>
	`
	};

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
	  },
	  beforeUnmount() {
	    var _this$menuInstance;
	    (_this$menuInstance = this.menuInstance) == null ? void 0 : _this$menuInstance.destroy();
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
	      const width = this.$refs.container.clientWidth;
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
	        bindElement: this.$refs.container,
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
	  XXS: 'XXS',
	  S: 'S',
	  L: 'L'
	});
	const SpinnerColor = Object.freeze({
	  grey: 'grey',
	  blue: 'blue'
	});

	// @vue/component
	const Spinner = {
	  name: 'MessengerSpinner',
	  props: {
	    size: {
	      type: String,
	      default: SpinnerSize.S
	    },
	    color: {
	      type: String,
	      default: SpinnerColor.blue
	    }
	  },
	  computed: {
	    sizeClassName() {
	      return `--size-${this.size.toLowerCase()}`;
	    },
	    colorClassName() {
	      return `--color-${this.color.toLowerCase()}`;
	    }
	  },
	  template: `
		<div class="bx-im-elements-spinner__container bx-im-elements-spinner__scope">
			<div class="bx-im-elements-spinner__spinner" :class="[sizeClassName, colorClassName]"></div>
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
	      highlightWidth: 0,
	      isFirstCall: true
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
	    const savedTabIndex = localStorage.getItem('lastOpenedTabIndex');
	    if (this.$refs.tabs.scrollWidth > this.$refs.tabs.offsetWidth) {
	      this.hasRightControl = true;
	    }
	    if (savedTabIndex) {
	      this.currentElementIndex = parseInt(savedTabIndex, 10);
	    }
	    this.updateHighlightPosition(this.currentElementIndex);
	    setTimeout(() => {
	      this.isFirstCall = false;
	    }, 100);
	  },
	  beforeUnmount() {
	    localStorage.setItem('lastOpenedTabIndex', this.currentElementIndex.toString());
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
				<div class="bx-im-elements-tabs__highlight" :class="isFirstCall ? '' : '--transition'" :style="highlightStyle"></div>
				<div
					v-for="(tab, index) in tabs"
					:key="tab.id"
					class="bx-im-elements-tabs__item"
					:class="[isSelectedTab(index) ? '--selected' : '']"
					@click="onTabClick({index: index})"
					:title="tab.title"
				>
					<div class="bx-im-elements-tabs__item-title" :class="isFirstCall ? '' : '--transition'">{{ tab.title }}</div>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const AudioPlayer$$1 = ui_vue3.BitrixVue.cloneComponent(ui_vue3_components_audioplayer.AudioPlayer, {
	  name: 'AudioPlayer',
	  components: {
	    Avatar
	  },
	  props: {
	    file: {
	      type: Object,
	      required: true
	    },
	    authorId: {
	      type: Number,
	      required: true
	    },
	    timelineType: {
	      type: Number,
	      required: true
	    },
	    withContextMenu: {
	      type: Boolean,
	      default: true
	    },
	    withAvatar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      ...this.parentData(),
	      showContextButton: false
	    };
	  },
	  computed: {
	    AvatarSize: () => AvatarSize,
	    fileSize() {
	      return im_v2_lib_utils.Utils.file.formatFileSize(this.file.size);
	    },
	    fileAuthorDialogId() {
	      return this.authorId.toString();
	    },
	    progressPosition() {
	      if (!this.loaded || this.state === ui_vue3_components_audioplayer.AudioPlayerState.none) {
	        return {
	          width: '100%'
	        };
	      }
	      return {
	        width: `${this.progressInPixel}px`
	      };
	    },
	    activeTimelineStyles() {
	      const TIMELINE_VERTICAL_SHIFT = 44;
	      const ACTIVE_TIMELINE_VERTICAL_SHIFT = 19;
	      const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT + ACTIVE_TIMELINE_VERTICAL_SHIFT;
	      return {
	        ...this.progressPosition,
	        'background-position-y': `-${shift}px`
	      };
	    },
	    timelineStyles() {
	      const TIMELINE_VERTICAL_SHIFT = 44;
	      const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT;
	      return {
	        'background-position-y': `-${shift}px`
	      };
	    }
	  },
	  template: `
		<div 
			class="bx-im-audio-player__container bx-im-audio-player__scope" 
			ref="body"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-audio-player__control-container">
				<button :class="['bx-im-audio-player__control-button', {
					'bx-im-audio-player__control-loader': loading,
					'bx-im-audio-player__control-play': !loading && state !== State.play,
					'bx-im-audio-player__control-pause': !loading && state === State.play,
				}]" @click="clickToButton"></button>
				<div v-if="withAvatar" class="bx-im-audio-player__author-avatar-container">
					<Avatar :dialogId="fileAuthorDialogId" :size="AvatarSize.XS"></Avatar>
				</div>
			</div>
			<div class="bx-im-audio-player__timeline-container">
				<div class="bx-im-audio-player__track-container" @click="setPosition" ref="track">
					<div class="bx-im-audio-player__track-mask" :style="timelineStyles"></div>
					<div class="bx-im-audio-player__track-mask --active" :style="activeTimelineStyles"></div>
					<div class="bx-im-audio-player__track-seek" :style="seekPosition"></div>
					<div class="bx-im-audio-player__track-event" @mousemove="seeking"></div>
				</div>
				<div class="bx-im-audio-player__timer-container">
					{{fileSize}}, {{labelTime}}
				</div>
			</div>
			<button
				v-if="showContextButton && withContextMenu"
				class="bx-im-messenger__context-menu-icon bx-im-audio-player__context-menu-button"
				@click="$emit('contextMenuClick', $event)"
			></button>
			<audio 
				v-if="src" 
				:src="src" 
				class="bx-im-audio-player__audio-source" 
				ref="source" 
				:preload="preload"
				@abort="audioEventRouter('abort', $event)"
				@error="audioEventRouter('error', $event)"
				@suspend="audioEventRouter('suspend', $event)"
				@canplay="audioEventRouter('canplay', $event)"
				@canplaythrough="audioEventRouter('canplaythrough', $event)"
				@durationchange="audioEventRouter('durationchange', $event)"
				@loadeddata="audioEventRouter('loadeddata', $event)"
				@loadedmetadata="audioEventRouter('loadedmetadata', $event)"
				@timeupdate="audioEventRouter('timeupdate', $event)"
				@play="audioEventRouter('play', $event)"
				@playing="audioEventRouter('playing', $event)"
				@pause="audioEventRouter('pause', $event)"
			></audio>
		</div>
	`
	});

	// @vue/component
	const ChatTitleWithHighlighting$$1 = ui_vue3.BitrixVue.cloneComponent(ChatTitle, {
	  name: 'ChatTitleWithHighlighting',
	  props: {
	    textToHighlight: {
	      type: String,
	      default: ''
	    }
	  },
	  computed: {
	    dialogName() {
	      // noinspection JSUnresolvedVariable
	      return im_v2_lib_textHighlighter.highlightText(this.parentDialogName, this.textToHighlight);
	    }
	  }
	});

	// @vue/component
	const SearchInput$$1 = {
	  name: 'SearchInput',
	  components: {
	    Spinner
	  },
	  props: {
	    placeholder: {
	      type: String,
	      default: ''
	    },
	    searchMode: {
	      type: Boolean,
	      default: true
	    },
	    withIcon: {
	      type: Boolean,
	      default: true
	    },
	    withLoader: {
	      type: Boolean,
	      default: false
	    },
	    isLoading: {
	      type: Boolean,
	      default: false
	    },
	    delayForFocusOnStart: {
	      type: Number,
	      default: 0
	    }
	  },
	  emits: ['queryChange', 'inputFocus', 'inputBlur', 'keyPressed', 'close'],
	  data() {
	    return {
	      query: '',
	      hasFocus: false
	    };
	  },
	  computed: {
	    SpinnerSize: () => SpinnerSize,
	    SpinnerColor: () => SpinnerColor,
	    isEmptyQuery() {
	      return this.query.length === 0;
	    }
	  },
	  watch: {
	    searchMode(newValue) {
	      if (newValue === true) {
	        this.focus();
	      } else {
	        this.query = '';
	        this.blur();
	      }
	    }
	  },
	  created() {
	    if (this.delayForFocusOnStart > 0) {
	      setTimeout(() => {
	        this.focus();
	      }, this.delayForFocusOnStart);
	    }
	  },
	  methods: {
	    onInputUpdate() {
	      this.$emit('queryChange', this.query);
	    },
	    onFocus() {
	      this.focus();
	      this.$emit('inputFocus');
	    },
	    onCloseClick() {
	      this.query = '';
	      this.hasFocus = false;
	      this.$emit('queryChange', this.query);
	      this.$emit('close');
	    },
	    onClearInput() {
	      this.query = '';
	      this.focus();
	      this.$emit('queryChange', this.query);
	    },
	    onKeyUp(event) {
	      if (im_v2_lib_utils.Utils.key.isCombination(event, 'Escape')) {
	        this.onEscapePressed();
	        return;
	      }
	      this.$emit('keyPressed', event);
	    },
	    onEscapePressed() {
	      if (this.isEmptyQuery) {
	        this.onCloseClick();
	        this.blur();
	      } else {
	        this.onClearInput();
	      }
	    },
	    focus() {
	      this.hasFocus = true;
	      this.$refs.searchInput.focus();
	    },
	    blur() {
	      this.hasFocus = false;
	      this.$refs.searchInput.blur();
	    }
	  },
	  template: `
		<div class="bx-im-search-input__scope bx-im-search-input__container" :class="{'--has-focus': hasFocus}">
			<div v-if="!isLoading" class="bx-im-search-input__search-icon"></div>
			<Spinner 
				v-if="withLoader && isLoading" 
				:size="SpinnerSize.XXS" 
				:color="SpinnerColor.grey" 
				class="bx-im-search-input__loader"
			/>
			<input
				@focus="onFocus"
				@input="onInputUpdate"
				@keyup="onKeyUp"
				v-model="query"
				class="bx-im-search-input__element"
				:class="{'--with-icon': withIcon}"
				:placeholder="placeholder"
				ref="searchInput"
			/>
			<div v-if="hasFocus" class="bx-im-search-input__close-icon" @click="onCloseClick"></div>
		</div>
	`
	};

	const INPUT_PADDING = 5;

	// @vue/component
	const EditableChatTitle = {
	  name: 'EditableChatTitle',
	  components: {
	    ChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['newTitleSubmit'],
	  data() {
	    return {
	      isEditing: false,
	      inputWidth: 0,
	      showEditIcon: false,
	      chatTitle: ''
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    canBeRenamed() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformAction(im_v2_const.ChatActionType.rename, this.dialogId);
	    },
	    inputStyle() {
	      return {
	        width: `calc(${this.inputWidth}ch + ${INPUT_PADDING}px)`
	      };
	    }
	  },
	  watch: {
	    chatTitle() {
	      this.inputWidth = this.chatTitle.length;
	    }
	  },
	  mounted() {
	    this.chatTitle = this.dialog.name;
	  },
	  methods: {
	    async onTitleClick() {
	      if (!this.canBeRenamed) {
	        return;
	      }
	      if (!this.chatTitle) {
	        this.chatTitle = this.dialog.name;
	      }
	      this.isEditing = true;
	      await this.$nextTick();
	      this.$refs.titleInput.focus();
	    },
	    onNewTitleSubmit() {
	      if (!this.isEditing) {
	        return;
	      }
	      this.isEditing = false;
	      const nameNotChanged = this.chatTitle === this.dialog.name;
	      if (nameNotChanged || this.chatTitle === '') {
	        return;
	      }
	      this.$emit('newTitleSubmit', this.chatTitle);
	    },
	    onEditCancel() {
	      this.isEditing = false;
	      this.showEditIcon = false;
	      this.chatTitle = this.dialog.name;
	    }
	  },
	  template: `
		<div
			v-if="!isEditing"
			@click="onTitleClick"
			@mouseover="showEditIcon = true"
			@mouseleave="showEditIcon = false"
			class="bx-im-elements-editable-chat-title__wrap"
			:class="{'--can-rename': canBeRenamed}"
		>
			<div class="bx-im-elements-editable-chat-title__container">
				<ChatTitle :dialogId="dialogId" :withMute="true" />
			</div>
			<div class="bx-im-elements-editable-chat-title__edit-icon_container">
				<div v-if="showEditIcon && canBeRenamed" class="bx-im-elements-editable-chat-title__edit-icon"></div>
			</div>
		</div>
		<div v-else class="bx-im-elements-editable-chat-title__input_container">
			<input
				v-model="chatTitle"
				:style="inputStyle"
				@focus="$event.target.select()"
				@blur="onNewTitleSubmit"
				@keyup.enter="onNewTitleSubmit"
				@keyup.esc="onEditCancel"
				type="text"
				class="bx-im-elements-editable-chat-title__input"
				ref="titleInput"
			/>
		</div>
	`
	};

	// @vue/component
	const ScrollWithGradient = {
	  name: 'ScrollWithGradient',
	  props: {
	    containerMaxHeight: {
	      type: Number,
	      default: 0,
	      required: false
	    },
	    gradientHeight: {
	      type: Number,
	      default: 0
	    },
	    withShadow: {
	      type: Boolean,
	      default: true
	    }
	  },
	  data() {
	    return {
	      showTopGradient: false,
	      showBottomGradient: false
	    };
	  },
	  computed: {
	    contentHeightStyle() {
	      if (!this.containerMaxHeight) {
	        return {
	          height: '100%'
	        };
	      }
	      return {
	        maxHeight: `${this.containerMaxHeight}px`
	      };
	    },
	    gradientHeightStyle() {
	      return {
	        maxHeight: `${this.gradientHeightStyle}px`
	      };
	    }
	  },
	  mounted() {
	    // const container = this.$refs['scroll-container'];
	    // this.showBottomGradient = container.scrollHeight > container.clientHeight;
	  },
	  methods: {
	    onScroll(event) {
	      this.$emit('scroll', event);
	      const scrollPosition = Math.ceil(event.target.scrollTop + event.target.clientHeight);
	      this.showBottomGradient = scrollPosition !== event.target.scrollHeight;
	      if (event.target.scrollTop === 0) {
	        this.showTopGradient = false;
	        return;
	      }
	      this.showTopGradient = true;
	    }
	  },
	  template: `
		<div class="bx-im-scroll-with-gradient__container">
			<Transition name="gradient-fade">
				<div v-if="showTopGradient" class="bx-im-scroll-with-gradient__gradient --top" :style="gradientHeightStyle">
					<div v-if="withShadow" class="bx-im-scroll-with-gradient__gradient-inner"></div>
				</div>
			</Transition>
			<div 
				class="bx-im-scroll-with-gradient__content" 
				:style="contentHeightStyle" 
				@scroll="onScroll"
				ref="scroll-container"
			>
				<slot></slot>
			</div>
			<Transition name="gradient-fade">
				<div v-if="showBottomGradient" class="bx-im-scroll-with-gradient__gradient --bottom" :style="gradientHeightStyle">
					<div v-if="withShadow" class="bx-im-scroll-with-gradient__gradient-inner"></div>
				</div>
			</Transition>
		</div>
	`
	};

	class UserService {
	  async loadReadUsers(messageId) {
	    im_v2_lib_logger.Logger.warn('Dialog-status: UserService: loadReadUsers', messageId);
	    const response = await im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imV2ChatMessageTailViewers, {
	      id: messageId
	    }).catch(error => {
	      // eslint-disable-next-line no-console
	      console.error('Dialog-status: UserService: loadReadUsers error', error);
	      throw new Error(error);
	    });
	    const users = response.data().users;
	    const userManager = new im_v2_lib_user.UserManager();
	    await userManager.setUsersToModel(Object.values(users));
	    return users.map(user => user.id);
	  }
	}

	// @vue/component
	const AdditionalUsers = {
	  components: {
	    UserListPopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    },
	    show: {
	      type: Boolean,
	      required: true
	    },
	    bindElement: {
	      type: Object,
	      required: true
	    }
	  },
	  emits: ['close'],
	  data() {
	    return {
	      showPopup: false,
	      loadingAdditionalUsers: false,
	      additionalUsers: []
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    }
	  },
	  watch: {
	    show(newValue, oldValue) {
	      if (!oldValue && newValue) {
	        this.showPopup = true;
	        void this.loadUsers();
	      }
	    }
	  },
	  methods: {
	    async loadUsers() {
	      this.loadingAdditionalUsers = true;
	      const userIds = await this.getUserService().loadReadUsers(this.dialog.lastMessageId).catch(() => {
	        this.loadingAdditionalUsers = false;
	      });
	      this.additionalUsers = this.prepareAdditionalUsers(userIds);
	      this.loadingAdditionalUsers = false;
	    },
	    onPopupClose() {
	      this.showPopup = false;
	      this.$emit('close');
	    },
	    prepareAdditionalUsers(userIds) {
	      const firstViewerId = this.dialog.lastMessageViews.firstViewer.userId;
	      return userIds.filter(userId => {
	        return userId !== im_v2_application_core.Core.getUserId() && userId !== firstViewerId;
	      });
	    },
	    getUserService() {
	      if (!this.userService) {
	        this.userService = new UserService();
	      }
	      return this.userService;
	    }
	  },
	  template: `
		<UserListPopup
			id="bx-im-dialog-read-users"
			:showPopup="showPopup"
			:loading="loadingAdditionalUsers"
			:userIds="additionalUsers"
			:bindElement="bindElement || {}"
			:withAngle="false"
			:forceTop="true"
			@close="onPopupClose"
		/>
	`
	};

	const TYPING_USERS_COUNT = 3;
	const MORE_USERS_CSS_CLASS = 'bx-im-dialog-chat-status__user-count';

	// @vue/component
	const DialogStatus = {
	  components: {
	    AdditionalUsers
	  },
	  props: {
	    dialogId: {
	      required: true,
	      type: String
	    }
	  },
	  data() {
	    return {
	      showAdditionalUsers: false,
	      additionalUsersLinkElement: null
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isUser() {
	      return this.dialog.type === im_v2_const.ChatType.user;
	    },
	    isChat() {
	      return !this.isUser;
	    },
	    typingStatus() {
	      if (!this.dialog.inited || this.dialog.writingList.length === 0) {
	        return '';
	      }
	      const firstTypingUsers = this.dialog.writingList.slice(0, TYPING_USERS_COUNT);
	      const text = firstTypingUsers.map(element => element.userName).join(', ');
	      const remainingUsersCount = this.dialog.writingList.length - TYPING_USERS_COUNT;
	      if (remainingUsersCount > 0) {
	        return this.loc('IM_ELEMENTS_STATUS_TYPING_PLURAL_MORE', {
	          '#USERS#': text,
	          '#COUNT#': remainingUsersCount
	        });
	      }
	      if (this.dialog.writingList.length > 1) {
	        return this.loc('IM_ELEMENTS_STATUS_TYPING_PLURAL', {
	          '#USERS#': text
	        });
	      }
	      return this.loc('IM_ELEMENTS_STATUS_TYPING', {
	        '#USER#': text
	      });
	    },
	    readStatus() {
	      if (!this.dialog.inited) {
	        return '';
	      }
	      if (this.lastMessageViews.countOfViewers === 0) {
	        return '';
	      }
	      if (this.isUser) {
	        return this.formatUserViewStatus();
	      }
	      return this.formatChatViewStatus();
	    },
	    lastMessageViews() {
	      return this.dialog.lastMessageViews;
	    }
	  },
	  methods: {
	    formatUserViewStatus() {
	      const {
	        date
	      } = this.lastMessageViews.firstViewer;
	      return this.loc('IM_ELEMENTS_STATUS_READ_USER', {
	        '#DATE#': im_v2_lib_dateFormatter.DateFormatter.formatByTemplate(date, im_v2_lib_dateFormatter.DateTemplate.messageReadStatus)
	      });
	    },
	    formatChatViewStatus() {
	      const {
	        countOfViewers,
	        firstViewer
	      } = this.lastMessageViews;
	      if (countOfViewers === 1) {
	        return this.loc('IM_ELEMENTS_STATUS_READ_CHAT', {
	          '#USER#': main_core.Text.encode(firstViewer.userName)
	        });
	      }
	      return this.loc('IM_ELEMENTS_STATUS_READ_CHAT_PLURAL', {
	        '#USERS#': main_core.Text.encode(firstViewer.userName),
	        '#LINK_START#': `<span class="${MORE_USERS_CSS_CLASS}" ref="moreUsersLink">`,
	        '#COUNT#': countOfViewers - 1,
	        '#LINK_END#': '</span>'
	      });
	    },
	    onClick(event) {
	      if (!event.target.matches(`.${MORE_USERS_CSS_CLASS}`)) {
	        return;
	      }
	      this.onMoreUsersClick();
	    },
	    onMoreUsersClick() {
	      this.additionalUsersLinkElement = document.querySelector(`.${MORE_USERS_CSS_CLASS}`);
	      this.showAdditionalUsers = true;
	    },
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div @click="onClick" class="bx-im-dialog-chat-status__container">
			<div v-if="typingStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --typing"></div>
				<div class="bx-im-dialog-chat-status__text">{{ typingStatus }}</div>
			</div>
			<div v-else-if="readStatus" class="bx-im-dialog-chat-status__content">
				<div class="bx-im-dialog-chat-status__icon --read"></div>
				<div v-html="readStatus" class="bx-im-dialog-chat-status__text"></div>
			</div>
			<AdditionalUsers
				:dialogId="dialogId"
				:show="showAdditionalUsers"
				:bindElement="additionalUsersLinkElement || {}"
				@close="showAdditionalUsers = false"
			/>
		</div>
	`
	};

	exports.Avatar = Avatar;
	exports.AvatarSize = AvatarSize;
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
	exports.SpinnerColor = SpinnerColor;
	exports.Toggle = Toggle;
	exports.ToggleSize = ToggleSize;
	exports.MessengerTabs = MessengerTabs;
	exports.TabsColorScheme = TabsColorScheme;
	exports.AudioPlayer = AudioPlayer$$1;
	exports.ChatTitleWithHighlighting = ChatTitleWithHighlighting$$1;
	exports.SearchInput = SearchInput$$1;
	exports.EditableChatTitle = EditableChatTitle;
	exports.ScrollWithGradient = ScrollWithGradient;
	exports.DialogStatus = DialogStatus;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX,BX.Messenger.v2.Lib,BX,BX.Vue3.Directives,BX.UI,BX.Messenger.v2.Model,BX.Event,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Main,BX,BX.Vue3.Components,BX.Vue3,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
