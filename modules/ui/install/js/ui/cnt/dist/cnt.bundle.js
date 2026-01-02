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

	/**
	 * @namespace {BX.UI}
	 */
	class CounterStyle {}
	CounterStyle.FILLED_EXTRA = '--style-filled-extra';
	CounterStyle.FILLED = '--style-filled';
	CounterStyle.FILLED_INVERTED = '--style-filled-inverted';
	CounterStyle.FILLED_ALERT = '--style-filled-alert';
	CounterStyle.FILLED_ALERT_INVERTED = '--style-filled-alert-inverted';
	CounterStyle.FILLED_WARNING = '--style-filled-warning';
	CounterStyle.FILLED_SUCCESS = '--style-filled-success';
	CounterStyle.FILLED_SUCCESS_INVERTED = '--style-filled-success-inverted';
	CounterStyle.FILLED_NO_ACCENT = '--style-filled-no-accent';
	CounterStyle.FILLED_NO_ACCENT_INVERTED = '--style-filled-no-accent-inverted';
	CounterStyle.TINTED_NO_ACCENT = '--style-tinted-no-accent';
	CounterStyle.OUTLINE_NO_ACCENT = '--style-outline-no-accent';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	var _usePercentSymbol = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("usePercentSymbol");
	var _useAirDesign = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("useAirDesign");
	var _style = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("style");
	var _hideIfZero = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("hideIfZero");
	var _node = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("node");
	var _id = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("id");
	var _getBorderClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBorderClassname");
	var _createAirCounterContainer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("createAirCounterContainer");
	var _setPositiveValue = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("setPositiveValue");
	class Counter {
	  static initFromCounterNode(node) {
	    var _Object$values$find, _Object$values$find2, _Object$values$find3;
	    if (main_core.Dom.hasClass(node, Counter.BaseClassname) === false) {
	      return null;
	    }
	    const options = {};
	    options.useAirDesign = main_core.Dom.hasClass(node, '--air');
	    options.style = (_Object$values$find = Object.values(Counter.Style).find(value => main_core.Dom.hasClass(node, value))) != null ? _Object$values$find : Counter.Color.PRIMARY;
	    options.color = (_Object$values$find2 = Object.values(Counter.Color).find(value => main_core.Dom.hasClass(node, value))) != null ? _Object$values$find2 : Counter.Style.FILLED;
	    options.size = (_Object$values$find3 = Object.values(Counter.Size).find(value => main_core.Dom.hasClass(node, value))) != null ? _Object$values$find3 : Counter.Size.MEDIUM;
	    options.value = parseInt(main_core.Dom.attr(node, 'data-value'), 10);
	    options.hideIfZero = main_core.Dom.hasClass(node, '--hide-zero');
	    options.node = node;
	    options.id = node.id;
	    return new Counter(options);
	  }
	  static updateCounterNodeValue(node, value) {
	    const counter = Counter.initFromCounterNode(node);
	    if (counter && main_core.Type.isNumber(value)) {
	      counter.update(value);
	    }
	  }
	  constructor(options) {
	    var _options$node, _this$options, _this$options$style;
	    Object.defineProperty(this, _setPositiveValue, {
	      value: _setPositiveValue2
	    });
	    Object.defineProperty(this, _createAirCounterContainer, {
	      value: _createAirCounterContainer2
	    });
	    Object.defineProperty(this, _getBorderClassname, {
	      value: _getBorderClassname2
	    });
	    Object.defineProperty(this, _usePercentSymbol, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _useAirDesign, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _style, {
	      writable: true,
	      value: CounterStyle.FILLED
	    });
	    Object.defineProperty(this, _hideIfZero, {
	      writable: true,
	      value: false
	    });
	    Object.defineProperty(this, _node, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _id, {
	      writable: true,
	      value: undefined
	    });
	    this.options = main_core.Type.isPlainObject(options) ? options : {};
	    babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign] = this.options.useAirDesign === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _node)[_node] = (_options$node = options.node) != null ? _options$node : null;
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
	    babelHelpers.classPrivateFieldLooseBase(this, _usePercentSymbol)[_usePercentSymbol] = ((_this$options = this.options) == null ? void 0 : _this$options.usePercentSymbol) === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _style)[_style] = (_this$options$style = this.options.style) != null ? _this$options$style : CounterStyle.FILLED;
	    babelHelpers.classPrivateFieldLooseBase(this, _hideIfZero)[_hideIfZero] = this.options.hideIfZero === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _id)[_id] = this.options.id;
	  }

	  // region Parameters
	  setValue(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _setPositiveValue)[_setPositiveValue](value);
	    main_core.Dom.attr(this.getContainer(), 'data-value', value);
	    main_core.Dom.removeClass(this.getContainer(), '--one-digit');
	    if (this.value < 10 && babelHelpers.classPrivateFieldLooseBase(this, _usePercentSymbol)[_usePercentSymbol] === false) {
	      main_core.Dom.addClass(this.getContainer(), '--one-digit');
	    }
	    return this;
	  }
	  getValue() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _usePercentSymbol)[_usePercentSymbol]) {
	      return this.value;
	    }
	    if (this.value <= this.maxValue) {
	      return this.value;
	    }
	    return `${this.maxValue}+`;
	  }
	  getRealValue() {
	    return this.value;
	  }
	  setMaxValue(value) {
	    if (main_core.Type.isNumber(value)) {
	      this.maxValue = value < 0 ? 0 : value;
	    }
	    return this;
	  }
	  getMaxValue() {
	    return this.maxValue;
	  }
	  getId() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _id)[_id];
	  }
	  isBorder() {
	    return this.border;
	  }
	  setAirDesign(flag = true) {
	    babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign] = flag === true;
	    if (!this.container) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]) {
	      main_core.Dom.addClass(this.container, '--air');
	    } else {
	      main_core.Dom.removeClass(this.container, '--air');
	    }
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
	  setStyle(style) {
	    if (this.container && babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]) {
	      main_core.Dom.removeClass(this.container, babelHelpers.classPrivateFieldLooseBase(this, _style)[_style]);
	      main_core.Dom.addClass(this.container, style);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _style)[_style] = style;
	  }
	  setSize(size) {
	    if (main_core.Type.isStringFilled(size)) {
	      main_core.Dom.removeClass(this.container, this.size);
	      this.size = size;
	      main_core.Dom.addClass(this.container, this.size);
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
	    const borderedCounterClassname = babelHelpers.classPrivateFieldLooseBase(this, _getBorderClassname)[_getBorderClassname](true);
	    if (border) {
	      main_core.Dom.addClass(this.container, borderedCounterClassname);
	    } else {
	      main_core.Dom.removeClass(this.container, borderedCounterClassname);
	    }
	    return this;
	  }
	  // endregion

	  // region Counter
	  update(value) {
	    if (this.container === null) {
	      this.createContainer(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    }
	    if (Boolean(this.animate) === true && babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign] === false) {
	      this.updateAnimated(value);
	    } else if (Boolean(this.animate) === false) {
	      this.setValue(value);
	      if (babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]) {
	        const oldCounterContainer = this.counterContainer;
	        this.counterContainer = null;
	        this.counterContainer = this.getCounterContainer();
	        main_core.Dom.replace(oldCounterContainer, this.counterContainer);
	      } else {
	        const percentSymbol = babelHelpers.classPrivateFieldLooseBase(this, _usePercentSymbol)[_usePercentSymbol] ? '%' : '';
	        main_core.Dom.adjust(this.counterContainer, {
	          text: `${this.getValue()}${percentSymbol}`
	        });
	      }
	    }
	  }
	  updateAnimated(value) {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    if (value > this.value && this.value < this.maxValue) {
	      main_core.Dom.addClass(this.counterContainer, 'ui-counter-plus');
	    } else if (value < this.value && this.value < this.maxValue) {
	      main_core.Dom.addClass(this.counterContainer, 'ui-counter-minus');
	    }
	    setTimeout(() => {
	      this.setValue(value);
	      main_core.Dom.adjust(this.counterContainer, {
	        text: this.getValue()
	      });
	    }, 250);
	    setTimeout(() => {
	      main_core.Dom.removeClass(this.counterContainer, 'ui-counter-plus');
	      main_core.Dom.removeClass(this.counterContainer, 'ui-counter-minus');
	    }, 500);
	  }
	  show() {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    main_core.Dom.addClass(this.container, 'ui-counter-show');
	    main_core.Dom.removeClass(this.container, 'ui-counter-hide');
	  }
	  hide() {
	    if (this.container === null) {
	      this.createContainer();
	    }
	    main_core.Dom.addClass(this.container, 'ui-counter-hide');
	    main_core.Dom.removeClass(this.container, 'ui-counter-show');
	  }
	  getCounterContainer() {
	    if (this.counterContainer === null && babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]) {
	      this.counterContainer = babelHelpers.classPrivateFieldLooseBase(this, _createAirCounterContainer)[_createAirCounterContainer]();
	    } else if (this.counterContainer === null) {
	      const percentSymbol = babelHelpers.classPrivateFieldLooseBase(this, _usePercentSymbol)[_usePercentSymbol] ? '%' : '';
	      this.counterContainer = main_core.Tag.render(_t2 || (_t2 = _`
				<div class="ui-counter-inner">${0}${0}</div>
			`), this.getValue(), percentSymbol);
	    }
	    return this.counterContainer;
	  }
	  // node params used only for vue3 component
	  createContainer(node = null) {
	    if (this.container === null) {
	      if (node) {
	        this.container = node;
	        this.container.className = 'ui-counter ui-counter__scope';
	        main_core.Dom.clean(this.container);
	        main_core.Dom.append(this.getCounterContainer(), this.container);
	      } else {
	        this.container = main_core.Tag.render(_t3 || (_t3 = _`
					<div class="ui-counter ui-counter__scope">${0}</div>
				`), this.getCounterContainer());
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _hideIfZero)[_hideIfZero]) {
	        main_core.Dom.addClass(this.container, '--hide-zero');
	      }
	      if (babelHelpers.classPrivateFieldLooseBase(this, _id)[_id]) {
	        main_core.Dom.attr(this.container, 'id', babelHelpers.classPrivateFieldLooseBase(this, _id)[_id]);
	      }
	      this.setAirDesign(babelHelpers.classPrivateFieldLooseBase(this, _useAirDesign)[_useAirDesign]);
	      this.setSize(this.size);
	      this.setColor(this.color);
	      this.setStyle(babelHelpers.classPrivateFieldLooseBase(this, _style)[_style]);
	      this.setBorder(this.border);
	      this.setValue(this.value);
	      this.createSecondaryContainer();
	      this.setSecondaryColor();
	    }
	    return this.container;
	  }

	  // endregion

	  getContainer() {
	    if (this.container === null) {
	      this.createContainer(babelHelpers.classPrivateFieldLooseBase(this, _node)[_node]);
	    }
	    return this.container;
	  }
	  renderTo(node) {
	    if (main_core.Type.isDomNode(node)) {
	      main_core.Dom.append(this.getContainer(), node);
	      return this.getContainer();
	    }
	    return null;
	  }

	  /** @deprecated used only for vue3 component */
	  renderOnNode(node) {
	    this.createContainer(node);
	  }
	  render() {
	    return this.getContainer();
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
	    Object.keys(this).forEach(property => {
	      if (Object.prototype.hasOwnProperty.call(this, property)) {
	        delete this[property];
	      }
	    });
	    Object.setPrototypeOf(this, null);
	  }
	}
	function _getBorderClassname2(border) {
	  if (border) {
	    return 'ui-counter-border';
	  }
	  return '';
	}
	function _createAirCounterContainer2() {
	  let symbol = '';
	  let value = this.value;
	  if (babelHelpers.classPrivateFieldLooseBase(this, _usePercentSymbol)[_usePercentSymbol]) {
	    symbol = '%';
	  } else if (this.value > this.maxValue) {
	    value = this.value > this.maxValue ? this.maxValue : this.value;
	    symbol = '+';
	  }
	  const valueContainer = main_core.Tag.render(_t4 || (_t4 = _`<span class="ui-counter__value">${0}</span>`), value);
	  const symbolContainer = main_core.Tag.render(_t5 || (_t5 = _`<span class="ui-counter__symbol">${0}</span>`), symbol);
	  return main_core.Tag.render(_t6 || (_t6 = _`
			<div class="ui-counter-inner">
				${0}
				${0}
			</div>
		`), valueContainer, symbolContainer);
	}
	function _setPositiveValue2(value) {
	  if (main_core.Type.isNumber(value)) {
	    this.value = value < 0 ? 0 : value;
	  }
	}
	Counter.Color = CounterColor;
	Counter.Size = CounterSize;
	Counter.Style = CounterStyle;
	Counter.BaseClassname = 'ui-counter';

	exports.Counter = Counter;
	exports.CounterColor = CounterColor;
	exports.CounterSize = CounterSize;
	exports.CounterStyle = CounterStyle;

}((this.BX.UI = this.BX.UI || {}),BX,BX));
//# sourceMappingURL=cnt.bundle.js.map
