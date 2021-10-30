this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,main_popup,main_core_events,landing_backend,landing_pageobject,main_core) {
	'use strict';

	var _templateObject;

	var BaseProcessor = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseProcessor, _EventEmitter);

	  function BaseProcessor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BaseProcessor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseProcessor).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.property = 'color';
	    _this.options = options;
	    _this.pseudoClass = null;
	    return _this;
	  }

	  babelHelpers.createClass(BaseProcessor, [{
	    key: "getProperty",
	    value: function getProperty() {
	      return main_core.Type.isArray(this.property) ? this.property : [this.property];
	    }
	  }, {
	    key: "getVariableName",
	    value: function getVariableName() {
	      return main_core.Type.isArray(this.variableName) ? this.variableName : [this.variableName];
	    }
	  }, {
	    key: "isNullValue",
	    value: function isNullValue(value) {
	      return value === null;
	    }
	  }, {
	    key: "getPseudoClass",
	    value: function getPseudoClass() {
	      return this.pseudoClass;
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return _this2.buildLayout();
	      });
	    }
	  }, {
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div>Base processor</div>"])));
	    }
	  }, {
	    key: "getClassName",
	    value: function getClassName() {
	      return [this.className];
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {}
	  }, {
	    key: "getStyle",
	    value: function getStyle() {
	      if (this.getValue() === null) {
	        return babelHelpers.defineProperty({}, this.getVariableName(), null);
	      }

	      return babelHelpers.defineProperty({}, this.getVariableName(), this.getValue().getStyleString());
	    }
	    /**
	     * Set value by new format
	     * @param value {string: string}
	     */

	  }, {
	    key: "setProcessorValue",
	    value: function setProcessorValue(value) {
	      // Just get last css variable
	      var processorProperty = this.getVariableName()[this.getVariableName().length - 1];
	      this.setValue(value[processorProperty]);
	    }
	    /**
	     * Set old-type value by computedStyle
	     * @param value {string: string} | null
	     */

	  }, {
	    key: "setDefaultValue",
	    value: function setDefaultValue(value) {
	      if (value !== null) {
	        var inlineProperty = this.getProperty()[this.getProperty().length - 1];

	        if (inlineProperty in value) {
	          this.setValue(value[inlineProperty]);
	          this.unsetActive();
	          return;
	        }
	      }

	      this.setValue(null);
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {}
	  }, {
	    key: "onReset",
	    value: function onReset() {
	      this.emit('onReset');
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {}
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.cache.delete('value');
	      this.emit('onChange');
	    }
	  }]);
	  return BaseProcessor;
	}(main_core_events.EventEmitter);

	var matcher = /^rgba? ?\((\d{1,3})[, ]+(\d{1,3})[, ]+(\d{1,3})([, ]+([\d\.]{1,5}))?\)$/i;
	function isRgbString(rgbString) {
	  return !!rgbString.match(matcher);
	}

	var matcherHex = /^#([\da-f]{3}){1,2}$/i;
	function isHex(hex) {
	  return !!hex.trim().match(matcherHex);
	}

	var matcherHsl = /^hsla?\((\d{1,3}), ?(\d{1,3})%, ?(\d{1,3})%(, ?([\d .]+))?\)/i;
	function isHslString(hsla) {
	  return !!hsla.trim().match(matcherHsl);
	}

	function hexToRgb(hex) {
	  if (hex.length === 4) {
	    var r = parseInt("0x".concat(hex[1]).concat(hex[1]), 16);
	    var g = parseInt("0x".concat(hex[2]).concat(hex[2]), 16);
	    var b = parseInt("0x".concat(hex[3]).concat(hex[3]), 16);
	    return {
	      r: r,
	      g: g,
	      b: b
	    };
	  }

	  if (hex.length === 7) {
	    var _r = parseInt("0x".concat(hex[1]).concat(hex[2]), 16);

	    var _g = parseInt("0x".concat(hex[3]).concat(hex[4]), 16);

	    var _b = parseInt("0x".concat(hex[5]).concat(hex[6]), 16);

	    return {
	      r: _r,
	      g: _g,
	      b: _b
	    };
	  }

	  return {
	    r: 255,
	    g: 255,
	    b: 255
	  };
	}

	function rgbToHsla(rgb) {
	  var r = rgb.r / 255;
	  var g = rgb.g / 255;
	  var b = rgb.b / 255;
	  var max = Math.max(r, g, b);
	  var min = Math.min(r, g, b);
	  var h,
	      s,
	      l = (max + min) / 2; // let l = h;
	  // let s;

	  if (max === min) {
	    h = s = 0;
	  } else {
	    var d = max - min;
	    s = l > 0.5 ? d / (2 - max - min) : d / (max + min);

	    switch (max) {
	      case r:
	        h = (g - b) / d + (g < b ? 6 : 0);
	        break;

	      case g:
	        h = (b - r) / d + 2;
	        break;

	      case b:
	        h = (r - g) / d + 4;
	        break;
	    }

	    h *= 0.6;
	  }

	  return {
	    h: Math.round(h * 100),
	    s: Math.round(s * 100),
	    l: Math.round(l * 100),
	    a: 'a' in rgb ? rgb.a : 1
	  };
	} // 	const v = Math.max(r, g, b);
	// 	const diff = v - Math.min(r, g, b);
	// 	const diffc = (c) => {
	// 		return (v - c) / 6 / diff + 1 / 2;
	// 	};
	//
	// 	if (diff === 0)
	// 	{
	// 		h = 0;
	// 		s = 0;
	// 	}
	// 	else
	// 	{
	// 		s = diff / v;
	// 		rdif = diffc(r);
	// 		gdif = diffc(g);
	// 		bdif = diffc(b);
	//
	// 		if (r === v)
	// 		{
	// 			h = bdif - gdif;
	// 		}
	// 		else if (g === v)
	// 		{
	// 			h = (1 / 3) + rdif - bdif;
	// 		}
	// 		else if (b === v)
	// 		{
	// 			h = (2 / 3) + gdif - rdif;
	// 		}
	//
	// 		if (h < 0)
	// 		{
	// 			h += 1;
	// 		}
	// 		else if (h > 1)
	// 		{
	// 			h -= 1;
	// 		}
	// 	}
	//
	// 	return {
	// 		h: h * 360,
	// 		s: s * 100,
	// 		l: v * 100,
	// 		a: rgb.a || 1,
	// 	};
	// }

	function hexToHsl(hex) {
	  var rgb = hexToRgb(hex.trim());
	  return rgbToHsla(rgb);
	}

	function rgbToHex(rgb) {
	  var r = rgb.r.toString(16);
	  var g = rgb.g.toString(16);
	  var b = rgb.b.toString(16);

	  if (r.length === 1) {
	    r = "0" + r;
	  }

	  if (g.length === 1) {
	    g = "0" + g;
	  }

	  if (b.length === 1) {
	    b = "0" + b;
	  }

	  return "#" + r + g + b;
	}

	function hslToRgb(hsl) {
	  // todo: a little not equal with reverce conversion :-/
	  // todo: f.e. hsl(73.53.50) it 166,195,60 and #a5c33c,
	  // todo: but in reverse #a5c33c => 165,195,60
	  // todo: because we save ColorValue in hsl can be some differences
	  var h = hsl.h;
	  var s = hsl.s / 100;
	  var l = hsl.l / 100;
	  var c = (1 - Math.abs(2 * l - 1)) * s;
	  var x = c * (1 - Math.abs(h / 60 % 2 - 1));
	  var m = l - c / 2;
	  var r = 0;
	  var g = 0;
	  var b = 0;

	  if (0 <= h && h < 60) {
	    r = c;
	    g = x;
	    b = 0;
	  } else if (60 <= h && h < 120) {
	    r = x;
	    g = c;
	    b = 0;
	  } else if (120 <= h && h < 180) {
	    r = 0;
	    g = c;
	    b = x;
	  } else if (180 <= h && h < 240) {
	    r = 0;
	    g = x;
	    b = c;
	  } else if (240 <= h && h < 300) {
	    r = x;
	    g = 0;
	    b = c;
	  } else if (300 <= h && h < 360) {
	    r = c;
	    g = 0;
	    b = x;
	  }

	  r = Math.round((r + m) * 255);
	  g = Math.round((g + m) * 255);
	  b = Math.round((b + m) * 255);
	  return {
	    r: r,
	    g: g,
	    b: b
	  };
	}

	function hslToHex(hsl) {
	  var rgb = hslToRgb(hsl);
	  return rgbToHex(rgb);
	}

	function rgbStringToHsla(rgbString) {
	  var matches = rgbString.trim().match(matcher);

	  if (matches.length > 0) {
	    return rgbToHsla({
	      r: main_core.Text.toNumber(matches[1]),
	      g: main_core.Text.toNumber(matches[2]),
	      b: main_core.Text.toNumber(matches[3]),
	      a: matches[5] ? main_core.Text.toNumber(matches[5]) : 1
	    });
	  }
	}

	function hslStringToHsl(hslString) {
	  var matches = hslString.trim().match(matcherHsl);

	  if (matches && matches.length > 0) {
	    return {
	      h: main_core.Text.toNumber(matches[1]),
	      s: main_core.Text.toNumber(matches[2]),
	      l: main_core.Text.toNumber(matches[3]),
	      a: matches[5] ? main_core.Text.toNumber(matches[5]) : 1
	    };
	  }
	}

	var matcher$1 = /^(var\()?((--[\w\d-]*?)(-opacity_([\d_]+)?)?)\)?$/i;
	function isCssVar(css) {
	  return !!css.trim().match(matcher$1);
	}
	function parseCssVar(css) {
	  var matches = css.trim().match(matcher$1);

	  if (!!matches) {
	    var cssVar = {
	      full: matches[2],
	      name: matches[3]
	    };

	    if (matches[5]) {
	      cssVar.opacity = +parseFloat(matches[5].replace('_', '.')).toFixed(1);
	    }

	    return cssVar;
	  }

	  return null;
	}

	var defaultColorValueOptions = {
	  h: 205,
	  s: 1,
	  l: 50,
	  a: 1
	};
	var defaultBgImageSize = 'cover';
	var defaultBgImageAttachment = 'scroll';
	var defaultOverlay = null;
	var defaultBgImageValueOptions = {
	  url: null,
	  size: defaultBgImageSize,
	  attachment: defaultBgImageAttachment,
	  overlay: defaultOverlay
	};

	var ColorValue = /*#__PURE__*/function () {
	  /**
	   * For preserve differences between hsl->rgb and rgb->hsl conversions we can save hex
	   * @type {?string}
	   */

	  /**
	   * if set css variable value - save them in '--var-name' format
	   * @type {?string}
	   */
	  function ColorValue(value) {
	    babelHelpers.classCallCheck(this, ColorValue);
	    this.value = defaultColorValueOptions;
	    this.hex = null;
	    this.cssVar = null;
	    this.setValue(value);
	  }

	  babelHelpers.createClass(ColorValue, [{
	    key: "getName",
	    value: function getName() {
	      if (this.hex) {
	        return this.getHex() + '_' + this.getOpacity();
	      }

	      var _this$getHsl = this.getHsl(),
	          h = _this$getHsl.h,
	          s = _this$getHsl.s,
	          l = _this$getHsl.l;

	      return "".concat(h, "-").concat(s, "-").concat(l, "-").concat(this.getOpacity());
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (main_core.Type.isObject(value)) {
	        if (value instanceof ColorValue) {
	          this.value = value.getHsla();
	          this.cssVar = value.getCssVar();
	          this.hex = value.getHexOriginal();
	        } else {
	          this.value = babelHelpers.objectSpread({}, this.value, value);
	        }
	      }

	      if (main_core.Type.isString(value)) {
	        if (isHslString(value)) {
	          this.value = hslStringToHsl(value);
	        } else if (isHex(value)) {
	          this.value = babelHelpers.objectSpread({}, hexToHsl(value), {
	            a: defaultColorValueOptions.a
	          });
	          this.hex = value;
	        } else if (isRgbString(value)) {
	          this.value = rgbStringToHsla(value);
	        } else if (isCssVar(value)) {
	          // todo: what about opacity in primary?
	          var cssVar = parseCssVar(value);

	          if (cssVar !== null) {
	            this.cssVar = cssVar.name;
	            this.setValue(main_core.Dom.style(document.documentElement, this.cssVar));

	            if (cssVar.opacity) {
	              this.setOpacity(cssVar.opacity);
	            }
	          }
	        }
	      }

	      this.value.h = Math.round(this.value.h);
	      this.value.s = Math.round(this.value.s);
	      this.value.l = Math.round(this.value.l);
	      this.value.a = +this.value.a.toFixed(1);
	      return this;
	    }
	  }, {
	    key: "setOpacity",
	    value: function setOpacity(opacity) {
	      this.setValue({
	        a: opacity
	      });
	      return this;
	    }
	  }, {
	    key: "lighten",
	    value: function lighten(percent) {
	      this.value.l = Math.min(this.value.l + percent, 100);
	      this.hex = null;
	      return this;
	    }
	  }, {
	    key: "darken",
	    value: function darken(percent) {
	      this.value.l = Math.max(this.value.l - percent, 0);
	      this.hex = null;
	      return this;
	    }
	  }, {
	    key: "saturate",
	    value: function saturate(percent) {
	      this.value.s = Math.min(this.value.s + percent, 100);
	      this.hex = null;
	      return this;
	    }
	  }, {
	    key: "desaturate",
	    value: function desaturate(percent) {
	      this.value.s = Math.max(this.value.s - percent, 0);
	      this.hex = null;
	      return this;
	    }
	  }, {
	    key: "adjustHue",
	    value: function adjustHue(degree) {
	      this.value.h = (this.value.h + degree) % 360;
	      return this;
	    }
	  }, {
	    key: "getHsl",
	    value: function getHsl() {
	      return {
	        h: this.value.h,
	        s: this.value.s,
	        l: this.value.l
	      };
	    }
	  }, {
	    key: "getHsla",
	    value: function getHsla() {
	      return {
	        h: this.value.h,
	        s: this.value.s,
	        l: this.value.l,
	        a: this.value.a
	      };
	    }
	    /**
	     * Return original hex-string or convert value to hex (w.o. alpha)
	     * @returns {string}
	     */

	  }, {
	    key: "getHex",
	    value: function getHex() {
	      return this.hex || hslToHex(this.value);
	    }
	    /**
	     * Return hex only if value created from hex-string
	     */

	  }, {
	    key: "getHexOriginal",
	    value: function getHexOriginal() {
	      return this.hex;
	    }
	  }, {
	    key: "getOpacity",
	    value: function getOpacity() {
	      var _this$value$a;

	      return (_this$value$a = this.value.a) !== null && _this$value$a !== void 0 ? _this$value$a : defaultColorValueOptions.a;
	    }
	  }, {
	    key: "getCssVar",
	    value: function getCssVar() {
	      return this.cssVar;
	    }
	    /**
	     * Get style string for set inline css var.
	     * Set hsla value or primary css var with opacity in format --var-name-opacity_12_3
	     * @returns {string}
	     */

	  }, {
	    key: "getStyleString",
	    value: function getStyleString() {
	      if (this.cssVar === null) {
	        if (this.hex && this.getOpacity() === defaultColorValueOptions.a) {
	          return this.hex;
	        }

	        var _this$value = this.value,
	            h = _this$value.h,
	            s = _this$value.s,
	            l = _this$value.l,
	            a = _this$value.a;
	        return "hsla(".concat(h, ", ").concat(s, "%, ").concat(l, "%, ").concat(a, ")");
	      } else {
	        var fullCssVar = this.cssVar;

	        if (this.value.a !== defaultColorValueOptions.a) {
	          fullCssVar = fullCssVar + '-opacity-' + String(this.value.a).replace('.', '_');
	        }

	        return "var(".concat(fullCssVar, ")");
	      }
	    }
	  }, {
	    key: "getStyleStringForOpacity",
	    value: function getStyleStringForOpacity() {
	      var _this$value2 = this.value,
	          h = _this$value2.h,
	          s = _this$value2.s,
	          l = _this$value2.l;
	      return "linear-gradient(to right, hsla(".concat(h, ", ").concat(s, "%, ").concat(l, "%, 1) 0%, hsla(").concat(h, ", ").concat(s, "%, ").concat(l, "%, 0) 100%)");
	    }
	  }, {
	    key: "getContrast",

	    /**
	     * Special formula for contrast. Not only color invert!
	     * @returns {string}
	     */
	    value: function getContrast() {
	      var k = 60; // math h range to 0-2pi radian and add modifier by sinus

	      var rad = this.getHsl().h * Math.PI / 180;
	      k += Math.sin(rad) * 10 + 5; // 10 & 5 is approximate coefficients
	      // lighten by started light

	      var deltaL = k - 45 * this.getHsl().l / 100;
	      return new ColorValue(this.value).setValue({
	        l: (this.getHsl().l + deltaL) % 100
	      });
	    }
	    /**
	     * Special formula for lighten, good for dark and light colors
	     */

	  }, {
	    key: "getLighten",
	    value: function getLighten() {
	      var _this$getHsl2 = this.getHsl(),
	          h = _this$getHsl2.h,
	          s = _this$getHsl2.s,
	          l = _this$getHsl2.l;

	      if (s > 0) {
	        s += (l - 50) / 100 * 60;
	        s = Math.min(100, Math.max(0, l));
	      }

	      l += 10 + 20 * l / 100;
	      l = Math.min(100, l);
	      return new ColorValue({
	        h: h,
	        s: s,
	        l: l
	      });
	    }
	  }], [{
	    key: "compare",
	    value: function compare(color1, color2) {
	      return color1.getHsla().h === color2.getHsla().h && color1.getHsla().s === color2.getHsla().s && color1.getHsla().l === color2.getHsla().l && color1.getHsla().a === color2.getHsla().a && color1.cssVar === color2.cssVar;
	    }
	  }, {
	    key: "getMedian",
	    value: function getMedian(color1, color2) {
	      return new ColorValue({
	        h: (color1.getHsla().h + color2.getHsla().h) / 2,
	        s: (color1.getHsla().s + color2.getHsla().s) / 2,
	        l: (color1.getHsla().l + color2.getHsla().l) / 2,
	        a: (color1.getHsla().a + color2.getHsla().a) / 2
	      });
	    }
	  }]);
	  return ColorValue;
	}();

	var _templateObject$1;

	var BaseControl = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseControl, _EventEmitter);

	  function BaseControl(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BaseControl);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseControl).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(BaseControl, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return _this2.buildLayout();
	      });
	    }
	  }, {
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-base-control\">\n\t\t\t\tBase control\n\t\t\t</div>\n\t\t"])));
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.cache.remember('value', function () {
	        return new ColorValue();
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.cache.set('value', value);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.cache.delete('value');
	      this.emit('onChange', event);
	    }
	  }, {
	    key: "setActive",
	    value: function setActive() {
	      main_core.Dom.addClass(this.getLayout(), BaseControl.ACTIVE_CLASS);
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      main_core.Dom.removeClass(this.getLayout(), BaseControl.ACTIVE_CLASS);
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return main_core.Dom.hasClass(this.getLayout(), BaseControl.ACTIVE_CLASS);
	    }
	  }]);
	  return BaseControl;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(BaseControl, "ACTIVE_CLASS", 'active');

	var _templateObject$2, _templateObject2, _templateObject3, _templateObject4, _templateObject5;

	var Hex = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(Hex, _BaseControl);

	  function Hex() {
	    var _this;

	    babelHelpers.classCallCheck(this, Hex);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Hex).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Hex');

	    _this.previewMode = false;
	    _this.onInput = _this.onInput.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onButtonClick = _this.onButtonClick.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Hex, [{
	    key: "setPreviewMode",
	    value: function setPreviewMode(preview) {
	      this.previewMode = !!preview;
	    }
	  }, {
	    key: "buildLayout",
	    value: function buildLayout() {
	      if (!this.previewMode) {
	        // todo: add Enter click handler
	        main_core.Event.bind(this.getInput(), 'input', this.onInput);
	        main_core.Event.bind(this.getButton(), 'click', this.onButtonClick);
	      }

	      return main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-hex\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getInput(), this.getButton());
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      var _this2 = this;

	      return this.cache.remember('input', function () {
	        return _this2.previewMode ? main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-hex-preview\"></div>"]))) : main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<input type=\"text\" name=\"hexInput\" value=\"\" class=\"landing-ui-field-color-hex-input\">"])));
	      });
	    }
	  }, {
	    key: "getButton",
	    value: function getButton() {
	      var _this3 = this;

	      return this.cache.remember('editButton', function () {
	        return _this3.previewMode ? main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<svg class=\"landing-ui-field-color-hex-preview-btn\" width=\"9\" height=\"9\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t<path\n\t\t\t\t\t\t\td=\"M7.108 0l1.588 1.604L2.486 7.8.896 6.194 7.108 0zM.006 8.49a.166.166 0 00.041.158.161.161 0 00.16.042l1.774-.478L.484 6.715.006 8.49z\"\n\t\t\t\t\t\t\tfill=\"#525C69\"\n\t\t\t\t\t\t\tfill-rule=\"evenodd\"/>\n\t\t\t\t\t</svg>"]))) : main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<svg class=\"landing-ui-field-color-hex-preview-btn\" width=\"12\" height=\"9\" xmlns=\"http://www.w3.org/2000/svg\">\n\t\t\t\t\t\t<path\n\t\t\t\t\t\t\td=\"M4.27 8.551L.763 5.304 2.2 3.902l2.07 1.846L9.836.533l1.439 1.402z\"\n\t\t\t\t\t\t\tfill=\"#525C69\"\n\t\t\t\t\t\t\tfill-rule=\"evenodd\"/>\n\t\t\t\t\t</svg>"])));
	      });
	    }
	  }, {
	    key: "onInput",
	    value: function onInput() {
	      var value = this.getInput().value.replace(/[^\da-f]/gi, '');
	      value = value.substring(0, 6);
	      this.getInput().value = '#' + value.toLowerCase();
	      this.onChange();
	    }
	  }, {
	    key: "onButtonClick",
	    value: function onButtonClick() {
	      this.onChange();
	      this.emit('onButtonClick', {
	        color: this.getValue()
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      var color = this.getInput().value.length === 7 && isHex(this.getInput().value) ? new ColorValue(this.getInput().value) : null;
	      this.setValue(color);
	      this.emit('onChange', {
	        color: color
	      });
	    }
	  }, {
	    key: "adjustColors",
	    value: function adjustColors(textColor, bgColor) {
	      main_core.Dom.style(this.getInput(), 'background-color', bgColor);
	      main_core.Dom.style(this.getInput(), 'color', textColor);
	      main_core.Dom.style(this.getButton().querySelector('path'), 'fill', textColor);
	    }
	  }, {
	    key: "focus",
	    value: function focus() {
	      if (!this.previewMode) {
	        if (this.getValue() === null) {
	          this.getInput().value = '#';
	        }

	        this.getInput().focus();
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this4 = this;

	      return this.cache.remember('value', function () {
	        return _this4.getInput().value === Hex.DEFAULT_TEXT ? null : new ColorValue(_this4.getInput().value);
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Hex.prototype), "setValue", this).call(this, value);

	      if (value !== null) {
	        this.adjustColors(value.getContrast().getHex(), value.getHex());
	        this.setActive();
	      } else {
	        this.adjustColors(Hex.DEFAULT_COLOR, Hex.DEFAULT_BG);
	        this.unsetActive();
	      }

	      if (this.previewMode) {
	        this.getInput().innerText = value !== null ? value.getHex() : Hex.DEFAULT_TEXT;
	      } else if (landing_pageobject.PageObject.getRootWindow().document.activeElement !== this.getInput()) {
	        this.getInput().value = value !== null ? value.getHex() : Hex.DEFAULT_TEXT;
	      }
	    }
	  }, {
	    key: "setActive",
	    value: function setActive() {
	      main_core.Dom.addClass(this.getInput(), Hex.ACTIVE_CLASS);
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      main_core.Dom.removeClass(this.getInput(), Hex.ACTIVE_CLASS);
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return main_core.Dom.hasClass(this.getInput(), Hex.ACTIVE_CLASS);
	    }
	  }]);
	  return Hex;
	}(BaseControl);

	babelHelpers.defineProperty(Hex, "DEFAULT_TEXT", '#HEX');
	babelHelpers.defineProperty(Hex, "DEFAULT_COLOR", '#000000');
	babelHelpers.defineProperty(Hex, "DEFAULT_BG", '#eeeeee');

	var _templateObject$3, _templateObject2$1;

	var Spectrum = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(Spectrum, _BaseControl);
	  babelHelpers.createClass(Spectrum, null, [{
	    key: "getDefaultSaturation",
	    // todo: debug, del method, change calls, change css
	    value: function getDefaultSaturation() {
	      var global = window.top.document.location.saturation;
	      var urlParam = new URL(window.top.document.location).searchParams.get('saturation');
	      var saturation = global || urlParam || Spectrum.DEFAULT_SATURATION;
	      window.top.document.body.style.setProperty('--saturation', saturation + '%');
	      return parseInt(saturation);
	    }
	  }]);

	  function Spectrum(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Spectrum);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Spectrum).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Spectrum');

	    _this.onPickerDragStart = _this.onPickerDragStart.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPickerDragMove = _this.onPickerDragMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPickerDragEnd = _this.onPickerDragEnd.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onScroll = _this.onScroll.bind(babelHelpers.assertThisInitialized(_this));
	    _this.document = landing_pageobject.PageObject.getRootWindow().document;
	    _this.scrollContext = options.contentRoot;
	    main_core.Event.bind(_this.getLayout(), 'mousedown', _this.onPickerDragStart);
	    return _this;
	  }

	  babelHelpers.createClass(Spectrum, [{
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-spectrum\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getPicker());
	    }
	  }, {
	    key: "getPicker",
	    value: function getPicker() {
	      return this.cache.remember('picker', function () {
	        return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-spectrum-picker\"></div>"])));
	      });
	    }
	  }, {
	    key: "getPickerPos",
	    value: function getPickerPos() {
	      return {
	        x: main_core.Text.toNumber(main_core.Dom.style(this.getPicker(), 'left')),
	        y: main_core.Text.toNumber(main_core.Dom.style(this.getPicker(), 'top'))
	      };
	    }
	  }, {
	    key: "onPickerDragStart",
	    value: function onPickerDragStart(event) {
	      if (event.ctrlKey || event.metaKey || event.button) {
	        return;
	      }

	      main_core.Event.bind(this.scrollContext, 'scroll', this.onScroll);
	      main_core.Event.bind(this.document, 'mousemove', this.onPickerDragMove);
	      main_core.Event.bind(this.document, 'mouseup', this.onPickerDragEnd);
	      main_core.Dom.addClass(this.document.body, 'landing-ui-field-color-draggable');
	      this.onScroll();
	      this.showPicker();
	      this.onPickerDragMove(event);
	    }
	  }, {
	    key: "onPickerDragMove",
	    value: function onPickerDragMove(event) {
	      if (event.target === this.getPicker()) {
	        return;
	      }

	      this.setPickerPos(event.pageX, event.pageY);
	      this.onChange();
	    }
	  }, {
	    key: "onPickerDragEnd",
	    value: function onPickerDragEnd() {
	      main_core.Event.unbind(this.scrollContext, 'scroll', this.onScroll);
	      main_core.Event.unbind(this.document, 'mousemove', this.onPickerDragMove);
	      main_core.Event.unbind(this.document, 'mouseup', this.onPickerDragEnd);
	      main_core.Dom.removeClass(this.document.body, 'landing-ui-field-color-draggable');
	    }
	  }, {
	    key: "onScroll",
	    value: function onScroll() {
	      this.cache.delete('layoutSize');
	    }
	  }, {
	    key: "getLayoutRect",
	    value: function getLayoutRect() {
	      var _this2 = this;

	      return this.cache.remember('layoutSize', function () {
	        var layoutRect = _this2.getLayout().getBoundingClientRect();

	        return {
	          width: layoutRect.width,
	          height: layoutRect.height,
	          top: layoutRect.top,
	          left: layoutRect.left
	        };
	      });
	    }
	    /**
	     * Set picker by absolut page coords
	     * @param x
	     * @param y
	     */

	  }, {
	    key: "setPickerPos",
	    value: function setPickerPos(x, y) {
	      var _this$getLayoutRect = this.getLayoutRect(),
	          width = _this$getLayoutRect.width,
	          height = _this$getLayoutRect.height,
	          top = _this$getLayoutRect.top,
	          left = _this$getLayoutRect.left;

	      var leftToSet = Math.min(Math.max(x - left, 0), width);
	      leftToSet = leftToSet > width / Spectrum.HUE_RANGE * Spectrum.HUE_RANGE_GRAY_THRESHOLD ? width / Spectrum.HUE_RANGE * Spectrum.HUE_RANGE_GRAY_MIDDLE : leftToSet;
	      main_core.Dom.style(this.getPicker(), {
	        left: "".concat(leftToSet, "px"),
	        top: "".concat(Math.min(Math.max(y - top, 0), height), "px")
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this3 = this;

	      return this.cache.remember('value', function () {
	        if (main_core.Dom.hasClass(_this3.getPicker(), Spectrum.HIDE_CLASS)) {
	          return null;
	        }

	        var layoutWidth = _this3.getLayout().getBoundingClientRect().width;

	        var h = _this3.getPickerPos().x / layoutWidth * Spectrum.HUE_RANGE;

	        var layoutHeight = _this3.getLayout().getBoundingClientRect().height;

	        var l = (1 - _this3.getPickerPos().y / layoutHeight) * 100;

	        if (isNaN(h) || isNaN(l)) {
	          return null;
	        }

	        return new ColorValue({
	          h: Math.min(h, Spectrum.HUE_RANGE_GRAY_THRESHOLD),
	          s: h >= Spectrum.HUE_RANGE_GRAY_THRESHOLD ? 0 : Spectrum.getDefaultSaturation(),
	          l: l
	        });
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Spectrum.prototype), "setValue", this).call(this, value);

	      if (value !== null && Spectrum.isSpectrumValue(value)) {
	        // in first set value we can't match bounding client rect (layout not render). Then, use percents
	        var _value$getHsl = value.getHsl(),
	            h = _value$getHsl.h,
	            s = _value$getHsl.s,
	            l = _value$getHsl.l;

	        var left = s === 0 ? Spectrum.HUE_RANGE_GRAY_MIDDLE / Spectrum.HUE_RANGE * 100 : h / Spectrum.HUE_RANGE * 100;
	        main_core.Dom.style(this.getPicker(), 'left', "".concat(left, "%"));
	        var top = 100 - l;
	        main_core.Dom.style(this.getPicker(), 'top', "".concat(top, "%"));
	        this.showPicker();
	      } else {
	        this.hidePicker();
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.cache.delete('value');
	      this.emit('onChange', {
	        color: this.getValue()
	      });
	    }
	  }, {
	    key: "hidePicker",
	    value: function hidePicker() {
	      main_core.Dom.addClass(this.getPicker(), Spectrum.HIDE_CLASS);
	    }
	  }, {
	    key: "showPicker",
	    value: function showPicker() {
	      main_core.Dom.removeClass(this.getPicker(), Spectrum.HIDE_CLASS);
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.getValue() !== null && Spectrum.isSpectrumValue(this.getValue());
	    }
	  }], [{
	    key: "isSpectrumValue",
	    value: function isSpectrumValue(value) {
	      return value !== null && (value.getHsl().s === Spectrum.getDefaultSaturation() || value.getHsl().s === 0);
	    }
	  }]);
	  return Spectrum;
	}(BaseControl);

	babelHelpers.defineProperty(Spectrum, "DEFAULT_SATURATION", 100);
	babelHelpers.defineProperty(Spectrum, "HUE_RANGE", 375);
	babelHelpers.defineProperty(Spectrum, "HUE_RANGE_GRAY_THRESHOLD", 360);
	babelHelpers.defineProperty(Spectrum, "HUE_RANGE_GRAY_MIDDLE", 367);
	babelHelpers.defineProperty(Spectrum, "HIDE_CLASS", 'hidden');

	var _templateObject$4, _templateObject2$2;

	var Recent = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Recent, _EventEmitter);

	  function Recent() {
	    var _this;

	    babelHelpers.classCallCheck(this, Recent);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Recent).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();
	    return _this;
	  }

	  babelHelpers.createClass(Recent, [{
	    key: "getLayout",
	    value: function getLayout() {
	      this.initItems();
	      return this.getLayoutContainer();
	    }
	  }, {
	    key: "getLayoutContainer",
	    value: function getLayoutContainer() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-recent\"></div>"])));
	      });
	    }
	  }, {
	    key: "initItems",
	    value: function initItems() {
	      var _this2 = this;

	      if (Recent.itemsLoaded) {
	        this.buildItemsLayout();
	      } else {
	        landing_backend.Backend.getInstance().action("Utils::getUserOption", {
	          name: Recent.USER_OPTION_NAME
	        }).then(function (result) {
	          if (result && main_core.Type.isString(result.items)) {
	            Recent.items = [];
	            result.items.split(',').forEach(function (item) {
	              if (isHex(item) && Recent.items.length < Recent.MAX_ITEMS) {
	                Recent.items.push(item);
	              }
	            });
	            Recent.itemsLoaded = true;

	            _this2.buildItemsLayout();
	          }
	        }); // todo: what if ajax error?
	      }
	    }
	  }, {
	    key: "buildItemsLayout",
	    value: function buildItemsLayout() {
	      var _this3 = this;

	      main_core.Dom.clean(this.getLayoutContainer());
	      Recent.items.forEach(function (item) {
	        if (isHex(item)) {
	          var itemLayout = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div \n\t\t\t\t\tclass=\"landing-ui-field-color-recent-item\" \n\t\t\t\t\tstyle=\"background:", "\"\n\t\t\t\t\tdata-value=\"", "\"\n\t\t\t\t></div>"])), item, item);
	          main_core.Event.bind(itemLayout, 'click', function () {
	            return _this3.onItemClick(event);
	          });
	          main_core.Dom.append(itemLayout, _this3.getLayoutContainer());
	        }
	      });
	      return this;
	    }
	  }, {
	    key: "onItemClick",
	    value: function onItemClick(event) {
	      this.emit('onChange', {
	        hex: event.currentTarget.dataset.value
	      });
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(hex) {
	      if (isHex(hex)) {
	        var pos = Recent.items.indexOf(hex);

	        if (pos !== -1) {
	          Recent.items.splice(pos, 1);
	        }

	        Recent.items.unshift(hex);

	        if (Recent.items.length > Recent.MAX_ITEMS) {
	          Recent.items.splice(Recent.MAX_ITEMS);
	        }

	        this.buildItemsLayout();
	        this.saveItems();
	      }

	      return this;
	    }
	  }, {
	    key: "saveItems",
	    value: function saveItems() {
	      if (Recent.items.length > 0) {
	        BX.userOptions.save('landing', Recent.USER_OPTION_NAME, 'items', Recent.items);
	      }

	      return this;
	    }
	  }]);
	  return Recent;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(Recent, "USER_OPTION_NAME", 'color_field_recent_colors');
	babelHelpers.defineProperty(Recent, "MAX_ITEMS", 6);
	babelHelpers.defineProperty(Recent, "items", []);
	babelHelpers.defineProperty(Recent, "itemsLoaded", false);

	var _templateObject$5, _templateObject2$3, _templateObject3$1, _templateObject4$1;

	var Colorpicker = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(Colorpicker, _BaseControl);

	  function Colorpicker(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Colorpicker);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Colorpicker).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Colorpicker');

	    _this.popupId = 'colorpicker_popup_' + main_core.Text.getRandom();
	    _this.popupTargetContainer = options.contentRoot;
	    _this.hexPreview = new Hex();

	    _this.hexPreview.setPreviewMode(true);

	    main_core.Event.bind(_this.hexPreview.getLayout(), 'click', function () {
	      _this.recent.buildItemsLayout();

	      _this.previously = _this.getValue();

	      _this.getPopup().show();

	      if (_this.getPopup().isShown()) {
	        _this.hex.focus();
	      }
	    }); // popup

	    _this.hex = new Hex();

	    _this.hex.subscribe('onChange', _this.onHexChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.hex.subscribe('onButtonClick', _this.onSelectClick.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.spectrum = new Spectrum(options);

	    _this.spectrum.subscribe('onChange', _this.onSpectrumChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.recent = new Recent();

	    _this.recent.subscribe('onChange', _this.onRecentChange.bind(babelHelpers.assertThisInitialized(_this)));

	    main_core.Event.bind(_this.getCancelButton(), 'click', _this.onCancelClick.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Event.bind(_this.getSelectButton(), 'click', _this.onSelectClick.bind(babelHelpers.assertThisInitialized(_this))); // end popup

	    _this.previously = _this.getValue();
	    return _this;
	  }

	  babelHelpers.createClass(Colorpicker, [{
	    key: "onHexChange",
	    value: function onHexChange(event) {
	      this.setValue(event.getData().color);
	      this.onChange(event);
	    }
	  }, {
	    key: "onSpectrumChange",
	    value: function onSpectrumChange(event) {
	      this.setValue(event.getData().color);
	      this.onChange(event);
	    }
	  }, {
	    key: "onRecentChange",
	    value: function onRecentChange(event) {
	      var recentColor = new ColorValue(event.getData().hex);
	      this.setValue(recentColor);
	      this.onChange(new main_core_events.BaseEvent({
	        data: {
	          color: recentColor
	        }
	      }));
	    }
	  }, {
	    key: "onCancelClick",
	    value: function onCancelClick() {
	      this.setValue(this.previously);
	      this.getPopup().close();
	      this.onChange(new main_core_events.BaseEvent({
	        data: {
	          color: this.getValue()
	        }
	      }));
	    }
	  }, {
	    key: "onSelectClick",
	    value: function onSelectClick(event) {
	      var value = event instanceof main_core_events.BaseEvent ? event.getData().color : this.getValue();

	      if (value !== null) {
	        this.recent.addItem(this.getValue().getHex());
	      }

	      this.getPopup().close();
	    }
	  }, {
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-colorpicker\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.hexPreview.getLayout());
	    }
	  }, {
	    key: "getPopupContent",
	    value: function getPopupContent() {
	      return main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-popup-container\">\n\t\t\t\t<div class=\"landing-ui-field-color-popup-head\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t\t<div class=\"landing-ui-field-color-popup-footer\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.recent.getLayout(), this.hex.getLayout(), this.spectrum.getLayout(), this.getSelectButton(), this.getCancelButton());
	    }
	  }, {
	    key: "getSelectButton",
	    value: function getSelectButton() {
	      return this.cache.remember('selectButton', function () {
	        return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button class=\"ui-btn ui-btn-xs ui-btn-primary\">\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-BUTTON_SELECT'));
	      });
	    }
	  }, {
	    key: "getCancelButton",
	    value: function getCancelButton() {
	      return this.cache.remember('cancelButton', function () {
	        return main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<button class=\"ui-btn ui-btn-xs ui-btn-light-border\">\n\t\t\t\t\t", "\n\t\t\t\t</button>\n\t\t\t"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-BUTTON_CANCEL'));
	      });
	    }
	  }, {
	    key: "getHexPreviewObject",
	    value: function getHexPreviewObject() {
	      return this.hexPreview;
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this2 = this;

	      return this.cache.remember('popup', function () {
	        return main_popup.PopupManager.create({
	          id: _this2.popupId,
	          className: 'landing-ui-field-color-spectrum-popup',
	          autoHide: true,
	          bindElement: _this2.hexPreview.getLayout(),
	          bindOptions: {
	            forceTop: true,
	            forceLeft: true
	          },
	          padding: 0,
	          contentPadding: 14,
	          width: 260,
	          offsetTop: -37,
	          offsetLeft: -180,
	          content: _this2.getPopupContent(),
	          closeByEsc: true,
	          targetContainer: _this2.popupTargetContainer
	        });
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this3 = this;

	      return this.cache.remember('value', function () {
	        return _this3.spectrum.getValue();
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Colorpicker.prototype), "setValue", this).call(this, value);
	      this.spectrum.setValue(value);
	      this.hex.setValue(value);
	      this.hexPreview.setValue(value);
	      this.setActivity(value);
	    }
	  }, {
	    key: "setActivity",
	    value: function setActivity(value) {
	      if (value !== null) {
	        if (this.spectrum.isActive()) {
	          this.hex.unsetActive();
	        } else {
	          this.hex.setActive();
	        }

	        this.hexPreview.setActive();
	      }
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      this.hex.unsetActive();
	      this.hexPreview.unsetActive();
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.hex.isActive() || this.hexPreview.isActive();
	    }
	  }]);
	  return Colorpicker;
	}(BaseControl);

	var _templateObject$6;

	var Primary = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Primary, _EventEmitter);

	  // todo: layout or control?
	  function Primary() {
	    var _this;

	    babelHelpers.classCallCheck(this, Primary);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Primary).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Primary');

	    main_core.Event.bind(_this.getLayout(), 'click', function () {
	      return _this.onClick();
	    });
	    return _this;
	  }

	  babelHelpers.createClass(Primary, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-primary\">\n\t\t\t\t\t<i class=\"landing-ui-field-color-primary-preview\"></i>\n\t\t\t\t\t<span class=\"landing-ui-field-color-primary-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</span>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-PRIMARY_TITLE'));
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.cache.remember('value', function () {
	        return new ColorValue(Primary.CSS_VAR);
	      });
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      this.setActive();
	      this.emit('onChange', {
	        color: this.getValue()
	      });
	    }
	  }, {
	    key: "setActive",
	    value: function setActive() {
	      main_core.Dom.addClass(this.getLayout(), Primary.ACTIVE_CLASS);
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      main_core.Dom.removeClass(this.getLayout(), Primary.ACTIVE_CLASS);
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return main_core.Dom.hasClass(this.getLayout(), Primary.ACTIVE_CLASS);
	    }
	  }, {
	    key: "isPrimaryValue",
	    value: function isPrimaryValue(value) {
	      return value !== null && this.getValue().getCssVar() === value.getCssVar();
	    }
	  }]);
	  return Primary;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(Primary, "ACTIVE_CLASS", 'active');
	babelHelpers.defineProperty(Primary, "CSS_VAR", '--primary');

	function regexpWoStartEnd(regexp) {
	  return new RegExp(regexpToString(regexp));
	}
	function regexpToString(regexp) {
	  return regexp.source.replace(/(^\^)|(\$$)/g, '');
	}

	var matcherGradient = /^(linear|radial)-gradient\(.*\)$/i;
	var matcherGradientAngle = /^(linear|radial)-gradient\(.*?((\d)+deg).*?\)$/ig;
	var hexMatcher = regexpToString(matcherHex);
	var matcherGradientColors = new RegExp('((rgba|hsla)?\\([\\d% .,]+\\)|transparent|' + hexMatcher + ')+', 'ig'); // todo: whooooouuuu, is so not-good
	// todo: add hex greaident match
	// todo: for tests
	// "linear-gradient(45deg, rgb(71, 155, 255) 0%, rgb(0, 207, 78) 100%)"
	// "linear-gradient(45deg, #123321 0%, #543asdbd 100%)"
	// "linear-gradient(rgb(71, 155, 255) 0%, rgb(0, 207, 78) 100%)"
	// "radial-gradient(circle farthest-side, rgb(34, 148, 215), rgb(39, 82, 150))"

	function isGradientString(rgbString) {
	  return !!rgbString.trim().match(matcherGradient);
	}

	var GradientValue = /*#__PURE__*/function () {
	  function GradientValue(value) {
	    babelHelpers.classCallCheck(this, GradientValue);
	    this.value = {
	      from: new ColorValue('#ffffff'),
	      to: new ColorValue(Primary.CSS_VAR),
	      angle: GradientValue.DEFAULT_ANGLE,
	      type: GradientValue.DEFAULT_TYPE
	    };
	    this.setValue(value);
	  }

	  babelHelpers.createClass(GradientValue, [{
	    key: "getName",
	    value: function getName() {
	      return this.value.from.getName() + '_' + this.value.to.getName() + '_' + this.getAngle() + '_' + this.getType();
	    } // todo: parse grad string?

	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (main_core.Type.isObject(value)) {
	        if (value instanceof GradientValue) {
	          this.value.from = new ColorValue(value.getFrom());
	          this.value.to = new ColorValue(value.getTo());
	          this.value.angle = value.getAngle();
	          this.value.type = value.getType();
	        } else {
	          if ('from' in value) {
	            this.value.from = new ColorValue(value.from);
	          }

	          if ('to' in value) {
	            this.value.to = new ColorValue(value.to);
	          }

	          if ('angle' in value) {
	            this.value.angle = main_core.Text.toNumber(value.angle);
	          }

	          if ('type' in value) {
	            this.value.type = value.type;
	          }
	        }
	      } else if (main_core.Type.isString(value) && isGradientString(value)) {
	        this.parseGradientString(value);
	      }

	      return this;
	    }
	  }, {
	    key: "setOpacity",
	    value: function setOpacity(opacity) {
	      this.value.from.setOpacity(opacity);
	      this.value.to.setOpacity(opacity);
	      return this;
	    }
	  }, {
	    key: "parseGradientString",
	    value: function parseGradientString(value) {
	      var typeMatches = value.trim().match(matcherGradient);

	      if (!!typeMatches) {
	        this.setValue({
	          type: typeMatches[1]
	        });
	      }

	      var angleMatches = value.trim().match(matcherGradientAngle);

	      if (!!angleMatches) {
	        this.setValue({
	          angle: angleMatches[2]
	        });
	      }

	      var colorMatches = value.trim().match(matcherGradientColors);

	      if (colorMatches && colorMatches.length > 0) {
	        this.setValue({
	          from: new ColorValue(colorMatches[0])
	        });
	        this.setValue({
	          to: new ColorValue(colorMatches[colorMatches.length - 1])
	        });
	      }
	    }
	  }, {
	    key: "getFrom",
	    value: function getFrom() {
	      return this.value.from;
	    }
	  }, {
	    key: "getTo",
	    value: function getTo() {
	      return this.value.to;
	    }
	  }, {
	    key: "getAngle",
	    value: function getAngle() {
	      return this.value.angle;
	    }
	  }, {
	    key: "setAngle",
	    value: function setAngle(angle) {
	      if (main_core.Type.isNumber(angle)) {
	        this.value.angle = Math.min(Math.max(angle, 0), 360);
	      }

	      return this;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.value.type;
	    }
	  }, {
	    key: "setType",
	    value: function setType(type) {
	      if (type === GradientValue.TYPE_RADIAL || type === GradientValue.TYPE_LINEAR) {
	        this.value.type = type;
	      }

	      return this;
	    }
	  }, {
	    key: "getOpacity",
	    value: function getOpacity() {
	      var _ref;

	      return (_ref = (this.value.from.getOpacity() + this.value.to.getOpacity()) / 2) !== null && _ref !== void 0 ? _ref : defaultColorValueOptions.a;
	    }
	  }, {
	    key: "getStyleString",
	    value: function getStyleString() {
	      var angle = this.value.angle;
	      var type = this.value.type;
	      var fromString = this.value.from.getStyleString();
	      var toString = this.value.to.getStyleString();
	      return type === 'linear' ? "linear-gradient(".concat(angle, "deg, ").concat(fromString, " 0%, ").concat(toString, " 100%)") : "radial-gradient(circle farthest-side at 50% 50%, ".concat(fromString, " 0%, ").concat(toString, " 100%)");
	    }
	  }, {
	    key: "getStyleStringForOpacity",
	    value: function getStyleStringForOpacity() {
	      return "radial-gradient(at top left, ".concat(this.value.from.getHex(), ", transparent)") + ", radial-gradient(at bottom left, ".concat(this.value.to.getHex(), ", transparent)");
	    }
	  }], [{
	    key: "compare",
	    value: function compare(value1, value2) {
	      var full = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : true;
	      var base = ColorValue.compare(value1.getFrom(), value2.getFrom()) && ColorValue.compare(value1.getTo(), value2.getTo()) || ColorValue.compare(value1.getTo(), value2.getFrom()) && ColorValue.compare(value1.getFrom(), value2.getTo());
	      var ext = full ? value1.getAngle() === value2.getAngle() && value1.getType() === value2.getType() : true;
	      return base && ext;
	    }
	  }]);
	  return GradientValue;
	}();

	babelHelpers.defineProperty(GradientValue, "TYPE_RADIAL", 'radial');
	babelHelpers.defineProperty(GradientValue, "TYPE_LINEAR", 'linear');
	babelHelpers.defineProperty(GradientValue, "DEFAULT_ANGLE", 180);
	babelHelpers.defineProperty(GradientValue, "DEFAULT_TYPE", 'linear');

	var defaultType = 'color';
	var gradientType = 'gradient';

	var Generator = /*#__PURE__*/function () {
	  function Generator() {
	    babelHelpers.classCallCheck(this, Generator);
	  }

	  babelHelpers.createClass(Generator, null, [{
	    key: "getDefaultPresets",
	    value: function getDefaultPresets() {
	      return Generator.cache.remember('default', function () {
	        var presets = [];
	        Generator.defaultPresets.forEach(function (preset) {
	          presets.push({
	            id: preset.id,
	            type: 'color',
	            items: preset.items.map(function (item) {
	              return new ColorValue(hexToHsl(item));
	            })
	          });
	        });
	        return presets;
	      });
	    }
	  }, {
	    key: "getPrimaryColorPreset",
	    value: function getPrimaryColorPreset() {
	      return this.cache.remember('primary', function () {
	        var preset = {
	          id: 'defaultPrimary',
	          items: []
	        };
	        var primary = new ColorValue(main_core.Dom.style(document.documentElement, '--primary').trim());
	        preset.items.push(new ColorValue(primary));

	        if (primary.getHsl().s <= 10) {
	          var lBeforeCount = primary.getHsl().l > 50 ? Math.ceil(primary.getHsl().l / 100 * 5) : Math.floor(primary.getHsl().l / 100 * 5);
	          var lAfterCount = 5 - lBeforeCount;
	          var deltaLBefore = primary.getHsl().l / (lBeforeCount + 1);
	          var deltaLAfter = (100 - primary.getHsl().l) / (lAfterCount + 1);

	          for (var i = 1; i <= lBeforeCount; i++) {
	            preset.items.push(new ColorValue(primary).darken(deltaLBefore * i));
	          }

	          for (var ii = 1; ii <= lAfterCount; ii++) {
	            preset.items.push(new ColorValue(primary).lighten(deltaLAfter * ii));
	          }

	          var deltaBitrixL = 15;
	          var deltaBitrixS = 15;
	          var bitrixColor = new ColorValue(Generator.BITRIX_COLOR);
	          preset.items[6] = new ColorValue(bitrixColor);
	          preset.items[7] = new ColorValue(bitrixColor.darken(deltaBitrixL).saturate(deltaBitrixS));
	          preset.items[8] = new ColorValue(bitrixColor.darken(deltaBitrixL).saturate(deltaBitrixS));
	          bitrixColor.lighten(deltaBitrixL * 2).desaturate(deltaBitrixS * 2);
	          preset.items[9] = new ColorValue(bitrixColor.lighten(deltaBitrixL).desaturate(deltaBitrixS));
	          preset.items[10] = new ColorValue(bitrixColor.lighten(deltaBitrixL).desaturate(deltaBitrixS));
	          bitrixColor.darken(deltaBitrixL * 2).saturate(deltaBitrixS * 2);
	          preset.items[11] = new ColorValue(bitrixColor).adjustHue(180);
	        } else {
	          var deltaL = (90 - primary.getHsl().l) / 3;
	          var deltaL2 = (primary.getHsl().l - 10) / 3;
	          var deltaS = (90 - primary.getHsl().s) / 3;
	          var deltaS2 = (primary.getHsl().s - 10) / 3;
	          preset.items[1] = new ColorValue(primary.darken(deltaL2).saturate(deltaS));
	          preset.items[2] = new ColorValue(primary.darken(deltaL2).saturate(deltaS));
	          preset.items[3] = new ColorValue(primary.darken(deltaL2).saturate(deltaS));
	          primary.lighten(deltaL2 * 3).desaturate(deltaS * 3);
	          preset.items[4] = new ColorValue(primary.desaturate(deltaS2).lighten(deltaL));
	          preset.items[5] = new ColorValue(primary.desaturate(deltaS2).lighten(deltaL));
	          preset.items[11] = new ColorValue(primary.desaturate(deltaS2).lighten(deltaL));
	          primary.saturate(deltaS2 * 3).darken(deltaL * 3);
	          preset.items[7] = new ColorValue(primary.adjustHue(40));
	          preset.items[8] = new ColorValue(primary.adjustHue(-80));
	          preset.items[9] = new ColorValue(primary.adjustHue(180));
	          preset.items[6] = new ColorValue(primary.adjustHue(40));
	          preset.items[10] = new ColorValue(primary.adjustHue(40));
	        }

	        return preset;
	      });
	    }
	  }, {
	    key: "getBlackAndWhitePreset",
	    value: function getBlackAndWhitePreset() {
	      return this.cache.remember('blackAndWhite', function () {
	        var preset = {
	          id: 'blackAndWhite',
	          items: []
	        };
	        var start = new ColorValue('#ffffff');
	        preset.items.push(new ColorValue(start));
	        preset.items.push(new ColorValue(start.darken(16.66)));
	        preset.items.push(new ColorValue(start.darken(16.66)));
	        preset.items.push(new ColorValue(start.darken(16.66)));
	        preset.items.push(new ColorValue(start.darken(16.66)));
	        preset.items.push(new ColorValue(start.darken(16.66)));
	        preset.items.push(new ColorValue(start.darken(16.7)));
	        return preset;
	      });
	    }
	  }, {
	    key: "getGradientByColorOptions",
	    value: function getGradientByColorOptions(options) {
	      var items = [];
	      var pairs = [[1, 2], [1, 4], [5, 12], [1, 8], [8, 9], [1, 9], [10, 7], [7, 11]];
	      pairs.forEach(function (pair) {
	        items.push(new GradientValue({
	          from: new ColorValue(options.items[pair[0] - 1]),
	          to: new ColorValue(options.items[pair[1] - 1]),
	          angle: GradientValue.DEFAULT_ANGLE,
	          type: GradientValue.DEFAULT_TYPE
	        }));
	      });
	      return {
	        type: gradientType,
	        items: items
	      };
	    }
	  }]);
	  return Generator;
	}();

	babelHelpers.defineProperty(Generator, "BITRIX_COLOR", '#2fc6f6');
	babelHelpers.defineProperty(Generator, "cache", new main_core.Cache.MemoryCache());
	babelHelpers.defineProperty(Generator, "defaultPresets", [{
	  id: 'agency',
	  items: ['#ff6366', '#40191a', '#803233', '#bf4b4d', '#e65a5c', '#ffc1c2', '#363643', '#57dca3', '#ee76ba', '#ffa864', '#eaeaec', '#fadbdc']
	}, {
	  id: 'accounting',
	  items: ['#a5c33c', '#384215', '#6f8228', '#8fa834', '#b0cf40', '#dae6ae', '#4c4c4c', '#5d84e6', '#cd506b', '#fe6466', '#dfdfdf', '#e9f0cf']
	}, {
	  id: 'app',
	  items: ['#4fd2c2', '#1f524c', '#379187', '#46b8aa', '#54dece', '#c8f1ec', '#6639b6', '#e81c62', '#9a69ca', '#6279d8', '#ffc337', '#e9faf8']
	}, {
	  id: 'architecture',
	  items: ['#c94645', '#4a1919', '#8a2f2f', '#b03c3c', '#d64949', '#eec3c3', '#363643', '#446d90', '#a13773', '#c98145', '#eaeaec', '#f9e8e7']
	}, {
	  id: 'business',
	  items: ['#3949a0', '#232c61', '#313e87', '#3e4fad', '#556ced', '#d8d7dc', '#14122c', '#1d1937', '#a03949', '#2f295a', '#c87014', '#f4f4f5']
	}, {
	  id: 'charity',
	  items: ['#f5f219', '#f58419', '#f5cc19', '#a8e32a', '#f9f76a', '#fcfbb6', '#000000', '#262e37', '#74797f', '#e569b1', '#edeef0', '#fefedf']
	}, {
	  id: 'construction',
	  items: ['#f7b70b', '#382a02', '#785905', '#b88907', '#dea509', '#fdf1d1', '#111111', '#a3a3a3', '#f7410b', '#f70b4b', '#d6dde9', '#fef9ea']
	}, {
	  id: 'consulting',
	  items: ['#21a79b', '#38afa5', '#14665f', '#1c8c83', '#30f2e2', '#a9ddd9', '#ec4672', '#58d400', '#f0ac00', '#2d6faf', '#2da721', '#e6f5f4']
	}, {
	  id: 'corporate',
	  items: ['#6ab8ee', '#31556e', '#4e86ad', '#5fa3d4', '#70c1fa', '#d2e9f8', '#36e2c0', '#ffaa3c', '#ee6a76', '#ffa468', '#5feb99', '#ebf4fb']
	}, {
	  id: 'courses',
	  items: ['#6bda95', '#2c593d', '#4b9969', '#5ebf83', '#70e69d', '#c2f0d3', '#31556e', '#ff947d', '#738ed3', '#f791ab', '#ffb67d', '#e2f8eb']
	}, {
	  id: 'event',
	  items: ['#f73859', '#380d14', '#781c2b', '#b82a42', '#de334f', '#fdbbc6', '#151726', '#ffb553', '#30d59b', '#b265e0', '#edeef0', '#ffeaed']
	}, {
	  id: 'gym',
	  items: ['#6b7de0', '#2f3661', '#4d5aa1', '#5f6fc7', '#7284ed', '#e4e8fa', '#333333', '#ffd367', '#a37fe8', '#e06b7d', '#6dc1e0', '#f4f6fd']
	}, {
	  id: 'lawyer',
	  items: ['#e74c3c', '#69231b', '#a8382c', '#cf4536', '#f55240', '#f9d0cb', '#4e4353', '#5a505e', '#e7863c', '#38a27f', '#e2e1e3', '#fdeeec']
	}, {
	  id: 'photography',
	  items: ['#f7a700', '#382600', '#785200', '#b87d00', '#de9800', '#fde8ba', '#333333', '#0b5aa0', '#e93d18', '#06c4ed', '#3672a8', '#fff6e3']
	}, {
	  id: 'restaurant',
	  items: ['#e6125d', '#660829', '#a60d43', '#cc1052', '#f21361', '#facfde', '#0eb88e', '#00946f', '#e04292', '#9b12e6', '#bfde00', '#fef2f6']
	}, {
	  id: 'shipping',
	  items: ['#ff0000', '#400000', '#800000', '#bf0000', '#e60000', '#ffb4b4', '#333333', '#ff822a', '#d63986', '#00ac6b', '#ffb800', '#fff3f3']
	}, {
	  id: 'spa',
	  items: ['#9dba04', '#313b01', '#667a02', '#86a103', '#a6c704', '#e4ecb9', '#ba7c04', '#cf54bb', '#049dba', '#1d7094', '#eead2f', '#f2f6dd']
	}, {
	  id: 'travel',
	  items: ['#ee4136', '#6e1f19', '#ad3128', '#d43c31', '#fa4639', '#fef1f0', '#31353e', '#3e434d', '#ee8036', '#428abc', '#eaebec', '#c3c4c7']
	}, {
	  id: 'wedding',
	  items: ['#d65779', '#572431', '#963e55', '#bd4d6b', '#e35d81', '#f7dfe5', '#af58a7', '#6bc34b', '#ec8c60', '#50a098', '#57b9d6', '#fdf4f6']
	}]);

	var _templateObject$7, _templateObject2$4;

	var Preset = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Preset, _EventEmitter);

	  function Preset(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Preset);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Preset).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Preset');

	    _this.id = options.id;
	    _this.type = options.type || defaultType;
	    _this.items = options.items;
	    _this.activeItem = null;
	    return _this;
	  }

	  babelHelpers.createClass(Preset, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getGradientPreset",
	    value: function getGradientPreset() {
	      var options = this.type === gradientType ? {
	        type: gradientType,
	        items: this.items
	      } : Generator.getGradientByColorOptions({
	        items: this.items
	      });
	      return new Preset(options);
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-preset\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.items.map(function (item) {
	          return _this2.getItemLayout(item.getName());
	        }));
	      });
	    }
	  }, {
	    key: "getItemLayout",
	    value: function getItemLayout(name) {
	      var _this3 = this;

	      return this.cache.remember(name, function () {
	        var color = _this3.getItemByName(name);

	        var style = main_core.Type.isString(color) ? color : color.getStyleString();
	        var item = main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div\n\t\t\t\t\tclass=\"landing-ui-field-color-preset-item\"\n\t\t\t\t\tstyle=\"background: ", "\"\n\t\t\t\t\tdata-name=\"", "\"\n\t\t\t\t></div>\n\t\t\t"])), style, name);
	        main_core.Event.bind(item, 'click', _this3.onItemClick.bind(_this3));
	        return item;
	      });
	    }
	  }, {
	    key: "getItemByName",
	    value: function getItemByName(name) {
	      return this.items.find(function (item) {
	        return name === item.getName();
	      }) || null;
	    }
	  }, {
	    key: "isPresetValue",
	    value: function isPresetValue(value) {
	      if (value === null) {
	        return false;
	      }

	      return this.items.some(function (item) {
	        if (item instanceof ColorValue && value instanceof ColorValue) {
	          return ColorValue.compare(item, value);
	        } else if (item instanceof GradientValue && value instanceof GradientValue) {
	          return GradientValue.compare(item, value, false);
	        }

	        return false;
	      });
	    }
	  }, {
	    key: "onItemClick",
	    value: function onItemClick(event) {
	      this.setActiveItem(event.currentTarget.dataset.name);
	      var value = null;

	      if (this.activeItem !== null) {
	        value = this.activeItem instanceof GradientValue ? new GradientValue(this.activeItem) : new ColorValue(this.activeItem);
	      }

	      this.emit('onChange', {
	        color: value
	      });
	    }
	  }, {
	    key: "setActiveItem",
	    value: function setActiveItem(name) {
	      var _this4 = this;

	      this.activeItem = this.getItemByName(name);
	      this.items.forEach(function (item) {
	        var itemName = item.getName();

	        if (name === itemName) {
	          main_core.Dom.addClass(_this4.getItemLayout(itemName), Preset.ACTIVE_CLASS);
	        } else {
	          main_core.Dom.removeClass(_this4.getItemLayout(itemName), Preset.ACTIVE_CLASS);
	        }
	      });
	    }
	  }, {
	    key: "setActiveValue",
	    value: function setActiveValue(value) {
	      if (value !== null) {
	        if (value instanceof GradientValue) {
	          this.setActiveItem(new GradientValue(value).setAngle(GradientValue.DEFAULT_ANGLE).setType(GradientValue.DEFAULT_TYPE).getName());
	        } else {
	          this.setActiveItem(value.getName());
	        }
	      }
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      var _this5 = this;

	      this.items.forEach(function (item) {
	        main_core.Dom.removeClass(_this5.getItemLayout(item.getName()), Preset.ACTIVE_CLASS);
	      });
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      var _this6 = this;

	      return this.items.some(function (item) {
	        return main_core.Dom.hasClass(_this6.getItemLayout(item.getName()), Preset.ACTIVE_CLASS);
	      });
	    }
	  }]);
	  return Preset;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(Preset, "ACTIVE_CLASS", 'active');

	var _templateObject$8, _templateObject2$5, _templateObject3$2, _templateObject4$2, _templateObject5$1;

	var PresetCollection = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(PresetCollection, _EventEmitter);

	  function PresetCollection(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, PresetCollection);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(PresetCollection).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.PresetCollection');

	    _this.popupId = 'presets-popup_' + main_core.Text.getRandom();
	    _this.popupTargetContainer = options.contentRoot;
	    _this.presets = {};
	    _this.onPresetClick = _this.onPresetClick.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(_this.getOpenButton(), 'click', function () {
	      _this.getPopup().toggle();
	    });
	    return _this;
	  }

	  babelHelpers.createClass(PresetCollection, [{
	    key: "addDefaultPresets",
	    value: function addDefaultPresets() {
	      var _this2 = this;

	      this.addPreset(Generator.getPrimaryColorPreset());
	      Generator.getDefaultPresets().map(function (item) {
	        _this2.addPreset(item);
	      });
	    }
	  }, {
	    key: "addPreset",
	    value: function addPreset(options) {
	      this.cache.delete('popupLayout');

	      if (!Object.keys(this.presets).length || !(options.id in this.presets)) {
	        this.presets[options.id] = options;
	      }
	    }
	  }, {
	    key: "getActivePreset",
	    value: function getActivePreset() {
	      return this.getActiveId() ? this.getPresetById(this.getActiveId()) : null;
	    }
	  }, {
	    key: "getDefaultPreset",
	    value: function getDefaultPreset() {
	      return Object.keys(this.presets).length ? this.getPresetById(Object.keys(this.presets)[0]) : null;
	    }
	  }, {
	    key: "getActiveId",
	    value: function getActiveId() {
	      return PresetCollection.activeId;
	    }
	  }, {
	    key: "getPresetById",
	    value: function getPresetById(id) {
	      var _this3 = this;

	      if (id in this.presets) {
	        return this.cache.remember(id, function () {
	          return new Preset(_this3.presets[id]);
	        });
	      } else {
	        return null;
	      }
	    }
	  }, {
	    key: "getPresetByItemValue",
	    value: function getPresetByItemValue(value) {
	      if (value === null) {
	        return null;
	      }

	      for (var _id in this.presets) {
	        if (this.getPresetById(_id) && this.getPresetById(_id).isPresetValue(value)) {
	          return this.getPresetById(_id);
	        }
	      }

	      return null;
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this4 = this;

	      return this.cache.remember('value', function () {
	        return main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-presets\">\n\t\t\t\t\t<div class=\"landing-ui-field-color-presets-left\">\n\t\t\t\t\t\t<span class=\"landing-ui-field-color-presets-title\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</span>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"landing-ui-field-color-presets-right\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-PRESETS_TITLE'), _this4.getOpenButton());
	      });
	    }
	  }, {
	    key: "getOpenButton",
	    value: function getOpenButton() {
	      return this.cache.remember('openButton', function () {
	        return main_core.Tag.render(_templateObject2$5 || (_templateObject2$5 = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-field-color-presets-open\">\n\t\t\t\t", "\n\t\t\t</span>"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-PRESETS_MORE'));
	      });
	    }
	  }, {
	    key: "getTitleContainer",
	    value: function getTitleContainer() {
	      var _this5 = this;

	      return this.cache.remember('titleContainer', function () {
	        return _this5.getLayout().querySelector('.landing-ui-field-color-presets-left');
	      });
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this6 = this;

	      // todo: bind to event target? or need button
	      return this.cache.remember('popup', function () {
	        return main_popup.PopupManager.create({
	          id: _this6.popupId,
	          className: 'presets-popup',
	          autoHide: true,
	          bindElement: _this6.getOpenButton(),
	          bindOptions: {
	            forceTop: true,
	            forceLeft: true
	          },
	          width: 280,
	          offsetLeft: -200,
	          content: _this6.getPopupLayout(),
	          closeByEsc: true,
	          targetContainer: _this6.popupTargetContainer
	        });
	      });
	    }
	  }, {
	    key: "getPopupLayout",
	    value: function getPopupLayout() {
	      var _this7 = this;

	      return this.cache.remember('popupLayout', function () {
	        var layouts = main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-presets-popup\">\n\t\t\t\t<div class=\"landing-ui-field-color-presets-popup-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-field-color-presets-popup-inner\"></div>\n\t\t\t</div>"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-PRESETS_MORE_COLORS'));
	        var innerLayouts = layouts.querySelector('.landing-ui-field-color-presets-popup-inner');

	        for (var presetId in _this7.presets) {
	          var layout = _this7.getPresetLayout(presetId);

	          if (presetId === _this7.getActiveId()) {
	            main_core.Dom.addClass(layout, PresetCollection.ACTIVE_CLASS);
	          }

	          main_core.Event.bind(layout, 'click', _this7.onPresetClick);
	          main_core.Dom.append(layout, innerLayouts);
	        }

	        return layouts;
	      });
	    }
	  }, {
	    key: "getPresetLayout",
	    value: function getPresetLayout(presetId) {
	      var _this8 = this;

	      return this.cache.remember(presetId + 'layout', function () {
	        return main_core.Tag.render(_templateObject4$2 || (_templateObject4$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-presets-preset\" data-id=\"", "\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), presetId, _this8.presets[presetId].items.map(function (item) {
	          return main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["<div\n\t\t\t\t\t\t\t\tclass=\"landing-ui-field-color-presets-preset-item\"\n\t\t\t\t\t\t\t\tstyle=\"background: ", "\"\n\t\t\t\t\t\t\t></div>"])), main_core.Type.isString(item) ? item : item.getStyleString());
	        }));
	      });
	    }
	  }, {
	    key: "onPresetClick",
	    value: function onPresetClick(event) {
	      this.getPopup().close();
	      this.setActiveItem(event.currentTarget.dataset.id);
	      this.emit('onChange', {
	        preset: this.getActivePreset()
	      });
	    }
	  }, {
	    key: "setActiveItem",
	    value: function setActiveItem(presetId) {
	      PresetCollection.activeId = presetId;

	      for (var _id2 in this.presets) {
	        main_core.Dom.removeClass(this.getPresetLayout(_id2), PresetCollection.ACTIVE_CLASS);

	        if (_id2 === presetId) {
	          main_core.Dom.addClass(this.getPresetLayout(_id2), PresetCollection.ACTIVE_CLASS);
	        }
	      }
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      for (var presetId in this.presets) {
	        main_core.Dom.removeClass(this.getPresetLayout(presetId), PresetCollection.ACTIVE_CLASS);
	      }
	    }
	  }]);
	  return PresetCollection;
	}(main_core_events.EventEmitter);

	babelHelpers.defineProperty(PresetCollection, "activeId", null);
	babelHelpers.defineProperty(PresetCollection, "ACTIVE_CLASS", 'active');

	var _templateObject$9;

	var Reset = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Reset, _EventEmitter);

	  function Reset(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Reset);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Reset).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Reset');

	    main_core.Event.bind(_this.getLayout(), 'click', function () {
	      return _this.onClick();
	    });
	    var hint = BX.UI.Hint.createInstance({
	      popupParameters: {
	        targetContainer: options.contentRoot,
	        padding: 0
	      }
	    });
	    hint.init(_this.getLayout());
	    return _this;
	  }

	  babelHelpers.createClass(Reset, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$9 || (_templateObject$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-reset-container\">\n\t\t\t\t\t<div class=\"landing-ui-field-color-reset\"\n\t\t\t\t\t\tdata-hint=\"", "\"\n\t\t\t\t\t\tdata-hint-no-icon\n\t\t\t\t\t>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-RESET_HINT'));
	      });
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      this.emit('onReset');
	    }
	  }]);
	  return Reset;
	}(main_core_events.EventEmitter);

	var _templateObject$a;

	var Zeroing = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Zeroing, _EventEmitter);

	  function Zeroing() {
	    var _this;

	    babelHelpers.classCallCheck(this, Zeroing);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Zeroing).call(this));
	    _this.cache = new main_core.Cache.MemoryCache();

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Transparent');

	    main_core.Event.bind(_this.getLayout(), 'click', function () {
	      return _this.onClick();
	    });
	    return _this;
	  }

	  babelHelpers.createClass(Zeroing, [{
	    key: "getLayout",
	    value: function getLayout() {
	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject$a || (_templateObject$a = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-preset-item landing-ui-field-color-transparent\"></div>"])));
	      });
	    }
	  }, {
	    key: "onClick",
	    value: function onClick() {
	      this.emit('onChange', {
	        color: null
	      });
	    }
	  }]);
	  return Zeroing;
	}(main_core_events.EventEmitter);

	var _templateObject$b, _templateObject2$6;

	var ColorSet = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(ColorSet, _BaseControl);

	  function ColorSet(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ColorSet);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ColorSet).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.ColorSet');

	    _this.reset = new Reset(options);

	    _this.reset.subscribe('onReset', function () {
	      _this.emit('onReset');
	    });

	    _this.zeroing = new Zeroing();

	    _this.zeroing.subscribe('onChange', function (event) {
	      _this.unsetActive();

	      _this.setValue(event.getData().color); // todo: need reload computed props and reinit


	      _this.onChange(event);
	    });

	    _this.blackAndWhitePreset = new Preset(Generator.getBlackAndWhitePreset());

	    _this.blackAndWhitePreset.subscribe('onChange', function (event) {
	      _this.preset.unsetActive();

	      _this.onPresetItemChange(event);
	    });

	    _this.colorpicker = new Colorpicker(options);

	    _this.colorpicker.subscribe('onChange', function (event) {
	      _this.preset.unsetActive();

	      _this.blackAndWhitePreset.unsetActive();

	      _this.onChange(event);
	    });

	    _this.presets = new PresetCollection(options);

	    _this.presets.subscribe('onChange', function (event) {
	      _this.setPreset(event.getData().preset);
	    });

	    _this.presets.addDefaultPresets();

	    var preset = _this.presets.getActivePreset() || _this.presets.getDefaultPreset();

	    if (preset) {
	      _this.setPreset(preset); // todo: what if not preset?

	    }

	    return _this;
	  }

	  babelHelpers.createClass(ColorSet, [{
	    key: "buildLayout",
	    value: function buildLayout() {
	      main_core.Dom.append(this.reset.getLayout(), this.presets.getTitleContainer());
	      main_core.Dom.prepend(this.zeroing.getLayout(), this.blackAndWhitePreset.getLayout());
	      return main_core.Tag.render(_templateObject$b || (_templateObject$b = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-colorset\">\n\t\t\t\t<div class=\"landing-ui-field-color-colorset-top\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t\t<div class=\"landing-ui-field-color-colorset-bottom\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.presets.getLayout(), this.getPresetContainer(), this.blackAndWhitePreset.getLayout(), this.colorpicker.getLayout());
	    }
	  }, {
	    key: "getTitleLayout",
	    value: function getTitleLayout() {
	      var _this2 = this;

	      return this.cache.remember('titleLayout', function () {
	        return _this2.getLayout().querySelector('.landing-ui-field-color-colorset-title');
	      });
	    }
	  }, {
	    key: "getPresetContainer",
	    value: function getPresetContainer() {
	      return this.cache.remember('presetContainer', function () {
	        return main_core.Tag.render(_templateObject2$6 || (_templateObject2$6 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-colorset-preset-container\"></div>"])));
	      });
	    }
	  }, {
	    key: "setPreset",
	    value: function setPreset(preset) {
	      var _this3 = this;

	      this.preset = preset;
	      this.preset.unsetActive();

	      if (this.getValue() !== null && this.preset.isPresetValue(this.getValue())) {
	        this.unsetActive();
	        this.preset.setActiveValue(this.getValue());
	      } else {
	        this.unsetActive();
	        this.colorpicker.setValue(this.getValue());
	      }

	      this.preset.subscribe('onChange', function (event) {
	        _this3.blackAndWhitePreset.unsetActive();

	        _this3.onPresetItemChange(event);
	      });
	      main_core.Dom.clean(this.getPresetContainer());
	      main_core.Dom.append(preset.getLayout(), this.getPresetContainer());
	      this.emit('onPresetChange', {
	        preset: preset
	      });
	    }
	  }, {
	    key: "getPreset",
	    value: function getPreset() {
	      return this.preset;
	    }
	  }, {
	    key: "onPresetItemChange",
	    value: function onPresetItemChange(event) {
	      this.colorpicker.setValue(event.getData().color);
	      this.colorpicker.unsetActive();
	      this.onChange(event);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.cache.set('value', event.getData().color);
	      this.emit('onChange', event);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this4 = this;

	      return this.cache.remember('value', function () {
	        return _this4.colorpicker.getValue();
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(ColorSet.prototype), "setValue", this).call(this, value);
	      this.colorpicker.setValue(value);
	      var activePreset = this.presets.getActiveId() ? this.presets.getPresetById(this.presets.getActiveId()) : this.presets.getPresetByItemValue(value);

	      if (activePreset !== null) {
	        this.setPreset(activePreset);
	        this.presets.setActiveItem(activePreset.getId());
	      }

	      if (value !== null && this.blackAndWhitePreset.isPresetValue(value)) {
	        this.unsetActive();
	        this.blackAndWhitePreset.setActiveValue(value);
	      }
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      this.preset.unsetActive();
	      this.blackAndWhitePreset.unsetActive();
	      this.colorpicker.unsetActive();
	    }
	  }, {
	    key: "isActive",
	    value: function isActive() {
	      return this.preset.isActive() || this.blackAndWhitePreset.isActive() || this.colorpicker.isActive();
	    }
	  }]);
	  return ColorSet;
	}(BaseControl);

	var _templateObject$c, _templateObject2$7, _templateObject3$3;

	var Opacity = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(Opacity, _BaseControl);

	  function Opacity() {
	    var _this;

	    babelHelpers.classCallCheck(this, Opacity);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Opacity).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Opacity');

	    _this.document = landing_pageobject.PageObject.getRootWindow().document;
	    _this.onPickerDragStart = _this.onPickerDragStart.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPickerDragMove = _this.onPickerDragMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPickerDragEnd = _this.onPickerDragEnd.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(_this.getLayout(), 'mousedown', _this.onPickerDragStart);
	    return _this;
	  }

	  babelHelpers.createClass(Opacity, [{
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$c || (_templateObject$c = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-opacity\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getPicker(), this.getColorLayout());
	    }
	  }, {
	    key: "onPickerDragStart",
	    value: function onPickerDragStart(event) {
	      if (event.ctrlKey || event.metaKey || event.button) {
	        return;
	      }

	      main_core.Event.bind(this.document, 'mousemove', this.onPickerDragMove);
	      main_core.Event.bind(this.document, 'mouseup', this.onPickerDragEnd);
	      main_core.Dom.addClass(this.document.body, 'landing-ui-field-color-draggable');
	      this.onPickerDragMove(event);
	    }
	  }, {
	    key: "onPickerDragMove",
	    value: function onPickerDragMove(event) {
	      if (event.target === this.getPicker()) {
	        return;
	      }

	      this.setPickerPos(event.pageX);
	      this.onChange();
	    }
	  }, {
	    key: "onPickerDragEnd",
	    value: function onPickerDragEnd() {
	      main_core.Event.unbind(this.document, 'mousemove', this.onPickerDragMove);
	      main_core.Event.unbind(this.document, 'mouseup', this.onPickerDragEnd);
	      main_core.Dom.removeClass(this.document.body, 'landing-ui-field-color-draggable');
	    }
	    /**
	     * Set picker by absolute page coords
	     * @param x
	     */

	  }, {
	    key: "setPickerPos",
	    value: function setPickerPos(x) {
	      var leftPos = Math.max(Math.min(x - this.getLayoutRect().left, this.getLayoutRect().width), 0);
	      main_core.Dom.style(this.getPicker(), {
	        left: "".concat(leftPos, "px")
	      });
	    }
	  }, {
	    key: "getLayoutRect",
	    value: function getLayoutRect() {
	      var _this2 = this;

	      return this.cache.remember('layoutSize', function () {
	        var layoutRect = _this2.getLayout().getBoundingClientRect();

	        return {
	          width: layoutRect.width,
	          left: layoutRect.left
	        };
	      });
	    }
	  }, {
	    key: "getColorLayout",
	    value: function getColorLayout() {
	      return this.cache.remember('colorLayout', function () {
	        return main_core.Tag.render(_templateObject2$7 || (_templateObject2$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-opacity-color\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getPicker",
	    value: function getPicker() {
	      return this.cache.remember('picker', function () {
	        return main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-opacity-picker\">\n\t\t\t\t\t<div class=\"landing-ui-field-color-opacity-picker-item\">\n\t\t\t\t\t\t<div class=\"landing-ui-field-color-opacity-picker-item-circle\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>"])));
	      });
	    }
	  }, {
	    key: "getDefaultValue",
	    value: function getDefaultValue() {
	      return this.cache.remember('default', function () {
	        return new ColorValue(Opacity.DEFAULT_COLOR);
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this3 = this;

	      return this.cache.remember('value', function () {
	        var pickerLeft = main_core.Text.toNumber(main_core.Dom.style(_this3.getPicker(), 'left'));
	        var layoutWidth = main_core.Text.toNumber(_this3.getLayout().getBoundingClientRect().width);
	        return _this3.getDefaultValue().setOpacity(1 - pickerLeft / layoutWidth);
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var valueToSet = value !== null ? value : this.getDefaultValue();
	      babelHelpers.get(babelHelpers.getPrototypeOf(Opacity.prototype), "setValue", this).call(this, valueToSet);
	      main_core.Dom.style(this.getColorLayout(), {
	        background: valueToSet.getStyleStringForOpacity()
	      });
	      main_core.Dom.style(this.getPicker(), {
	        left: "".concat(100 - valueToSet.getOpacity() * 100, "%")
	      });
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.cache.delete('value');
	      this.emit('onChange', {
	        color: this.getValue()
	      });
	    }
	  }]);
	  return Opacity;
	}(BaseControl);

	babelHelpers.defineProperty(Opacity, "DEFAULT_COLOR", '#cccccc');
	babelHelpers.defineProperty(Opacity, "PICKER_WIDTH", '#cccccc');

	var _templateObject$d, _templateObject2$8, _templateObject3$4, _templateObject4$3, _templateObject5$2, _templateObject6;

	var Tabs = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Tabs, _EventEmitter);

	  function Tabs() {
	    var _this;

	    babelHelpers.classCallCheck(this, Tabs);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tabs).call(this));
	    _this.tabs = [];
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.multiple = true;
	    _this.isBig = false;
	    _this.onToggle = _this.onToggle.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(Tabs, [{
	    key: "setMultiple",
	    value: function setMultiple(multiple) {
	      this.multiple = multiple;
	      return this;
	    }
	  }, {
	    key: "setBig",
	    value: function setBig(big) {
	      this.isBig = big;
	      this.multiple = false;
	      return this;
	    }
	  }, {
	    key: "appendTab",
	    value: function appendTab(id, title, items) {
	      var tab = new Tab({
	        id: id,
	        title: title,
	        items: main_core.Type.isArray(items) ? items : [items]
	      });
	      this.tabs.push(tab);
	      this.bindEvents(tab);
	      this.cache.delete('layout');
	      return this;
	    }
	  }, {
	    key: "prependTab",
	    value: function prependTab(id, title, items) {
	      var tab = new Tab({
	        id: id,
	        title: title,
	        items: main_core.Type.isArray(items) || [items]
	      });
	      this.tabs.unshift(tab);
	      this.bindEvents(tab);
	      this.cache.delete('layout');
	      return this;
	    }
	  }, {
	    key: "bindEvents",
	    value: function bindEvents(tab) {
	      tab.subscribe('onToggle', this.onToggle);
	      tab.subscribe('onShow', this.onToggle);
	      tab.subscribe('onHide', this.onToggle);
	    }
	  }, {
	    key: "onToggle",
	    value: function onToggle(event) {
	      this.emit('onToggle', event);
	    }
	  }, {
	    key: "showTab",
	    value: function showTab(id) {
	      if (!this.multiple) {
	        this.tabs.forEach(function (tab) {
	          tab.hide();
	        });
	      }

	      var tab = this.getTabById(id);

	      if (tab) {
	        tab.show();
	      }

	      return this;
	    }
	  }, {
	    key: "getTabById",
	    value: function getTabById(id) {
	      return this.tabs.find(function (tab) {
	        return tab.id === id;
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        var additional = _this2.isBig ? ' landing-ui-field-color-tabs--big' : '';
	        var layout = main_core.Tag.render(_templateObject$d || (_templateObject$d = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-tabs", "\"></div>"])), additional);

	        if (_this2.isBig) {
	          var head = main_core.Tag.render(_templateObject2$8 || (_templateObject2$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"landing-ui-field-color-tabs-head landing-ui-field-color-tabs-head--big\"></div>\n\t\t\t\t"])));
	          var content = main_core.Tag.render(_templateObject3$4 || (_templateObject3$4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t\t<div class=\"landing-ui-field-color-tabs-content landing-ui-field-color-tabs-content--big\"></div>\n\t\t\t\t"])));

	          _this2.tabs.forEach(function (tab) {
	            main_core.Dom.append(tab.getTitle(), head);
	            main_core.Dom.append(tab.getLayout(), content);
	          });

	          main_core.Dom.append(head, layout);
	          main_core.Dom.append(content, layout);
	        } else {
	          _this2.tabs.forEach(function (tab) {
	            var tabLayout = main_core.Tag.render(_templateObject4$3 || (_templateObject4$3 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-tabs-tab\">\n\t\t\t\t\t\t", "", "\n\t\t\t\t\t</div>"])), tab.getTitle(), tab.getLayout());
	            main_core.Dom.append(tabLayout, layout);
	          });
	        } // events


	        _this2.tabs.forEach(function (tab) {
	          main_core.Event.bind(tab.getTitle(), 'click', function () {
	            if (!_this2.multiple) {
	              _this2.tabs.forEach(function (tab) {
	                tab.hide();
	              });
	            }

	            tab.toggle();
	          });
	        });

	        return layout;
	      });
	    }
	  }]);
	  return Tabs;
	}(main_core_events.EventEmitter);
	var Tab = /*#__PURE__*/function (_EventEmitter2) {
	  babelHelpers.inherits(Tab, _EventEmitter2);

	  function Tab(options) {
	    var _this3;

	    babelHelpers.classCallCheck(this, Tab);
	    _this3 = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Tab).call(this));
	    _this3.id = options.id;
	    _this3.title = options.title;
	    _this3.items = options.items;
	    _this3.cache = new main_core.Cache.MemoryCache();
	    return _this3;
	  }

	  babelHelpers.createClass(Tab, [{
	    key: "getId",
	    value: function getId() {
	      return this.id;
	    }
	  }, {
	    key: "getTitle",
	    value: function getTitle() {
	      var _this4 = this;

	      return this.cache.remember('title', function () {
	        return main_core.Tag.render(_templateObject5$2 || (_templateObject5$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"landing-ui-field-color-tabs-tab-toggler\">\n\t\t\t\t\t<span class=\"landing-ui-field-color-tabs-tab-toggler-icon\"></span>\n\t\t\t\t\t<span class=\"landing-ui-field-color-tabs-tab-toggler-name\">", "</span>\n\t\t\t\t</span>\n\t\t\t"])), _this4.title);
	      });
	    }
	  }, {
	    key: "getLayout",
	    value: function getLayout() {
	      var _this5 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-tabs-tab-content\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this5.items.map(function (item) {
	          return item.getLayout();
	        }));
	      });
	    }
	  }, {
	    key: "toggle",
	    value: function toggle() {
	      main_core.Dom.toggleClass(this.getLayout(), Tab.SHOW_CLASS);
	      main_core.Dom.toggleClass(this.getTitle(), Tab.SHOW_CLASS);
	      this.emit('onToggle', {
	        tab: this.title
	      });
	      return this;
	    }
	  }, {
	    key: "show",
	    value: function show() {
	      main_core.Dom.addClass(this.getLayout(), Tab.SHOW_CLASS);
	      main_core.Dom.addClass(this.getTitle(), Tab.SHOW_CLASS);
	      this.emit('onShow', {
	        tab: this.title
	      });
	      return this;
	    }
	  }, {
	    key: "hide",
	    value: function hide() {
	      main_core.Dom.removeClass(this.getLayout(), Tab.SHOW_CLASS);
	      main_core.Dom.removeClass(this.getTitle(), Tab.SHOW_CLASS);
	      this.emit('onHide', {
	        tab: this.title
	      });
	      return this;
	    }
	  }]);
	  return Tab;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(Tab, "SHOW_CLASS", 'show');

	var _templateObject$e;

	var Color = /*#__PURE__*/function (_BaseProcessor) {
	  babelHelpers.inherits(Color, _BaseProcessor);

	  function Color(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Color);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Color).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.Color');

	    _this.property = 'color';
	    _this.variableName = '--color';
	    _this.className = 'g-color';
	    _this.colorSet = new ColorSet(options);

	    _this.colorSet.subscribe('onChange', _this.onColorSetChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.colorSet.subscribe('onReset', _this.onReset.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.opacity = new Opacity();

	    _this.opacity.subscribe('onChange', _this.onOpacityChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.primary = new Primary();

	    _this.primary.subscribe('onChange', _this.onPrimaryChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.tabs = new Tabs().appendTab('Opacity', main_core.Loc.getMessage('LANDING_FIELD_COLOR-TAB_OPACITY'), _this.opacity);
	    return _this;
	  }

	  babelHelpers.createClass(Color, [{
	    key: "isNullValue",
	    value: function isNullValue(value) {
	      // todo: check different browsers
	      return value === null || value === 'none' || value === 'rgba(0, 0, 0, 0)';
	    }
	  }, {
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$e || (_templateObject$e = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-color\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.colorSet.getLayout(), this.primary.getLayout(), this.tabs.getLayout());
	    }
	  }, {
	    key: "onColorSetChange",
	    value: function onColorSetChange(event) {
	      this.primary.unsetActive();
	      var color = event.getData().color;

	      if (color !== null) {
	        color.setOpacity(this.opacity.getValue().getOpacity());
	        this.opacity.setValue(color);
	      }

	      this.onChange();
	    }
	  }, {
	    key: "onOpacityChange",
	    value: function onOpacityChange() {
	      this.onChange();
	    }
	  }, {
	    key: "onPrimaryChange",
	    value: function onPrimaryChange(event) {
	      this.colorSet.setValue(event.getData().color);
	      this.onColorSetChange(event);
	      this.colorSet.unsetActive();
	      this.primary.setActive();
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      this.colorSet.unsetActive();
	      this.primary.unsetActive();
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var valueObj = value !== null ? new ColorValue(value) : null;
	      this.colorSet.setValue(valueObj);
	      this.opacity.setValue(valueObj); // todo: what about opacity in primary?

	      if (this.primary.isPrimaryValue(valueObj)) {
	        this.primary.setActive();
	        this.colorSet.unsetActive();
	      }

	      if (value !== null && valueObj.getOpacity() < 1) {
	        this.tabs.showTab('Opacity');
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this2 = this;

	      return this.cache.remember('value', function () {
	        var value = _this2.primary.isActive() ? _this2.primary.getValue() : _this2.colorSet.getValue();
	        return value === null ? null : value.setOpacity(_this2.opacity.getValue().getOpacity());
	      });
	    }
	  }]);
	  return Color;
	}(BaseProcessor);

	babelHelpers.defineProperty(Color, "PRIMARY_VAR", 'var(--primary)');

	var ColorHover = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(ColorHover, _Color);

	  function ColorHover(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ColorHover);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ColorHover).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.ColorHover');

	    _this.property = 'color';
	    _this.variableName = '--color-hover';
	    _this.className = 'g-color--hover';
	    _this.pseudoClass = ':hover';
	    return _this;
	  }

	  return ColorHover;
	}(Color);

	var _templateObject$f, _templateObject2$9, _templateObject3$5, _templateObject4$4, _templateObject5$3, _templateObject6$1, _templateObject7;

	var Gradient = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(Gradient, _BaseControl);

	  function Gradient(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Gradient);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Gradient).call(this));
	    babelHelpers.defineProperty(babelHelpers.assertThisInitialized(_this), "ROTATE_STEP", 45);

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Gradient');

	    _this.popupId = 'gradient_popup_' + main_core.Text.getRandom();
	    _this.popupTargetContainer = options.contentRoot;
	    _this.colorpickerFrom = new Colorpicker(options);

	    _this.colorpickerFrom.subscribe('onChange', function (event) {
	      _this.onColorChange(event.getData().color, null);
	    });

	    _this.colorpickerTo = new Colorpicker(options);

	    _this.colorpickerTo.subscribe('onChange', function (event) {
	      _this.onColorChange(null, event.getData().color);
	    });

	    main_core.Event.bind(_this.getPopupButton(), 'click', _this.onPopupOpen.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Event.bind(_this.getRotateButton(), 'click', _this.onRotate.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Event.bind(_this.getSwitchTypeButton(), 'click', _this.onSwitchType.bind(babelHelpers.assertThisInitialized(_this)));
	    main_core.Event.bind(_this.getSwapButton(), 'click', _this.onSwap.bind(babelHelpers.assertThisInitialized(_this)));
	    _this.preset = null;
	    return _this;
	  }

	  babelHelpers.createClass(Gradient, [{
	    key: "onColorChange",
	    value: function onColorChange(fromValue, toValue) {
	      if (fromValue === null && toValue === null) {
	        return;
	      }

	      var valueToSet = this.getValue() || new GradientValue();
	      var fromValueToSet = fromValue || valueToSet.getFrom() || new GradientValue().getFrom();
	      var toValueToSet = toValue || valueToSet.getTo() || new GradientValue().getTo();
	      valueToSet.setValue({
	        from: fromValueToSet,
	        to: toValueToSet
	      });
	      this.setValue(valueToSet);
	      this.preset.unsetActive();
	      this.onChange();
	    }
	  }, {
	    key: "onPopupOpen",
	    value: function onPopupOpen() {
	      this.getPopup().toggle();
	    }
	  }, {
	    key: "onRotate",
	    value: function onRotate(event) {
	      // todo: not set colorpicker active
	      if (!Gradient.isButtonEnable(event.target)) {
	        return;
	      }

	      var value = this.getValue();

	      if (value !== null) {
	        value.setValue({
	          angle: (value.getAngle() + this.ROTATE_STEP) % 360
	        });
	        this.setValue(value);
	        this.onChange();
	      }

	      this.getPopup().close();
	    }
	  }, {
	    key: "onSwitchType",
	    value: function onSwitchType(event) {
	      // todo: not set colorpicker active
	      if (!Gradient.isButtonEnable(event.target)) {
	        return;
	      }

	      var value = this.getValue();

	      if (value !== null) {
	        if (value.getType() === GradientValue.TYPE_LINEAR) {
	          value.setValue({
	            type: GradientValue.TYPE_RADIAL
	          });
	          Gradient.disableButton(this.getRotateButton());
	        } else {
	          value.setValue({
	            type: GradientValue.TYPE_LINEAR
	          });
	          Gradient.enableButton(this.getRotateButton());
	        }

	        this.setValue(value);
	        this.onChange();
	      }

	      this.getPopup().close();
	    }
	  }, {
	    key: "onSwap",
	    value: function onSwap(event) {
	      // todo: not set colorpicker active
	      if (!Gradient.isButtonEnable(event.target)) {
	        return;
	      }

	      var value = this.getValue();

	      if (value !== null) {
	        value.setValue({
	          to: value.getFrom(),
	          from: value.getTo()
	        });
	        this.setValue(value);
	        this.onChange();
	      }

	      this.getPopup().close();
	    }
	  }, {
	    key: "correctColorpickerColors",
	    value: function correctColorpickerColors() {
	      var value = this.getValue();

	      if (value !== null) {
	        var angle = value.getAngle();
	        var hexFrom = this.colorpickerFrom.getHexPreviewObject();
	        var hexTo = this.colorpickerTo.getHexPreviewObject();
	        var colorFrom = value.getFrom();
	        var colorTo = value.getTo();

	        if (value.getType() === GradientValue.TYPE_LINEAR) {
	          if (angle === 270 || angle === 90) {
	            var median = ColorValue.getMedian(colorFrom, colorTo).getContrast().getHex();
	            hexFrom.adjustColors(median, 'transparent');
	            hexTo.adjustColors(median, 'transparent');
	          } else if (angle >= 135 && angle <= 225) {
	            hexFrom.adjustColors(colorFrom.getContrast().getHex(), 'transparent');
	            hexTo.adjustColors(colorTo.getContrast().getHex(), 'transparent');
	          } else {
	            hexFrom.adjustColors(colorTo.getContrast().getHex(), 'transparent');
	            hexTo.adjustColors(colorFrom.getContrast().getHex(), 'transparent');
	          }
	        } else if (value.getType() === GradientValue.TYPE_RADIAL) {
	          hexFrom.adjustColors(colorTo.getContrast().getHex(), 'transparent');
	          hexTo.adjustColors(colorTo.getContrast().getHex(), 'transparent');
	        }
	      }
	    }
	  }, {
	    key: "getPopup",
	    value: function getPopup() {
	      var _this2 = this;

	      return this.cache.remember('popup', function () {
	        return main_popup.PopupManager.create({
	          id: _this2.popupId,
	          className: 'landing-ui-field-color-gradient-preset-popup',
	          autoHide: true,
	          bindElement: _this2.getPopupButton(),
	          bindOptions: {
	            forceTop: true,
	            forceLeft: true
	          },
	          offsetLeft: 15,
	          angle: {
	            offset: -5
	          },
	          padding: 0,
	          contentPadding: 7,
	          content: _this2.getPopupContent(),
	          closeByEsc: true,
	          targetContainer: _this2.popupTargetContainer
	        });
	      });
	    }
	  }, {
	    key: "getPopupContent",
	    value: function getPopupContent() {
	      var _this3 = this;

	      return this.cache.remember('popupContainer', function () {
	        return main_core.Tag.render(_templateObject$f || (_templateObject$f = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-gradient-preset-popup-container\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this3.getRotateButton(), _this3.getSwapButton());
	      });
	    }
	  }, {
	    key: "buildLayout",
	    value: function buildLayout() {
	      if (this.preset) {
	        main_core.Dom.clean(this.getPresetContainer());
	        main_core.Dom.append(this.preset.getLayout(), this.getPresetContainer());
	      }

	      return main_core.Tag.render(_templateObject2$9 || (_templateObject2$9 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-gradient\">\n\t\t\t\t", "\n\t\t\t\t<div class=\"landing-ui-field-color-gradient-container\">\n\t\t\t\t\t<div class=\"landing-ui-field-color-gradient-from\">", "</div>\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"landing-ui-field-color-gradient-to\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"landing-ui-field-color-gradient-switch-type-container\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.getPresetContainer(), this.colorpickerFrom.getLayout(), this.getPopupButton(), this.colorpickerTo.getLayout(), this.getSwitchTypeButton());
	    }
	  }, {
	    key: "getContainerLayout",
	    value: function getContainerLayout() {
	      // todo: do better after change vyorstka
	      return this.getLayout().querySelector('.landing-ui-field-color-gradient-container');
	    }
	  }, {
	    key: "getPresetContainer",
	    value: function getPresetContainer() {
	      return this.cache.remember('presetContainer', function () {
	        return main_core.Tag.render(_templateObject3$5 || (_templateObject3$5 = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-color-gradient-preset-container\"></div>"])));
	      });
	    }
	  }, {
	    key: "getPopupButton",
	    value: function getPopupButton() {
	      return this.cache.remember('popupButton', function () {
	        return main_core.Tag.render(_templateObject4$4 || (_templateObject4$4 = babelHelpers.taggedTemplateLiteral(["<span class=\"landing-ui-field-color-gradient-open-popup\"></span>"])));
	      });
	    }
	  }, {
	    key: "getSwitchTypeButton",
	    value: function getSwitchTypeButton() {
	      return this.cache.remember('switchTypeButton', function () {
	        return main_core.Tag.render(_templateObject5$3 || (_templateObject5$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span\n\t\t\t\t\tclass=\"landing-ui-field-color-gradient-switch-type\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t></span>"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_SWITCH_TYPE'));
	      });
	    }
	  }, {
	    key: "getRotateButton",
	    value: function getRotateButton() {
	      return this.cache.remember('rotateButton', function () {
	        return main_core.Tag.render(_templateObject6$1 || (_templateObject6$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span\n\t\t\t\t\tclass=\"landing-ui-field-color-gradient-rotate\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t></span>"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_ROTATE'));
	      });
	    }
	  }, {
	    key: "getSwapButton",
	    value: function getSwapButton() {
	      return this.cache.remember('swapButton', function () {
	        return main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span\n\t\t\t\t\tclass=\"landing-ui-field-color-gradient-swap\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t></span>"])), main_core.Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_SWAP'));
	      });
	    }
	  }, {
	    key: "setPreset",
	    value: function setPreset(preset) {
	      var _this4 = this;

	      this.preset = preset;
	      this.preset.subscribe('onChange', function (event) {
	        _this4.setValue(event.getData().color);

	        _this4.unsetColorpickerActive();

	        _this4.onChange(event);
	      });
	      main_core.Dom.clean(this.getPresetContainer());
	      main_core.Dom.append(preset.getLayout(), this.getPresetContainer());
	    }
	  }, {
	    key: "getPreset",
	    value: function getPreset() {
	      return this.preset;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this5 = this;

	      return this.cache.remember('value', function () {
	        if (_this5.colorpickerFrom.getValue() === null || _this5.colorpickerTo.getValue() === null) {
	          return null;
	        }

	        var rotate = _this5.getRotateButton().dataset.rotate;

	        rotate = rotate ? main_core.Text.toNumber(rotate) : 0;
	        var type = _this5.getSwitchTypeButton().dataset.type || GradientValue.TYPE_LINEAR;
	        return new GradientValue({
	          from: _this5.colorpickerFrom.getValue(),
	          to: _this5.colorpickerTo.getValue(),
	          angle: rotate,
	          type: type
	        });
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Gradient.prototype), "setValue", this).call(this, value);

	      if (value === null) {
	        this.colorpickerFrom.setValue(null);
	        this.colorpickerTo.setValue(null);
	        this.unsetActive();
	        main_core.Dom.style(this.getContainerLayout(), 'background', new GradientValue().getStyleString());
	        Gradient.disableButton(this.getRotateButton());
	        Gradient.disableButton(this.getSwitchTypeButton());
	        Gradient.disableButton(this.getSwapButton());
	      } else {
	        // todo: how set default type and rotation?
	        this.colorpickerFrom.setValue(value.getFrom());
	        this.colorpickerTo.setValue(value.getTo());
	        this.correctColorpickerColors();
	        this.getRotateButton().dataset.rotate = value.getAngle();
	        this.getSwitchTypeButton().dataset.type = value.getType();
	        main_core.Dom.style(this.getRotateButton(), 'transform', "rotate(".concat(value.getAngle(), "deg)"));
	        main_core.Dom.style(this.getContainerLayout(), 'background', this.getValue().getStyleString());
	        Gradient.enableButton(this.getSwitchTypeButton());
	        Gradient.enableButton(this.getSwapButton());

	        if (value.getType() === GradientValue.TYPE_RADIAL) {
	          Gradient.disableButton(this.getRotateButton());
	          this.getSwitchTypeButton().innerText = main_core.Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_DO_LINEAR');
	        } else {
	          Gradient.enableButton(this.getRotateButton());
	          this.getSwitchTypeButton().innerText = main_core.Loc.getMessage('LANDING_FIELD_COLOR-GRADIENT_DO_RADIAL');
	        }

	        this.setActive();
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      this.emit('onChange', {
	        gradient: this.getValue()
	      });
	    }
	  }, {
	    key: "setActive",
	    value: function setActive() {
	      var value = this.getValue();

	      if (this.preset.isPresetValue(value)) {
	        this.preset.setActiveValue(value);
	        this.unsetColorpickerActive();
	      } else {
	        this.preset.unsetActive();
	        this.setColorpickerActive();
	      }
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      this.preset.unsetActive();
	      this.unsetColorpickerActive();
	    }
	  }, {
	    key: "setColorpickerActive",
	    value: function setColorpickerActive() {
	      main_core.Dom.addClass(this.getContainerLayout(), Gradient.ACTIVE_CLASS);
	    }
	  }, {
	    key: "unsetColorpickerActive",
	    value: function unsetColorpickerActive() {
	      this.colorpickerFrom.unsetActive();
	      this.colorpickerTo.unsetActive();
	      main_core.Dom.removeClass(this.getContainerLayout(), Gradient.ACTIVE_CLASS);
	    }
	  }], [{
	    key: "disableButton",
	    value: function disableButton(button) {
	      main_core.Dom.addClass(button, Gradient.DISABLE_CLASS);
	    }
	  }, {
	    key: "enableButton",
	    value: function enableButton(button) {
	      main_core.Dom.removeClass(button, Gradient.DISABLE_CLASS);
	    }
	  }, {
	    key: "isButtonEnable",
	    value: function isButtonEnable(button) {
	      return !main_core.Dom.hasClass(button, Gradient.DISABLE_CLASS);
	    }
	  }]);
	  return Gradient;
	}(BaseControl);

	babelHelpers.defineProperty(Gradient, "DISABLE_CLASS", 'disable');

	var BgColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(BgColor, _Color);

	  function BgColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BgColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BgColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColor');

	    _this.property = ['background-image', 'background-color'];
	    _this.variableName = '--bg';
	    _this.className = 'g-bg';
	    _this.activeControl = null;
	    _this.gradient = new Gradient(options);

	    _this.gradient.subscribe('onChange', _this.onGradientChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.tabs.prependTab('Gradient', main_core.Loc.getMessage('LANDING_FIELD_COLOR-TAB_GRADIENT'), _this.gradient);

	    _this.setGradientPreset(_this.colorSet.getPreset());

	    _this.colorSet.subscribe('onPresetChange', function (event) {
	      _this.setGradientPreset(event.getData().preset);
	    });

	    _this.tabs.subscribe('onToggle', _this.onTabsToggle.bind(babelHelpers.assertThisInitialized(_this)));

	    return _this;
	  }

	  babelHelpers.createClass(BgColor, [{
	    key: "isNullValue",
	    value: function isNullValue(value) {
	      return value === null || value === 'none' || value === 'rgba(0, 0, 0, 0)';
	    }
	  }, {
	    key: "setGradientPreset",
	    value: function setGradientPreset(preset) {
	      var _this2 = this;

	      var gradientPreset = preset.getGradientPreset();
	      this.gradient.setPreset(gradientPreset);
	      gradientPreset.subscribe('onChange', function () {
	        _this2.activeControl = _this2.gradient;

	        _this2.onChange();
	      });
	      var value = this.getValue();

	      if (value !== null && value instanceof GradientValue) {
	        console.log("bg grad value", value);

	        if (this.gradient.getPreset().isPresetValue(value)) {
	          this.colorSet.getPreset().unsetActive(); // todo: unset active color preset

	          this.gradient.getPreset().setActiveValue(value);
	        }
	      }
	    }
	  }, {
	    key: "onColorSetChange",
	    value: function onColorSetChange(event) {
	      this.activeControl = this.colorSet;
	      this.gradient.unsetActive();
	      babelHelpers.get(babelHelpers.getPrototypeOf(BgColor.prototype), "onColorSetChange", this).call(this, event);
	    }
	  }, {
	    key: "onGradientChange",
	    value: function onGradientChange(event) {
	      this.activeControl = this.gradient;
	      this.colorSet.unsetActive();
	      var gradValue = event.getData().gradient;

	      if (gradValue !== null) {
	        this.opacity.setValue(gradValue.setOpacity(this.opacity.getValue().getOpacity()));
	      }

	      this.onChange();
	    }
	  }, {
	    key: "onOverlayOpacityChange",
	    value: function onOverlayOpacityChange() {
	      this.onChange();
	    }
	  }, {
	    key: "onTabsToggle",
	    value: function onTabsToggle() {
	      this.gradient.getPopup().close();
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      this.colorSet.unsetActive();
	      this.gradient.unsetActive();
	      this.primary.unsetActive();
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.colorSet.setValue(null); // todo: need? what set default?

	      this.gradient.setValue(null);
	      this.unsetActive();
	      this.activeControl = null;

	      if (main_core.Type.isNull(value)) ; else if (isRgbString(value) || isHex(value) || isHslString(value) || isCssVar(value)) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(BgColor.prototype), "setValue", this).call(this, value);
	        this.activeControl = this.colorSet;
	      } else if (isGradientString(value)) {
	        var gradientValue = new GradientValue(value);
	        this.gradient.setValue(gradientValue);
	        this.opacity.setValue(gradientValue);
	        this.tabs.showTab('Gradient');

	        if (gradientValue.getOpacity() < 1) {
	          this.tabs.showTab('Opacity');
	        }

	        this.activeControl = this.gradient; // todo: set default value for colorset (from preset?) and unset active for them
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this3 = this;

	      return this.cache.remember('value', function () {
	        if (_this3.activeControl === null) {
	          return null;
	        } else if (_this3.activeControl === _this3.gradient) {
	          var gradValue = _this3.gradient.getValue();

	          return gradValue === null ? gradValue : gradValue.setOpacity(_this3.opacity.getValue().getOpacity());
	        } else {
	          return babelHelpers.get(babelHelpers.getPrototypeOf(BgColor.prototype), "getValue", _this3).call(_this3);
	        }
	      });
	    }
	  }]);
	  return BgColor;
	}(Color);

	var matcherBgImage = /url\(['"]?([^ '"]*)['"]?\)([\w \/]*)/i;
	function isBgImageString(bgImage) {
	  if (!!bgImage.trim().match(matcherBgImage)) {
	    return true;
	  }

	  return !!bgImage.trim().match(getMatcherWithOverlay());
	}

	function getMatcherWithOverlay() {
	  var matcherBgString = regexpToString(matcherBgImage);
	  var matcherGradientString = regexpToString(matcherGradient);
	  return new RegExp("^".concat(matcherGradientString, ",").concat(matcherBgString));
	}

	var BgImageValue = /*#__PURE__*/function () {
	  function BgImageValue(value) {
	    babelHelpers.classCallCheck(this, BgImageValue);
	    // todo: add 2x, file ids
	    this.value = defaultBgImageValueOptions;
	    this.setValue(value);
	  }

	  babelHelpers.createClass(BgImageValue, [{
	    key: "getName",
	    value: function getName() {
	      return "\n\t\t\t".concat(this.value.url.replace(/[^\w\d]/g, ''), "_").concat(this.value.size, "_").concat(this.value.attachment, "\n\t\t");
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      if (main_core.Type.isObject(value)) {
	        if (value instanceof BgImageValue) {
	          // todo: add 2x and file IDs
	          this.value.url = value.getUrl();
	          this.value.url2x = value.getUrl2x();
	          this.value.fileId = value.getFileId();
	          this.value.fileId2x = value.getFileId2x();
	          this.value.size = value.getSize();
	          this.value.attachment = value.getAttachment();
	        } else {
	          this.value = babelHelpers.objectSpread({}, this.value, value);
	        }
	      }

	      if (main_core.Type.isString(value) && isBgImageString(value)) {
	        this.parseBgString(value);
	      }

	      return this;
	    }
	  }, {
	    key: "parseBgString",
	    value: function parseBgString(string) {
	      // todo: check matcher for 2x
	      var options = defaultBgImageValueOptions;
	      var matchesBg = string.trim().match(regexpWoStartEnd(matcherBgImage));

	      if (!!matchesBg) {
	        options.url = matchesBg[1];
	        options.size = matchesBg[2].indexOf('auto') === -1 ? defaultBgImageSize : 'auto';
	        options.attachment = matchesBg[2].indexOf('fixed') === -1 ? defaultBgImageAttachment : 'fixed';
	      }

	      var matchesOverlay = string.trim().match(regexpWoStartEnd(matcherGradientColors));

	      if (!!string.trim().match(regexpWoStartEnd(matcherGradient)) && !!matchesOverlay) {
	        options.overlay = new ColorValue(matchesOverlay[0]);
	      }

	      this.setValue(options);
	    }
	  }, {
	    key: "setOpacity",
	    value: function setOpacity(opacity) {
	      // todo: what for image?
	      return this;
	    }
	  }, {
	    key: "setUrl",
	    value: function setUrl(value) {
	      this.setValue({
	        url: value
	      });
	      return this;
	    }
	  }, {
	    key: "setUrl2x",
	    value: function setUrl2x(value) {
	      this.setValue({
	        url2x: value
	      });
	      return this;
	    }
	  }, {
	    key: "setFileId",
	    value: function setFileId(value) {
	      this.setValue({
	        fileId: value
	      });
	      return this;
	    }
	  }, {
	    key: "setFileId2x",
	    value: function setFileId2x(value) {
	      this.setValue({
	        fileId2x: value
	      });
	      return this;
	    }
	  }, {
	    key: "setSize",
	    value: function setSize(value) {
	      this.setValue({
	        size: value
	      });
	      return this;
	    }
	  }, {
	    key: "setAttachment",
	    value: function setAttachment(value) {
	      this.setValue({
	        attachment: value
	      });
	      return this;
	    }
	  }, {
	    key: "setOverlay",
	    value: function setOverlay(value) {
	      this.setValue({
	        overlay: value
	      });
	    }
	  }, {
	    key: "getUrl",
	    value: function getUrl() {
	      return this.value.url;
	    }
	  }, {
	    key: "getUrl2x",
	    value: function getUrl2x() {
	      return this.value.url2x;
	    }
	  }, {
	    key: "getFileId",
	    value: function getFileId() {
	      return this.value.fileId;
	    }
	  }, {
	    key: "getFileId2x",
	    value: function getFileId2x() {
	      return this.value.fileId2x;
	    }
	  }, {
	    key: "getSize",
	    value: function getSize() {
	      return this.value.size;
	    }
	  }, {
	    key: "getAttachment",
	    value: function getAttachment() {
	      var needBool = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      return needBool ? this.value.attachment === 'fixed' : this.value.attachment;
	    }
	  }, {
	    key: "getOverlay",
	    value: function getOverlay() {
	      return this.value.overlay;
	    }
	  }, {
	    key: "getOpacity",
	    value: function getOpacity() {
	      // todo: how image can have opacity?
	      return 1;
	    }
	  }, {
	    key: "getStyleString",
	    value: function getStyleString() {
	      var style = '';

	      if (this.value.overlay !== null) {
	        style = "linear-gradient(".concat(this.value.overlay.getStyleString(), ",").concat(this.value.overlay.getStyleString(), ")");
	      } // todo: what if url is null


	      var _this$value = this.value,
	          url = _this$value.url,
	          url2x = _this$value.url2x,
	          size = _this$value.size,
	          attachment = _this$value.attachment;
	      var endString = "center / ".concat(size, " ").concat(attachment);

	      if (url !== null) {
	        style = style.length ? style + ',' : '';

	        if (url2x !== null) {
	          style += "-webkit-image-set(url('".concat(url, "') 1x, url('").concat(url2x, "') 2x) ").concat(endString, ",");
	          style += "image-set(url('".concat(url, "') 1x, url('").concat(url2x, "') 2x) ").concat(endString, ",");
	        }

	        style += "url('".concat(url, "') ").concat(endString);
	      }

	      return style;
	    }
	  }, {
	    key: "getStyleStringForOpacity",
	    value: function getStyleStringForOpacity() {
	      // todo: how image can have opacity?
	      return '';
	    }
	  }], [{
	    key: "getSizeItemsForButtons",
	    value: function getSizeItemsForButtons() {
	      return [{
	        name: main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_COVER'),
	        value: 'cover'
	      }, {
	        name: main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_MOSAIC'),
	        value: 'auto'
	      }];
	    }
	  }, {
	    key: "getAttachmentValueByBool",
	    value: function getAttachmentValueByBool(value) {
	      return value ? 'fixed' : 'scroll';
	    }
	  }]);
	  return BgImageValue;
	}();

	var _templateObject$g;

	var Image = /*#__PURE__*/function (_BaseControl) {
	  babelHelpers.inherits(Image, _BaseControl);

	  function Image(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Image);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Image).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Image');

	    _this.options = options; // todo: set dimensions from block

	    var rootWindow = landing_pageobject.PageObject.getRootWindow();
	    _this.imgField = new rootWindow.BX.Landing.UI.Field.Image({
	      id: 'landing_ui_color_image_' + main_core.Text.getRandom().toLowerCase(),
	      className: 'landing-ui-field-color-image-image',
	      compactMode: true,
	      disableLink: true,
	      // selector: options.selector,
	      disableAltField: true,
	      allowClear: true,
	      dimensions: {
	        width: 1920
	      },
	      uploadParams: {
	        action: "Block::uploadFile",
	        block: _this.options.block.id
	      },
	      contentRoot: _this.options.contentRoot
	    });

	    _this.imgField.subscribe('change', _this.onImageChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.sizeField = new BX.Landing.UI.Field.Dropdown({
	      // todo: need commented fields?
	      id: 'landing_ui_color_image_size_' + main_core.Text.getRandom().toLowerCase(),
	      // title: 'size field title',
	      // description: 'ButtonGroup size description',
	      title: main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_SIZE_TITLE'),
	      className: 'landing-ui-field-color-image-size',
	      // selector: this.options.selector,
	      items: BgImageValue.getSizeItemsForButtons(),
	      onChange: _this.onSizeChange.bind(babelHelpers.assertThisInitialized(_this)),
	      contentRoot: _this.options.contentRoot
	    });
	    _this.attachmentField = new BX.Landing.UI.Field.Checkbox({
	      // todo: need commented fields?
	      id: 'landing_ui_color_image_attach_' + main_core.Text.getRandom().toLowerCase(),
	      className: 'landing-ui-field-color-image-attachment',
	      // title: 'attachement field title',
	      // description: 'ButtonGroup size description',
	      multiple: false,
	      compact: true,
	      // selector: options.selector,
	      items: [{
	        name: main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_FIXED'),
	        value: true
	      }],
	      onChange: _this.onAttachmentChange.bind(babelHelpers.assertThisInitialized(_this))
	    });
	    return _this;
	  }

	  babelHelpers.createClass(Image, [{
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$g || (_templateObject$g = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-image\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.imgField.getLayout(), this.sizeField.getLayout(), this.attachmentField.getLayout());
	    }
	  }, {
	    key: "onImageChange",
	    value: function onImageChange(event) {
	      var value = this.getValue() || new BgImageValue();
	      value.setUrl(event.getData().value.src);
	      value.setFileId(event.getData().value.id);

	      if (event.getData().value.src2x) {
	        value.setUrl2x(event.getData().value.src2x);
	        value.setFileId2x(event.getData().value.id2x);
	      }

	      this.setValue(value);
	      this.onChange(new main_core_events.BaseEvent({
	        data: {
	          image: value
	        }
	      }));
	      this.saveNode(value);
	    }
	  }, {
	    key: "saveNode",
	    value: function saveNode(value) {
	      var style = this.options.styleNode;
	      var block = this.options.block;
	      var selector = style.selector;

	      if (style.selector === block.selector || style.selector === block.makeAbsoluteSelector(block.selector)) {
	        selector = '#wrapper';
	      } else if (!style.isSelectGroup()) {
	        selector = BX.Landing.Utils.join(style.selector.split("@")[0], "@", style.getElementIndex(style.getNode()[0]));
	      } else {
	        selector = style.selector.split("@")[0];
	      }

	      var data = babelHelpers.defineProperty({}, selector, {});

	      if (value.getFileId()) {
	        data[selector].id = value.getFileId();
	      }

	      if (value.getFileId2x()) {
	        data[selector].id2x = value.getFileId2x();
	      }

	      landing_backend.Backend.getInstance().action("Landing\\Block::updateNodes", {
	        block: this.options.block.id,
	        data: data
	      });
	    }
	  }, {
	    key: "onSizeChange",
	    value: function onSizeChange(size) {
	      if (main_core.Type.isString(size)) {
	        var value = this.getValue() || new BgImageValue();
	        value.setSize(size);
	        this.setValue(value);
	        this.onChange(new main_core_events.BaseEvent({
	          data: {
	            image: value
	          }
	        }));
	      }
	    }
	  }, {
	    key: "onAttachmentChange",
	    value: function onAttachmentChange(event) {
	      if (event instanceof main_core_events.BaseEvent) {
	        var value = this.getValue() || new BgImageValue();
	        value.setAttachment(BgImageValue.getAttachmentValueByBool(this.attachmentField.getValue()));
	        this.setValue(value);
	        this.onChange(new main_core_events.BaseEvent({
	          data: {
	            image: value
	          }
	        }));
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange(event) {
	      // todo: can call parent?
	      // if not image - null
	      this.emit('onChange', event);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this2 = this;

	      // todo: get size and attachement from controls
	      return this.cache.remember('value', function () {
	        var imgValue = _this2.imgField.getValue();

	        var url = imgValue.src;

	        if (url === null) {
	          return null;
	        } else {
	          var value = new BgImageValue({
	            url: url,
	            fileId: imgValue.id
	          });

	          if (imgValue.src2x) {
	            value.setUrl2x(imgValue.src2x);
	            value.setFileId2x(imgValue.fileId2x);
	          }

	          var size = _this2.sizeField.getValue();

	          if (size !== null) {
	            value.setSize(size);
	          }

	          value.setAttachment(BgImageValue.getAttachmentValueByBool(_this2.attachmentField.getValue())); // todo: set overlay

	          return value;
	        }
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      // todo: can delete prev image
	      babelHelpers.get(babelHelpers.getPrototypeOf(Image.prototype), "setValue", this).call(this, value);

	      if (value === null) {
	        this.imgField.setValue({
	          src: ''
	        }, true); // todo: what set size and attachement?
	      } else {
	        if (value.getUrl() !== null) {
	          this.setActive();
	        }

	        var imgFieldValue = {
	          type: 'image',
	          src: value.getUrl(),
	          id: value.getFileId()
	        };

	        if (value.getUrl2x()) {
	          imgFieldValue.src2x = value.getUrl2x();
	          imgFieldValue.id2x = value.getFileId2x();
	        }

	        this.imgField.setValue(imgFieldValue, true);
	        this.sizeField.setValue(value.getSize(), true);
	        this.attachmentField.setValue([value.getAttachment(true)]);
	      }
	    }
	  }, {
	    key: "setActive",
	    value: function setActive() {
	      main_core.Dom.addClass(this.imgField.getLayout(), Image.ACTIVE_CLASS);
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      main_core.Dom.removeClass(this.imgField.getLayout(), Image.ACTIVE_CLASS);
	    }
	  }]);
	  return Image;
	}(BaseControl);

	var _templateObject$h;

	var Bg = /*#__PURE__*/function (_BgColor) {
	  babelHelpers.inherits(Bg, _BgColor);

	  function Bg(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Bg);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Bg).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.Bg');

	    _this.parentVariableName = _this.variableName;
	    _this.variableName = [_this.parentVariableName, Bg.BG_URL_VAR, Bg.BG_URL_2X_VAR, Bg.BG_OVERLAY_VAR, Bg.BG_SIZE_VAR, Bg.BG_ATTACHMENT_VAR];
	    _this.parentClassName = _this.className;
	    _this.className = 'g-bg-image';
	    _this.image = new Image(options);

	    _this.image.subscribe('onChange', _this.onImageChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.overlay = new ColorSet(options);

	    _this.overlay.subscribe('onChange', _this.onOverlayChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.overlayOpacity = new Opacity();

	    _this.overlayOpacity.setValue(new ColorValue().setOpacity(0.5));

	    _this.overlayOpacity.subscribe('onChange', _this.onOverlayOpacityChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.imageTabs = new Tabs().appendTab('Overlay', main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_OVERLAY'), [_this.overlay, _this.overlayOpacity]);
	    _this.bigTabs = new Tabs().setBig(true).appendTab('Color', main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_COLOR'), [_this.colorSet, _this.primary, _this.tabs]).appendTab('Image', main_core.Loc.getMessage('LANDING_FIELD_COLOR-BG_IMAGE'), [_this.image, _this.imageTabs]);
	    return _this;
	  }

	  babelHelpers.createClass(Bg, [{
	    key: "buildLayout",
	    value: function buildLayout() {
	      return main_core.Tag.render(_templateObject$h || (_templateObject$h = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-color-color\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.bigTabs.getLayout());
	    }
	  }, {
	    key: "onColorSetChange",
	    value: function onColorSetChange(event) {
	      this.image.unsetActive();
	      babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "onColorSetChange", this).call(this, event);
	    }
	  }, {
	    key: "onGradientChange",
	    value: function onGradientChange(event) {
	      this.image.unsetActive();
	      babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "onGradientChange", this).call(this, event);
	    }
	  }, {
	    key: "onImageChange",
	    value: function onImageChange() {
	      // todo: can drop image from b_landing_file after change
	      this.unsetActive();
	      this.activeControl = this.image;
	      this.image.setActive();
	      this.onChange();
	    }
	  }, {
	    key: "onOverlayChange",
	    value: function onOverlayChange(event) {
	      var color = event.getData().color;
	      color.setOpacity(this.overlayOpacity.getValue().getOpacity());
	      this.overlayOpacity.setValue(color);
	      this.onChange();
	    }
	  }, {
	    key: "onOverlayOpacityChange",
	    value: function onOverlayOpacityChange() {
	      this.onChange();
	    }
	  }, {
	    key: "unsetActive",
	    value: function unsetActive() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "unsetActive", this).call(this);
	      this.image.unsetActive(); // todo: unset overlay?
	    }
	    /**
	     * Set value by new format
	     */

	  }, {
	    key: "setProcessorValue",
	    value: function setProcessorValue(value) {
	      // Just get last css variable
	      this.setValue(value);
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.image.setValue(null);
	      this.bigTabs.showTab('Color');

	      if (main_core.Type.isNull(value)) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "setValue", this).call(this, value);
	      } else if (main_core.Type.isString(value)) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "setValue", this).call(this, value);
	      } else if (this.parentVariableName in value && main_core.Type.isString(value[this.parentVariableName])) {
	        babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "setValue", this).call(this, value[this.parentVariableName]);
	      } else if (main_core.Type.isObject(value)) {
	        // todo: super.setValue null?
	        var bgValue = new BgImageValue();

	        if (Bg.BG_URL_VAR in value) {
	          bgValue.setUrl(value[Bg.BG_URL_VAR].replace(/url\(["']/i, '').replace(/['"]\)/i, ''));
	        }

	        if (Bg.BG_URL_2X_VAR in value) {
	          bgValue.setUrl2x(value[Bg.BG_URL_2X_VAR].replace(/url\(["']/i, '').replace(/['"]\)/i, ''));
	        }

	        if (Bg.BG_SIZE_VAR in value) {
	          bgValue.setSize(value[Bg.BG_SIZE_VAR]);
	        }

	        if (Bg.BG_ATTACHMENT_VAR in value) {
	          bgValue.setAttachment(value[Bg.BG_ATTACHMENT_VAR]);
	        }

	        this.image.setValue(bgValue);
	        this.bigTabs.showTab('Image');
	        this.activeControl = this.image;

	        if (Bg.BG_OVERLAY_VAR in value) {
	          var overlayValue = new ColorValue(value[Bg.BG_OVERLAY_VAR]);
	          this.overlay.setValue(overlayValue);
	          this.overlayOpacity.setValue(overlayValue);
	          this.imageTabs.showTab('Overlay');
	        }
	      }
	    } // todo: create base value instead interface. In this case can return ALL types, color, grad, bg

	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var _this2 = this;

	      return this.cache.remember('value', function () {
	        if (_this2.activeControl === _this2.image) {
	          var imageValue = _this2.image.getValue();

	          var overlayValue = _this2.overlay.getValue();

	          if (imageValue !== null && _this2.overlay.isActive() && overlayValue !== null) {
	            overlayValue.setOpacity(_this2.overlayOpacity.getValue().getOpacity());
	            imageValue.setOverlay(overlayValue);
	          }

	          return imageValue;
	        } else {
	          return babelHelpers.get(babelHelpers.getPrototypeOf(Bg.prototype), "getValue", _this2).call(_this2);
	        }
	      });
	    }
	  }, {
	    key: "getClassName",
	    value: function getClassName() {
	      var value = this.getValue();

	      if (value === null || value instanceof ColorValue || value instanceof GradientValue) {
	        return [this.parentClassName];
	      }

	      return [this.className];
	    } // todo: what about fileid?

	  }, {
	    key: "getStyle",
	    value: function getStyle() {
	      var _ref2;

	      if (this.getValue() === null) {
	        var _ref;

	        // todo: not null, but what?
	        return _ref = {}, babelHelpers.defineProperty(_ref, this.parentVariableName, null), babelHelpers.defineProperty(_ref, Bg.BG_URL_VAR, null), babelHelpers.defineProperty(_ref, Bg.BG_URL_2X_VAR, null), babelHelpers.defineProperty(_ref, Bg.BG_OVERLAY_VAR, null), babelHelpers.defineProperty(_ref, Bg.BG_SIZE_VAR, null), babelHelpers.defineProperty(_ref, Bg.BG_ATTACHMENT_VAR, null), _ref;
	      }

	      var value = this.getValue();
	      var color = null;
	      var image = null;
	      var image2x = null;
	      var overlay = null; // let size = 'cover';

	      var size = null; // let attachment = 'scroll';

	      var attachment = null;

	      if (value instanceof ColorValue || value instanceof GradientValue) {
	        // todo: need change class if not a image?
	        color = value.getStyleString();
	      } else {
	        image = "url('".concat(value.getUrl(), "')");
	        image2x = "url('".concat(value.getUrl2x(), "')");
	        overlay = value.getOverlay() ? value.getOverlay().getStyleString() : 'transparent';
	        size = value.getSize();
	        attachment = value.getAttachment();
	      }

	      return _ref2 = {}, babelHelpers.defineProperty(_ref2, this.parentVariableName, color), babelHelpers.defineProperty(_ref2, Bg.BG_URL_VAR, image), babelHelpers.defineProperty(_ref2, Bg.BG_URL_2X_VAR, image2x), babelHelpers.defineProperty(_ref2, Bg.BG_OVERLAY_VAR, overlay), babelHelpers.defineProperty(_ref2, Bg.BG_SIZE_VAR, size), babelHelpers.defineProperty(_ref2, Bg.BG_ATTACHMENT_VAR, attachment), _ref2;
	    }
	  }]);
	  return Bg;
	}(BgColor);

	babelHelpers.defineProperty(Bg, "BG_URL_VAR", '--bg-url');
	babelHelpers.defineProperty(Bg, "BG_URL_2X_VAR", '--bg-url-2x');
	babelHelpers.defineProperty(Bg, "BG_OVERLAY_VAR", '--bg-overlay');
	babelHelpers.defineProperty(Bg, "BG_SIZE_VAR", '--bg-size');
	babelHelpers.defineProperty(Bg, "BG_ATTACHMENT_VAR", '--bg-attachment');

	var BorderColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(BorderColor, _Color);

	  function BorderColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BorderColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BorderColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BorderColor');

	    _this.property = 'border-color';
	    _this.variableName = '--border-color';
	    _this.className = 'g-border-color';
	    return _this;
	  }

	  return BorderColor;
	}(Color);

	var BorderColorHover = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(BorderColorHover, _Color);

	  function BorderColorHover(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BorderColorHover);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BorderColorHover).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BorderColorHover');

	    _this.property = 'border-color';
	    _this.variableName = '--border-color--hover';
	    _this.className = 'g-border-color--hover';
	    _this.pseudoClass = ':hover';
	    return _this;
	  }

	  return BorderColorHover;
	}(Color);

	var BgColorHover = /*#__PURE__*/function (_BgColor) {
	  babelHelpers.inherits(BgColorHover, _BgColor);

	  function BgColorHover(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BgColorHover);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BgColorHover).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColorHover');

	    _this.property = ['background-image', 'background-color'];
	    _this.variableName = '--bg-hover';
	    _this.className = 'g-bg--hover';
	    _this.pseudoClass = ':hover';
	    return _this;
	  }

	  return BgColorHover;
	}(BgColor);

	var BgColorAfter = /*#__PURE__*/function (_BgColor) {
	  babelHelpers.inherits(BgColorAfter, _BgColor);

	  function BgColorAfter(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BgColorAfter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BgColorAfter).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColorAfter');

	    _this.property = ['background-image', 'background-color'];
	    _this.variableName = '--bg--after';
	    _this.className = 'g-bg--after';
	    _this.pseudoClass = ':after';
	    var opacityValue = _this.getValue() || new ColorValue();

	    _this.opacity.setValue(opacityValue.setOpacity(0.5));

	    _this.tabs.showTab('Opacity');

	    return _this;
	  }

	  return BgColorAfter;
	}(BgColor);

	var BgColorBefore = /*#__PURE__*/function (_BgColor) {
	  babelHelpers.inherits(BgColorBefore, _BgColor);

	  function BgColorBefore(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BgColorBefore);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BgColorBefore).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BgColorBefore');

	    _this.property = ['background-image', 'background-color'];
	    _this.variableName = '--bg--before';
	    _this.className = 'g-bg--before';
	    _this.pseudoClass = ':before';
	    var opacityValue = _this.getValue() || new ColorValue();

	    _this.opacity.setValue(opacityValue.setOpacity(0.5));

	    _this.tabs.showTab('Opacity');

	    return _this;
	  }

	  return BgColorBefore;
	}(BgColor);

	var NavbarColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarColor, _Color);

	  function NavbarColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColor');

	    _this.property = 'color';
	    _this.variableName = '--navbar-color';
	    _this.className = 'u-navbar-color';
	    return _this;
	  }

	  return NavbarColor;
	}(Color);

	var NavbarColorHover = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarColorHover, _Color);

	  function NavbarColorHover(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarColorHover);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarColorHover).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColorHover');

	    _this.property = 'color';
	    _this.variableName = '--navbar-color--hover';
	    _this.className = 'u-navbar-color--hover';
	    _this.pseudoClass = ':hover';
	    return _this;
	  }

	  return NavbarColorHover;
	}(Color);

	var NavbarColorFixMoment = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarColorFixMoment, _Color);

	  function NavbarColorFixMoment(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarColorFixMoment);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarColorFixMoment).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColorFixMoment');

	    _this.property = 'color';
	    _this.variableName = '--navbar-color--fix-moment';
	    _this.className = 'u-navbar-color--fix-moment';
	    return _this;
	  }

	  return NavbarColorFixMoment;
	}(Color);

	var NavbarColorFixMomentHover = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarColorFixMomentHover, _Color);

	  function NavbarColorFixMomentHover(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarColorFixMomentHover);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarColorFixMomentHover).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarColorFixMomentHover');

	    _this.property = 'color';
	    _this.variableName = '--navbar-color--fix-moment--hover';
	    _this.className = 'u-navbar-color--fix-moment--hover';
	    _this.pseudoClass = ':hover';
	    return _this;
	  }

	  return NavbarColorFixMomentHover;
	}(Color);

	var NavbarBgColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarBgColor, _Color);

	  function NavbarBgColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarBgColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarBgColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarBgColor');

	    _this.property = 'background-color';
	    _this.variableName = '--navbar-bg-color';
	    _this.className = 'u-navbar-bg';
	    return _this;
	  }

	  return NavbarBgColor;
	}(Color);

	var NavbarBgColorHover = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarBgColorHover, _Color);

	  function NavbarBgColorHover(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarBgColorHover);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarBgColorHover).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarBgColorHover');

	    _this.property = 'background-color';
	    _this.variableName = '--navbar-bg-color--hover';
	    _this.className = 'u-navbar-bg--hover';
	    _this.pseudoClass = ':hover';
	    return _this;
	  }

	  return NavbarBgColorHover;
	}(Color);

	var BorderColorTop = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(BorderColorTop, _Color);

	  function BorderColorTop(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, BorderColorTop);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BorderColorTop).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.BorderColorTop');

	    _this.property = 'border-top-color';
	    _this.variableName = '--border-color-top';
	    _this.className = 'g-border-color-top';
	    return _this;
	  }

	  return BorderColorTop;
	}(Color);

	var FillColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(FillColor, _Color);

	  function FillColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FillColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FillColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.FillColor');

	    _this.property = 'fill';
	    _this.variableName = '--fill-first';
	    _this.className = 'g-fill-first';
	    return _this;
	  }

	  return FillColor;
	}(Color);

	var FillColorSecond = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(FillColorSecond, _Color);

	  function FillColorSecond(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, FillColorSecond);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FillColorSecond).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.FillColorSecond');

	    _this.property = 'fill';
	    _this.variableName = '--fill-second';
	    _this.className = 'g-fill-second';
	    return _this;
	  }

	  return FillColorSecond;
	}(Color);

	var ButtonColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(ButtonColor, _Color);

	  function ButtonColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ButtonColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ButtonColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.ButtonColor');

	    _this.property = 'background-color'; // order is important! Base variable must be last. Hack :-/

	    _this.variableName = [ButtonColor.COLOR_CONTRAST_VAR, ButtonColor.COLOR_HOVER_VAR, ButtonColor.COLOR_LIGHT_VAR, ButtonColor.COLOR_VAR];
	    _this.className = 'g-button-color'; //todo: ?

	    return _this;
	  }

	  babelHelpers.createClass(ButtonColor, [{
	    key: "getStyle",
	    value: function getStyle() {
	      var _ref2;

	      if (this.getValue() === null) {
	        var _ref;

	        return _ref = {}, babelHelpers.defineProperty(_ref, ButtonColor.COLOR_CONTRAST_VAR, null), babelHelpers.defineProperty(_ref, ButtonColor.COLOR_HOVER_VAR, null), babelHelpers.defineProperty(_ref, ButtonColor.COLOR_LIGHT_VAR, null), babelHelpers.defineProperty(_ref, ButtonColor.COLOR_VAR, null), _ref;
	      }

	      var value = this.getValue();
	      var valueContrast = value.getContrast().lighten(10);
	      var valueHover = new ColorValue(value).lighten(10);
	      var valueLight = value.getLighten();
	      return _ref2 = {}, babelHelpers.defineProperty(_ref2, ButtonColor.COLOR_CONTRAST_VAR, valueContrast.getStyleString()), babelHelpers.defineProperty(_ref2, ButtonColor.COLOR_HOVER_VAR, valueHover.getStyleString()), babelHelpers.defineProperty(_ref2, ButtonColor.COLOR_LIGHT_VAR, valueLight.getStyleString()), babelHelpers.defineProperty(_ref2, ButtonColor.COLOR_VAR, value.getStyleString()), _ref2;
	    }
	  }]);
	  return ButtonColor;
	}(Color);

	babelHelpers.defineProperty(ButtonColor, "COLOR_CONTRAST_VAR", '--button-color-contrast');
	babelHelpers.defineProperty(ButtonColor, "COLOR_HOVER_VAR", '--button-color-hover');
	babelHelpers.defineProperty(ButtonColor, "COLOR_LIGHT_VAR", '--button-color-light');
	babelHelpers.defineProperty(ButtonColor, "COLOR_VAR", '--button-color');

	var NavbarCollapseBgColor = /*#__PURE__*/function (_Color) {
	  babelHelpers.inherits(NavbarCollapseBgColor, _Color);

	  function NavbarCollapseBgColor(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, NavbarCollapseBgColor);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(NavbarCollapseBgColor).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Processor.NavbarCollapseBgColor');

	    _this.property = 'background-color';
	    _this.variableName = '--navbar-collapse-bg-color';
	    _this.className = 'u-navbar-collapse-bg';
	    return _this;
	  }

	  return NavbarCollapseBgColor;
	}(Color);

	var ColorField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ColorField, _BaseField);

	  function ColorField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ColorField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ColorField).call(this, options));
	    _this.items = 'items' in options && options.items ? options.items : [];
	    _this.postfix = typeof options.postfix === 'string' ? options.postfix : '';
	    _this.frame = babelHelpers.typeof(options.frame) === 'object' ? options.frame : null;
	    var processorOptions = {
	      block: options.block,
	      styleNode: options.styleNode,
	      selector: options.selector,
	      contentRoot: _this.contentRoot
	    };
	    _this.changeHandler = typeof options.onChange === "function" ? options.onChange : function () {};
	    _this.resetHandler = typeof options.onReset === "function" ? options.onReset : function () {}; // todo: rename "subtype"

	    switch (options.subtype) {
	      case 'color':
	        _this.processor = new Color(processorOptions);
	        break;

	      case 'color-hover':
	        _this.processor = new ColorHover(processorOptions);
	        break;

	      case 'bg':
	        _this.processor = new Bg(processorOptions);
	        break;

	      case 'bg-color':
	        _this.processor = new BgColor(processorOptions);
	        break;

	      case 'bg-color-hover':
	        _this.processor = new BgColorHover(processorOptions);
	        break;

	      case 'bg-color-after':
	        _this.processor = new BgColorAfter(processorOptions);
	        break;

	      case 'bg-color-before':
	        _this.processor = new BgColorBefore(processorOptions);
	        break;

	      case 'border-color':
	        _this.processor = new BorderColor(processorOptions);
	        break;

	      case 'border-color-hover':
	        _this.processor = new BorderColorHover(processorOptions);
	        break;

	      case 'border-color-top':
	        _this.processor = new BorderColorTop(processorOptions);
	        break;

	      case 'navbar-color':
	        _this.processor = new NavbarColor(processorOptions);
	        break;

	      case 'navbar-color-hover':
	        _this.processor = new NavbarColorHover(processorOptions);
	        break;

	      case 'navbar-color-fix-moment':
	        _this.processor = new NavbarColorFixMoment(processorOptions);
	        break;

	      case 'navbar-color-fix-moment-hover':
	        _this.processor = new NavbarColorFixMomentHover(processorOptions);
	        break;

	      case 'navbar-bg-color':
	        _this.processor = new NavbarBgColor(processorOptions);
	        break;

	      case 'navbar-bg-color-hover':
	        _this.processor = new NavbarBgColorHover(processorOptions);
	        break;

	      case 'navbar-collapse-bg-color':
	        _this.processor = new NavbarCollapseBgColor(processorOptions);
	        break;

	      case 'fill-color':
	        _this.processor = new FillColor(processorOptions);
	        break;

	      case 'fill-color-second':
	        _this.processor = new FillColorSecond(processorOptions);
	        break;

	      case 'button-color':
	        _this.processor = new ButtonColor(processorOptions);
	        break;

	      default:
	        break;
	    }

	    _this.property = _this.processor.getProperty()[_this.processor.getProperty().length - 1];

	    _this.processor.getClassName().forEach(function (item) {
	      return _this.items.push({
	        name: item,
	        value: item
	      });
	    }); // todo: what a input?


	    main_core.Dom.remove(_this.input);

	    _this.layout.classList.add("landing-ui-field-color");

	    main_core.Dom.append(_this.processor.getLayout(), _this.layout);

	    _this.processor.subscribe('onChange', _this.onChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.processor.subscribe('onReset', _this.onReset.bind(babelHelpers.assertThisInitialized(_this)));

	    return _this;
	  }

	  babelHelpers.createClass(ColorField, [{
	    key: "getInlineProperties",
	    value: function getInlineProperties() {
	      return this.processor.getVariableName();
	    }
	  }, {
	    key: "getComputedProperties",
	    value: function getComputedProperties() {
	      return this.processor.getProperty();
	    }
	  }, {
	    key: "getPseudoElement",
	    value: function getPseudoElement() {
	      return this.processor.getPseudoClass();
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.changeHandler({
	        className: this.processor.getClassName(),
	        style: this.processor.getStyle()
	      }, this.items, this.postfix, this.property);
	    }
	  }, {
	    key: "onReset",
	    value: function onReset() {
	      this.resetHandler(this.items, this.postfix, this.property);
	    } // todo: what a value must return? hsla? string?

	  }, {
	    key: "getValue",
	    value: function getValue() {
	      // todo: need convert processor value to obj? add toObj method to values
	      return this.processor.getValue();
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var _this2 = this;

	      var processorValue = null; // now for multiple properties get just last value. Maybe, need object-like values

	      this.getInlineProperties().forEach(function (prop) {
	        if (prop in value && !_this2.processor.isNullValue(value[prop])) {
	          if (!main_core.Type.isObject(processorValue)) {
	            processorValue = {};
	          }

	          processorValue[prop] = value[prop];
	        }
	      });
	      var defaultValue = null;
	      this.getComputedProperties().forEach(function (prop) {
	        if (prop in value && !_this2.processor.isNullValue(value[prop])) {
	          if (!main_core.Type.isObject(defaultValue)) {
	            defaultValue = {};
	          }

	          defaultValue[prop] = value[prop];
	        }
	      });

	      if (processorValue !== null) {
	        this.processor.setProcessorValue(processorValue);
	      } else {
	        this.processor.setDefaultValue(defaultValue);
	      }
	    }
	  }, {
	    key: "onFrameLoad",
	    value: function onFrameLoad() {
	      // todo: now not work with "group select", can use just any node from elements. If group - need forEach
	      var value = this.data.styleNode.getValue(true);
	      this.setValue(value.style);
	    }
	  }]);
	  return ColorField;
	}(landing_ui_field_basefield.BaseField);

	exports.ColorField = ColorField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX.Main,BX.Event,BX.Landing,BX.Landing,BX));
//# sourceMappingURL=color_field.bundle.js.map
