this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	var Control = /*#__PURE__*/function () {
	  function Control(node) {
	    babelHelpers.classCallCheck(this, Control);
	    this.node = node;
	    return this;
	  }

	  babelHelpers.createClass(Control, [{
	    key: "setParent",
	    value: function setParent(parent) {
	      this.parent = parent;
	      return this;
	    }
	  }, {
	    key: "setDefaultValue",
	    value: function setDefaultValue(defaultValue) {
	      this.defaultValue = defaultValue;
	      return this;
	    }
	  }, {
	    key: "setChangeHandler",
	    value: function setChangeHandler(onChange) {
	      main_core.Event.bind(this.node, "change", onChange);
	    }
	  }, {
	    key: "setClickHandler",
	    value: function setClickHandler(onClick) {
	      main_core.Event.bind(this.node, "click", onClick);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.parent && this.parent.getValue() !== true ? this.defaultValue : this.getValueInternal();
	    }
	    /**
	     * @private
	     */

	  }, {
	    key: "getValueInternal",
	    value: function getValueInternal() {
	      //if(this.node.type === 'checkbox')
	      //{
	      //	return this.node.checked;
	      //}
	      //return this.node.value;
	      return this.node;
	    }
	  }]);
	  return Control;
	}();

	var _templateObject, _templateObject2;
	var DesignPreview = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(DesignPreview, _EventEmitter);

	  function DesignPreview(form) {
	    var _window$fontsProxyUrl;

	    var _this;

	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    var phrase = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	    var id = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : null;
	    babelHelpers.classCallCheck(this, DesignPreview);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DesignPreview).call(this));

	    _this.setEventNamespace('BX.Landing.SettingsForm.DesignPreview');

	    _this.form = form;
	    _this.phrase = phrase;
	    _this.id = id;
	    _this.options = options;
	    _this.fontProxyUrl = (_window$fontsProxyUrl = window.fontsProxyUrl) !== null && _window$fontsProxyUrl !== void 0 ? _window$fontsProxyUrl : 'fonts.googleapis.com';

	    _this.initControls();

	    _this.initLayout();

	    _this.applyStyles();

	    _this.onApplyStyles = _this.applyStyles.bind(babelHelpers.assertThisInitialized(_this));
	    return _this;
	  }

	  babelHelpers.createClass(DesignPreview, [{
	    key: "initLayout",
	    value: function initLayout() {
	      var _this2 = this;

	      this.createLayout();
	      this.styleNode = document.createElement("style");
	      main_core.Dom.append(this.styleNode, this.layout);
	      main_core.Dom.append(this.layout, this.form);
	      var paramsObserver = {
	        threshold: 1
	      };
	      var observer = new IntersectionObserver(function (entries) {
	        entries.forEach(function (entry) {
	          var availableHeight = document.documentElement.clientHeight - DesignPreview.HEIGHT_PAGE_TITLE_WRAP;

	          if (entry.target.getBoundingClientRect().height <= availableHeight) {
	            if (entry.isIntersecting) {
	              if (!_this2.hasOwnProperty('defaultIntersecting')) {
	                _this2.defaultIntersecting = true;
	              }

	              if (_this2.defaultIntersecting) {
	                _this2.unFixElement();
	              }
	            } else {
	              if (!_this2.hasOwnProperty('defaultIntersecting')) {
	                _this2.defaultIntersecting = false;
	              }

	              if (_this2.defaultIntersecting) {
	                _this2.fixElement();
	              }
	            }
	          }
	        });
	      }, paramsObserver);
	      observer.observe(this.layoutContent.parentNode);
	    }
	  }, {
	    key: "initControls",
	    value: function initControls() {
	      this.controls = {};

	      for (var group in this.options) {
	        if (!this.options.hasOwnProperty(group)) {
	          continue;
	        }

	        for (var key in this.options[group]) {
	          if (!this.options[group].hasOwnProperty(key)) {
	            continue;
	          }

	          if (!this.controls[group]) {
	            this.controls[group] = {};
	          }

	          var control = new Control(this.options[group][key]['control']);
	          control.setChangeHandler(this.applyStyles.bind(this));

	          if (group === 'theme' && key !== 'use') {
	            control.setClickHandler(this.applyStyles.bind(this));
	          }

	          if (group === 'background' && key === 'field') {
	            control.setClickHandler(this.applyStyles.bind(this));
	          }

	          this.controls[group][key] = control;
	        }
	      } // parents and default


	      for (var _group in this.controls) {
	        if (!this.controls.hasOwnProperty(_group)) {
	          continue;
	        }

	        for (var _key in this.controls[_group]) {
	          if (!this.controls[_group].hasOwnProperty(_key)) {
	            continue;
	          }

	          if (_key !== 'use' && this.controls[_group]['use']) {
	            this.controls[_group][_key].setParent(this.controls[_group]['use']);

	            if (this.options[_group][_key]['defaultValue']) {
	              this.controls[_group][_key].setDefaultValue(this.options[_group][_key]['defaultValue']);
	            }
	          }
	        }
	      }

	      if (this.controls.theme.corporateColor.node) {
	        this.controls.theme.corporateColor.node.subscribe('onSelectCustomColor', this.applyStyles.bind(this));
	      }

	      if (this.controls.background.image.node) {
	        this.controls.background.image.node.subscribe('change', this.onApplyStyles.bind(this));
	      }

	      if (this.controls.typo.textColor.node) {
	        main_core_events.EventEmitter.subscribe(this.controls.typo.textColor.node, 'BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
	        main_core_events.EventEmitter.subscribe(this.controls.typo.textColor.node, 'BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
	      }

	      if (this.controls.typo.hColor.node) {
	        main_core_events.EventEmitter.subscribe(this.controls.typo.hColor.node, 'BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
	        main_core_events.EventEmitter.subscribe(this.controls.typo.hColor.node, 'BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
	      }

	      if (this.controls.background.color.node) {
	        main_core_events.EventEmitter.subscribe(this.controls.background.color.node, 'BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
	        main_core_events.EventEmitter.subscribe(this.controls.background.color.node, 'BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
	      }

	      this.panel = BX.Landing.UI.Panel.GoogleFonts.getInstance();
	      main_core.Dom.append(this.panel.layout, document.body);
	      var fieldCode = this.controls.typo.textFont.node;
	      var fieldCodeH = this.controls.typo.hFont.node;

	      if (fieldCode && fieldCodeH) {
	        fieldCode.setAttribute("value", this.convertFont(fieldCode.value));
	        fieldCodeH.setAttribute("value", this.convertFont(fieldCodeH.value));
	        main_core.Event.bind(fieldCode, 'click', this.onCodeClick.bind(this));
	        main_core.Event.bind(fieldCodeH, 'click', this.onCodeClick.bind(this));
	      }
	    }
	  }, {
	    key: "onCodeClick",
	    value: function onCodeClick(event) {
	      var _this3 = this;

	      this.panel.show({
	        hideOverlay: true,
	        context: window
	      }).then(function (font) {
	        var element = event.target;
	        element.setAttribute("value", font.family);

	        _this3.onApplyStyles();
	      });
	    }
	  }, {
	    key: "onApplyStyles",
	    value: function onApplyStyles() {
	      this.applyStyles();
	    }
	  }, {
	    key: "applyStyles",
	    value: function applyStyles() {
	      this.styleNode.innerHTML = this.generateCss();
	    }
	  }, {
	    key: "generateSelectorStart",
	    value: function generateSelectorStart(className) {
	      return '#' + className + ' {';
	    }
	  }, {
	    key: "generateSelectorEnd",
	    value: function generateSelectorEnd(selector) {
	      return selector + ' }';
	    }
	  }, {
	    key: "getCSSPart1",
	    value: function getCSSPart1(css) {
	      var colorPrimary;
	      var setColors = this.controls.theme.baseColors.node;
	      var colorPickerElement;

	      if (this.controls.theme.corporateColor.node) {
	        colorPickerElement = this.controls.theme.corporateColor.node.element;
	      }

	      var activeColorNode;

	      if (setColors) {
	        activeColorNode = setColors.querySelector('.active');
	      }

	      var isActiveColorPickerElement;

	      if (colorPickerElement) {
	        isActiveColorPickerElement = main_core.Dom.hasClass(colorPickerElement, 'active');
	      }

	      if (activeColorNode) {
	        colorPrimary = activeColorNode.dataset.value;
	      }

	      if (isActiveColorPickerElement) {
	        colorPrimary = colorPickerElement.dataset.value;
	      } //for 'design page', if use not checked, use color from 'design site'


	      if (this.controls.theme.use.node) {
	        if (this.controls.theme.use.node.checked === false) {
	          colorPrimary = this.controls.theme.corporateColor.defaultValue;
	        }
	      }

	      if (colorPrimary) {
	        if (colorPrimary[0] !== '#') {
	          colorPrimary = '#' + colorPrimary;
	        }

	        css += "--design-preview-primary: ".concat(colorPrimary, ";");
	      }

	      return css;
	    }
	  }, {
	    key: "getCSSPart2",
	    value: function getCSSPart2(css) {
	      var textColor;
	      var textFont;
	      var hFont;
	      var textSize;
	      var fontWeight;
	      var fontLineHeight;
	      var hColor;
	      var hWeight;

	      if (this.controls.typo.textColor.node) {
	        textColor = this.controls.typo.textColor.node.input.value;
	      }

	      if (this.controls.typo.textFont.node) {
	        textFont = this.controls.typo.textFont.node.value;
	      }

	      if (this.controls.typo.hFont.node) {
	        hFont = this.controls.typo.hFont.node.value;
	      }

	      if (this.controls.typo.textSize.node) {
	        textSize = Math.round(this.controls.typo.textSize.node.value * DesignPreview.DEFAULT_FONT_SIZE) + 'px';
	      }

	      if (this.controls.typo.textWeight.node) {
	        fontWeight = this.controls.typo.textWeight.node.value;
	      }

	      if (this.controls.typo.textLineHeight.node) {
	        fontLineHeight = this.controls.typo.textLineHeight.node.value;
	      }

	      if (this.controls.typo.hColor.node) {
	        hColor = this.controls.typo.hColor.node.input.value;
	      }

	      if (this.controls.typo.hWeight.node) {
	        hWeight = this.controls.typo.hWeight.node.value;
	      }

	      if (this.controls.typo.use.node) {
	        if (this.controls.typo.use.node.checked === false) {
	          textColor = this.controls.typo.textColor.defaultValue;
	          textFont = this.controls.typo.textFont.defaultValue;
	          hFont = this.controls.typo.hFont.defaultValue;
	          textSize = Math.round(this.controls.typo.textSize.defaultValue * DesignPreview.DEFAULT_FONT_SIZE) + 'px';
	          fontWeight = this.controls.typo.textWeight.defaultValue;
	          fontLineHeight = this.controls.typo.textLineHeight.defaultValue;
	          hColor = this.controls.typo.hColor.defaultValue;
	          hWeight = this.controls.typo.hWeight.defaultValue;
	        }
	      }

	      if (textFont) {
	        main_core.Dom.append(this.createFontLink(textFont), this.form);
	      }

	      if (hFont) {
	        main_core.Dom.append(this.createFontLink(hFont), this.form);
	      }

	      css += "--design-preview-color: ".concat(textColor, ";");
	      css += "--design-preview-font-theme: ".concat(textFont, ";");
	      css += "--design-preview-font-size: ".concat(textSize, ";");
	      css += "--design-preview-font-weight: ".concat(fontWeight, ";");
	      css += "--design-preview-line-height: ".concat(fontLineHeight, ";");

	      if (hColor) {
	        css += "--design-preview-color-h: ".concat(hColor, ";");
	      } else {
	        css += "--design-preview-color-h: ".concat(textColor, ";");
	      }

	      if (hWeight) {
	        css += "--design-preview-font-weight-h: ".concat(hWeight, ";");
	      } else {
	        css += "--design-preview-font-weight-h: ".concat(fontWeight, ";");
	      }

	      if (this.controls.typo.hFont.node) {
	        css += "--design-preview-font-h-theme: ".concat(hFont, ";");
	      } else {
	        css += "--design-preview-font-h-theme: ".concat(textFont, ";");
	      }

	      return css;
	    }
	  }, {
	    key: "createFontLink",
	    value: function createFontLink(font) {
	      var link = document.createElement('link');
	      link.rel = 'stylesheet';
	      link.href = 'https://' + this.fontProxyUrl + '/css2?family=';
	      link.href += font.replace(' ', '+');
	      link.href += ':wght@100;200;300;400;500;600;700;800;900';
	      return link;
	    }
	  }, {
	    key: "getCSSPart3",
	    value: function getCSSPart3(css) {
	      var bgColor = this.controls.background.color.node.input.value;
	      var bgFieldNode = this.controls.background.field.node;
	      var bgPictureElement = bgFieldNode.getElementsByClassName('landing-ui-field-image-hidden');
	      var bgPicture = bgPictureElement[0].getAttribute('src');
	      var bgPosition = this.controls.background.position.node.value;

	      if (this.controls.background.use.node.checked === true) {
	        css += "--design-preview-bg: ".concat(bgColor, ";");
	      } else {
	        bgPicture = '';

	        if (this.controls.background.useSite) {
	          if (this.controls.background.useSite.defaultValue === 'Y') {
	            bgColor = this.controls.background.color.defaultValue;
	            bgPicture = this.controls.background.field.defaultValue;
	            bgPosition = this.controls.background.position.defaultValue;
	            css += "--design-preview-bg: ".concat(bgColor, ";");
	          }
	        }
	      }

	      if (this.controls.background.position) {
	        if (bgPosition === 'center') {
	          css += "background-image: url(".concat(bgPicture, ");");
	          css += "background-attachment: scroll;";
	          css += "background-position: center;";
	          css += "background-repeat: no-repeat;";
	          css += "background-size: cover;";
	        }

	        if (bgPosition === 'repeat') {
	          css += "background-image: url(".concat(bgPicture, ");");
	          css += "background-attachment: scroll;";
	          css += "background-position: center;";
	          css += "background-repeat: repeat;";
	          css += "background-size: 50%;";
	        }

	        if (bgPosition === 'center_repeat_y') {
	          css += "background-image: url(".concat(bgPicture, ");");
	          css += "background-attachment: scroll;";
	          css += "background-position: top;";
	          css += "background-repeat: repeat-y;";
	          css += "background-size: 100%;";
	        }
	      }

	      return css;
	    }
	  }, {
	    key: "generateCss",
	    value: function generateCss() {
	      var css;
	      css = this.generateSelectorStart(this.id);
	      css = this.getCSSPart1(css);
	      css = this.getCSSPart2(css);
	      css = this.getCSSPart3(css);
	      css = this.generateSelectorEnd(css);
	      return css;
	    }
	  }, {
	    key: "createLayout",
	    value: function createLayout() {
	      this.layout = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-design-preview-wrap\"></div>"])));
	      this.layoutContent = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\" class=\"landing-design-preview\"><h2 class=\"landing-design-preview-title\">", "</h2><h4 class=\"landing-design-preview-subtitle\">", "</h4><p class=\"landing-design-preview-text\">", "</p><p class=\"landing-design-preview-text\">", "</p><div class=\"\"><a class=\"landing-design-preview-button\">", "</a></div></div>"])), this.id, this.phrase.title, this.phrase.subtitle, this.phrase.text1, this.phrase.text2, this.phrase.button);
	      main_core.Dom.append(this.layoutContent, this.layout);
	    }
	  }, {
	    key: "fixElement",
	    value: function fixElement() {
	      var designPreviewWrap = this.layoutContent.parentNode;
	      var designPreviewWrapPosition = designPreviewWrap.getBoundingClientRect();
	      var paddingDesignPreview = 20;
	      var maxWidth = designPreviewWrapPosition.width - paddingDesignPreview * 2;
	      var designForm = designPreviewWrap.parentNode;
	      var designFormPosition = designForm.getBoundingClientRect();
	      var designPreviewPosition = this.layoutContent.getBoundingClientRect();
	      var bodyWidth = document.body.clientWidth;
	      var paddingDesignForm = 20;
	      var positionFixedRight = bodyWidth - designFormPosition.right + paddingDesignForm;

	      if (designFormPosition.height > designPreviewPosition.height) {
	        var fixedStyle;
	        fixedStyle = 'position: fixed; ';
	        fixedStyle += 'top: 20px; ';
	        fixedStyle += 'margin-top: 0; ';
	        fixedStyle += 'right: ' + positionFixedRight + 'px;';
	        fixedStyle += 'max-width: ' + maxWidth + 'px;';
	        this.layoutContent.setAttribute("style", fixedStyle);
	      }
	    }
	  }, {
	    key: "unFixElement",
	    value: function unFixElement() {
	      this.layoutContent.setAttribute("style", '');
	    }
	  }, {
	    key: "convertFont",
	    value: function convertFont(font) {
	      font = font.replace('g-font-', '');
	      font = font.replaceAll('-', ' ');
	      font = font.replace('ibm ', 'IBM ');
	      font = font.replace('pt ', 'PT ');
	      font = font.replace(/sc(?:(?![a-z]))/i, 'SC');
	      font = font.replace(/jp(?:(?![a-z]))/i, 'JP');
	      font = font.replace(/kr(?:(?![a-z]))/i, 'KR');
	      font = font.replace(/tc(?:(?![a-z]))/i, 'TC');
	      font = font.replace(/(^|\s)\S/g, function (firstSymbol) {
	        return firstSymbol.toUpperCase();
	      });
	      return font;
	    }
	  }]);
	  return DesignPreview;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(DesignPreview, "DEFAULT_FONT_SIZE", 14);
	babelHelpers.defineProperty(DesignPreview, "HEIGHT_PAGE_TITLE_WRAP", 74);

	exports.DesignPreview = DesignPreview;

}((this.BX.Landing.SettingsForm = this.BX.Landing.SettingsForm || {}),BX,BX.Event));
//# sourceMappingURL=designpreview.bundle.js.map
