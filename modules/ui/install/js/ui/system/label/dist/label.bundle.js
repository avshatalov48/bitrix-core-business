/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
this.BX.UI.System = this.BX.UI.System || {};
(function (exports,main_core) {
	'use strict';

	const LabelStyle = Object.freeze({
	  FILLED_EXTRA: 'filledExtra',
	  FILLED: 'filled',
	  FILLED_ALERT: 'filledAlert',
	  FILLED_WARNING: 'filledWarning',
	  FILLED_SUCCESS: 'filledSuccess',
	  FILLED_NO_ACCENT: 'filledNoAccent',
	  FILLED_INVERTED: 'filledInverted',
	  FILLED_ALERT_INVERTED: 'filledAlertInverted',
	  FILLED_WARNING_INVERTED: 'filledWarningInverted',
	  FILLED_SUCCESS_INVERTED: 'filledSuccessInverted',
	  FILLED_NO_ACCENT_INVERTED: 'filledNoAccentInverted',
	  TINTED: 'tinted',
	  TINTED_SUCCESS: 'tintedSuccess',
	  TINTED_WARNING: 'tintedWarning',
	  TINTED_NO_ACCENT: 'tintedNoAccent',
	  COLLAB: 'collab',
	  OUTLINE_NO_ACCENT: 'outlineNoAccent'
	});

	const LabelSize = Object.freeze({
	  MD: 'md',
	  SM: 'sm',
	  XS: 'xs'
	});

	const LabelIcon = Object.freeze({
	  NONE: '',
	  CHECK: 'check',
	  ATTENTION: 'attention',
	  CROSS: 'cross',
	  QUESTION: 'question',
	  CHECK_STROKE: 'checkStroke',
	  CROSS_STROKE: 'crossStroke',
	  PROCESS_STROKE: 'processStroke'
	});

	let _ = t => t,
	  _t,
	  _t2;
	var _size = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("size");
	var _value = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("value");
	var _style = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("style");
	var _border = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("border");
	var _icon = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("icon");
	var _wrapper = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("wrapper");
	var _getClassname = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getClassname");
	var _validateSize = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateSize");
	var _validateStyle = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("validateStyle");
	class Label {
	  constructor(options = {}) {
	    var _options$size, _options$style, _options$value, _options$icon;
	    Object.defineProperty(this, _validateStyle, {
	      value: _validateStyle2
	    });
	    Object.defineProperty(this, _validateSize, {
	      value: _validateSize2
	    });
	    Object.defineProperty(this, _getClassname, {
	      value: _getClassname2
	    });
	    Object.defineProperty(this, _size, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _value, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _style, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _border, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _icon, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _wrapper, {
	      writable: true,
	      value: null
	    });
	    this.setSize((_options$size = options.size) != null ? _options$size : LabelSize.MD);
	    this.setStyle((_options$style = options.style) != null ? _options$style : LabelStyle.FILLED);
	    babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = (_options$value = options.value) != null ? _options$value : '';
	    babelHelpers.classPrivateFieldLooseBase(this, _border)[_border] = options.border === true;
	    babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = (_options$icon = options.icon) != null ? _options$icon : null;
	  }
	  render() {
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = main_core.Tag.render(_t || (_t = _`
			<div class="${0}" title="${0}">
				<div class="ui-system-label__inner">
					<div class="ui-system-label__value">
						${0}
					</div>
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _getClassname)[_getClassname](), babelHelpers.classPrivateFieldLooseBase(this, _value)[_value], babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]);
	    return babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper];
	  }

	  /*
	  * @deprecated used only in vue extension
	  * */
	  renderOnNode(node) {
	    // eslint-disable-next-line no-param-reassign
	    node.className = '';
	    // eslint-disable-next-line no-param-reassign
	    node.innerHTML = '';

	    // eslint-disable-next-line no-param-reassign
	    node.className = babelHelpers.classPrivateFieldLooseBase(this, _getClassname)[_getClassname]();
	    main_core.Dom.attr(node, 'title', babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]);
	    const nodeInner = main_core.Tag.render(_t2 || (_t2 = _`
			<div class="ui-system-label__inner">
				<div class="ui-system-label__value">
					${0}
				</div>
			</div>
		`), babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]);
	    main_core.Dom.append(nodeInner, node);
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = node;
	  }
	  getStyle() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _style)[_style];
	  }
	  setStyle(style) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _validateStyle)[_validateStyle](style) === false) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], `--style-${babelHelpers.classPrivateFieldLooseBase(this, _style)[_style]}`);
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], `--style-${style}`);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _style)[_style] = style;
	  }
	  setSize(size) {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _validateSize)[_validateSize](size) === false) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], `--size-${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}`);
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], `--size-${size}`);
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _size)[_size] = size;
	  }
	  getSize() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _size)[_size];
	  }
	  getValue() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _value)[_value];
	  }
	  setValue(value) {
	    babelHelpers.classPrivateFieldLooseBase(this, _value)[_value] = value;
	    if (babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      const valueElement = babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper].querySelector('.ui-system-label__value');
	      if (valueElement) {
	        valueElement.innerText = value;
	      }
	      main_core.Dom.attr(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], 'title', babelHelpers.classPrivateFieldLooseBase(this, _value)[_value]);
	    }
	  }
	  setIcon(icon) {
	    main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], [`--icon-${babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]}`, '--icon-mode']);
	    if (icon) {
	      babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = icon;
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], [`--icon-${babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]}`, '--icon-mode']);
	    } else {
	      babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon] = null;
	    }
	  }
	  setBordered(flag = true) {
	    babelHelpers.classPrivateFieldLooseBase(this, _border)[_border] = flag === true;
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]) {
	      return;
	    }
	    if (babelHelpers.classPrivateFieldLooseBase(this, _border)[_border]) {
	      main_core.Dom.addClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], '--bordered');
	    } else {
	      main_core.Dom.removeClass(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper], '--bordered');
	    }
	  }
	  destroy() {
	    main_core.Dom.remove(babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper]);
	    babelHelpers.classPrivateFieldLooseBase(this, _wrapper)[_wrapper] = null;
	  }
	}
	function _getClassname2() {
	  const classes = ['ui-system-label', `--size-${babelHelpers.classPrivateFieldLooseBase(this, _size)[_size]}`, `--style-${babelHelpers.classPrivateFieldLooseBase(this, _style)[_style]}`];
	  if (babelHelpers.classPrivateFieldLooseBase(this, _border)[_border]) {
	    classes.push('--bordered');
	  }
	  if (babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]) {
	    classes.push(`--icon-mode --icon-${babelHelpers.classPrivateFieldLooseBase(this, _icon)[_icon]}`);
	  }
	  return classes.join(' ');
	}
	function _validateSize2(size) {
	  const isValid = Object.values(LabelSize).includes(size);
	  if (isValid === false) {
	    console.warn('UI.System.Label: invalid size', size);
	  }
	  return isValid;
	}
	function _validateStyle2(style) {
	  const isValid = Object.values(LabelStyle).includes(style);
	  if (isValid === false) {
	    console.warn('UI.System.Label: invalid style', style);
	  }
	  return isValid;
	}

	const Label$$1 = {
	  name: 'UILabel',
	  props: {
	    size: {
	      type: String,
	      required: false,
	      default: LabelSize.MD,
	      validator: value => {
	        return Object.values(LabelSize).includes(value);
	      }
	    },
	    style: {
	      type: String,
	      required: false,
	      default: LabelStyle.FILLED,
	      validator: value => {
	        return Object.values(LabelStyle).includes(value);
	      }
	    },
	    bordered: {
	      type: Boolean,
	      required: false,
	      default: false
	    },
	    value: {
	      type: String,
	      required: true
	    },
	    icon: {
	      type: String,
	      required: false,
	      default: '',
	      validator: value => Object.values(LabelIcon).includes(value)
	    }
	  },
	  watch: {
	    value(newValue) {
	      var _this$label;
	      (_this$label = this.label) == null ? void 0 : _this$label.setValue(newValue);
	    },
	    size(newSize) {
	      var _this$label2;
	      (_this$label2 = this.label) == null ? void 0 : _this$label2.setSize(newSize);
	    },
	    style(newStyle) {
	      var _this$label3;
	      (_this$label3 = this.label) == null ? void 0 : _this$label3.setStyle(newStyle);
	    },
	    bordered(flag) {
	      var _this$label4;
	      (_this$label4 = this.label) == null ? void 0 : _this$label4.setBordered(flag);
	    },
	    icon(iconName) {
	      var _this$label5;
	      (_this$label5 = this.label) == null ? void 0 : _this$label5.setIcon(iconName);
	    }
	  },
	  beforeMount() {
	    this.label = new Label({
	      size: this.size,
	      style: this.style,
	      bordered: this.bordered,
	      value: this.value,
	      icon: this.icon
	    });
	  },
	  unmount() {
	    this.label.destroy();
	    this.label = null;
	  },
	  mounted() {
	    this.label.renderOnNode(this.$refs.container);
	  },
	  template: `
		<div ref="container"></div>
	`
	};

	var vue = /*#__PURE__*/Object.freeze({
		Label: Label$$1
	});

	exports.Vue = vue;
	exports.LabelStyle = LabelStyle;
	exports.LabelSize = LabelSize;
	exports.LabelIcon = LabelIcon;
	exports.Label = Label;

}((this.BX.UI.System.Label = this.BX.UI.System.Label || {}),BX));
//# sourceMappingURL=label.bundle.js.map
