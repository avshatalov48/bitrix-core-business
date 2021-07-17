this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
(function (exports,main_core) {
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

	function _templateObject2() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=Roboto:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=Roboto+Slab:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=Montserrat:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=Alegreya+Sans:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=Cormorant+Infant:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=PT+Sans+Caption:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link\n\t\t\t\t\trel=\"stylesheet\"\n\t\t\t\t\thref=\"https://fonts.googleapis.com/css?family=PT+Sans+Narrow:300,400,500,600,700,900\"\n\t\t\t\t>\n\t\t\t\t<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=PT+Sans:300,400,500,600,700,900\">\n\t\t\t\t<link rel=\"stylesheet\" href=\"https://fonts.googleapis.com/css?family=Lobster:300,400,500,600,700,900\">\n\t\t\t</div>\n\t\t"]);

	  _templateObject2 = function _templateObject2() {
	    return data;
	  };

	  return data;
	}

	function _templateObject() {
	  var data = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-design-preview-wrap\">\n\t\t\t\t<div class=\"landing-design-preview\">\n\t\t\t\t\t<h2 class=\"landing-design-preview-title\">", "</h2>\n\t\t\t\t\t<h4 class=\"landing-design-preview-subtitle\">", "</h4>\n\t\t\t\t\t<p class=\"landing-design-preview-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<p class=\"landing-design-preview-text\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</p>\n\t\t\t\t\t<div class=\"\">\n\t\t\t\t\t\t<a href=\"#\" class=\"landing-design-preview-button\">", "</a>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"]);

	  _templateObject = function _templateObject() {
	    return data;
	  };

	  return data;
	}
	var DesignPreview = /*#__PURE__*/function () {
	  function DesignPreview(form) {
	    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
	    var phrase = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
	    babelHelpers.classCallCheck(this, DesignPreview);
	    this.form = form;
	    this.phrase = phrase;
	    this.initControls(options);
	    this.initLayout();
	    this.applyStyles();
	    this.onApplyStyles = this.applyStyles.bind(this);
	  }

	  babelHelpers.createClass(DesignPreview, [{
	    key: "initLayout",
	    value: function initLayout() {
	      var _this = this;

	      this.layout = DesignPreview.createLayout(this.phrase);
	      this.fonts = DesignPreview.loadFonts();
	      this.styleNode = document.createElement("style");
	      main_core.Dom.append(this.styleNode, this.layout);
	      main_core.Dom.append(this.layout, this.form);
	      main_core.Dom.append(this.fonts, this.form);
	      var paramsObserver = {
	        threshold: 1
	      };
	      var observer = new IntersectionObserver(function (entries) {
	        entries.forEach(function (entry) {
	          if (entry.isIntersecting) {
	            if (!_this.hasOwnProperty('defaultIntersecting')) {
	              _this.defaultIntersecting = true;
	            }

	            if (_this.defaultIntersecting) {
	              _this.unFixElement();
	            }
	          } else {
	            if (!_this.hasOwnProperty('defaultIntersecting')) {
	              _this.defaultIntersecting = false;
	            }

	            if (_this.defaultIntersecting) {
	              _this.fixElement();
	            }
	          }
	        });
	      }, paramsObserver);
	      var elementDesignPreview = document.querySelector('.landing-design-preview-wrap');
	      observer.observe(elementDesignPreview);
	    }
	  }, {
	    key: "initControls",
	    value: function initControls(options) {
	      this.controls = {};

	      for (var group in options) {
	        if (!options.hasOwnProperty(group)) {
	          continue;
	        }

	        for (var key in options[group]) {
	          if (!options[group].hasOwnProperty(key)) {
	            continue;
	          }

	          if (!this.controls[group]) {
	            this.controls[group] = {};
	          }

	          var control = new Control(options[group][key]['control']);
	          control.setChangeHandler(this.applyStyles.bind(this));

	          if (group === 'theme' && key !== 'use') {
	            control.setClickHandler(this.applyStyles.bind(this));
	          }

	          if (group === 'background' && key === 'picture') {
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

	            if (options[_group][_key]['defaultValue']) {
	              this.controls[_group][_key].setDefaultValue(options[_group][_key]['defaultValue']);
	            }
	          }
	        }
	      }

	      BX.addCustomEvent('BX.Landing.ColorPicker:onSelectColor', this.onApplyStyles.bind(this));
	      BX.addCustomEvent('BX.Landing.ColorPicker:onClearColorPicker', this.onApplyStyles.bind(this));
	      BX.addCustomEvent('BX.Landing.UI.Field.Image:onChangeImage', this.onApplyStyles.bind(this));
	    }
	  }, {
	    key: "generateSelectorStart",
	    value: function generateSelectorStart(className) {
	      return '.' + className + ' {';
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
	      var setColors = BX('set-colors');
	      var colorPickerElement = BX('colorpicker-theme');
	      var activeColorNode = setColors.querySelector('.active');
	      var isActiveColorPickerElement = colorPickerElement.classList.contains('active');

	      if (activeColorNode) {
	        colorPrimary = activeColorNode.dataset.value;
	      }

	      if (isActiveColorPickerElement) {
	        colorPrimary = colorPickerElement.dataset.value;
	      }

	      if (colorPrimary[0] !== '#') {
	        colorPrimary = '#' + colorPrimary;
	      } //for 'design page', if use not checked, use color from 'design site'


	      if (this.controls.theme.use.node) {
	        if (this.controls.theme.use.node.checked === false) {
	          colorPrimary = this.controls.theme.corporateColor.defaultValue;
	        }
	      }

	      css += "--design-preview-primary: ".concat(colorPrimary, ";");
	      return css;
	    }
	  }, {
	    key: "getCSSPart2",
	    value: function getCSSPart2(css) {
	      var textColor = this.controls.typo.textColor.node.value;
	      var font = this.convertFont(this.controls.typo.textFont.node.value);
	      var hFont = this.convertFont(this.controls.typo.hFont.node.value);
	      var textSize = Math.round(this.controls.typo.textSize.node.value * DesignPreview.DEFAULT_FONT_SIZE) + 'px';
	      var fontWeight = this.controls.typo.textWeight.node.value;
	      var fontLineHeight = this.controls.typo.textLineHeight.node.value;
	      var hColor = this.controls.typo.hColor.node.value;
	      var hWeight = this.controls.typo.hWeight.node.value;

	      if (this.controls.typo.use.node) {
	        if (this.controls.typo.use.node.checked === false) {
	          textColor = this.controls.typo.textColor.defaultValue;
	          font = this.convertFont(this.controls.typo.textFont.defaultValue);
	          hFont = this.convertFont(this.controls.typo.hFont.defaultValue);
	          textSize = Math.round(this.controls.typo.textSize.defaultValue * DesignPreview.DEFAULT_FONT_SIZE) + 'px';
	          fontWeight = this.controls.typo.textWeight.defaultValue;
	          fontLineHeight = this.controls.typo.textLineHeight.defaultValue;
	          hColor = this.controls.typo.hColor.defaultValue;
	          hWeight = this.controls.typo.hWeight.defaultValue;
	        }
	      }

	      css += "--design-preview-color: ".concat(textColor, ";");
	      css += "--design-preview-font: ".concat(font, ";");
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

	      if (this.controls.typo.hFont.node.value) {
	        css += "--design-preview-font-h: ".concat(hFont, ";");
	      } else {
	        css += "--design-preview-font-h: ".concat(font, ";");
	      }

	      return css;
	    }
	  }, {
	    key: "getCSSPart3",
	    value: function getCSSPart3(css) {
	      var bgColor = this.controls.background.color.node.value;
	      var bgFieldNode = BX('landing-form-background-field');
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
	            bgPicture = this.controls.background.picture.defaultValue;
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
	      css = this.generateSelectorStart('landing-design-preview');
	      css = this.getCSSPart1(css);
	      css = this.getCSSPart2(css);
	      css = this.getCSSPart3(css);
	      css = this.generateSelectorEnd(css);
	      return css;
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
	    key: "convertFont",
	    value: function convertFont(font) {
	      switch (font) {
	        case 'g-font-open-sans':
	          font = '"Open Sans", Helvetica, Arial, sans-serif';
	          break;

	        case 'g-font-roboto':
	          font = '"Roboto", Arial, sans-serif';
	          break;

	        case 'g-font-roboto-slab':
	          font = '"Roboto Slab", Helvetica, Arial, sans-serif';
	          break;

	        case 'g-font-montserrat':
	          font = '"Montserrat", Arial, sans-serif';
	          break;

	        case 'g-font-alegreya-sans':
	          font = '"Alegreya Sans", sans-serif';
	          break;

	        case 'g-font-cormorant-infant':
	          font = '"Cormorant Infant", serif';
	          break;

	        case 'g-font-pt-sans-caption':
	          font = '"PT Sans Caption", sans-serif';
	          break;

	        case 'g-font-pt-sans-narrow':
	          font = '"PT Sans Narrow", sans-serif';
	          break;

	        case 'g-font-pt-sans':
	          font = '"PT Sans", sans-serif';
	          break;

	        case 'g-font-lobster':
	          font = '"Lobster", cursive';
	          break;

	        default:
	          font = '"Montserrat", Arial, sans-serif';
	      }

	      return font;
	    }
	  }, {
	    key: "fixElement",
	    value: function fixElement() {
	      var paddingDesignForm = 20;
	      var designForm = document.querySelector('.landing-design-form');
	      var designFormPosition = designForm.getBoundingClientRect();
	      var designPreview = document.querySelector('.landing-design-preview');
	      var designPreviewPosition = designPreview.getBoundingClientRect();
	      var bodyWidth = document.body.clientWidth;
	      var positionFixedRight = bodyWidth - designFormPosition.right + paddingDesignForm;
	      var paddingDesignPreview = 25;
	      var designPreviewWrap = document.querySelector('.landing-design-preview-wrap');
	      var designPreviewWrapPosition = designPreviewWrap.getBoundingClientRect();
	      var maxWidth = designPreviewWrapPosition.width - paddingDesignPreview * 2;

	      if (designFormPosition.height > designPreviewPosition.height) {
	        var fixedStyle;
	        fixedStyle = 'position: fixed; ';
	        fixedStyle += 'top: 20px; ';
	        fixedStyle += 'margin-top: 0; ';
	        fixedStyle += 'right: ' + positionFixedRight + 'px;';
	        fixedStyle += 'max-width: ' + maxWidth + 'px;';
	        designPreview.setAttribute("style", fixedStyle);
	      }
	    }
	  }, {
	    key: "unFixElement",
	    value: function unFixElement() {
	      var designPreview = document.querySelector('.landing-design-preview');
	      designPreview.setAttribute("style", '');
	    }
	  }], [{
	    key: "createLayout",
	    value: function createLayout(phrase) {
	      return main_core.Tag.render(_templateObject(), phrase.title, phrase.subtitle, phrase.text1, phrase.text2, phrase.button);
	    }
	  }, {
	    key: "loadFonts",
	    value: function loadFonts() {
	      return main_core.Tag.render(_templateObject2());
	    }
	  }]);
	  return DesignPreview;
	}();
	babelHelpers.defineProperty(DesignPreview, "DEFAULT_FONT_SIZE", 14);

	exports.DesignPreview = DesignPreview;

}((this.BX.Landing.SettingsForm = this.BX.Landing.SettingsForm || {}),BX));
//# sourceMappingURL=designpreview.bundle.js.map
