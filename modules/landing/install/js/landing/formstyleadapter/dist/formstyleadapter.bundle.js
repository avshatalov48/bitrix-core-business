this.BX = this.BX || {};
(function (exports,main_core,main_core_events,landing_ui_form_styleform,landing_loc,landing_ui_field_colorpickerfield,landing_backend,landing_env,landing_ui_field_color,landing_pageobject,landing_ui_panel_formsettingspanel) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var themesMap = new Map();
	themesMap.set('business-light', {
	  theme: 'business-light',
	  dark: false,
	  style: '',
	  color: {
	    primary: '#0f58d0ff',
	    primaryText: '#ffffffff',
	    background: '#ffffffff',
	    text: '#000000ff',
	    fieldBackground: '#00000011',
	    fieldFocusBackground: '#ffffffff',
	    fieldBorder: '#00000016'
	  },
	  shadow: true,
	  font: {
	    uri: '',
	    family: ''
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('business-dark', {
	  theme: 'business-dark',
	  dark: true,
	  style: '',
	  color: {
	    primary: '#0f58d0ff',
	    primaryText: '#ffffffff',
	    background: '#282d30ff',
	    text: '#ffffffff',
	    fieldBackground: '#ffffff11',
	    fieldFocusBackground: '#00000028',
	    fieldBorder: '#ffffff16'
	  },
	  shadow: true,
	  font: {
	    uri: '',
	    family: ''
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('modern-light', {
	  theme: 'modern-light',
	  dark: false,
	  style: 'modern',
	  color: {
	    primary: '#ffd110ff',
	    primaryText: '#000000ff',
	    background: '#ffffffff',
	    text: '#000000ff',
	    fieldBackground: '#00000000',
	    fieldFocusBackground: '#00000000',
	    fieldBorder: '#00000011'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap&subset=cyrillic',
	    family: 'Open Sans'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('modern-dark', {
	  theme: 'modern-dark',
	  dark: true,
	  style: 'modern',
	  color: {
	    primary: '#ffd110ff',
	    primaryText: '#000000ff',
	    background: '#282d30ff',
	    text: '#ffffffff',
	    fieldBackground: '#00000000',
	    fieldFocusBackground: '#00000000',
	    fieldBorder: '#ffffff11'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap&subset=cyrillic',
	    family: 'Open Sans'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('classic-light', {
	  theme: 'classic-light',
	  dark: false,
	  style: '',
	  color: {
	    primary: '#000000ff',
	    primaryText: '#ffffffff',
	    background: '#ffffffff',
	    text: '#000000ff',
	    fieldBackground: '#00000011',
	    fieldFocusBackground: '#0000000a',
	    fieldBorder: '#00000011'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&display=swap&subset=cyrillic',
	    family: 'PT Serif'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('classic-dark', {
	  theme: 'classic-dark',
	  dark: true,
	  style: '',
	  color: {
	    primary: '#ffffffff',
	    primaryText: '#000000ff',
	    background: '#000000ff',
	    text: '#ffffffff',
	    fieldBackground: '#ffffff11',
	    fieldFocusBackground: '#ffffff0a',
	    fieldBorder: '#ffffff11'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&display=swap&subset=cyrillic',
	    family: 'PT Serif'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('fun-light', {
	  theme: 'fun-light',
	  dark: false,
	  style: '',
	  color: {
	    primary: '#f09b22ff',
	    primaryText: '#000000ff',
	    background: '#ffffffff',
	    text: '#000000ff',
	    fieldBackground: '#f09b2211',
	    fieldFocusBackground: '#0000000a',
	    fieldBorder: '#00000011'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=Pangolin&display=swap&subset=cyrillic',
	    family: 'Pangolin'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('fun-dark', {
	  theme: 'fun-dark',
	  dark: true,
	  style: '',
	  color: {
	    primary: '#f09b22ff',
	    primaryText: '#000000ff',
	    background: '#221400ff',
	    text: '#ffffffff',
	    fieldBackground: '#f09b2211',
	    fieldFocusBackground: '#ffffff0a',
	    fieldBorder: '#f09b220a'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=Pangolin&display=swap&subset=cyrillic',
	    family: 'Pangolin'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('pixel-light', {
	  theme: 'pixel-light',
	  dark: true,
	  style: '',
	  color: {
	    primary: '#00a74cff',
	    primaryText: '#ffffffff',
	    background: '#282d30ff',
	    text: '#90ee90ff',
	    fieldBackground: '#ffffff11',
	    fieldFocusBackground: '#00000028',
	    fieldBorder: '#ffffff16'
	  },
	  shadow: true,
	  font: {
	    uri: 'https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap&subset=cyrillic',
	    family: 'Press Start 2P'
	  },
	  border: {
	    left: false,
	    top: false,
	    bottom: true,
	    right: false
	  }
	});
	themesMap.set('pixel-dark', _objectSpread(_objectSpread({}, themesMap.get('pixel-light')), {}, {
	  theme: 'pixel-dark'
	}));

	function ownKeys$1(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread$1(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys$1(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys$1(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @memberOf BX.Landing
	 */
	var FormStyleAdapter = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(FormStyleAdapter, _EventEmitter);
	  function FormStyleAdapter(options) {
	    var _this;
	    babelHelpers.classCallCheck(this, FormStyleAdapter);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(FormStyleAdapter).call(this));
	    _this.setEventNamespace('BX.Landing.FormStyleAdapter');
	    _this.options = _objectSpread$1({}, options);
	    _this.cache = new main_core.Cache.MemoryCache();
	    _this.onDebouncedFormChange = main_core.Runtime.debounce(_this.onDebouncedFormChange, 500);
	    return _this;
	  }
	  babelHelpers.createClass(FormStyleAdapter, [{
	    key: "setFormOptions",
	    value: function setFormOptions(options) {
	      this.cache.set('formOptions', _objectSpread$1({}, options));
	    }
	  }, {
	    key: "getFormOptions",
	    value: function getFormOptions() {
	      return this.cache.get('formOptions');
	    }
	  }, {
	    key: "load",
	    value: function load() {
	      var _this2 = this;
	      if (main_core.Text.capitalize(landing_env.Env.getInstance().getOptions().params.type) === 'SMN') {
	        this.setFormOptions({
	          data: {
	            design: main_core.Runtime.clone(this.getCrmForm().design)
	          }
	        });
	        return Promise.resolve(this);
	      }
	      return main_core.Runtime.loadExtension('crm.form.client').then(function (_ref) {
	        var FormClient = _ref.FormClient;
	        if (FormClient) {
	          return FormClient.getInstance().getOptions(_this2.options.formId).then(function (result) {
	            _this2.setFormOptions(main_core.Runtime.merge(main_core.Runtime.clone(result), {
	              data: {
	                design: main_core.Runtime.clone(_this2.getCrmForm().design)
	              }
	            }));
	            return _this2;
	          });
	        }
	        return null;
	      });
	    }
	  }, {
	    key: "getThemeField",
	    value: function getThemeField() {
	      var _this3 = this;
	      return this.cache.remember('themeField', function () {
	        var theme = _this3.getFormOptions().data.design.theme;
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.Landing.UI.Field.Dropdown({
	          selector: 'theme',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_TITLE'),
	          content: main_core.Type.isString(theme) ? theme.split('-')[0] : '',
	          onChange: _this3.onThemeChange.bind(_this3),
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_BUSINESS'),
	            value: 'business'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_MODERN'),
	            value: 'modern'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_CLASSIC'),
	            value: 'classic'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_FUN'),
	            value: 'fun'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_THEME_FIELD_ITEM_PIXEL'),
	            value: 'pixel'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getDarkField",
	    value: function getDarkField() {
	      var _this4 = this;
	      return this.cache.remember('darkField', function () {
	        var theme = _this4.getFormOptions().data.design.theme;
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.Landing.UI.Field.Dropdown({
	          selector: 'dark',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_DARK_FIELD_TITLE'),
	          content: main_core.Type.isString(theme) ? theme.split('-')[1] : '',
	          onChange: _this4.onThemeChange.bind(_this4),
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_DARK_FIELD_ITEM_LIGHT'),
	            value: 'light'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_DARK_FIELD_ITEM_DARK'),
	            value: 'dark'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "onThemeChange",
	    value: function onThemeChange() {
	      var themeId = this.getStyleForm().serialize().theme;
	      var theme = themesMap.get(themeId);
	      if (theme) {
	        if (main_core.Type.isPlainObject(theme.color)) {
	          this.getPrimaryColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.primary)
	          });
	          this.getPrimaryTextColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.primaryText)
	          });
	          this.getBackgroundColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.background)
	          });
	          this.getTextColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.text)
	          });
	          this.getFieldBackgroundColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.fieldBackground)
	          });
	          this.getFieldFocusBackgroundColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.fieldFocusBackground)
	          });
	          this.getFieldBorderColorField().setValue({
	            '--color': FormStyleAdapter.prepareColorFieldValue(theme.color.fieldBorder)
	          });
	        }
	        this.getStyleField().setValue(theme.style);
	        if (main_core.Type.isBoolean(theme.shadow)) {
	          this.getShadowField().setValue(theme.shadow);
	        }
	        if (main_core.Type.isPlainObject(theme.font)) {
	          var font = _objectSpread$1({}, theme.font);
	          if (!main_core.Type.isStringFilled(font.family)) {
	            font.family = landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT');
	          }
	          this.getFontField().setValue(font);
	        }
	        if (main_core.Type.isPlainObject(theme.border)) {
	          var borders = Object.entries(theme.border).reduce(function (acc, _ref2) {
	            var _ref3 = babelHelpers.slicedToArray(_ref2, 2),
	              key = _ref3[0],
	              value = _ref3[1];
	            if (value) {
	              acc.push(key);
	            }
	            return acc;
	          }, []);
	          this.getBorderField().setValue(borders);
	        }
	      }
	    }
	  }, {
	    key: "getShadowField",
	    value: function getShadowField() {
	      var _this5 = this;
	      return this.cache.remember('shadow', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.Landing.UI.Field.Dropdown({
	          selector: 'shadow',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_SHADOW'),
	          content: _this5.getFormOptions().data.design.shadow,
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_SHADOW_USE'),
	            value: true
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_SHADOW_NOT_USE'),
	            value: false
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getStyleField",
	    value: function getStyleField() {
	      var _this6 = this;
	      return this.cache.remember('styleField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        return new rootWindow.BX.Landing.UI.Field.Dropdown({
	          selector: 'style',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_STYLE_FIELD_TITLE'),
	          content: _this6.getFormOptions().data.design.style,
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_STYLE_FIELD_ITEM_STANDARD'),
	            value: ''
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_STYLE_FIELD_ITEM_MODERN'),
	            value: 'modern'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getPrimaryColorField",
	    value: function getPrimaryColorField() {
	      var _this7 = this;
	      return this.cache.remember('primaryColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'primary',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_PRIMARY_COLOR'),
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this7.getFormOptions().data.design.color.primary)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getPrimaryTextColorField",
	    value: function getPrimaryTextColorField() {
	      var _this8 = this;
	      return this.cache.remember('primaryTextColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'primaryText',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_PRIMARY_TEXT_COLOR'),
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this8.getFormOptions().data.design.color.primaryText)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getBackgroundColorField",
	    value: function getBackgroundColorField() {
	      var _this9 = this;
	      return this.cache.remember('backgroundColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'background',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BACKGROUND_COLOR'),
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this9.getFormOptions().data.design.color.background)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getTextColorField",
	    value: function getTextColorField() {
	      var _this10 = this;
	      return this.cache.remember('textColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'text',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_TEXT_COLOR'),
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this10.getFormOptions().data.design.color.text)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getFieldBackgroundColorField",
	    value: function getFieldBackgroundColorField() {
	      var _this11 = this;
	      return this.cache.remember('fieldBackgroundColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'fieldBackground',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_BACKGROUND_COLOR'),
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this11.getFormOptions().data.design.color.fieldBackground)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getFieldFocusBackgroundColorField",
	    value: function getFieldFocusBackgroundColorField() {
	      var _this12 = this;
	      return this.cache.remember('fieldFocusBackgroundColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'fieldFocusBackground',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_FOCUS_BACKGROUND_COLOR'),
	          value: _this12.getFormOptions().data.design.color.fieldFocusBackground,
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this12.getFormOptions().data.design.color.fieldFocusBackground)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getFieldBorderColorField",
	    value: function getFieldBorderColorField() {
	      var _this13 = this;
	      return this.cache.remember('fieldBorderColorField', function () {
	        var rootWindow = landing_pageobject.PageObject.getRootWindow();
	        var field = new rootWindow.BX.Landing.UI.Field.ColorField({
	          selector: 'fieldBorder',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FIELD_BORDER_COLOR'),
	          value: _this13.getFormOptions().data.design.color.fieldBorder,
	          subtype: 'color'
	        });
	        main_core.Dom.hide(field.layout.querySelector('.landing-ui-field-color-primary'));
	        field.setValue({
	          '--color': FormStyleAdapter.prepareColorFieldValue(_this13.getFormOptions().data.design.color.fieldBorder)
	        });
	        return field;
	      });
	    }
	  }, {
	    key: "getFontField",
	    value: function getFontField() {
	      var _this14 = this;
	      return this.cache.remember('fontField', function () {
	        var value = _objectSpread$1({}, _this14.getFormOptions().data.design.font);
	        if (!main_core.Type.isStringFilled(value.family)) {
	          value.family = landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT');
	        }
	        return new BX.Landing.UI.Field.Font({
	          selector: 'font',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT'),
	          headlessMode: true,
	          value: value
	        });
	      });
	    }
	  }, {
	    key: "getBorderField",
	    value: function getBorderField() {
	      var _this15 = this;
	      return this.cache.remember('borderField', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          selector: 'border',
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER'),
	          value: function () {
	            var border = _this15.getFormOptions().data.design.border;
	            return Object.entries(border).reduce(function (acc, _ref4) {
	              var _ref5 = babelHelpers.slicedToArray(_ref4, 2),
	                key = _ref5[0],
	                value = _ref5[1];
	              if (value) {
	                acc.push(key);
	              }
	              return acc;
	            }, []);
	          }(),
	          items: [{
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_LEFT'),
	            value: 'left'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_RIGHT'),
	            value: 'right'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_TOP'),
	            value: 'top'
	          }, {
	            name: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_BORDER_BOTTOM'),
	            value: 'bottom'
	          }]
	        });
	      });
	    }
	  }, {
	    key: "getStyleForm",
	    value: function getStyleForm() {
	      var _this16 = this;
	      var collapsed = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : true;
	      return this.cache.remember('styleForm', function () {
	        return new landing_ui_form_styleform.StyleForm({
	          title: landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FORM_TITLE'),
	          fields: [_this16.getThemeField(), _this16.getDarkField(), _this16.getStyleField(), _this16.getShadowField(), _this16.getPrimaryColorField(), _this16.getPrimaryTextColorField(), _this16.getBackgroundColorField(), _this16.getTextColorField(), _this16.getFieldBackgroundColorField(), _this16.getFieldFocusBackgroundColorField(), _this16.getFieldBorderColorField(), _this16.getFontField(), _this16.getBorderField()],
	          onChange: main_core.Runtime.throttle(_this16.onFormChange.bind(_this16), 16),
	          serializeModifier: function serializeModifier(value) {
	            value.theme = "".concat(value.theme, "-").concat(value.dark);
	            value.dark = value.dark === 'dark';
	            value.shadow = main_core.Text.toBoolean(value.shadow);
	            value.color = {
	              primary: FormStyleAdapter.convertColorFieldValueToHexa(value.primary.getHex(), value.primary.getOpacity()),
	              primaryText: FormStyleAdapter.convertColorFieldValueToHexa(value.primaryText.getHex(), value.primaryText.getOpacity()),
	              text: FormStyleAdapter.convertColorFieldValueToHexa(value.text.getHex(), value.text.getOpacity()),
	              background: FormStyleAdapter.convertColorFieldValueToHexa(value.background.getHex(), value.background.getOpacity()),
	              fieldBackground: FormStyleAdapter.convertColorFieldValueToHexa(value.fieldBackground.getHex(), value.fieldBackground.getOpacity()),
	              fieldFocusBackground: FormStyleAdapter.convertColorFieldValueToHexa(value.fieldFocusBackground.getHex(), value.fieldFocusBackground.getOpacity()),
	              fieldBorder: FormStyleAdapter.convertColorFieldValueToHexa(value.fieldBorder.getHex(), value.fieldBorder.getOpacity())
	            };
	            value.border = {
	              left: value.border.includes('left'),
	              right: value.border.includes('right'),
	              top: value.border.includes('top'),
	              bottom: value.border.includes('bottom')
	            };
	            if (value.font.family === landing_loc.Loc.getMessage('LANDING_FORM_STYLE_ADAPTER_FONT_DEFAULT')) {
	              value.font.family = '';
	              value.font.uri = '';
	            }
	            delete value.primary;
	            delete value.primaryText;
	            delete value.text;
	            delete value.background;
	            delete value.fieldBackground;
	            delete value.fieldFocusBackground;
	            delete value.fieldBorder;
	            return value;
	          },
	          collapsed: collapsed,
	          specialType: 'crm_forms'
	        });
	      });
	    }
	  }, {
	    key: "getCrmForm",
	    value: function getCrmForm() {
	      var formApp = main_core.Reflection.getClass('b24form.App');
	      if (formApp) {
	        if (this.options.instanceId) {
	          return formApp.get(this.options.instanceId);
	        }
	        return formApp.list()[0];
	      }
	      return null;
	    }
	  }, {
	    key: "onFormChange",
	    value: function onFormChange(event) {
	      var currentFormOptions = this.getFormOptions();
	      var designOptions = {
	        data: {
	          design: event.getTarget().serialize()
	        }
	      };
	      var mergedOptions = main_core.Runtime.merge(currentFormOptions, designOptions);
	      this.setFormOptions(mergedOptions);
	      this.getCrmForm().design.adjust(mergedOptions.data.design);
	      var formSettingsPanel = landing_ui_panel_formsettingspanel.FormSettingsPanel.getInstance();
	      if (formSettingsPanel.isShown()) {
	        var initialOptions = formSettingsPanel.getInitialFormOptions();
	        var currentOptions = formSettingsPanel.getFormOptions();
	        initialOptions.data.design = mergedOptions.data.design;
	        formSettingsPanel.setInitialFormOptions(initialOptions);
	        currentOptions.data.design = mergedOptions.data.design;
	        formSettingsPanel.setFormOptions(currentOptions);
	      }
	      this.onDebouncedFormChange();
	    } // eslint-disable-next-line class-methods-use-this
	  }, {
	    key: "isCrmFormPage",
	    value: function isCrmFormPage() {
	      return landing_env.Env.getInstance().getSpecialType() === 'crm_forms';
	    }
	  }, {
	    key: "saveFormDesign",
	    value: function saveFormDesign() {
	      var _this17 = this;
	      return main_core.Runtime.loadExtension('crm.form.client').then(function (_ref6) {
	        var FormClient = _ref6.FormClient;
	        if (FormClient) {
	          var formClient = FormClient.getInstance();
	          var formOptions = _this17.getFormOptions();
	          formClient.resetCache(formOptions.id);
	          return formClient.saveOptions(formOptions);
	        }
	        return null;
	      });
	    }
	  }, {
	    key: "saveBlockDesign",
	    value: function saveBlockDesign() {
	      var _this18 = this;
	      var currentBlock = this.options.currentBlock;
	      var design = this.getFormOptions().data.design;
	      var formNode = currentBlock.node.querySelector('.bitrix24forms');
	      main_core.Dom.attr(formNode, {
	        'data-b24form-design': design,
	        'data-b24form-use-style': 'Y'
	      });
	      main_core.Runtime.loadExtension('crm.form.client').then(function (_ref7) {
	        var FormClient = _ref7.FormClient;
	        if (FormClient) {
	          var formClient = FormClient.getInstance();
	          var formOptions = _this18.getFormOptions();
	          formClient.resetCache(formOptions.id);
	        }
	      });
	      landing_backend.Backend.getInstance().action('Landing\\Block::updateNodes', {
	        block: currentBlock.id,
	        data: {
	          '.bitrix24forms': {
	            attrs: {
	              'data-b24form-design': JSON.stringify(design),
	              'data-b24form-use-style': 'Y'
	            }
	          }
	        },
	        lid: currentBlock.lid,
	        siteId: currentBlock.siteId
	      }, {
	        code: currentBlock.manifest.code
	      }).then(BX.Landing.History.getInstance().push());
	    }
	  }, {
	    key: "onDebouncedFormChange",
	    value: function onDebouncedFormChange() {
	      var _this19 = this;
	      if (this.isCrmFormPage()) {
	        main_core.Runtime.loadExtension('landing.ui.panel.formsettingspanel').then(function (_ref8) {
	          var FormSettingsPanel = _ref8.FormSettingsPanel;
	          var formSettingsPanel = FormSettingsPanel.getInstance();
	          formSettingsPanel.setCurrentBlock(_this19.options.currentBlock);
	          void _this19.saveFormDesign();
	          if (formSettingsPanel.useBlockDesign()) {
	            formSettingsPanel.disableUseBlockDesign();
	          }
	        });
	      } else {
	        this.saveBlockDesign();
	      }
	    }
	  }], [{
	    key: "prepareColorFieldValue",
	    value: function prepareColorFieldValue(color) {
	      return landing_ui_field_colorpickerfield.ColorPickerField.toRgba.apply(landing_ui_field_colorpickerfield.ColorPickerField, babelHelpers.toConsumableArray(landing_ui_field_colorpickerfield.ColorPickerField.parseHex(color)));
	    }
	  }, {
	    key: "convertColorFieldValueToHexa",
	    value: function convertColorFieldValueToHexa(value) {
	      var opacity = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
	      var parsedPrimary = landing_ui_field_colorpickerfield.ColorPickerField.parseHex(value);
	      if (!main_core.Type.isNil(opacity)) {
	        parsedPrimary[3] = opacity;
	      }
	      return landing_ui_field_colorpickerfield.ColorPickerField.toHex.apply(landing_ui_field_colorpickerfield.ColorPickerField, babelHelpers.toConsumableArray(parsedPrimary));
	    }
	  }]);
	  return FormStyleAdapter;
	}(main_core_events.EventEmitter);

	exports.FormStyleAdapter = FormStyleAdapter;

}((this.BX.Landing = this.BX.Landing || {}),BX,BX.Event,BX.Landing.UI.Form,BX.Landing,BX.Landing.Ui.Field,BX.Landing,BX.Landing,BX.Landing.UI.Field,BX.Landing,BX.Landing.UI.Panel));
//# sourceMappingURL=formstyleadapter.bundle.js.map
