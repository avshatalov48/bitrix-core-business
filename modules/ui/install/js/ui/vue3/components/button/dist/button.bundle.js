/* eslint-disable */
this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,main_core,ui_buttons) {
	'use strict';

	const Button = {
	  name: 'UiButton',
	  emits: ['click'],
	  props: {
	    text: {
	      type: String,
	      default: ''
	    },
	    size: String,
	    state: {
	      type: String,
	      default: undefined,
	      validator(val) {
	        return main_core.Type.isUndefined(val) || Object.values(ui_buttons.ButtonState).includes(val);
	      }
	    },
	    id: String,
	    color: String,
	    round: Boolean,
	    icon: String,
	    noCaps: Boolean,
	    disabled: Boolean,
	    clocking: Boolean,
	    waiting: Boolean,
	    dataset: Object,
	    buttonClass: String
	  },
	  created() {
	    this.button = new ui_buttons.Button({
	      id: this.id,
	      text: this.text,
	      size: this.size,
	      color: this.color,
	      round: this.round,
	      icon: this.icon,
	      noCaps: this.noCaps,
	      onclick: () => {
	        this.$emit('click');
	      },
	      dataset: this.dataset,
	      className: this.buttonClass
	    });
	  },
	  mounted() {
	    var _this$button;
	    const button = (_this$button = this.button) == null ? void 0 : _this$button.render();
	    const slot = this.$refs.button.firstElementChild;
	    if (slot) {
	      button.append(slot);
	    }
	    this.$refs.button.replaceWith(button);
	  },
	  watch: {
	    text: {
	      handler(text) {
	        var _this$button2;
	        (_this$button2 = this.button) == null ? void 0 : _this$button2.setText(text);
	      }
	    },
	    size: {
	      handler(size) {
	        var _this$button3;
	        (_this$button3 = this.button) == null ? void 0 : _this$button3.setSize(size);
	      }
	    },
	    color: {
	      handler(color) {
	        var _this$button4;
	        (_this$button4 = this.button) == null ? void 0 : _this$button4.setColor(color);
	      }
	    },
	    state: {
	      handler(state) {
	        var _this$button5;
	        (_this$button5 = this.button) == null ? void 0 : _this$button5.setState(state);
	      }
	    },
	    icon: {
	      handler(icon) {
	        var _this$button6;
	        (_this$button6 = this.button) == null ? void 0 : _this$button6.setIcon(icon);
	      }
	    },
	    disabled: {
	      handler(disabled) {
	        var _this$button7;
	        (_this$button7 = this.button) == null ? void 0 : _this$button7.setDisabled(Boolean(disabled));
	      },
	      immediate: true,
	      flush: 'sync'
	    },
	    waiting: {
	      handler(waiting) {
	        var _this$button8;
	        if (waiting !== ((_this$button8 = this.button) == null ? void 0 : _this$button8.isWaiting())) {
	          var _this$button9;
	          (_this$button9 = this.button) == null ? void 0 : _this$button9.setWaiting(waiting);
	        }
	      },
	      immediate: true
	    },
	    clocking: {
	      handler(clocking) {
	        var _this$button10;
	        if (clocking !== ((_this$button10 = this.button) == null ? void 0 : _this$button10.isClocking())) {
	          var _this$button11;
	          (_this$button11 = this.button) == null ? void 0 : _this$button11.setClocking(clocking);
	        }
	      },
	      immediate: true
	    }
	  },
	  template: `
		<span>
			<button ref="button">
				<slot></slot>
			</button>
		</span>
	`
	};

	exports.ButtonColor = ui_buttons.ButtonColor;
	exports.ButtonSize = ui_buttons.ButtonSize;
	exports.ButtonIcon = ui_buttons.ButtonIcon;
	exports.Button = Button;

}((this.BX.Vue3.Components = this.BX.Vue3.Components || {}),BX,BX.UI));
//# sourceMappingURL=button.bundle.js.map
