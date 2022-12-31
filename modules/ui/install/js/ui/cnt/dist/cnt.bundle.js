this.BX = this.BX || {};
(function (exports,ui_fonts_opensans,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	var CounterColor = function CounterColor() {
	  babelHelpers.classCallCheck(this, CounterColor);
	};

	babelHelpers.defineProperty(CounterColor, "DANGER", "ui-counter-danger");
	babelHelpers.defineProperty(CounterColor, "WARNING", "ui-counter-warning");
	babelHelpers.defineProperty(CounterColor, "SUCCESS", "ui-counter-success");
	babelHelpers.defineProperty(CounterColor, "PRIMARY", "ui-counter-primary");
	babelHelpers.defineProperty(CounterColor, "GRAY", "ui-counter-gray");
	babelHelpers.defineProperty(CounterColor, "LIGHT", "ui-counter-light");
	babelHelpers.defineProperty(CounterColor, "WHITE", "ui-counter-white");
	babelHelpers.defineProperty(CounterColor, "DARK", "ui-counter-dark");
	babelHelpers.defineProperty(CounterColor, "THEME", "ui-counter-theme");

	/**
	 * @namespace {BX.UI}
	 */
	var CounterSize = function CounterSize() {
	  babelHelpers.classCallCheck(this, CounterSize);
	};

	babelHelpers.defineProperty(CounterSize, "SMALL", "ui-counter-sm");
	babelHelpers.defineProperty(CounterSize, "LARGE", "ui-counter-lg");
	babelHelpers.defineProperty(CounterSize, "MEDIUM", "ui-counter-md");

	var _templateObject, _templateObject2;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getBorderClassname = /*#__PURE__*/new WeakSet();

	var Counter = /*#__PURE__*/function () {
	  function Counter(options) {
	    babelHelpers.classCallCheck(this, Counter);

	    _classPrivateMethodInitSpec(this, _getBorderClassname);

	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.container = null;
	    this.counterContainer = null;
	    this.animate = main_core.Type.isBoolean(this.options.animate) ? this.options.animate : false;
	    this.value = main_core.Type.isNumber(this.options.value) ? this.options.value : 0;
	    this.maxValue = main_core.Type.isNumber(this.options.maxValue) ? this.options.maxValue : 99;
	    this.size = main_core.Type.isString(this.options.size) ? this.options.size : BX.UI.Counter.Size.MEDIUM;
	    this.color = main_core.Type.isString(this.options.color) ? this.options.color : BX.UI.Counter.Color.PRIMARY;
	    this.border = main_core.Type.isBoolean(this.options.border) ? this.options.border : false;
	  } //region Parameters


	  babelHelpers.createClass(Counter, [{
	    key: "setValue",
	    value: function setValue(value) {
	      if (main_core.Type.isNumber(value)) {
	        this.value = value < 0 ? 0 : value;
	      }

	      return this;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.value <= this.maxValue) {
	        return this.value;
	      } else {
	        return this.maxValue + "+";
	      }
	    }
	  }, {
	    key: "setMaxValue",
	    value: function setMaxValue(value) {
	      if (main_core.Type.isNumber(value)) {
	        this.value = value < 0 ? 0 : value;
	      }

	      return this;
	    }
	  }, {
	    key: "getMaxValue",
	    value: function getMaxValue() {
	      return this.maxValue;
	    }
	  }, {
	    key: "isBorder",
	    value: function isBorder() {
	      return this.border;
	    }
	  }, {
	    key: "setColor",
	    value: function setColor(color) {
	      if (main_core.Type.isStringFilled(color)) {
	        if (this.container === null) {
	          this.createContainer();
	        }

	        main_core.Dom.removeClass(this.container, this.color);
	        this.color = color;
	        main_core.Dom.addClass(this.container, this.color);
	      }

	      return this;
	    }
	  }, {
	    key: "setSize",
	    value: function setSize(size) {
	      if (main_core.Type.isStringFilled(size)) {
	        BX.removeClass(this.container, this.size);
	        this.size = size;
	        BX.addClass(this.container, this.size);
	      }

	      return this;
	    }
	  }, {
	    key: "setAnimate",
	    value: function setAnimate(animate) {
	      if (main_core.Type.isBoolean(animate)) {
	        this.animate = animate;
	      }

	      return this;
	    }
	  }, {
	    key: "setBorder",
	    value: function setBorder(border) {
	      if (!main_core.Type.isBoolean(border)) {
	        console.warn('Parameter "border" is not boolean');
	        return this;
	      }

	      this.border = border;

	      var borderedCounterClassname = _classPrivateMethodGet(this, _getBorderClassname, _getBorderClassname2).call(this, border);

	      if (border) {
	        main_core.Dom.addClass(this.container, borderedCounterClassname);
	      } else {
	        main_core.Dom.removeClass(this.container, borderedCounterClassname);
	      }

	      return this;
	    }
	  }, {
	    key: "update",
	    //endregion
	    // region Counter
	    value: function update(value) {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      if (this.animate == true) {
	        this.updateAnimated(value);
	      } else if (this.animate == false) {
	        this.setValue(value);
	        main_core.Dom.adjust(this.counterContainer, {
	          text: this.getValue()
	        });
	      }
	    }
	  }, {
	    key: "updateAnimated",
	    value: function updateAnimated(value) {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      if (value > this.value && this.value < this.maxValue) {
	        main_core.Dom.addClass(this.counterContainer, "ui-counter-plus");
	      } else if (value < this.value && this.value < this.maxValue) {
	        main_core.Dom.addClass(this.counterContainer, "ui-counter-minus");
	      }

	      setTimeout(function () {
	        this.setValue(value);
	        main_core.Dom.adjust(this.counterContainer, {
	          text: this.getValue()
	        });
	      }.bind(this), 250);
	      setTimeout(function () {
	        main_core.Dom.removeClass(this.counterContainer, "ui-counter-plus");
	        main_core.Dom.removeClass(this.counterContainer, "ui-counter-minus");
	      }.bind(this), 500);
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      main_core.Dom.addClass(this.container, "ui-counter-show");
	      main_core.Dom.removeClass(this.container, "ui-counter-hide");
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      main_core.Dom.addClass(this.container, "ui-counter-hide");
	      main_core.Dom.removeClass(this.container, "ui-counter-show");
	    }
	  }, {
	    key: "getCounterContainer",
	    value: function getCounterContainer() {
	      if (this.counterContainer === null) {
	        this.counterContainer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter-inner\">", "</div>\n\t\t\t"])), this.getValue());
	      }

	      return this.counterContainer;
	    }
	  }, {
	    key: "createContainer",
	    value: function createContainer() {
	      if (this.container === null) {
	        this.container = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-counter\">", "</div>\n\t\t\t"])), this.getCounterContainer());
	        this.setSize(this.size);
	        this.setColor(this.color);
	        this.setBorder(this.border);
	      }

	      return this.container;
	    } //endregion

	  }, {
	    key: "getContainer",
	    value: function getContainer() {
	      if (this.container === null) {
	        this.createContainer();
	      }

	      return this.container;
	    }
	  }, {
	    key: "renderTo",
	    value: function renderTo(node) {
	      if (main_core.Type.isDomNode(node)) {
	        return node.appendChild(this.getContainer());
	      }

	      return null;
	    }
	  }, {
	    key: "destroy",
	    value: function destroy() {
	      main_core.Dom.remove(this.container);
	      this.container = null;
	      this.finished = false;
	      this.textAfterContainer = null;
	      this.textBeforeContainer = null;
	      this.bar = null;
	      this.svg = null;

	      for (var property in this) {
	        if (this.hasOwnProperty(property)) {
	          delete this[property];
	        }
	      }

	      Object.setPrototypeOf(this, null);
	    }
	  }]);
	  return Counter;
	}();

	function _getBorderClassname2(border) {
	  if (border) {
	    return 'ui-counter-border';
	  } else {
	    return '';
	  }
	}

	babelHelpers.defineProperty(Counter, "Color", CounterColor);
	babelHelpers.defineProperty(Counter, "Size", CounterSize);

	exports.Counter = Counter;

}((this.BX.UI = this.BX.UI || {}),BX,BX));
//# sourceMappingURL=cnt.bundle.js.map
