this.BX = this.BX || {};
(function (exports,main_core,ui_buttons) {
	'use strict';

	var _templateObject;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getContainer = /*#__PURE__*/new WeakSet();

	var _getButtons = /*#__PURE__*/new WeakSet();

	var _render = /*#__PURE__*/new WeakSet();

	var ButtonsPanel = /*#__PURE__*/function () {
	  function ButtonsPanel(options) {
	    var _this = this;

	    babelHelpers.classCallCheck(this, ButtonsPanel);

	    _classPrivateMethodInitSpec(this, _render);

	    _classPrivateMethodInitSpec(this, _getButtons);

	    _classPrivateMethodInitSpec(this, _getContainer);

	    options = main_core.Type.isPlainObject(options) ? options : {};
	    this.target = main_core.Type.isDomNode(options.target) ? options.target : null;
	    var buttons = main_core.Type.isArray(options.buttons) ? options.buttons : [];
	    this.container = null;
	    this.buttons = [];
	    buttons.forEach(function (button) {
	      if (button instanceof ui_buttons.Button) {
	        _this.buttons.push(button);
	      } else if (main_core.Type.isPlainObject(button)) {
	        if (button.splitButton) {
	          _this.buttons.push(new ui_buttons.SplitButton(button));
	        } else {
	          _this.buttons.push(new ui_buttons.Button(button));
	        }
	      }
	    });
	  }

	  babelHelpers.createClass(ButtonsPanel, [{
	    key: "collapse",
	    value: function collapse() {
	      var buttons = Object.values(_classPrivateMethodGet(this, _getButtons, _getButtons2).call(this));

	      for (var i = buttons.length - 1; i >= 0; i--) {
	        var button = buttons[i];

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
	  }, {
	    key: "expand",
	    value: function expand() {}
	  }, {
	    key: "init",
	    value: function init() {
	      _classPrivateMethodGet(this, _render, _render2).call(this);
	    }
	  }]);
	  return ButtonsPanel;
	}();

	function _getContainer2() {
	  if (!this.container) {
	    this.container = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-button-panel__container ui-button-panel__scope\"></div>\n\t\t\t"])));
	  }

	  return this.container;
	}

	function _getButtons2() {
	  return this.buttons;
	}

	function _render2() {
	  var _this2 = this;

	  main_core.Dom.append(_classPrivateMethodGet(this, _getContainer, _getContainer2).call(this), this.target);

	  if (_classPrivateMethodGet(this, _getButtons, _getButtons2).call(this).length > 0) {
	    _classPrivateMethodGet(this, _getButtons, _getButtons2).call(this).forEach(function (button) {
	      main_core.Dom.append(button.getContainer(), _classPrivateMethodGet(_this2, _getContainer, _getContainer2).call(_this2));
	    });
	  }
	}

	exports.ButtonsPanel = ButtonsPanel;

}((this.BX.UI = this.BX.UI || {}),BX,BX.UI));
//# sourceMappingURL=buttons-panel.bundle.js.map
