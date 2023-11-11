/* eslint-disable */
(function (exports,main_core,main_popup,ui_hint,ui_vue) {
	'use strict';

	var _templateObject;
	ui_vue.BitrixVue.directive('bx-hint', {
	  bind: function bind(element, bindings) {
	    main_core.Event.bind(element, 'mouseenter', function () {
	      return TooltipManager.show(element, bindings);
	    });
	    main_core.Event.bind(element, 'mouseleave', function () {
	      return TooltipManager.hide();
	    });
	  }
	});
	var Tooltip = /*#__PURE__*/function () {
	  function Tooltip() {
	    babelHelpers.classCallCheck(this, Tooltip);
	    this.popup = null;
	    this.elements;
	  }
	  babelHelpers.createClass(Tooltip, [{
	    key: "show",
	    value: function show(element) {
	      var bindings = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	      if (this.popup) {
	        this.popup.close();
	      }
	      var popupOptions = {};
	      var text;
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
	        var content = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<span class='ui-hint-content'></span>"])));
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
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.popup) {
	        this.popup.close();
	      }
	    }
	  }]);
	  return Tooltip;
	}();
	var TooltipManager = new Tooltip();

	/**
	 * Hint Vue component
	 *
	 * @package bitrix
	 * @subpackage ui
	 * @copyright 2001-2021 Bitrix
	 */
	ui_vue.BitrixVue.component('bx-hint', {
	  props: {
	    text: {
	      "default": ''
	    },
	    html: {
	      "default": ''
	    },
	    position: {
	      "default": 'bottom'
	    },
	    popupOptions: {
	      "default": function _default() {
	        return {};
	      }
	    }
	  },
	  template: "\n\t\t<span class=\"ui-hint\" v-bx-hint=\"{text, html, position, popupOptions}\" data-hint-init=\"vue\">\n\t\t\t<span class=\"ui-hint-icon\"/>\n\t\t</span>\n\t"
	});

}((this.window = this.window || {}),BX,BX.Main,BX,BX));
//# sourceMappingURL=hint.bundle.js.map
