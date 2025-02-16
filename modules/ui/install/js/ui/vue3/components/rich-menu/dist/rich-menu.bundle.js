/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.Vue3 = this.BX.UI.Vue3 || {};
(function (exports,ui_vue3_components_hint,ui_vue3_components_popup) {
	'use strict';

	// @vue/component
	const RichMenu = {
	  name: 'RichMenu',
	  template: `
		<div class="ui-rich-menu__container">
			<slot name="header"></slot>
			<slot></slot>
			<slot name="footer"></slot>
		</div>
	`
	};

	const RichMenuItemIcon = Object.freeze({
	  check: 'check',
	  copy: 'copy',
	  'opened-eye': 'opened-eye',
	  pencil: 'pencil',
	  'red-lock': 'red-lock',
	  role: 'role',
	  settings: 'settings',
	  'trash-bin': 'trash-bin'
	});

	// @vue/component
	const RichMenuItem = {
	  name: 'RichMenuItem',
	  components: {
	    Hint: ui_vue3_components_hint.Hint
	  },
	  props: {
	    icon: {
	      type: String,
	      required: false,
	      default: '',
	      validator(value) {
	        return value === '' || Object.keys(RichMenuItemIcon).includes(value);
	      }
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
	    hint: {
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
	  computed: {
	    formattedCounter() {
	      if (this.counter === 0) {
	        return '';
	      }
	      return this.counter > 99 ? '99+' : String(this.counter);
	    }
	  },
	  template: `
		<div class="ui-rich-menu-item__container" :class="{'--disabled': disabled}">
			<div class="ui-rich-menu-item__content" :class="{'--with-icon': !!icon}">
				<div v-if="icon" class="ui-rich-menu-item__icon" :class="'--' + icon"></div>
				<div class="ui-rich-menu-item__text-content" :class="{'--with-subtitle': !!subtitle}">
					<div class="ui-rich-menu-item__title">
						<div class="ui-rich-menu-item__title_text">{{ title }}</div>
						<slot name="after-title"></slot>
						<div v-if="counter" class="ui-rich-menu-item__title_counter">{{ formattedCounter }}</div>
					</div>
					<div v-if="subtitle" :title="subtitle" class="ui-rich-menu-item__subtitle">{{ subtitle }}</div>
					<slot name="below-content"></slot>
				</div>
				<Hint v-if="hint" :text="hint"/>
			</div>
		</div>
	`
	};

	const defaultPopupOptions = Object.freeze({
	  width: 275,
	  padding: 0,
	  closeIcon: false,
	  autoHide: true,
	  closeByEsc: true,
	  animation: 'fading',
	  contentBorderRadius: '10px'
	});
	const RichMenuPopup = {
	  name: 'RichMenuPopup',
	  emits: ['close'],
	  components: {
	    Popup: ui_vue3_components_popup.Popup,
	    RichMenu
	  },
	  props: {
	    popupOptions: {
	      /** @type PopupOptions */
	      type: Object,
	      default: {}
	    }
	  },
	  computed: {
	    allOptions() {
	      return {
	        ...defaultPopupOptions,
	        ...this.popupOptions
	      };
	    }
	  },
	  template: `
		<Popup @close="$emit('close')" :options="allOptions">
			<RichMenu v-bind="$attrs">
				<template #header>
					<slot name="header"></slot>
				</template>
				<slot></slot>
				<template #footer>
					<slot name="footer"></slot>
				</template>
			</RichMenu>
		</Popup>
	`
	};

	exports.RichMenu = RichMenu;
	exports.RichMenuItem = RichMenuItem;
	exports.RichMenuItemIcon = RichMenuItemIcon;
	exports.RichMenuPopup = RichMenuPopup;

}((this.BX.UI.Vue3.Components = this.BX.UI.Vue3.Components || {}),BX.Vue3.Components,BX.UI.Vue3.Components));
//# sourceMappingURL=rich-menu.bundle.js.map
