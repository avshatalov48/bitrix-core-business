/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,main_core,ui_buttons,ui_iconSet_api_core) {
	'use strict';

	const allIcons = new Set([ui_buttons.ButtonIcon, ui_iconSet_api_core.Set, ui_iconSet_api_core.Outline].flatMap(it => Object.values(it)));
	const iconValidator = val => main_core.Type.isNil(val) || allIcons.has(val);

	// @vue/component
	const Button = {
	  name: 'UiButton',
	  props: {
	    class: {
	      type: String,
	      default: undefined
	    },
	    text: {
	      type: String,
	      default: ''
	    },
	    link: {
	      type: String,
	      default: ''
	    },
	    tag: {
	      type: String,
	      default: ''
	    },
	    size: {
	      type: String,
	      default: undefined,
	      validator(val) {
	        return main_core.Type.isNil(val) || Object.values(ui_buttons.ButtonSize).includes(val);
	      }
	    },
	    state: {
	      type: String,
	      default: undefined,
	      validator(val) {
	        return main_core.Type.isNil(val) || Object.values(ui_buttons.ButtonState).includes(val);
	      }
	    },
	    style: {
	      type: String,
	      required: false,
	      default: null,
	      validator(val) {
	        return main_core.Type.isNil(val) || Object.values(ui_buttons.AirButtonStyle).includes(val);
	      }
	    },
	    noCaps: {
	      type: Boolean,
	      default: true
	    },
	    disabled: Boolean,
	    loading: Boolean,
	    dropdown: Boolean,
	    wide: Boolean,
	    collapsed: Boolean,
	    id: {
	      type: String,
	      default: ''
	    },
	    type: {
	      type: String,
	      required: false,
	      default: 'button',
	      validator: type => ['button', 'submit', 'reset'].includes(type)
	    },
	    dataset: {
	      type: Object,
	      default: () => ({})
	    },
	    leftIcon: {
	      type: String,
	      default: null,
	      validator: iconValidator
	    },
	    rightIcon: {
	      type: String,
	      default: null,
	      validator: iconValidator
	    },
	    collapsedIcon: {
	      type: String,
	      default: null,
	      validator: iconValidator
	    },
	    leftCounterColor: {
	      type: String,
	      required: false,
	      default: null
	    },
	    rightCounterColor: {
	      type: String,
	      required: false,
	      default: null
	    },
	    leftCounterValue: {
	      type: Number,
	      default: 0
	    },
	    rightCounterValue: {
	      type: Number,
	      default: 0
	    },
	    removeLeftCorners: {
	      type: Boolean,
	      default: false
	    },
	    removeRightCorners: {
	      type: Boolean,
	      default: false
	    },
	    shimmer: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['click'],
	  data() {
	    return {
	      isMounted: false
	    };
	  },
	  watch: {
	    text(text) {
	      var _this$button;
	      (_this$button = this.button) == null ? void 0 : _this$button.setText(text);
	    },
	    size(size) {
	      var _this$button2;
	      (_this$button2 = this.button) == null ? void 0 : _this$button2.setSize(size);
	    },
	    state(state) {
	      var _this$button3;
	      (_this$button3 = this.button) == null ? void 0 : _this$button3.setState(state);
	    },
	    icon(icon) {
	      var _this$button4;
	      (_this$button4 = this.button) == null ? void 0 : _this$button4.setIcon(icon);
	    },
	    collapsedIcon(collapsedIcon) {
	      var _this$button5;
	      (_this$button5 = this.button) == null ? void 0 : _this$button5.setCollapsedIcon(collapsedIcon);
	    },
	    disabled(disabled) {
	      var _this$button6, _this$button7;
	      (_this$button6 = this.button) == null ? void 0 : _this$button6.setDisabled(!disabled);
	      (_this$button7 = this.button) == null ? void 0 : _this$button7.setDisabled(Boolean(disabled));
	    },
	    loading: {
	      handler(loading) {
	        var _this$button8;
	        if (loading !== ((_this$button8 = this.button) == null ? void 0 : _this$button8.isWaiting())) {
	          var _this$button9;
	          (_this$button9 = this.button) == null ? void 0 : _this$button9.setWaiting(loading);
	        }
	      },
	      immediate: true
	    },
	    leftIcon(icon) {
	      var _this$button10;
	      (_this$button10 = this.button) == null ? void 0 : _this$button10.setIcon(icon, 'left');
	    },
	    rightIcon(icon) {
	      var _this$button11;
	      (_this$button11 = this.button) == null ? void 0 : _this$button11.setIcon(icon, 'right');
	    },
	    leftCounterColor(color) {
	      var _this$button$getLeftC;
	      (_this$button$getLeftC = this.button.getLeftCounter()) == null ? void 0 : _this$button$getLeftC.setColor(color);
	    },
	    rightCounterColor(color) {
	      var _this$button$getRight;
	      (_this$button$getRight = this.button.getRightCounter()) == null ? void 0 : _this$button$getRight.setColor(color);
	    },
	    leftCounterValue(value) {
	      if (value === 0) {
	        this.button.setLeftCounter(null);
	      } else if (value > 0 && this.button.getLeftCounter()) {
	        this.button.getLeftCounter().setValue(value);
	      } else if (value > 0) {
	        this.button.setLeftCounter({
	          value,
	          color: this.leftCounterColor
	        });
	      }
	    },
	    rightCounterValue(value) {
	      if (value === 0) {
	        this.button.setRightCounter(null);
	      } else if (value > 0 && this.button.getRightCounter()) {
	        this.button.getRightCounter().setValue(value);
	      } else if (value > 0) {
	        this.button.setRightCounter({
	          value,
	          color: this.leftCounterColor
	        });
	      }
	    },
	    wide(wide) {
	      this.button.setWide(wide);
	    },
	    style(style) {
	      this.button.setStyle(style);
	    },
	    dropdown(dropdown) {
	      this.button.setDropdown(dropdown);
	    },
	    noCaps(noCaps) {
	      this.button.setNoCaps(noCaps);
	    },
	    collapsed(collapsed) {
	      this.button.setCollapsed(collapsed);
	    },
	    removeLeftCorners(remove) {
	      var _this$button12;
	      (_this$button12 = this.button) == null ? void 0 : _this$button12.setLeftCorners(remove === false);
	    },
	    removeRightCorners(remove) {
	      var _this$button13;
	      (_this$button13 = this.button) == null ? void 0 : _this$button13.setRightCorners(remove === false);
	    },
	    shimmer(shimmer) {
	      if (shimmer) {
	        var _this$button14;
	        (_this$button14 = this.button) == null ? void 0 : _this$button14.startShimmer();
	      } else {
	        var _this$button15;
	        (_this$button15 = this.button) == null ? void 0 : _this$button15.stopShimmer();
	      }
	    },
	    type(type) {
	      var _this$button16;
	      main_core.Dom.attr((_this$button16 = this.button) == null ? void 0 : _this$button16.getContainer(), 'type', type);
	    }
	  },
	  created() {
	    const button = new ui_buttons.Button({
	      id: this.id,
	      className: this.class,
	      text: this.text,
	      link: this.link,
	      tag: this.tag,
	      size: this.size,
	      useAirDesign: true,
	      noCaps: this.noCaps,
	      collapsedIcon: this.collapsedIcon,
	      onclick: () => {
	        this.$emit('click');
	      },
	      dataset: this.dataset,
	      dropdown: this.dropdown,
	      disabled: this.disabled,
	      style: this.style,
	      wide: this.wide,
	      removeLeftCorners: this.removeLeftCorners,
	      removeRightCorners: this.removeRightCorners
	    });
	    if (this.collapsed) {
	      button.setCollapsed(true);
	    }
	    if (this.leftIcon) {
	      button.setIcon(this.leftIcon, 'left');
	    }
	    if (this.rightIcon) {
	      button.setIcon(this.leftIcon, 'right');
	    }
	    if (this.leftCounterValue) {
	      button.setLeftCounter({
	        value: this.leftCounterValue,
	        color: this.leftCounterColor
	      });
	    } else {
	      button.setLeftCounter(null);
	    }
	    if (this.rightCounterValue) {
	      button.setRightCounter({
	        value: this.rightCounterValue,
	        color: this.rightCounterColor
	      });
	    }
	    if (this.shimmer) {
	      button.startShimmer();
	    }
	    this.button = button;
	  },
	  mounted() {
	    var _this$button17;
	    const button = (_this$button17 = this.button) == null ? void 0 : _this$button17.render();
	    main_core.Dom.attr(button, 'type', this.type);
	    this.$refs.button.after(button);
	    this.isMounted = true;
	  },
	  unmounted() {
	    var _this$button18, _this$button18$getCon;
	    (_this$button18 = this.button) == null ? void 0 : (_this$button18$getCon = _this$button18.getContainer()) == null ? void 0 : _this$button18$getCon.remove();
	  },
	  template: `
		<button v-if="!isMounted" ref="button"></button>
	`
	};

	exports.ButtonColor = ui_buttons.ButtonColor;
	exports.ButtonSize = ui_buttons.ButtonSize;
	exports.ButtonIcon = ui_buttons.ButtonIcon;
	exports.AirButtonStyle = ui_buttons.AirButtonStyle;
	exports.ButtonCounterColor = ui_buttons.ButtonCounterColor;
	exports.ButtonState = ui_buttons.ButtonState;
	exports.ButtonTag = ui_buttons.ButtonTag;
	exports.Button = Button;

}((this.BX.Vue3.Components = this.BX.Vue3.Components || {}),BX,BX.UI,BX.UI.IconSet));
//# sourceMappingURL=button.bundle.js.map
