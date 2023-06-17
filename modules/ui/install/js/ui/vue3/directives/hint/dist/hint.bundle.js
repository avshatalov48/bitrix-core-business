this.BX = this.BX || {};
this.BX.Vue3 = this.BX.Vue3 || {};
(function (exports,main_popup,main_core) {
	'use strict';

	let _ = t => t,
	  _t;
	class Tooltip {
	  constructor() {
	    this.popup = null;
	  }
	  show(element, bindings = {}) {
	    if (this.popup) {
	      this.popup.close();
	    }
	    let popupOptions = {};
	    let text;
	    if (main_core.Type.isObject(bindings.value)) {
	      if (bindings.value.text) {
	        text = main_core.Text.encode(bindings.value.text);
	      } else if (bindings.value.html) {
	        text = bindings.value.html;
	      }
	      if (main_core.Type.isObject(bindings.value.popupOptions)) {
	        popupOptions = bindings.value.popupOptions;
	      }
	      if (bindings.value.position === 'top') {
	        if (!main_core.Type.isObject(popupOptions.bindOptions)) {
	          popupOptions.bindOptions = {};
	        }
	        popupOptions.bindOptions.position = 'top';
	      }
	    } else {
	      text = bindings.value;
	      if (main_core.Type.isUndefined(element.dataset.hintHtml)) {
	        text = main_core.Text.encode(text);
	      }
	    }
	    popupOptions.bindElement = element;
	    if (main_core.Type.isUndefined(popupOptions.id)) {
	      popupOptions.id = 'bx-vue-hint';
	    }
	    if (main_core.Type.isUndefined(popupOptions.darkMode)) {
	      popupOptions.darkMode = true;
	    }
	    if (main_core.Type.isUndefined(popupOptions.content)) {
	      const content = main_core.Tag.render(_t || (_t = _`<span class='ui-hint-content'></span>`));
	      content.innerHTML = text;
	      popupOptions.content = content;
	    }
	    if (main_core.Type.isUndefined(popupOptions.autoHide)) {
	      popupOptions.autoHide = true;
	    }
	    if (!main_core.Type.isObject(popupOptions.bindOptions)) {
	      popupOptions.bindOptions = {};
	    }
	    if (main_core.Type.isUndefined(popupOptions.bindOptions.position)) {
	      popupOptions.bindOptions.position = 'bottom';
	    }
	    popupOptions.cacheable = false;
	    this.popup = new main_popup.Popup(popupOptions);
	    this.popup.show();
	  }
	  hide() {
	    if (this.popup) {
	      this.popup.close();
	    }
	  }
	}
	const TooltipManager = new Tooltip();

	/**
	 * Hint Vue directive
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2022 Bitrix
	 */
	const hint = {
	  beforeMount(element, bindings) {
	    if (!bindings.value) {
	      return;
	    }
	    main_core.Event.bind(element, 'mouseenter', () => TooltipManager.show(element, bindings));
	    main_core.Event.bind(element, 'mouseleave', () => TooltipManager.hide());
	  }
	};

	exports.hint = hint;

}((this.BX.Vue3.Directives = this.BX.Vue3.Directives || {}),BX.Main,BX));
//# sourceMappingURL=hint.bundle.js.map
