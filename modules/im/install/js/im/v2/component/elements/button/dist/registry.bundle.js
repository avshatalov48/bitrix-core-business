/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core) {
	'use strict';

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
	  Link: 'link',
	  Collab: 'collab',
	  Delete: 'delete',
	  Forward: 'forward'
	};
	const ButtonIcon = {
	  Plus: 'plus',
	  Link: 'link',
	  Call: 'call',
	  EndCall: 'end-call',
	  AddUser: 'add-user',
	  Camera: 'camera',
	  Delete: 'delete',
	  Forward: 'forward'
	};

	// @vue/component
	const ChatButton = {
	  name: 'ChatButton',
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
	          hoverColor: '',
	          textHoverColor: ''
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
	        var _this$customColorSche;
	        result['--im-button__border-color'] = this.customColorScheme.borderColor;
	        result['--im-button__background-color'] = this.customColorScheme.backgroundColor;
	        result['--im-button__color'] = this.customColorScheme.textColor;
	        result['--im-button__background-color_hover'] = this.customColorScheme.hoverColor;
	        result['--im-button__color_hover'] = (_this$customColorSche = this.customColorScheme.textHoverColor) != null ? _this$customColorSche : this.customColorScheme.textColor;
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
	        result.backgroundColor = this.customColorScheme.iconColor;
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
	      return main_core.Type.isPlainObject(this.customColorScheme) && main_core.Type.isStringFilled(this.customColorScheme.borderColor) && main_core.Type.isStringFilled(this.customColorScheme.iconColor) && main_core.Type.isStringFilled(this.customColorScheme.textColor) && main_core.Type.isStringFilled(this.customColorScheme.hoverColor);
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

	// @vue/component
	const SegmentButton = {
	  name: 'SegmentButton',
	  props: {
	    tabs: {
	      type: Array,
	      required: true,
	      validator(value) {
	        return main_core.Type.isArrayFilled(value);
	      }
	    },
	    activeTabId: {
	      type: String,
	      required: true
	    }
	  },
	  emits: ['segmentSelected'],
	  methods: {
	    isTabActive(tab) {
	      return this.activeTabId === tab.id;
	    },
	    onTabClick(tab) {
	      this.$emit('segmentSelected', tab.id);
	    }
	  },
	  template: `
		<div class="bx-im-segment-button__container">
			<button
				v-for="tab in tabs"
				:class="{'--active': isTabActive(tab)}"
				class="--ellipsis"
				@click="onTabClick(tab)"
			>
				{{ tab.title }}
			</button>
		</div>
	`
	};

	exports.ChatButton = ChatButton;
	exports.ButtonColor = ButtonColor;
	exports.ButtonSize = ButtonSize;
	exports.ButtonIcon = ButtonIcon;
	exports.SegmentButton = SegmentButton;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX));
//# sourceMappingURL=registry.bundle.js.map
