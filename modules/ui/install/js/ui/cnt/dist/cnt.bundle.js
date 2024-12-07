/* eslint-disable */
this.BX = this.BX || {};
(function (exports,ui_fonts_opensans,main_core) {
	'use strict';

	/**
	 * @namespace {BX.UI}
	 */
	class CounterColor {}
	CounterColor.DANGER = "ui-counter-danger";
	CounterColor.WARNING = "ui-counter-warning";
	CounterColor.SUCCESS = "ui-counter-success";
	CounterColor.PRIMARY = "ui-counter-primary";
	CounterColor.GRAY = "ui-counter-gray";
	CounterColor.LIGHT = "ui-counter-light";
	CounterColor.WHITE = "ui-counter-white";
	CounterColor.DARK = "ui-counter-dark";
	CounterColor.THEME = "ui-counter-theme";

	/**
	 * @namespace {BX.UI}
	 */
	class CounterSize {}
	CounterSize.SMALL = "ui-counter-sm";
	CounterSize.LARGE = "ui-counter-lg";
	CounterSize.MEDIUM = "ui-counter-md";

	let _ = t => t,
	  _t,
	  _t2,
	  _t3;
	var _getBorderClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBorderClassname");
	class Counter {
	  constructor(options) {
	    Object.defineProperty(this, _getBorderClassname, {
	      value: _getBorderClassname2
	    });
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    this.container = null;
	    this.counterContainer = null;
	    this.animate = main_core.Type.isBoolean(this.options.animate) ? this.options.animate : false;
	    this.isDouble = main_core.Type.isBoolean(this.options.isDouble) ? this.options.isDouble : false;
	    this.value = main_core.Type.isNumber(this.options.value) ? this.options.value : 0;
	    this.maxValue = main_core.Type.isNumber(this.options.maxValue) ? this.options.maxValue : 99;
	    this.size = main_core.Type.isString(this.options.size) ? this.options.size : BX.UI.Counter.Size.MEDIUM;
	    this.color = main_core.Type.isString(this.options.color) ? this.options.color : BX.UI.Counter.Color.PRIMARY;
	    this.secondaryColor = main_core.Type.isString(this.options.secondaryColor) ? this.options.secondaryColor : BX.UI.Counter.Color.PRIMARY;
	    this.border = main_core.Type.isBoolean(this.options.border) ? this.options.border : false;
	  }

	  //region Parameters
	  setValue(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.value = value < 0 ? 0 : value;
	    }
	    return this;
	  }
	  getValue() {
	    if (this.value <= this.maxValue) {
	      return this.value;
	    } else {
	      return this.maxValue + "+";
	    }
	  }
	  setMaxValue(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.value = value < 0 ? 0 : value;
	    }
	    return this;
	  }
	  getMaxValue() {
	    return this.maxValue;
	  }
	  isBorder() {
	    return this.border;
	  }
	  setColor(color) {
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
	  setSize(size) {
	    if (main_core.Type.isStringFilled(size)) {
	      BX.removeClass(this.container, this.size);
	      this.size = size;
	      BX.addClass(this.container, this.size);
	    }
	    return this;
	  }
	  setAnimate(animate) {
	    if (main_core.Type.isBoolean(animate)) {
	      this.animate = animate;
	    }
	    return this;
	  }
	  createSecondaryContainer() {
	    if (this.isDouble) {
	      this.secondaryContainer = main_core.Tag.render(_t || (_t = _`
				<div class="ui-counter-secondary"></div>
			`));
	    }
	    main_core.Dom.append(this.secondaryContainer, this.container);
	  }
	  setSecondaryColor() {
	    if (this.secondaryContainer === null) {
	      this.createSecondaryContainer();
	    }
	    main_core.Dom.removeClass(this.secondaryContainer, this.secondaryColor);
	    main_core.Dom.addClass(this.secondaryContainer, this.secondaryColor);
	  }
	  setBorder(border) {
	    if (!main_core.Type.isBoolean(border)) {
	      console.warn('Parameter "border" is not boolean');
	      return this;
	    }
	    this.border = border;
	    const borderedCounterClassname = babelHelpers.classPrivateFieldLooseBase(this, _getBorderClassname)[_getBorderClassname](border);
	    if (border) {
	      main_core.Dom.addClass(this.container, borderedCounterClassname);
	    } else {
	      main_core.Dom.removeClass(this.container, borderedCounterClassname);
	    }
	    return this;
	  }
	  //endregion

	  // region Counter
	  update(value) {
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
	  updateAnimated(value) {
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
	  show() {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    main_core.Dom.addClass(this.container, "ui-counter-show");
	    main_core.Dom.removeClass(this.container, "ui-counter-hide");
	  }
	  hide() {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    main_core.Dom.addClass(this.container, "ui-counter-hide");
	    main_core.Dom.removeClass(this.container, "ui-counter-show");
	  }
	  getCounterContainer() {
	    if (this.counterContainer === null) {
	      this.counterContainer = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-counter-inner">${0}</div>
			`), this.getValue());
	    }
	    return this.counterContainer;
	  }
	  createContainer() {
	    if (this.container === null) {
	      this.container = main_core.Tag.render(_t3 || (_t3 = _`
				<div class="ui-counter">${0}</div>
			`), this.getCounterContainer());
	      this.setSize(this.size);
	      this.setColor(this.color);
	      this.setBorder(this.border);
	      this.createSecondaryContainer();
	      this.setSecondaryColor();
	    }
	    return this.container;
	  }

	  //endregion

	  getContainer() {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    return this.container;
	  }
	  renderTo(node) {
	    if (main_core.Type.isDomNode(node)) {
	      return node.appendChild(this.getContainer());
	    }
	    return null;
	  }
	  destroy() {
	    main_core.Dom.remove(this.container);
	    this.container = null;
	    this.secondaryContainer = null;
	    this.finished = false;
	    this.textAfterContainer = null;
	    this.textBeforeContainer = null;
	    this.bar = null;
	    this.svg = null;
	    for (const property in this) {
	      if (this.hasOwnProperty(property)) {
	        delete this[property];
	      }
	    }
	    Object.setPrototypeOf(this, null);
	  }
	}
	function _getBorderClassname2(border) {
	  if (border) {
	    return 'ui-counter-border';
	  } else {
	    return '';
	  }
	}
	Counter.Color = CounterColor;
	Counter.Size = CounterSize;

	exports.Counter = Counter;
	exports.CounterColor = CounterColor;
	exports.CounterSize = CounterSize;

}((this.BX.UI = this.BX.UI || {}),BX,BX));
//# sourceMappingURL=cnt.bundle.js.map
