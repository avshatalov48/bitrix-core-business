this.BX = this.BX || {};
(function (exports,main_core_events) {
	'use strict';

	/**
	 * ColorPicker for Theme site.
	 */

	var ColorPickerTheme = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(ColorPickerTheme, _EventEmitter);

	  function ColorPickerTheme(node, allColors, currentColor) {
	    var _this;

	    babelHelpers.classCallCheck(this, ColorPickerTheme);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ColorPickerTheme).call(this));

	    _this.setEventNamespace('BX.Landing.ColorPickerTheme');

	    _this.element = node;
	    _this.input = _this.element.firstElementChild;
	    _this.allColors = allColors;
	    _this.currentColor = currentColor;

	    _this.init();

	    return _this;
	  }

	  babelHelpers.createClass(ColorPickerTheme, [{
	    key: "init",
	    value: function init() {
	      this.setMetric();
	      var color = this.initPreviewColor();
	      var active = this.isActive();
	      this.element.style.backgroundColor = color;
	      this.element.dataset.value = color;
	      this.element.classList.add('landing-colorpicker-theme');

	      if (active) {
	        this.input.setAttribute('value', color);
	        this.element.classList.add('active');
	      }

	      this.colorPicker = new BX.ColorPicker({
	        bindElement: this.element,
	        popupOptions: {
	          angle: false,
	          offsetTop: 5
	        },
	        onColorSelected: this.onColorSelected.bind(this),
	        colors: this.getGridColors(),
	        selectedColor: this.getSelectedColor()
	      });
	      BX.bind(this.element, 'click', this.open.bind(this));
	    }
	  }, {
	    key: "setMetric",
	    value: function setMetric() {
	      this.metrika = null;

	      if (typeof BX.Landing.Metrika !== 'undefined') {
	        this.metrika = new BX.Landing.Metrika();
	      }
	    }
	  }, {
	    key: "initPreviewColor",
	    value: function initPreviewColor() {
	      var color;

	      if (this.currentColor) {
	        if (this.isHex(this.currentColor)) {
	          color = this.isBaseColor() ? ColorPickerTheme.DEFAULT_COLOR_PICKER_COLOR : this.currentColor;
	        } else {
	          color = ColorPickerTheme.DEFAULT_COLOR_PICKER_COLOR;
	        }
	      } else {
	        color = ColorPickerTheme.DEFAULT_COLOR_PICKER_COLOR;
	      }

	      return color;
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      if (!this.isHex(this.currentColor)) {
	        return false;
	      }

	      return !this.isBaseColor();
	    }
	  }, {
	    key: "isBaseColor",
	    value: function isBaseColor() {
	      return this.allColors.includes(this.currentColor);
	    }
	  }, {
	    key: "getSelectedColor",
	    value: function getSelectedColor() {
	      var color;

	      if (this.element.dataset.value) {
	        color = this.element.dataset.value;
	      }

	      color = this.prepareColor(color);

	      if (!this.isHex(color)) {
	        color = '';
	      }

	      return color;
	    }
	  }, {
	    key: "onColorSelected",
	    value: function onColorSelected(color) {
	      this.element.classList.add('ui-colorpicker-selected');
	      this.element.dataset.value = color.substr(1);
	      this.element.style.backgroundColor = color;
	      var event = new main_core_events.BaseEvent({
	        data: {
	          color: color,
	          node: this.element
	        }
	      });
	      this.emit('onSelectColor', event);
	      this.emit('onSelectCustomColor', event);
	      this.input.setAttribute('value', color);
	      this.sendMetric(color);
	    }
	  }, {
	    key: "sendMetric",
	    value: function sendMetric(color) {
	      if (this.metrika) {
	        this.metrika.sendLabel(null, 'Color::CustomSet', color.substr(1));
	      }
	    }
	  }, {
	    key: "open",
	    value: function open() {
	      this.colorPicker.open();
	    }
	  }, {
	    key: "getGridColors",
	    value: function getGridColors() {
	      return [['#f4f5fb', '#d2d6ef', '#b0b8e3', '#8f99d6', '#6d7bca', '#4b5cbe', '#3e4fac'], ['#eaecfb', '#d5daf6', '#c0c7f2', '#abb5ed', '#96a2e9', '#8190e4', '#7888e2'], ['#e8f4fc', '#d1e9fa', '#badef7', '#a3d3f5', '#8cc8f2', '#75bdf0', '#6cb8ef'], ['#ebfaf8', '#caf1ed', '#aeeae3', '#9ae5dc', '#85e0d5', '#71dace', '#5dd5c7'], ['#eafbf9', '#c8f4f0', '#aaeee8', '#90e9e2', '#5ddfd4', '#2ad5c7', '#26c0b3'], ['#ebfaf0', '#d6f5e2', '#c2f0d3', '#adebc5', '#99e6b6', '#85e0a8', '#70db99'], ['#f6f9eb', '#e8efcc', '#dbe7b1', '#d1e09a', '#c4d77e', '#b8cf63', '#a9c544'], ['#fafee6', '#f3febe', '#edfd9b', '#e8fc82', '#d0e859', '#b5d31d', '#a7c804'], ['#fefee6', '#fdfcce', '#fcfbb6', '#fbf993', '#f9f771', '#f7f445', '#f6f223'], ['#fef8e6', '#fdf1ce', '#fdeab5', '#fce092', '#fbd570', '#f9c943', '#f8bc16'], ['#fde9e8', '#fbd3d0', '#f9bdb9', '#f7a7a1', '#f5918a', '#f27269', '#ee463a'], ['#f9ebeb', '#f4d7d7', '#eec4c4', '#e8b0b0', '#e29c9c', '#d77575', '#ca4949'], ['#fceae8', '#f9d6d2', '#f7c1bb', '#f4aca4', '#f1978e', '#ee8377', '#e75140'], ['#ffe6e6', '#ffd1d2', '#ffc2c3', '#ffa9aa', '#fe9496', '#fe8082', '#fe6769'], ['#fee8e7', '#fdd2ce', '#fcbbb6', '#fba59d', '#fa8e85', '#f9786c', '#f75445'], ['#ffe5e5', '#fcc', '#ffb3b3', '#f99', '#ff8080', '#f66', '#ff0a0a'], ['#fee7ea', '#fdced6', '#fcb6c1', '#fb9dad', '#fa8598', '#f96c84', '#f73b5a'], ['#fde7ef', '#fbd0df', '#f9b8cf', '#f7a1bf', '#f580a9', '#f25a8f', '#ec135f'], ['#faeaef', '#f5d6de', '#f0c1ce', '#ebadbd', '#e698ad', '#e1849d', '#d75b7c'], ['#f2f2f2', '#dedede', '#ccc', '#b3b3b3', '#999', '#666', '#404040']].map(function (item, index, arr) {
	        return arr.map(function (row) {
	          return row[index];
	        });
	      });
	    }
	  }, {
	    key: "prepareColor",
	    value: function prepareColor(color) {
	      if (color[0] !== '#') {
	        color = '#' + color;
	      }

	      return color;
	    }
	  }, {
	    key: "isHex",
	    value: function isHex(color) {
	      var isCorrect = false;

	      if (color.length === 4 || color.length === 7) {
	        if (color.match(ColorPickerTheme.MATCH_HEX)) {
	          isCorrect = true;
	        }
	      }

	      return isCorrect;
	    }
	  }]);
	  return ColorPickerTheme;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(ColorPickerTheme, "DEFAULT_COLOR_PICKER_COLOR", '#f25a8f');
	babelHelpers.defineProperty(ColorPickerTheme, "MATCH_HEX", /#?([0-9A-F]{3}){1,2}$/i);

	exports.ColorPickerTheme = ColorPickerTheme;

}((this.BX.Landing = this.BX.Landing || {}),BX.Event));
//# sourceMappingURL=colorpickertheme.bundle.js.map
