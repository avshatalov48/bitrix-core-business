this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,landing_ui_field_basefield,main_core_events,main_core,ui_draganddrop_draggable,landing_ui_component_internal,landing_loc,landing_pageobject) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	var Opacity = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(Opacity, _EventEmitter);

	  function Opacity() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, Opacity);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Opacity).call(this));

	    _this.setEventNamespace('BX.Landing.UI.Field.Color.Opacity');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.options = _objectSpread({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onPickerDragStart = _this.onPickerDragStart.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPickerDragMove = _this.onPickerDragMove.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onPickerDragEnd = _this.onPickerDragEnd.bind(babelHelpers.assertThisInitialized(_this)); // @fixme: Add 'context' parameter for Draggable

	    _this.draggable = new window.top.BX.UI.DragAndDrop.Draggable({
	      container: _this.getLayout(),
	      draggable: '.landing-ui-field-color-opacity-picker',
	      type: ui_draganddrop_draggable.Draggable.HEADLESS
	    });

	    _this.draggable.subscribe('start', _this.onPickerDragStart);

	    _this.draggable.subscribe('move', _this.onPickerDragMove);

	    _this.draggable.subscribe('end', _this.onPickerDragEnd);

	    return _this;
	  }

	  babelHelpers.createClass(Opacity, [{
	    key: "getLayout",
	    value: function getLayout() {
	      var _this2 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-opacity\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this2.getPicker(), _this2.getColorLayout());
	      });
	    }
	  }, {
	    key: "getColorLayout",
	    value: function getColorLayout() {
	      return this.cache.remember('colorLayout', function () {
	        return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-color-opacity-color\"></div>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getColorLayoutWidth",
	    value: function getColorLayoutWidth() {
	      var _this3 = this;

	      return this.cache.remember('colorLayoutWidth', function () {
	        return _this3.getColorLayout().getBoundingClientRect().width - 6;
	      });
	    }
	  }, {
	    key: "getPicker",
	    value: function getPicker() {
	      return this.cache.remember('picker', function () {
	        return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div \n\t\t\t\t\tclass=\"landing-ui-field-color-opacity-picker\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t></div>\n\t\t\t"])), landing_loc.Loc.getMessage('LANDING_COLORPICKER_FIELD_CHANGE_COLOR_OPACITY'));
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var pickerLeft = main_core.Text.toNumber(main_core.Dom.style(this.getPicker(), 'left'));
	      var layoutWidth = main_core.Text.toNumber(this.getLayout().getBoundingClientRect().width);
	      return 1 - (pickerLeft / layoutWidth).toFixed(1);
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(_ref) {
	      var parsedColor = _ref.parsedColor,
	          _ref$skipOpacity = _ref.skipOpacity,
	          skipOpacity = _ref$skipOpacity === void 0 ? false : _ref$skipOpacity;
	      var from = "rgba(".concat([parsedColor.slice(0, 3), 100].join(', '), ")");
	      var to = "rgba(".concat([parsedColor.slice(0, 3), 0].join(', '), ")");
	      main_core.Dom.style(this.getColorLayout(), {
	        background: "linear-gradient(to right, ".concat(from, " 0%, ").concat(to, " 100%)")
	      });

	      if (!skipOpacity) {
	        var opacity = parsedColor[3] || 0;
	        var leftPercent = 100 - opacity * 100;
	        main_core.Dom.style(this.getPicker(), {
	          left: "calc(".concat(leftPercent, "% - ").concat(leftPercent === 100 ? '6px' : '0px', ")")
	        });
	      }
	    }
	  }, {
	    key: "onPickerDragStart",
	    value: function onPickerDragStart() {
	      this.cache.set('pickerStartPos', {
	        left: main_core.Text.toNumber(main_core.Dom.style(this.getPicker(), 'left'))
	      });
	      var wrapper = landing_pageobject.PageObject.getRootWindow().document.querySelector('.landing-ui-view-wrapper');
	      main_core.Dom.style(wrapper, 'pointer-events', 'none');
	    }
	  }, {
	    key: "onPickerDragMove",
	    value: function onPickerDragMove(event) {
	      var _event$getData = event.getData(),
	          offsetX = _event$getData.offsetX;

	      var _this$cache$get = this.cache.get('pickerStartPos'),
	          left = _this$cache$get.left;

	      var leftPos = Math.min(Math.max(left + offsetX, 0), this.getColorLayoutWidth());
	      main_core.Dom.style(this.getPicker(), {
	        left: "".concat(leftPos, "px")
	      });
	      this.emit('onChange');
	    }
	  }, {
	    key: "onPickerDragEnd",
	    value: function onPickerDragEnd() {
	      var wrapper = landing_pageobject.PageObject.getRootWindow().document.querySelector('.landing-ui-view-wrapper');
	      main_core.Dom.style(wrapper, 'pointer-events', null);
	    }
	  }]);
	  return Opacity;
	}(main_core_events.EventEmitter);

	var _templateObject$1, _templateObject2$1, _templateObject3$1, _templateObject4;
	var ColorPickerField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ColorPickerField, _BaseField);

	  function ColorPickerField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ColorPickerField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ColorPickerField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.ColorPickerField');

	    _this.setLayoutClass('landing-ui-field-colorpicker');

	    main_core.Dom.append(_this.getColorLayout(), _this.input);

	    _this.setValue(_this.options.value);

	    return _this;
	  }

	  babelHelpers.createClass(ColorPickerField, [{
	    key: "getUid",
	    value: function getUid() {
	      return this.cache.remember('uid', function () {
	        ColorPickerField.id += 1;
	        return "".concat(main_core.Text.getRandom()).concat(ColorPickerField.id);
	      });
	    }
	  }, {
	    key: "getColorLabelInner",
	    value: function getColorLabelInner() {
	      return this.cache.remember('colorLabelInner', function () {
	        return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<span class=\"landing-ui-field-colorpicker-label-inner\"></span>\n\t\t\t"])));
	      });
	    }
	  }, {
	    key: "getColorLabel",
	    value: function getColorLabel() {
	      var _this2 = this;

	      return this.cache.remember('colorLabel', function () {
	        return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label \n\t\t\t\t\tclass=\"landing-ui-field-colorpicker-label\"\n\t\t\t\t\tfor=\"", "\"\n\t\t\t\t\ttitle=\"", "\"\n\t\t\t\t>\n\t\t\t\t\t", "\n\t\t\t\t</label>\n\t\t\t"])), _this2.getUid(), landing_loc.Loc.getMessage('LANDING_COLORPICKER_FIELD_CHANGE_COLOR_TITLE'), _this2.getColorLabelInner());
	      });
	    }
	  }, {
	    key: "getColorInput",
	    value: function getColorInput() {
	      var _this3 = this;

	      return this.cache.remember('colorInput', function () {
	        return main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\ttype=\"color\" \n\t\t\t\t\tclass=\"landing-ui-field-colorpicker-input\"\n\t\t\t\t\tid=\"", "\"\n\t\t\t\t\toninput=\"", "\"\n\t\t\t\t\tonchange=\"", "\"\n\t\t\t\t>\n\t\t\t"])), _this3.getUid(), _this3.onInputChange.bind(_this3), _this3.onInputChange.bind(_this3));
	      });
	    }
	  }, {
	    key: "onInputChange",
	    value: function onInputChange() {
	      this.setValue(this.getColorInput().value, false, true);
	    }
	  }, {
	    key: "getColorLayout",
	    value: function getColorLayout() {
	      var _this4 = this;

	      return this.cache.remember('colorLayout', function () {
	        return main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-colorpicker-layout\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this4.getColorLabel(), _this4.getColorInput(), _this4.getOpacityField().getLayout());
	      });
	    }
	  }, {
	    key: "getOpacityField",
	    value: function getOpacityField() {
	      var _this5 = this;

	      return this.cache.remember('opacityField', function () {
	        return new Opacity({
	          onChange: function onChange() {
	            var parsedValue = ColorPickerField.parseHex(_this5.getColorInput().value);
	            parsedValue[3] = _this5.getOpacityField().getValue();
	            main_core.Dom.style(_this5.getColorLabelInner(), {
	              backgroundColor: ColorPickerField.toRgba.apply(ColorPickerField, babelHelpers.toConsumableArray(parsedValue))
	            });

	            _this5.emit('onChange');
	          }
	        });
	      }); // return this.cache.remember('opacityField', () => {
	      // 	const createOpacityItems = () => {
	      // 		return Array.from({length: 101}, (item, index) => {
	      // 			return {name: `${index}%`, value: `${(100 - index) / 100}`};
	      // 		});
	      // 	};
	      //
	      // 	return new window.top.BX.Landing.UI.Field.Range({
	      // 		title: Loc.getMessage('LANDING_COLORPICKER_FIELD_OPACITY_TITLE'),
	      // 		items: createOpacityItems(),
	      // 		onChange: () => {
	      // 			this.emit('onChange');
	      // 		},
	      // 	});
	      // });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      var preventEvent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var skipOpacity = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      var parsedValue = ColorPickerField.parseHex(value);
	      var hex = ColorPickerField.toHex.apply(ColorPickerField, babelHelpers.toConsumableArray(parsedValue));

	      if (value.length === 7) {
	        parsedValue[3] = this.getOpacityField().getValue();
	      }

	      main_core.Dom.style(this.getColorLabelInner(), {
	        backgroundColor: ColorPickerField.toRgba(parsedValue)
	      });
	      this.getColorInput().value = hex.slice(0, 7);
	      this.getOpacityField().setValue({
	        parsedColor: parsedValue,
	        skipOpacity: skipOpacity
	      });

	      if (!preventEvent) {
	        this.emit('onChange');
	      }
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var parsedHex = ColorPickerField.parseHex(this.getColorInput().value);
	      parsedHex[3] = this.getOpacityField().getValue();
	      return ColorPickerField.toHex.apply(ColorPickerField, babelHelpers.toConsumableArray(parsedHex));
	    }
	  }], [{
	    key: "prepareHex",
	    value: function prepareHex(hex) {
	      if (main_core.Type.isStringFilled(hex)) {
	        var preparedHex = hex.replace('#', '');

	        if (preparedHex.length === 3) {
	          return "#".concat(preparedHex.split('').reduce(function (acc, item) {
	            return "".concat(acc).concat(item).concat(item);
	          }, ''));
	        }
	      }

	      return hex;
	    }
	  }, {
	    key: "parseHex",
	    value: function parseHex(hex) {
	      hex = ColorPickerField.fillHex(hex);
	      var parts = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})?$/i.exec(hex);

	      if (!parts) {
	        parts = [0, 0, 0, 1];
	      } else {
	        parts = [parseInt(parts[1], 16), parseInt(parts[2], 16), parseInt(parts[3], 16), parseInt(100 * (parseInt(parts[4] || 'ff', 16) / 255)) / 100];
	      }

	      return parts;
	    }
	  }, {
	    key: "fillHex",
	    value: function fillHex(hex, fillAlpha) {
	      if (hex.length === 4 || fillAlpha && hex.length === 5) {
	        hex = hex.replace(/([a-f0-9])/gi, '$1$1');
	      }

	      if (fillAlpha && hex.length === 7) {
	        hex += 'ff';
	      }

	      return hex;
	    }
	  }, {
	    key: "toHex",
	    value: function toHex() {
	      for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
	        args[_key] = arguments[_key];
	      }

	      args[3] = typeof args[3] === 'undefined' ? 1 : args[3];
	      args[3] = parseInt(255 * args[3]);
	      return "#".concat(args.map(function (part) {
	        part = part.toString(16);
	        return part.length === 1 ? "0".concat(part) : part;
	      }).join(''));
	    }
	  }, {
	    key: "hexToRgba",
	    value: function hexToRgba(hex) {
	      return "rgba(".concat(this.parseHex(hex).join(', '), ")");
	    }
	  }, {
	    key: "toRgba",
	    value: function toRgba() {
	      for (var _len2 = arguments.length, args = new Array(_len2), _key2 = 0; _key2 < _len2; _key2++) {
	        args[_key2] = arguments[_key2];
	      }

	      return "rgba(".concat(args.join(', '), ")");
	    }
	  }]);
	  return ColorPickerField;
	}(landing_ui_field_basefield.BaseField);
	babelHelpers.defineProperty(ColorPickerField, "id", 0);

	exports.ColorPickerField = ColorPickerField;

}((this.BX.Landing.Ui.Field = this.BX.Landing.Ui.Field || {}),BX.Landing.UI.Field,BX.Event,BX,BX.UI.DragAndDrop,BX.Landing.UI.Component,BX.Landing,BX.Landing));
//# sourceMappingURL=colorpickerfield.bundle.js.map
