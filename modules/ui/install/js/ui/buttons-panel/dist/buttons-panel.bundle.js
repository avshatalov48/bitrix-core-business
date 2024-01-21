/* eslint-disable */
this.BX = this.BX || {};
(function (exports,main_core,ui_buttons) {
	'use strict';

	let _ = t => t,
	  _t;
	var _getContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getContainer");
	var _getButtons = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getButtons");
	var _render = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("render");
	class ButtonsPanel {
	  constructor(options) {
	    Object.defineProperty(this, _render, {
	      value: _render2
	    });
	    Object.defineProperty(this, _getButtons, {
	      value: _getButtons2
	    });
	    Object.defineProperty(this, _getContainer, {
	      value: _getContainer2
	    });
	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    const buttons = main_core.Type.isArray(options.buttons) ? options.buttons : [];
	    this.container = null;
	    this.buttons = [];
	    buttons.forEach(button => {
	      if (button instanceof ui_buttons.Button) {
	        this.buttons.push(button);
	      } else if (main_core.Type.isPlainObject(button)) {
	        if (button.splitButton) {
	          this.buttons.push(new ui_buttons.SplitButton(button));
	        } else {
	          this.buttons.push(new ui_buttons.Button(button));
	        }
	      }
	    });
	  }
	  collapse() {
	    const buttons = Object.values(babelHelpers.classPrivateFieldLooseBase(this, _getButtons)[_getButtons]());
	    for (let i = buttons.length - 1; i >= 0; i--) {
	      let button = buttons[i];
	      if (!button.getIcon() && !main_core.Type.isStringFilled(button.getDataSet()['buttonCollapsedIcon'])) {
	        continue;
	      }
	      if (button.isCollapsed()) {
	        continue;
	      }
	      button.setCollapsed(true);
	      if (!button.getIcon()) {
	        button.setIcon(button.getDataSet()['buttonCollapsedIcon']);
	      }
	      break;
	    }
	  }
	  expand() {}
	  init() {
	    babelHelpers.classPrivateFieldLooseBase(this, _render)[_render]();
	  }
	}
	function _getContainer2() {
	  if (!this.container) {
	    this.container = main_core.Tag.render(_t || (_t = _`
				<div class="ui-button-panel__container ui-button-panel__scope"></div>
			`));
	  }
	  return this.container;
	}
	function _getButtons2() {
	  return this.buttons;
	}
	function _render2() {
	  main_core.Dom.append(babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer](), this.target);
	  if (babelHelpers.classPrivateFieldLooseBase(this, _getButtons)[_getButtons]().length > 0) {
	    babelHelpers.classPrivateFieldLooseBase(this, _getButtons)[_getButtons]().forEach(button => {
	      main_core.Dom.append(button.getContainer(), babelHelpers.classPrivateFieldLooseBase(this, _getContainer)[_getContainer]());
	    });
	  }
	}

	exports.ButtonsPanel = ButtonsPanel;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI));
//# sourceMappingURL=buttons-panel.bundle.js.map
