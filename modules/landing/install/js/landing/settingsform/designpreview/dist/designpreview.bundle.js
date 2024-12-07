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
	    var type = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : null;
	    babelHelpers.classCallCheck(this, DesignPreview);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DesignPreview).call(this));
	    _this.setEventNamespace('BX.Landing.SettingsForm.DesignPreview');
	    _this.form = form;
	    _this.phrase = phrase;
	    _this.id = id;
	    _this.options = options;
	    _this.type = type;
	    window.fontsProxyUrl = (_window$fontsProxyUrl = window.fontsProxyUrl) !== null && _window$fontsProxyUrl !== void 0 ? _window$fontsProxyUrl : 'fonts.googleapis.com';
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
	      this.styleNode = document.createElement('style');
	      main_core.Dom.append(this.styleNode, this.layout);
	      main_core.Dom.append(this.layout, this.form);
	      var paramsObserver = {
	        threshold: 1
	      };
	      var observer = new IntersectionObserver(function (entries) {
	        entries.forEach(function (entry) {
	          var availableHeight = document.documentElement.clientHeight - DesignPreview.HEIGHT_PAGE_TITLE_WRAP;
	          if (entry.target.getBoundingClientRect().height <= availableHeight) {
	            _this2.toggleIntersectionState(entry);
	          }
	        });
	      }, paramsObserver);
	      observer.observe(this.layoutContent.parentNode);
	    }
	  }, {
	    key: "toggleIntersectionState",
	    value: function toggleIntersectionState(entry) {
	      if (entry.isIntersecting) {
	        if (!('defaultIntersecting' in this)) {
	          this.defaultIntersecting = true;
	        }
	        if (this.defaultIntersecting) {
	          this.unFixElement();
	        }
	      } else {
	        if (!('defaultIntersecting' in this)) {
	          this.defaultIntersecting = false;
	        }
	        if (this.defaultIntersecting) {
	          this.fixElement();
	        }
	      }
	    }
	  }, {
	    key: "initControls",
	    value: function initControls() {
	      this.controls = {};
	      this.initOptions();

	      // parents and default
	      var controlsKeys = Object.keys(this.controls);
	      for (var _i = 0, _controlsKeys = controlsKeys; _i < _controlsKeys.length; _i++) {
	        var group = _controlsKeys[_i];
	        if (!(group in this.controls)) {
	          continue;
	        }
	        var keys = Object.keys(this.controls[group]);
	        for (var _i2 = 0, _keys = keys; _i2 < _keys.length; _i2++) {
	          var key = _keys[_i2];
	          if (!(key in this.controls[group])) {
	            continue;
	          }
	          if (key !== 'use' && this.controls[group].use) {
	            this.setupControls(group, key);
	          }
	        }
	      }
	      this.initSubscribes();
	      this.panel = BX.Landing.UI.Panel.GoogleFonts.getInstance();
	      main_core.Dom.append(this.panel.layout, document.body);
	      this.setupFontFields();
	    }
	  }, {
	    key: "initOptions",
	    value: function initOptions() {
	      var optionKeys = Object.keys(this.options);
	      for (var _i3 = 0, _optionKeys = optionKeys; _i3 < _optionKeys.length; _i3++) {
	        var group = _optionKeys[_i3];
	        if (!(group in this.options)) {
	          continue;
	        }
	        var groupKeys = Object.keys(this.options[group]);
	        for (var _i4 = 0, _groupKeys = groupKeys; _i4 < _groupKeys.length; _i4++) {
	          var key = _groupKeys[_i4];
	          if (!(key in this.options[group])) {
	            continue;
	          }
	          if (!this.controls[group]) {
	            this.controls[group] = {};
	          }
	          var control = new Control(this.options[group][key].control);
	          this.initControlHandlers(control, group, key);
	          this.controls[group][key] = control;
	        }
	      }
	    }
	  }, {
	    key: "initControlHandlers",
	    value: function initControlHandlers(control, group, key) {
	      control.setChangeHandler(this.applyStyles.bind(this));
	      if (control.node && key === 'use') {
	        main_core.Event.bind(control.node.parentNode, 'click', this.onCheckboxClick.bind(this, group));
	      }
	      if (group === 'theme' && key !== 'use') {
	        control.setClickHandler(this.applyStyles.bind(this));
	      }
	      if (group === 'background' && key === 'field') {
	        control.setClickHandler(this.applyStyles.bind(this));
	      }
	    }
	  }, {
	    key: "setupControls",
	    value: function setupControls(group, key) {
	      this.controls[group][key].setParent(this.controls[group].use);
	      if (this.options[group][key].defaultValue) {
	        this.controls[group][key].setDefaultValue(this.options[group][key].defaultValue);
	      }
	    }
	  }, {
	    key: "initSubscribes",
	    value: function initSubscribes() {
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
	    }
	  }, {
	    key: "setupFontFields",
	    value: function setupFontFields() {
	      if (this.controls.typo.textFont.node && this.controls.typo.hFont.node) {
	        this.controls.typo.textFont.node.setAttribute('value', this.convertFont(this.controls.typo.textFont.node.value));
	        this.controls.typo.hFont.node.setAttribute('value', this.convertFont(this.controls.typo.hFont.node.value));
	        main_core.Event.bind(this.controls.typo.textFont.node, 'click', this.onCodeClick.bind(this));
	        main_core.Event.bind(this.controls.typo.hFont.node, 'click', this.onCodeClick.bind(this));
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
	        element.setAttribute('value', font.family);
	        _this3.onApplyStyles();
	      })["catch"](function (error) {
	        console.error(error);
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
	      var _this4 = this;
	      this.styleNode.innerHTML = this.generateCss();
	      setTimeout(function () {
	        var layoutHeight = parseInt(window.getComputedStyle(_this4.layoutContent.parentNode).height, 10);
	        var formHeight = parseInt(window.getComputedStyle(_this4.form).height, 10);
	        if (layoutHeight > formHeight) {
	          layoutHeight += 20;
	          BX.Dom.style(_this4.form, 'min-height', "".concat(layoutHeight, "px"));
	          var formSection = _this4.form.querySelector('.ui-form-section');
	          if (formSection) {
	            BX.Dom.style(formSection, 'min-height', "".concat(layoutHeight, "px"));
	          }
	        }
	      }, 1000);
	    }
	  }, {
	    key: "onCheckboxClick",
	    value: function onCheckboxClick(group) {
	      this.controls[group].use.node.check = !this.controls[group].use.node.checked;
	      this.applyStyles();
	    }
	  }, {
	    key: "generateSelectorStart",
	    value: function generateSelectorStart(className) {
	      return "#".concat(className, " {");
	    }
	  }, {
	    key: "generateSelectorEnd",
	    value: function generateSelectorEnd(selector) {
	      return "".concat(selector, " }");
	    }
	  }, {
	    key: "getCSSPart1",
	    value: function getCSSPart1(css) {
	      var colorPrimary = '';
	      var setColors = this.controls.theme.baseColors.node;
	      var colorPickerElement = '';
	      if (this.controls.theme.corporateColor.node) {
	        colorPickerElement = this.controls.theme.corporateColor.node.element;
	      }
	      var activeColorNode = '';
	      if (setColors) {
	        activeColorNode = setColors.querySelector('.active');
	      }
	      var isActiveColorPickerElement = '';
	      if (colorPickerElement) {
	        isActiveColorPickerElement = main_core.Dom.hasClass(colorPickerElement, 'active');
	      }
	      if (activeColorNode) {
	        colorPrimary = activeColorNode.dataset.value;
	      }
	      if (isActiveColorPickerElement) {
	        colorPrimary = colorPickerElement.dataset.value;
	      }

	      // for "design page", if you use the unchecked box, use the color from "design site"
	      if (this.controls.theme.use.node && this.controls.theme.use.node.check === false) {
	        colorPrimary = this.controls.theme.corporateColor.defaultValue;
	      }
	      var preparedCss = css;
	      if (colorPrimary) {
	        if (colorPrimary[0] !== '#') {
	          colorPrimary = "#".concat(colorPrimary);
	        }
	        preparedCss += "--design-preview-primary: ".concat(colorPrimary, ";");
	      }
	      return preparedCss;
	    }
	  }, {
	    key: "getCSSPart2",
	    value: function getCSSPart2(css) {
	      var textColor = this.getControlValue(this.controls.typo.textColor.node, this.controls.typo.textColor.node.input.value);
	      var textFont = this.getControlValue(this.controls.typo.textFont.node, this.controls.typo.textFont.node.value);
	      var hFont = this.getControlValue(this.controls.typo.hFont.node, this.controls.typo.hFont.node.value);
	      var fontWeight = this.getControlValue(this.controls.typo.textWeight.node, this.controls.typo.textWeight.node.value);
	      var fontLineHeight = this.getControlValue(this.controls.typo.textLineHeight.node, this.controls.typo.textLineHeight.node.value);
	      var hColor = this.getControlValue(this.controls.typo.hColor.node, this.controls.typo.hColor.node.input.value);
	      var hWeight = this.getControlValue(this.controls.typo.hWeight.node, this.controls.typo.hWeight.node.value);
	      var textSize = '';
	      if (this.controls.typo.textSize.node) {
	        textSize = "".concat(Math.round(this.controls.typo.textSize.node.value * DesignPreview.DEFAULT_FONT_SIZE), "px");
	      }
	      if (this.controls.typo.use.node && this.controls.typo.use.node.check === false) {
	        textColor = this.controls.typo.textColor.defaultValue;
	        textFont = this.controls.typo.textFont.defaultValue;
	        hFont = this.controls.typo.hFont.defaultValue;
	        textSize = "".concat(Math.round(this.controls.typo.textSize.defaultValue * DesignPreview.DEFAULT_FONT_SIZE), "px");
	        fontWeight = this.controls.typo.textWeight.defaultValue;
	        fontLineHeight = this.controls.typo.textLineHeight.defaultValue;
	        hColor = this.controls.typo.hColor.defaultValue;
	        hWeight = this.controls.typo.hWeight.defaultValue;
	      }
	      this.appendFontLinks(textFont);
	      this.appendFontLinks(hFont);
	      var preparedCss = css;
	      preparedCss += "--design-preview-color: ".concat(textColor, ";");
	      preparedCss += "--design-preview-font-theme: ".concat(textFont, ";");
	      preparedCss += "--design-preview-font-size: ".concat(textSize, ";");
	      preparedCss += "--design-preview-font-weight: ".concat(fontWeight, ";");
	      preparedCss += "--design-preview-line-height: ".concat(fontLineHeight, ";");
	      if (hColor) {
	        preparedCss += "--design-preview-color-h: ".concat(hColor, ";");
	      } else {
	        preparedCss += "--design-preview-color-h: ".concat(textColor, ";");
	      }
	      if (hWeight) {
	        preparedCss += "--design-preview-font-weight-h: ".concat(hWeight, ";");
	      } else {
	        preparedCss += "--design-preview-font-weight-h: ".concat(fontWeight, ";");
	      }
	      if (this.controls.typo.hFont.node) {
	        preparedCss += "--design-preview-font-h-theme: ".concat(hFont, ";");
	      } else {
	        preparedCss += "--design-preview-font-h-theme: ".concat(textFont, ";");
	      }
	      return preparedCss;
	    }
	  }, {
	    key: "createFontLink",
	    value: function createFontLink(font) {
	      var link = document.createElement('link');
	      link.rel = 'stylesheet';
	      link.href = "https://".concat(window.fontsProxyUrl, "/css2?family=");
	      link.href += font.replace(' ', '+');
	      link.href += ':wght@100;200;300;400;500;600;700;800;900';
	      return link;
	    }
	  }, {
	    key: "getControlValue",
	    value: function getControlValue(element, value) {
	      if (element) {
	        return value;
	      }
	      return '';
	    }
	  }, {
	    key: "appendFontLinks",
	    value: function appendFontLinks(font) {
	      if (font) {
	        main_core.Dom.append(this.createFontLink(font), this.form);
	      }
	    }
	  }, {
	    key: "getCSSPart3",
	    value: function getCSSPart3(css) {
	      var preparedCss = css;
	      var bgColor = this.controls.background.color.node.input.value;
	      var bgFieldNode = this.controls.background.field.node;
	      var bgPictureElement = bgFieldNode.getElementsByClassName('landing-ui-field-image-hidden');
	      var bgPicture = bgPictureElement[0].getAttribute('src');
	      var bgPosition = this.controls.background.position.node.value;
	      if (this.controls.background.use.node.check === true) {
	        preparedCss += "--design-preview-bg: ".concat(bgColor, ";");
	      } else {
	        bgPicture = '';
	        if (this.controls.background.useSite && this.controls.background.useSite.defaultValue === 'Y') {
	          bgColor = this.controls.background.color.defaultValue;
	          bgPicture = this.controls.background.field.defaultValue;
	          bgPosition = this.controls.background.position.defaultValue;
	          preparedCss += "--design-preview-bg: ".concat(bgColor, ";");
	        }
	      }
	      if (this.options.background.image.defaultValue && bgPicture === '') {
	        bgPicture = this.options.background.image.defaultValue;
	      }
	      if (bgPicture) {
	        preparedCss += "background-image: url(".concat(bgPicture, ");");
	      }
	      if (this.controls.background.position) {
	        if (bgPosition === 'center') {
	          preparedCss += 'background-attachment: scroll;';
	          preparedCss += 'background-position: center;';
	          preparedCss += 'background-repeat: no-repeat;';
	          preparedCss += 'background-size: cover;';
	        }
	        if (bgPosition === 'repeat') {
	          preparedCss += 'background-attachment: scroll;';
	          preparedCss += 'background-position: center;';
	          preparedCss += 'background-repeat: repeat;';
	          preparedCss += 'background-size: 50%;';
	        }
	        if (bgPosition === 'center_repeat_y') {
	          preparedCss += 'background-attachment: scroll;';
	          preparedCss += 'background-position: top;';
	          preparedCss += 'background-repeat: repeat-y;';
	          preparedCss += 'background-size: 100%;';
	        }
	      }
	      return preparedCss;
	    }
	  }, {
	    key: "generateCss",
	    value: function generateCss() {
	      var css = this.generateSelectorStart(this.id);
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
	      if (this.type === null) {
	        this.layoutContent = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div id=\"", "\" class=\"landing-design-preview\"><h2 class=\"landing-design-preview-title\">", "</h2><h4 class=\"landing-design-preview-subtitle\">", "</h4><p class=\"landing-design-preview-text\">", "</p><p class=\"landing-design-preview-text\">", "</p><div class=\"\"><a class=\"landing-design-preview-button\">", "</a></div></div>"])), this.id, this.phrase.title, this.phrase.subtitle, this.phrase.text1, this.phrase.text2, this.phrase.button);
	      }
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
	        var fixedStyle = 'position: fixed; ';
	        fixedStyle += 'top: 20px; ';
	        fixedStyle += 'margin-top: 0; ';
	        fixedStyle += "right: ".concat(positionFixedRight, "px;");
	        fixedStyle += "max-width: ".concat(maxWidth, "px;");
	        this.layoutContent.setAttribute('style', fixedStyle);
	      }
	    }
	  }, {
	    key: "unFixElement",
	    value: function unFixElement() {
	      this.layoutContent.setAttribute('style', '');
	    }
	  }, {
	    key: "convertFont",
	    value: function convertFont(font) {
	      var convertFont = font;
	      convertFont = convertFont.replace('g-font-', '').replaceAll('-', ' ').replace('ibm ', 'IBM ').replace('pt ', 'PT ').replace(/sc(?![a-z])/i, 'SC').replace(/jp(?![a-z])/i, 'JP').replace(/kr(?![a-z])/i, 'KR').replace(/tc(?![a-z])/i, 'TC').replaceAll(/(^|\s)\S/g, function (firstSymbol) {
	        return firstSymbol.toUpperCase();
	      });
	      return convertFont;
	    }
	  }]);
	  return DesignPreview;
	}(main_core_events.EventEmitter);
	babelHelpers.defineProperty(DesignPreview, "DEFAULT_FONT_SIZE", 14);
	babelHelpers.defineProperty(DesignPreview, "HEIGHT_PAGE_TITLE_WRAP", 74);

	exports.DesignPreview = DesignPreview;

}((this.BX.Landing.SettingsForm = this.BX.Landing.SettingsForm || {}),BX,BX.Event));
//# sourceMappingURL=designpreview.bundle.js.map
