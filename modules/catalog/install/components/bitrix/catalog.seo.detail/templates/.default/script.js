this.BX = this.BX || {};
(function (exports,ui_messagecard,main_core_events,main_popup,main_core) {
	'use strict';

	var _templateObject, _templateObject2;
	var Base = /*#__PURE__*/function () {
	  function Base() {
	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var form = arguments.length > 1 ? arguments[1] : undefined;
	    babelHelpers.classCallCheck(this, Base);
	    this.form = form;
	    this.fields = options.FIELDS;
	    this.title = options.TITLE;
	    this.id = options.ID;
	    this.type = null;
	  }

	  babelHelpers.createClass(Base, [{
	    key: "layout",
	    value: function layout() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class='ui-slider-section'>\n\t\t\t\t<div class='ui-slider-heading-4'>", "</div>\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), main_core.Text.encode(this.title), this.getInfoWrapper(), this.getWrapper());
	    }
	  }, {
	    key: "getWrapper",
	    value: function getWrapper() {
	      return main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class='ui-form ui-form-section'></div>"])));
	    }
	  }, {
	    key: "getInfoWrapper",
	    value: function getInfoWrapper() {
	      return null;
	    }
	  }, {
	    key: "getForm",
	    value: function getForm() {
	      return this.form;
	    }
	  }, {
	    key: "getType",
	    value: function getType() {
	      return this.type;
	    }
	  }, {
	    key: "getInheritedLabel",
	    value: function getInheritedLabel() {
	      return '';
	    }
	  }]);
	  return Base;
	}();

	var SectionType = function SectionType() {
	  babelHelpers.classCallCheck(this, SectionType);
	};
	babelHelpers.defineProperty(SectionType, "ELEMENT", 'E');
	babelHelpers.defineProperty(SectionType, "SECTION", 'S');
	babelHelpers.defineProperty(SectionType, "MANAGEMENT", 'M');

	var _templateObject$1, _templateObject2$1;
	var Element = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Element, _Base);

	  function Element() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var form = arguments.length > 1 ? arguments[1] : undefined;
	    babelHelpers.classCallCheck(this, Element);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Element).call(this, options, form));
	    _this.inputFields = [];
	    Object.keys(options.FIELDS).forEach(function (fieldCode) {
	      var fieldScheme = options.FIELDS[fieldCode];

	      _this.inputFields.push(new SeoInput(fieldScheme, babelHelpers.assertThisInitialized(_this)));
	    });

	    if (options.MESSAGE) {
	      _this.message = new ui_messagecard.MessageCard({
	        id: options.MESSAGE.ID,
	        header: main_core.Text.encode(options.MESSAGE.HEADER),
	        description: _this.getInfoMessageDescription(options.MESSAGE.DESCRIPTION),
	        angle: false,
	        hidden: options.MESSAGE.HIDDEN === 'Y'
	      });
	      main_core_events.EventEmitter.subscribe(_this.message, 'onClose', function () {
	        _this.form.hideInfoMessage(_this.message.id);
	      });
	    }

	    _this.type = SectionType.ELEMENT;
	    return _this;
	  }

	  babelHelpers.createClass(Element, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      var wrapper = babelHelpers.get(babelHelpers.getPrototypeOf(Element.prototype), "getWrapper", this).call(this);
	      this.inputFields.forEach(function (field) {
	        main_core.Dom.append(field.layout(), wrapper);
	      });
	      return wrapper;
	    }
	  }, {
	    key: "getInfoWrapper",
	    value: function getInfoWrapper() {
	      return this.message ? this.message.getLayout() : null;
	    }
	  }, {
	    key: "getInfoMessageDescription",
	    value: function getInfoMessageDescription(description) {
	      var moreLink = main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["<a href=\"#\" class=\"ui-form-link\">", "</a>"])), main_core.Loc.getMessage('CSD_ELEMENT_INFO_MESSAGE_HELP_LINK_TITLE'));
	      main_core.Event.bind(moreLink, 'click', SeoDetail.openSeoHelpPage);
	      var descriptionHtml = main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div>", "</div>\n\t\t"])), main_core.Text.encode(description).replace('#HELP_LINK#', '<help-link></help-link>'));
	      main_core.Dom.replace(descriptionHtml.querySelector('help-link'), moreLink);
	      return descriptionHtml;
	    }
	  }, {
	    key: "toggleInputMenu",
	    value: function toggleInputMenu(field) {
	      this.getForm().toggleInputMenu(this, field);
	    }
	  }, {
	    key: "getInheritedLabel",
	    value: function getInheritedLabel() {
	      if (!this.form.isElementMode()) {
	        return main_core.Loc.getMessage('CSD_INHERIT_SECTION_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE');
	      }

	      return main_core.Loc.getMessage('CSD_INHERIT_ELEMENT_OVERWRITE_CHECKBOX_INPUT_TITLE');
	    }
	  }]);
	  return Element;
	}(Base);

	var _templateObject$2, _templateObject2$2, _templateObject3, _templateObject4, _templateObject5, _templateObject6, _templateObject7, _templateObject8, _templateObject9, _templateObject10, _templateObject11, _templateObject12, _templateObject13;

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _getTemplate = /*#__PURE__*/new WeakSet();

	var _getHint = /*#__PURE__*/new WeakSet();

	var _isInherited = /*#__PURE__*/new WeakSet();

	var _isTransliterated = /*#__PURE__*/new WeakSet();

	var _isLowerCase = /*#__PURE__*/new WeakSet();

	var _getWhitespace = /*#__PURE__*/new WeakSet();

	var _toggleLowercase = /*#__PURE__*/new WeakSet();

	var _toggleTransliterate = /*#__PURE__*/new WeakSet();

	var _inputWhitespaceChar = /*#__PURE__*/new WeakSet();

	var _isExistedAttributes = /*#__PURE__*/new WeakSet();

	var _isExistedInheritedCheckbox = /*#__PURE__*/new WeakSet();

	var _setTemplate = /*#__PURE__*/new WeakSet();

	var _toggleInherited = /*#__PURE__*/new WeakSet();

	var _isReadOnly = /*#__PURE__*/new WeakSet();

	var SeoInput = /*#__PURE__*/function () {
	  function SeoInput() {
	    var setting = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var section = arguments.length > 1 ? arguments[1] : undefined;
	    babelHelpers.classCallCheck(this, SeoInput);

	    _classPrivateMethodInitSpec(this, _isReadOnly);

	    _classPrivateMethodInitSpec(this, _toggleInherited);

	    _classPrivateMethodInitSpec(this, _setTemplate);

	    _classPrivateMethodInitSpec(this, _isExistedInheritedCheckbox);

	    _classPrivateMethodInitSpec(this, _isExistedAttributes);

	    _classPrivateMethodInitSpec(this, _inputWhitespaceChar);

	    _classPrivateMethodInitSpec(this, _toggleTransliterate);

	    _classPrivateMethodInitSpec(this, _toggleLowercase);

	    _classPrivateMethodInitSpec(this, _getWhitespace);

	    _classPrivateMethodInitSpec(this, _isLowerCase);

	    _classPrivateMethodInitSpec(this, _isTransliterated);

	    _classPrivateMethodInitSpec(this, _isInherited);

	    _classPrivateMethodInitSpec(this, _getHint);

	    _classPrivateMethodInitSpec(this, _getTemplate);

	    this.id = main_core.Text.encode(setting.ID);
	    this.title = main_core.Text.encode(setting.TITLE);
	    this.section = section;
	    this.handleInput = main_core.Runtime.debounce(this.onInput, 500, this);
	  }

	  babelHelpers.createClass(SeoInput, [{
	    key: "layout",
	    value: function layout() {
	      var _this = this;

	      this.input = main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input class='ui-ctl-element' name=\"", "\" value=\"", "\">\n\t\t"])), this.id, main_core.Text.encode(_classPrivateMethodGet(this, _getTemplate, _getTemplate2).call(this)));

	      if (!_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this)) {
	        main_core.Event.bind(this.input, 'keydown', function (event) {
	          _this.section.getForm().hideInputMenu();
	        });
	        main_core.Event.bind(this.input, 'input', this.handleInput);
	        main_core.Event.bind(this.input, 'click', this.toggleTemplatesMenu.bind(this));
	      }

	      if (!this.hintWrapper) {
	        this.hintWrapper = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["<div class='ui-ctl-label-text catalog-seo-detail-input-hint'>", "</div>"])), _classPrivateMethodGet(this, _getHint, _getHint2).call(this));
	      }

	      var menuButton = null;

	      if (!_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this)) {
	        menuButton = main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-ctl-after ui-ctl-icon-angle\" data-id=\"\"></div>"])));
	      }

	      this.inputWrapper = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), menuButton, this.input);
	      var inheritCheckbox = null;
	      var checkboxWrapper = null;

	      if (_classPrivateMethodGet(this, _isExistedInheritedCheckbox, _isExistedInheritedCheckbox2).call(this)) {
	        inheritCheckbox = main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" class=\"ui-ctl-element\">"])));
	        main_core.Event.bind(inheritCheckbox, 'change', _classPrivateMethodGet(this, _toggleInherited, _toggleInherited2).bind(this));
	        checkboxWrapper = main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t"])), inheritCheckbox, this.section.getInheritedLabel());
	      }

	      if (inheritCheckbox && !_classPrivateMethodGet(this, _isInherited, _isInherited2).call(this)) {
	        inheritCheckbox.checked = true;
	      } else if (!this.section.getForm().isCatalogMode() || _classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this)) {
	        main_core.Dom.addClass(this.inputWrapper, 'ui-ctl-disabled');
	        this.input.disabled = true;
	      }

	      var lowercaseCheckboxWrapper = null;
	      var transliterateCheckboxWrapper = null;
	      this.transliterateWrapper = null;

	      if (_classPrivateMethodGet(this, _isExistedAttributes, _isExistedAttributes2).call(this)) {
	        var lowercaseCheckbox = main_core.Tag.render(_templateObject7 || (_templateObject7 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" class=\"ui-ctl-element\">"])));
	        main_core.Event.bind(lowercaseCheckbox, 'change', _classPrivateMethodGet(this, _toggleLowercase, _toggleLowercase2).bind(this));
	        lowercaseCheckboxWrapper = main_core.Tag.render(_templateObject8 || (_templateObject8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t"])), lowercaseCheckbox, main_core.Loc.getMessage('CSD_LOWERCASE_CHECKBOX_INPUT_TITLE'));

	        if (_classPrivateMethodGet(this, _isLowerCase, _isLowerCase2).call(this)) {
	          lowercaseCheckbox.checked = true;
	        }

	        var transliterateCheckbox = main_core.Tag.render(_templateObject9 || (_templateObject9 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" class=\"ui-ctl-element\">"])));
	        main_core.Event.bind(transliterateCheckbox, 'change', _classPrivateMethodGet(this, _toggleTransliterate, _toggleTransliterate2).bind(this));
	        transliterateCheckboxWrapper = main_core.Tag.render(_templateObject10 || (_templateObject10 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</label>\n\t\t\t"])), transliterateCheckbox, main_core.Loc.getMessage('CSD_TRANSLITERATE_CHECKBOX_INPUT_TITLE'));

	        if (_classPrivateMethodGet(this, _isTransliterated, _isTransliterated2).call(this)) {
	          transliterateCheckbox.checked = true;
	        }

	        var whitespaceInput = main_core.Tag.render(_templateObject11 || (_templateObject11 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<input \n\t\t\t\t\tclass=\"ui-ctl-element ui-text-center\" \n\t\t\t\t\tsize=\"1\" maxlength=\"1\" \n\t\t\t\t\tvalue=\"", "\"\n\t\t\t\t>\n\t\t\t"])), _classPrivateMethodGet(this, _getWhitespace, _getWhitespace2).call(this));
	        main_core.Event.bind(whitespaceInput, 'input', _classPrivateMethodGet(this, _inputWhitespaceChar, _inputWhitespaceChar2).bind(this));
	        this.transliterateWrapper = main_core.Tag.render(_templateObject12 || (_templateObject12 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t", "\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</div>\n\t\t\t"])), whitespaceInput, main_core.Loc.getMessage('CSD_WHITESPACE_CHARACTER_INPUT_TITLE'));

	        if (!_classPrivateMethodGet(this, _isTransliterated, _isTransliterated2).call(this)) {
	          main_core.Dom.addClass(this.transliterateWrapper, 'ui-form-row-hidden');
	        }
	      }

	      return main_core.Tag.render(_templateObject13 || (_templateObject13 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t<div class='ui-form-label'>\n\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\t\t\t\t\t\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.title, this.inputWrapper, checkboxWrapper, lowercaseCheckboxWrapper, transliterateCheckboxWrapper, this.transliterateWrapper, this.hintWrapper);
	    }
	  }, {
	    key: "onInput",
	    value: function onInput(event) {
	      _classPrivateMethodGet(this, _setTemplate, _setTemplate2).call(this, event.target.value);
	    }
	  }, {
	    key: "refreshHint",
	    value: function refreshHint(template) {
	      var _this2 = this;

	      if (_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this)) {
	        return this;
	      }

	      this.section.getForm().getHint(this.id, template).then(function (result) {
	        var value = _this2.section.getForm().getValue(_this2.id);

	        value.hint = result.data;
	        _this2.hintWrapper.innerHTML = result.data;
	      });
	      return this;
	    }
	  }, {
	    key: "toggleTemplatesMenu",
	    value: function toggleTemplatesMenu() {
	      this.section.toggleInputMenu(this);
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      return this.input;
	    }
	  }, {
	    key: "addTemplateValue",
	    value: function addTemplateValue(template) {
	      this.getInput().value += template;

	      _classPrivateMethodGet(this, _setTemplate, _setTemplate2).call(this, this.getInput().value);
	    }
	  }]);
	  return SeoInput;
	}();

	function _getTemplate2() {
	  var value = this.section.getForm().getValue(this.id);
	  return main_core.Type.isStringFilled(value === null || value === void 0 ? void 0 : value.template) ? value.template : '';
	}

	function _getHint2() {
	  var value = this.section.getForm().getValue(this.id);
	  return main_core.Type.isStringFilled(value === null || value === void 0 ? void 0 : value.hint) ? value.hint : '';
	}

	function _isInherited2() {
	  var value = this.section.getForm().getValue(this.id);
	  return (value === null || value === void 0 ? void 0 : value.inherited) !== 'N';
	}

	function _isTransliterated2() {
	  var value = this.section.getForm().getValue(this.id);
	  return (value === null || value === void 0 ? void 0 : value.transliterate) === 'Y';
	}

	function _isLowerCase2() {
	  var value = this.section.getForm().getValue(this.id);
	  return (value === null || value === void 0 ? void 0 : value.lowercase) === 'Y';
	}

	function _getWhitespace2() {
	  var value = this.section.getForm().getValue(this.id);
	  return main_core.Type.isStringFilled(value === null || value === void 0 ? void 0 : value.whitespaceCharacter) ? value.whitespaceCharacter : '';
	}

	function _toggleLowercase2(event) {
	  var value = this.section.getForm().getValue(this.id);
	  value.lowercase = event.target.checked ? 'Y' : 'N';

	  if (main_core.Type.isStringFilled(value.template)) {
	    this.refreshHint(value);
	  }

	  return this;
	}

	function _toggleTransliterate2(event) {
	  var checkboxValue = event.target.checked;

	  if (checkboxValue) {
	    main_core.Dom.removeClass(this.transliterateWrapper, 'ui-form-row-hidden');
	  } else {
	    main_core.Dom.addClass(this.transliterateWrapper, 'ui-form-row-hidden');
	  }

	  var value = this.section.getForm().getValue(this.id);
	  value.transliterate = checkboxValue ? 'Y' : 'N';

	  if (main_core.Type.isStringFilled(value.template)) {
	    this.refreshHint(value);
	  }

	  return this;
	}

	function _inputWhitespaceChar2(event) {
	  var value = this.section.getForm().getValue(this.id);
	  value.whitespaceCharacter = event.target.value.slice(0, 1);

	  if (main_core.Type.isStringFilled(value.template) && _classPrivateMethodGet(this, _isTransliterated, _isTransliterated2).call(this)) {
	    this.refreshHint(value);
	  }

	  return (value === null || value === void 0 ? void 0 : value.transliterate) === 'Y';
	}

	function _isExistedAttributes2() {
	  var value = this.section.getForm().getValue(this.id);
	  return !_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this) && (value === null || value === void 0 ? void 0 : value.isExistedAttributes);
	}

	function _isExistedInheritedCheckbox2() {
	  return !_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this) && !this.section.getForm().isCatalogMode();
	}

	function _setTemplate2(template) {
	  if (_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this)) {
	    return this;
	  }

	  var value = this.section.getForm().getValue(this.id);
	  value.template = template;

	  if (main_core.Type.isStringFilled(template)) {
	    this.refreshHint(value);
	  } else {
	    value.hint = '';
	    this.hintWrapper.innerHTML = '';
	  }

	  return this;
	}

	function _toggleInherited2(event) {
	  if (_classPrivateMethodGet(this, _isReadOnly, _isReadOnly2).call(this) || this.section.getForm().isCatalogMode()) {
	    return this;
	  }

	  var isChecked = event.target.checked;
	  var value = this.section.getForm().getValue(this.id);
	  value.inherited = isChecked ? 'N' : 'Y';
	  this.input.disabled = !isChecked;

	  if (isChecked) {
	    main_core.Dom.removeClass(this.inputWrapper, 'ui-ctl-disabled');
	  } else {
	    main_core.Dom.addClass(this.inputWrapper, 'ui-ctl-disabled');
	  }

	  return this;
	}

	function _isReadOnly2() {
	  return this.section.getForm().isReadOnly();
	}

	var SeoDetailMode = function SeoDetailMode() {
	  babelHelpers.classCallCheck(this, SeoDetailMode);
	};
	babelHelpers.defineProperty(SeoDetailMode, "CATALOG", 'MODE_CATALOG');
	babelHelpers.defineProperty(SeoDetailMode, "SECTION", 'MODE_SECTION');
	babelHelpers.defineProperty(SeoDetailMode, "ELEMENT", 'MODE_ELEMENT');

	var Section = /*#__PURE__*/function (_Element) {
	  babelHelpers.inherits(Section, _Element);

	  function Section() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    var form = arguments.length > 1 ? arguments[1] : undefined;
	    babelHelpers.classCallCheck(this, Section);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Section).call(this, options, form));
	    _this.type = SectionType.SECTION;
	    return _this;
	  }

	  babelHelpers.createClass(Section, [{
	    key: "getInheritedLabel",
	    value: function getInheritedLabel() {
	      return main_core.Loc.getMessage('CSD_INHERIT_SECTION_OVERWRITE_CHECKBOX_INPUT_TITLE');
	    }
	  }]);
	  return Section;
	}(Element);

	var _templateObject$3, _templateObject2$3;

	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }

	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }

	var _isChecked = /*#__PURE__*/new WeakSet();

	var _setValue = /*#__PURE__*/new WeakSet();

	var CacheCheckbox = /*#__PURE__*/function () {
	  function CacheCheckbox(settings, section) {
	    babelHelpers.classCallCheck(this, CacheCheckbox);

	    _classPrivateMethodInitSpec$1(this, _setValue);

	    _classPrivateMethodInitSpec$1(this, _isChecked);

	    this.id = main_core.Text.encode(settings.ID);
	    this.title = main_core.Text.encode(settings.TITLE);
	    this.section = section;
	  }

	  babelHelpers.createClass(CacheCheckbox, [{
	    key: "layout",
	    value: function layout() {
	      var checkbox = main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["<input type=\"checkbox\" class=\"ui-ctl-element\">"])));
	      checkbox.checked = _classPrivateMethodGet$1(this, _isChecked, _isChecked2).call(this);
	      main_core.Event.bind(checkbox, 'change', _classPrivateMethodGet$1(this, _setValue, _setValue2).bind(this));
	      return main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-form-row\">\n\t\t\t\t<div class='ui-form-label'>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-form-content\">\n\t\t\t\t\t<label class=\"ui-ctl ui-ctl-checkbox ui-ctl-w100\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t\t<div class=\"ui-ctl-label-text\">", "</div>\n\t\t\t\t\t</label>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), checkbox, this.title);
	    }
	  }]);
	  return CacheCheckbox;
	}();

	function _isChecked2() {
	  var value = this.section.getForm().getValue(this.id);
	  return value.clearCache === 'Y';
	}

	function _setValue2(event) {
	  var value = this.section.getForm().getValue(this.id);
	  value.clearCache = event.target.checked ? 'Y' : 'N';
	  return this;
	}

	var Management = /*#__PURE__*/function (_Base) {
	  babelHelpers.inherits(Management, _Base);

	  function Management() {
	    babelHelpers.classCallCheck(this, Management);
	    return babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Management).apply(this, arguments));
	  }

	  babelHelpers.createClass(Management, [{
	    key: "getWrapper",
	    value: function getWrapper() {
	      var wrapper = babelHelpers.get(babelHelpers.getPrototypeOf(Management.prototype), "getWrapper", this).call(this);
	      var field = new CacheCheckbox(this.fields['SEO_CLEAR_VALUES'], this);
	      main_core.Dom.append(field.layout(), wrapper);
	      return wrapper;
	    }
	  }]);
	  return Management;
	}(Base);

	var _templateObject$4;

	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }

	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }

	var _isLocked = /*#__PURE__*/new WeakMap();

	var SeoDetail = /*#__PURE__*/function () {
	  function SeoDetail(settings) {
	    babelHelpers.classCallCheck(this, SeoDetail);

	    _classPrivateFieldInitSpec(this, _isLocked, {
	      writable: true,
	      value: false
	    });

	    babelHelpers.defineProperty(this, "templatePopup", null);
	    babelHelpers.defineProperty(this, "templatePopupField", null);
	    this.container = BX(settings.containerId);
	    this.form = this.container.querySelector('#' + settings.formId);
	    this.values = settings.values || {};
	    this.defaultValues = settings.values || {};
	    this.componentName = settings.componentName || '';
	    this.signedParameters = settings.signedParameters || '';
	    this.menuItems = settings.menuItems || [];
	    this.sections = [];
	    this.templatePopupField = null;
	    this.readOnly = settings.readOnly || false;
	    this.mode = settings.mode || '';
	    this.createSections(settings.schemeFields);
	  }

	  babelHelpers.createClass(SeoDetail, [{
	    key: "createSections",
	    value: function createSections(scheme) {
	      var _this = this;

	      Object.keys(scheme).forEach(function (fieldCode) {
	        var sectionOptions = scheme[fieldCode];
	        var section = null;

	        if (sectionOptions.TYPE === SectionType.SECTION) {
	          section = new Section(sectionOptions, _this);
	        } else if (sectionOptions.TYPE === SectionType.ELEMENT) {
	          section = new Element(sectionOptions, _this);
	        } else if (sectionOptions.TYPE === SectionType.MANAGEMENT && !_this.isReadOnly()) {
	          section = new Management(sectionOptions, _this);
	        }

	        if (section) {
	          _this.sections.push(section);
	        }
	      });
	      return this;
	    }
	  }, {
	    key: "layout",
	    value: function layout() {
	      var _this2 = this;

	      this.sections.forEach(function (section) {
	        main_core.Dom.append(section.layout(), _this2.form);
	      });
	    }
	  }, {
	    key: "getValue",
	    value: function getValue(id) {
	      this.values[id] = this.values[id] || {};
	      return this.values[id];
	    }
	  }, {
	    key: "getHint",
	    value: function getHint(templateId, template) {
	      return main_core.ajax.runComponentAction(this.componentName, 'getHint', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          templateId: templateId,
	          template: template
	        }
	      });
	    }
	  }, {
	    key: "getSaveButton",
	    value: function getSaveButton() {
	      return this.container.querySelector('#ui-button-panel-save');
	    }
	  }, {
	    key: "save",
	    value: function save() {
	      var _this3 = this;

	      if (babelHelpers.classPrivateFieldGet(this, _isLocked)) {
	        return;
	      }

	      babelHelpers.classPrivateFieldSet(this, _isLocked, true);
	      main_core.Dom.addClass(this.getSaveButton(), 'ui-btn-wait');
	      main_core.ajax.runComponentAction(this.componentName, 'save', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          values: this.values
	        }
	      }).then(function () {
	        babelHelpers.classPrivateFieldSet(_this3, _isLocked, false);
	        var notificationOptions = {
	          closeButton: true,
	          autoHideDelay: 3000,
	          content: main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<div>", "</div>"])), main_core.Loc.getMessage('CSD_SAVE_MESSAGE_NOTIFICATION'))
	        };
	        var notify = top.BX.UI.Notification.Center.notify(notificationOptions);
	        notify.show();

	        _this3.onFormCancel();
	      })["catch"](this.onError.bind(this));
	    }
	  }, {
	    key: "hideInfoMessage",
	    value: function hideInfoMessage(messageId) {
	      main_core.ajax.runComponentAction(this.componentName, 'hideInfoMessage', {
	        mode: 'class',
	        signedParameters: this.signedParameters,
	        data: {
	          messageId: messageId
	        }
	      });
	    }
	  }, {
	    key: "onError",
	    value: function onError() {
	      main_core.Dom.removeClass(this.getSaveButton(), "ui-btn-wait");
	      babelHelpers.classPrivateFieldSet(this, _isLocked, false);
	    }
	  }, {
	    key: "isReadOnly",
	    value: function isReadOnly() {
	      return this.readOnly;
	    }
	  }, {
	    key: "getFormFieldName",
	    value: function getFormFieldName(name) {
	      return 'fields[' + name + ']';
	    }
	  }, {
	    key: "toggleInputMenu",
	    value: function toggleInputMenu(section, field) {
	      if (this.templatePopupField && this.templatePopup && field.id !== this.templatePopupField) {
	        this.templatePopup.close();
	        this.templatePopup.destroy();
	        this.templatePopup = null;
	      }

	      if (!this.templatePopup) {
	        this.templatePopupField = field;
	        var items = this.getMenuItems(section.getType());
	        this.templatePopup = new main_popup.Menu({
	          bindElement: field.getInput(),
	          items: items
	        });
	      }

	      this.templatePopup.toggle();
	    }
	  }, {
	    key: "hideInputMenu",
	    value: function hideInputMenu() {
	      if (this.templatePopup) {
	        this.templatePopup.close();
	      }
	    }
	  }, {
	    key: "addInputTemplate",
	    value: function addInputTemplate(template) {
	      if (this.templatePopupField) {
	        this.templatePopupField.addTemplateValue(template);
	      }

	      if (this.templatePopup) {
	        this.templatePopup.close();
	      }
	    }
	  }, {
	    key: "getMenuItems",
	    value: function getMenuItems(type) {
	      var _Object$assign;

	      return (_Object$assign = Object.assign(this.menuItems[type])) !== null && _Object$assign !== void 0 ? _Object$assign : [];
	    }
	  }, {
	    key: "onFormCancel",
	    value: function onFormCancel() {
	      BX.SidePanel.Instance.close();
	    }
	  }, {
	    key: "isCatalogMode",
	    value: function isCatalogMode() {
	      return this.mode === SeoDetailMode.CATALOG;
	    }
	  }, {
	    key: "isElementMode",
	    value: function isElementMode() {
	      return this.mode === SeoDetailMode.ELEMENT;
	    }
	  }, {
	    key: "isSectionMode",
	    value: function isSectionMode() {
	      return this.mode === SeoDetailMode.SECTION;
	    }
	  }], [{
	    key: "create",
	    value: function create(settings) {
	      SeoDetail.instance = new SeoDetail(settings);
	      return SeoDetail.instance;
	    }
	  }, {
	    key: "onClickSave",
	    value: function onClickSave() {
	      SeoDetail.instance.save();
	    }
	  }, {
	    key: "onSelectTemplate",
	    value: function onSelectTemplate(template) {
	      SeoDetail.instance.addInputTemplate(template);
	    }
	  }, {
	    key: "openSeoHelpPage",
	    value: function openSeoHelpPage(event) {
	      if (top.BX.Helper) {
	        top.BX.Helper.show("redirect=detail&code=" + SeoDetail.HElP_ARTICLE_CODE);
	      }
	    }
	  }]);
	  return SeoDetail;
	}();
	babelHelpers.defineProperty(SeoDetail, "instance", null);
	babelHelpers.defineProperty(SeoDetail, "HElP_ARTICLE_CODE", 17013874);

	exports.SeoDetail = SeoDetail;

}((this.BX.Catalog = this.BX.Catalog || {}),BX.UI,BX.Event,BX.Main,BX));
//# sourceMappingURL=script.js.map
