/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,ui_forms,main_popup,im_v2_const,im_v2_lib_autoDelete,main_core,im_v2_component_elements_popup,im_v2_lib_helpdesk) {
	'use strict';

	let _ = t => t,
	  _t;
	const MENU_ID = 'im-auto-delete-delay-dropdown';

	// @vue/component
	const AutoDeleteDropdown = {
	  name: 'AutoDeleteDropdown',
	  props: {
	    currentDelay: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['delayChange'],
	  data() {
	    return {
	      menuOpened: false,
	      selectedValue: im_v2_const.AutoDeleteDelay.Off
	    };
	  },
	  computed: {
	    isEnabled() {
	      return this.currentDelay !== im_v2_const.AutoDeleteDelay.Off;
	    },
	    autoDeleteText() {
	      return im_v2_lib_autoDelete.AutoDeleteManager.getStatusText(this.currentDelay);
	    }
	  },
	  beforeUnmount() {
	    var _this$menuInstance;
	    (_this$menuInstance = this.menuInstance) == null ? void 0 : _this$menuInstance.destroy();
	  },
	  methods: {
	    toggleMenu() {
	      if (this.currentDelay === im_v2_const.AutoDeleteDelay.Off) {
	        return;
	      }
	      this.menuInstance = this.getMenuInstance();
	      if (this.menuOpened) {
	        this.menuInstance.close();
	        return;
	      }
	      this.menuInstance.show();
	      this.menuOpened = true;
	    },
	    getMenuInstance() {
	      return main_popup.MenuManager.create({
	        id: MENU_ID,
	        bindOptions: {
	          forceBindPosition: true,
	          position: 'bottom'
	        },
	        offsetTop: 6,
	        targetContainer: document.body,
	        bindElement: this.$refs.dropdown,
	        className: 'bx-im-messenger__scope bx-im-auto-delete-dropdown__scope',
	        width: 193,
	        items: this.getMenuItems(),
	        events: {
	          onClose: () => {
	            this.menuOpened = false;
	            this.menuInstance.destroy();
	          }
	        }
	      });
	    },
	    getMenuItems() {
	      return Object.values(im_v2_const.AutoDeleteDelay).map(delay => {
	        const isSelected = delay === this.currentDelay;
	        return {
	          html: this.getMenuItemHtml(delay, isSelected),
	          onclick: () => {
	            this.$emit('delayChange', delay);
	            this.menuInstance.close();
	          }
	        };
	      });
	    },
	    getMenuItemHtml(delay, selected) {
	      const icon = selected ? '<span class="bx-im-auto-delete-dropdown__icon"></span>' : '';
	      return main_core.Tag.render(_t || (_t = _`
				<span class="bx-im-auto-delete-dropdown__item">
					${0}
					${0}
				</span>
			`), im_v2_lib_autoDelete.AutoDeleteManager.getStatusText(delay), icon);
	    }
	  },
	  template: `
		<div
			ref="dropdown"
			class="bx-im-auto-delete-dropdown__container bx-im-auto-delete-dropdown__scope"
			:class="{'--enabled': isEnabled}"
			@click="toggleMenu"
		>
			{{ autoDeleteText }}
		</div>
	`
	};

	// @vue/component
	const RadioGroup = {
	  name: 'RadioGroup',
	  props: {
	    items: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['change'],
	  computed: {
	    selectedValue() {
	      return this.items.find(option => option.selected).value;
	    }
	  },
	  methods: {
	    onInput(value) {
	      this.$emit('change', value);
	    }
	  },
	  template: `
		<div class="bx-im-auto-delete-popup-radio__container">
			<label v-for="option in items" class="bx-im-auto-delete-popup-radio__option">
				<input
					type="radio"
					class="bx-im-auto-delete-popup-radio__input"
					:value="option.value"
					:checked="option.value === selectedValue"
					@change="onInput(option.value)"
				/>
				<span class="bx-im-auto-delete-popup-radio__label">{{ option.text }}</span>
			</label>
		</div>
	`
	};

	const POPUP_ID = 'auto-delete-choose-delay-popup';

	// @vue/component
	const AutoDeletePopup = {
	  name: 'AutoDeletePopup',
	  components: {
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup,
	    RadioGroup
	  },
	  props: {
	    autoDeleteDelay: {
	      type: Number,
	      default: 0
	    }
	  },
	  emits: ['close', 'autoDeleteDelayChange'],
	  computed: {
	    POPUP_ID: () => POPUP_ID,
	    config() {
	      return {
	        overlay: true,
	        height: 342,
	        width: 400,
	        titleBar: this.loc('IM_ELEMENTS_AUTO_DELETE_POPUP_TITLE'),
	        closeIcon: true,
	        targetContainer: document.body,
	        fixed: true,
	        padding: 0,
	        autoHide: true,
	        contentPadding: 0,
	        contentBackground: '#fff',
	        className: 'bx-im-auto-delete-popup__scope'
	      };
	    },
	    options() {
	      return Object.values(im_v2_const.AutoDeleteDelay).map(delayValue => ({
	        value: delayValue,
	        text: im_v2_lib_autoDelete.AutoDeleteManager.getStatusText(delayValue),
	        selected: this.autoDeleteDelay === delayValue
	      }));
	    }
	  },
	  methods: {
	    onAutoDeleteDelayChange(delay) {
	      this.$emit('autoDeleteDelayChange', delay);
	      this.$emit('close');
	    },
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<MessengerPopup
			:config="config"
			:id="POPUP_ID"
			@close="$emit('close')"
		>
			<div class="bx-im-auto-delete-popup__container">
				<div class="bx-im-auto-delete-popup__info">
					{{ this.loc('IM_ELEMENTS_AUTO_DELETE_POPUP_INFO') }}
				</div>
				<RadioGroup :items="options" @change="onAutoDeleteDelayChange" />
			</div>
		</MessengerPopup>
	`
	};

	const POPUP_ID$1 = 'im-auto-delete-hint-popup';

	// @vue/component
	const AutoDeleteHint = {
	  name: 'AutoDeleteHint',
	  components: {
	    MessengerPopup: im_v2_component_elements_popup.MessengerPopup
	  },
	  props: {
	    bindElement: {
	      type: Object,
	      required: true
	    },
	    config: {
	      type: Object,
	      default: () => {}
	    }
	  },
	  emits: ['close'],
	  computed: {
	    POPUP_ID: () => POPUP_ID$1,
	    getConfig() {
	      return {
	        darkMode: true,
	        bindElement: this.bindElement,
	        angle: true,
	        width: 317,
	        closeIcon: true,
	        offsetLeft: 45,
	        className: 'bx-im-auto-delete-hint__scope',
	        contentBorderRadius: 0,
	        ...this.config
	      };
	    },
	    hintText() {
	      const ARTICLE_CODE = '24402288';
	      const articleCallback = im_v2_lib_helpdesk.getHelpdeskStringCallback(ARTICLE_CODE);
	      return main_core.Loc.getMessage('IM_ELEMENTS_AUTO_DELETE_NO_PERMISSION', {
	        '[helpdesklink]': `<a class="bx-im-auto-delete-hint__helpdesk-link" onclick="${articleCallback}">`,
	        '[/helpdesklink]': '</a>'
	      });
	    }
	  },
	  template: `
		<MessengerPopup
			:config="getConfig"
			:id="POPUP_ID"
			@close="$emit('close')"
		>
			<div class="bx-im-auto-delete-hint__container">
				<div class="bx-im-auto-delete-hint__text" v-html="hintText" />
			</div>
		</MessengerPopup>
	`
	};

	exports.AutoDeleteDropdown = AutoDeleteDropdown;
	exports.AutoDeletePopup = AutoDeletePopup;
	exports.AutoDeleteHint = AutoDeleteHint;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX.Main,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib));
//# sourceMappingURL=registry.bundle.js.map
