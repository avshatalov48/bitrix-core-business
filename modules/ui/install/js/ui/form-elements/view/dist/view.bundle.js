/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,ui_infoHelper,main_core_events,ui_section,main_popup,ui_switcher,main_core,ui_entitySelector) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4, _templateObject5;
	function _createForOfIteratorHelper(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }
	function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _id = /*#__PURE__*/new WeakMap();
	var _inputName = /*#__PURE__*/new WeakMap();
	var _isEnable = /*#__PURE__*/new WeakMap();
	var _bannerCode = /*#__PURE__*/new WeakMap();
	var _helpDeskCode = /*#__PURE__*/new WeakMap();
	var _label = /*#__PURE__*/new WeakMap();
	var _helpMessageProvider = /*#__PURE__*/new WeakMap();
	var _helpMessage = /*#__PURE__*/new WeakMap();
	var _errorContainer = /*#__PURE__*/new WeakMap();
	var _isFieldDisabled = /*#__PURE__*/new WeakMap();
	var BaseField = /*#__PURE__*/function (_EventEmitter) {
	  babelHelpers.inherits(BaseField, _EventEmitter);
	  function BaseField(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, BaseField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BaseField).call(this));
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _inputName, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _isEnable, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _bannerCode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _helpDeskCode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _label, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _helpMessageProvider, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _helpMessage, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _errorContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(babelHelpers.assertThisInitialized(_this), _isFieldDisabled, {
	      writable: true,
	      value: false
	    });
	    _this.setEventNamespace('UI.Section');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _label, main_core.Type.isStringFilled(params.label) ? params.label : '');
	    if (main_core.Type.isStringFilled(params.id)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _id, params.id);
	    } else if (!_this.id) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _id, _this.prefixId() + main_core.Text.getRandom(8));
	    }
	    if (main_core.Type.isStringFilled(params.inputName)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _inputName, params.inputName);
	    } else if (!babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _inputName)) {
	      babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _inputName, main_core.Text.getRandom(8));
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isEnable, params.isEnable !== false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _bannerCode, main_core.Type.isStringFilled(params.bannerCode) ? params.bannerCode : null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _helpDeskCode, main_core.Type.isStringFilled(params.helpDesk) ? params.helpDesk : null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _helpMessageProvider, params.helpMessageProvider);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isFieldDisabled, main_core.Type.isBoolean(params.isFieldDisabled) ? params.isFieldDisabled : false);
	    return _this;
	  }
	  babelHelpers.createClass(BaseField, [{
	    key: "getHelpMessage",
	    value: function getHelpMessage() {
	      if (babelHelpers.classPrivateFieldGet(this, _helpMessage) instanceof ui_section.HelpMessage) {
	        return babelHelpers.classPrivateFieldGet(this, _helpMessage);
	      }
	      babelHelpers.classPrivateFieldSet(this, _helpMessage, main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _helpMessageProvider)) ? babelHelpers.classPrivateFieldGet(this, _helpMessageProvider).call(this, this.getId(), this.getInputNode()) : null);
	      return babelHelpers.classPrivateFieldGet(this, _helpMessage);
	    }
	  }, {
	    key: "cleanError",
	    value: function cleanError() {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _errorContainer));
	      main_core.Dom.removeClass(this.getErrorBox(), '--error');
	    }
	  }, {
	    key: "setErrors",
	    value: function setErrors(errorMessages) {
	      this.cleanError();
	      main_core.Dom.addClass(this.getErrorBox(), '--error');
	      var _iterator = _createForOfIteratorHelper(errorMessages),
	        _step;
	      try {
	        for (_iterator.s(); !(_step = _iterator.n()).done;) {
	          var message = _step.value;
	          var error = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"ui-section__error-message\">\n\t\t\t\t\t<span class=\"ui-icon-set --warning\"></span>\n\t\t\t\t\t<span>", "</span>\n\t\t\t\t</div>\n\t\t\t"])), message);
	          main_core.Dom.append(error, this.renderErrors());
	        }
	      } catch (err) {
	        _iterator.e(err);
	      } finally {
	        _iterator.f();
	      }
	    }
	  }, {
	    key: "getErrorBox",
	    value: function getErrorBox() {
	      return this.getInputNode();
	    }
	  }, {
	    key: "renderErrors",
	    value: function renderErrors() {
	      if (babelHelpers.classPrivateFieldGet(this, _errorContainer)) {
	        return babelHelpers.classPrivateFieldGet(this, _errorContainer);
	      }
	      babelHelpers.classPrivateFieldSet(this, _errorContainer, main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-section__error-container\"></div>"]))));
	      return babelHelpers.classPrivateFieldGet(this, _errorContainer);
	    }
	  }, {
	    key: "getId",
	    value: function getId() {
	      return babelHelpers.classPrivateFieldGet(this, _id);
	    }
	  }, {
	    key: "getLabel",
	    value: function getLabel() {
	      return babelHelpers.classPrivateFieldGet(this, _label);
	    }
	  }, {
	    key: "prefixId",
	    value: function prefixId() {
	      return '';
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return '';
	    }
	  }, {
	    key: "getName",
	    value: function getName() {
	      return babelHelpers.classPrivateFieldGet(this, _inputName);
	    }
	  }, {
	    key: "getInputNode",
	    value: function getInputNode() {
	      return null;
	    }
	  }, {
	    key: "setName",
	    value: function setName(name) {
	      babelHelpers.classPrivateFieldSet(this, _inputName, name);
	    }
	  }, {
	    key: "cancel",
	    value: function cancel() {}
	  }, {
	    key: "render",
	    value: function render() {
	      if (this.field) {
	        return this.field;
	      }
	      this.field = this.renderContentField();
	      return this.field;
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      return main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral([""])));
	    }
	  }, {
	    key: "isEnable",
	    value: function isEnable() {
	      return babelHelpers.classPrivateFieldGet(this, _isEnable);
	    }
	  }, {
	    key: "getBannerCode",
	    value: function getBannerCode() {
	      return babelHelpers.classPrivateFieldGet(this, _bannerCode);
	    }
	  }, {
	    key: "showBanner",
	    value: function showBanner() {
	      if (this.getBannerCode()) {
	        BX.UI.InfoHelper.show(this.getBannerCode());
	      }
	    }
	  }, {
	    key: "getHelpdeskCode",
	    value: function getHelpdeskCode() {
	      return babelHelpers.classPrivateFieldGet(this, _helpDeskCode);
	    }
	  }, {
	    key: "showHelpdesk",
	    value: function showHelpdesk() {
	      if (this.getHelpdeskCode()) {
	        top.BX.Helper.show(this.getHelpdeskCode());
	      }
	    }
	  }, {
	    key: "renderLockElement",
	    value: function renderLockElement() {
	      var _this2 = this;
	      var lockElement = main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["<span class=\"ui-icon-set --lock field-has-lock\"></span>"])));
	      lockElement.addEventListener('click', function () {
	        _this2.showBanner();
	      });
	      return lockElement;
	    }
	  }, {
	    key: "renderMoreElement",
	    value: function renderMoreElement(helpdeskCode) {
	      return main_core.Tag.render(_templateObject5 || (_templateObject5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t", "\n\t\t"])), this.getMoreElement(helpdeskCode));
	    }
	  }, {
	    key: "getMoreElement",
	    value: function getMoreElement(helpdeskCode) {
	      return "\n\t\t\t<a class=\"more\" href=\"javascript:top.BX.Helper.show('".concat(helpdeskCode, "');\">\n\t\t\t\t").concat(main_core.Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE'), "\n\t\t\t</a>\n\t\t");
	    }
	  }, {
	    key: "isFieldDisabled",
	    value: function isFieldDisabled() {
	      return babelHelpers.classPrivateFieldGet(this, _isFieldDisabled);
	    }
	  }]);
	  return BaseField;
	}(main_core_events.EventEmitter);

	var _templateObject$1, _templateObject2$1;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _renderMore = /*#__PURE__*/new WeakMap();
	var _moreElement = /*#__PURE__*/new WeakMap();
	var _getMore = /*#__PURE__*/new WeakSet();
	var _renderHint = /*#__PURE__*/new WeakSet();
	var Checker = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(Checker, _BaseField);
	  function Checker(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Checker);
	    params.label = params.title;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Checker).call(this, params));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _renderHint);
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _getMore);
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _renderMore, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(babelHelpers.assertThisInitialized(_this), _moreElement, {
	      writable: true,
	      value: void 0
	    });
	    _this.hintOn = params.hintOn;
	    _this.hintOff = params.hintOff;
	    _this.hideSeparator = params.hideSeparator;
	    _this.alignCenter = params.alignCenter;
	    _this.noMarginBottom = params.noMarginBottom;
	    _this.size = params.size;
	    _this.switcher = new ui_switcher.Switcher({
	      inputName: _this.getName(),
	      checked: params.checked,
	      id: _this.getId(),
	      attributeName: params.attributeName,
	      handlers: params.handlers,
	      color: params.colors,
	      size: params.size
	    });
	    if (_this.isFieldDisabled()) {
	      _this.switcher.disable(true);
	    }
	    _this.defaultValue = params.checked;
	    main_core_events.EventEmitter.subscribe(_this.switcher, 'toggled', function () {
	      if (!_this.isEnable()) {
	        _this.switcher.check(_this.defaultValue, false);
	        if (!main_core.Type.isNil(_this.getHelpMessage())) {
	          _this.getHelpMessage().show();
	        }
	        return;
	      }
	      _this.switcher.inputNode.form.dispatchEvent(new Event('change'));
	      _this.changeHint(_this.isChecked());
	      _this.emit('change', _this.isChecked());
	    });
	    return _this;
	  }
	  babelHelpers.createClass(Checker, [{
	    key: "getValue",
	    value: function getValue() {
	      return this.switcher.inputNode.value;
	    }
	  }, {
	    key: "getInputNode",
	    value: function getInputNode() {
	      return this.switcher.node;
	    }
	  }, {
	    key: "prefixId",
	    value: function prefixId() {
	      return 'checker_';
	    }
	  }, {
	    key: "isChecked",
	    value: function isChecked() {
	      return this.switcher.isChecked();
	    }
	  }, {
	    key: "renderMore",
	    value: function renderMore() {
	      if (babelHelpers.classPrivateFieldGet(this, _renderMore)) {
	        return babelHelpers.classPrivateFieldGet(this, _renderMore);
	      }
	      babelHelpers.classPrivateFieldSet(this, _renderMore, !main_core.Type.isNil(this.getHelpdeskCode()) ? this.renderMoreElement(this.getHelpdeskCode()) : '');
	      return babelHelpers.classPrivateFieldGet(this, _renderMore);
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      var lockElement = !this.isEnable() ? this.renderLockElement() : null;
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div\n\t\t\t\tid=\"", "\" \n\t\t\t\tclass=\"\n\t\t\t\t\tui-section__field-switcher\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t\"\n\t\t\t>\n\t\t\t\t<div class=\"ui-section__field\">\n\t\t\t\t\t<div class=\"ui-section__switcher\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-section__field-inner\">\n\t\t\t\t\t\t<div class=\"ui-section__title\">\n\t\t\t\t\t\t\t", " ", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), this.getId(), this.hideSeparator ? '--hide-separator' : '', this.alignCenter ? '--align-center --gray-title' : '', this.noMarginBottom ? '--no-margin-bottom' : '', this.size ? "--".concat(this.size) : '', this.getInputNode(), this.getLabel(), lockElement, _classPrivateMethodGet(this, _renderHint, _renderHint2).call(this, this.isChecked()), this.renderErrors());
	    }
	  }, {
	    key: "getHint",
	    value: function getHint(isChecked) {
	      if (!main_core.Type.isStringFilled(this.hintOff)) {
	        return main_core.Type.isStringFilled(this.hintOn) ? this.hintOn : '';
	      }
	      var result = isChecked ? this.hintOn : this.hintOff;
	      return main_core.Type.isStringFilled(result) ? result : '';
	    }
	  }, {
	    key: "changeHint",
	    value: function changeHint(isChecked) {
	      var hintElement = this.field.querySelector('.ui-section__hint');
	      main_core.Dom.replace(hintElement, _classPrivateMethodGet(this, _renderHint, _renderHint2).call(this, isChecked));
	    }
	  }]);
	  return Checker;
	}(BaseField);
	function _getMore2() {
	  if (!babelHelpers.classPrivateFieldGet(this, _moreElement)) {
	    babelHelpers.classPrivateFieldSet(this, _moreElement, !main_core.Type.isNil(this.getHelpdeskCode()) ? this.getMoreElement(this.getHelpdeskCode()) : '');
	  }
	  return babelHelpers.classPrivateFieldGet(this, _moreElement);
	}
	function _renderHint2(isChecked) {
	  var result = '';
	  var moreText = _classPrivateMethodGet(this, _getMore, _getMore2).call(this);
	  var hintText = this.getHint(isChecked);
	  if (hintText.indexOf('#MORE_DETAILS#') === -1) {
	    result = hintText + ' ' + moreText;
	  } else {
	    result = hintText.replace('#MORE_DETAILS#', moreText);
	  }
	  return main_core.Tag.render(_templateObject2$1 || (_templateObject2$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__hint\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), result);
	}

	var _templateObject$2, _templateObject2$2;
	function _classPrivateFieldInitSpec$2(obj, privateMap, value) { _checkPrivateRedeclaration$2(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$2(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _hintDescElement = /*#__PURE__*/new WeakMap();
	var InlineChecker = /*#__PURE__*/function (_Checker) {
	  babelHelpers.inherits(InlineChecker, _Checker);
	  function InlineChecker(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, InlineChecker);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(InlineChecker).call(this, params));
	    _classPrivateFieldInitSpec$2(babelHelpers.assertThisInitialized(_this), _hintDescElement, {
	      writable: true,
	      value: void 0
	    });
	    _this.hintTitle = main_core.Type.isStringFilled(params.hintTitle) ? params.hintTitle : '';
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintDescElement, main_core.Tag.render(_templateObject$2 || (_templateObject$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__description\">\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), _this.isChecked() ? _this.hintOn : _this.hintOff));
	    return _this;
	  }
	  babelHelpers.createClass(InlineChecker, [{
	    key: "prefixId",
	    value: function prefixId() {
	      return 'inline_checker_';
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      var content = main_core.Tag.render(_templateObject2$2 || (_templateObject2$2 = babelHelpers.taggedTemplateLiteral(["\n\t\t<div id=\"", "\" class=\"ui-section__field-switcher --field-separator --align-center\">\n\t\t<div class=\"ui-section__field-inline-box\">\n\t\t\t<div class=\"ui-section__field-switcher-box\">\n\t\t\t\t<div class=\"ui-section__switcher\"></div>\n\t\t\t\t<div class=\"ui-section__switcher-title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t\t<div class=\"ui-section__field-inline-separator\"></div>\n\t\t\t<div class=\"ui-section__hint\">\n\t\t\t\t<div class=\"ui-section__title\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t\t"])), this.getId(), this.getLabel(), this.hintTitle, babelHelpers.classPrivateFieldGet(this, _hintDescElement));
	      this.switcher.renderTo(content.querySelector('.ui-section__switcher'));
	      return content;
	    }
	  }, {
	    key: "changeHint",
	    value: function changeHint(isChecked) {
	      babelHelpers.classPrivateFieldGet(this, _hintDescElement).innerText = this.getHint(isChecked);
	    }
	  }]);
	  return InlineChecker;
	}(Checker);

	var _templateObject$3, _templateObject2$3, _templateObject3$1;
	function _createForOfIteratorHelper$1(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$1(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$1(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$1(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$1(o, minLen); }
	function _arrayLikeToArray$1(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$1(obj, privateSet) { _checkPrivateRedeclaration$3(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$3(obj, privateMap, value) { _checkPrivateRedeclaration$3(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$3(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$1(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _items = /*#__PURE__*/new WeakMap();
	var _selectNode = /*#__PURE__*/new WeakMap();
	var _isMulti = /*#__PURE__*/new WeakMap();
	var _current = /*#__PURE__*/new WeakMap();
	var _buildSelector = /*#__PURE__*/new WeakSet();
	var _buildItems = /*#__PURE__*/new WeakSet();
	var ItemPicker = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ItemPicker, _BaseField);
	  function ItemPicker(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, ItemPicker);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ItemPicker).call(this, params));
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _buildItems);
	    _classPrivateMethodInitSpec$1(babelHelpers.assertThisInitialized(_this), _buildSelector);
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _items, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _selectNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _isMulti, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$3(babelHelpers.assertThisInitialized(_this), _current, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _items, params.items);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _isMulti, params.isMulti === true);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _current, params.current);
	    if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _isMulti) && _this.getName().substring(_this.getName().length - 2) !== '[]') {
	      _this.setName(_this.getName() + '[]');
	    }
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _selectNode, _classPrivateMethodGet$1(babelHelpers.assertThisInitialized(_this), _buildSelector, _buildSelector2).call(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(ItemPicker, [{
	    key: "prefixId",
	    value: function prefixId() {
	      return 'item_picker_';
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      return main_core.Tag.render(_templateObject$3 || (_templateObject$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t<div class=\"ui-section__picker-wrapper\" id=\"", "\">\n\t\t\t<div class=\"ui-section__field-label\">", "</div>\n\t\t\t", "\n\t\t\t", "\n\t\t\t", "\n\t\t</div>\n\t\t"])), this.getId(), this.getLabel(), _classPrivateMethodGet$1(this, _buildItems, _buildItems2).call(this), this.renderErrors(), this.getInputNode());
	    }
	  }, {
	    key: "getInputNode",
	    value: function getInputNode() {
	      return babelHelpers.classPrivateFieldGet(this, _selectNode);
	    }
	  }, {
	    key: "onClickHandler",
	    value: function onClickHandler(event) {
	      main_core.Dom.toggleClass(event.target, 'ui-section__selected');
	      if (!main_core.Dom.hasClass(event.target, 'ui-section__selected') && babelHelpers.classPrivateFieldGet(this, _isMulti)) {
	        this.unSelect(event.target);
	      } else {
	        this.select(event.target);
	      }
	    }
	  }, {
	    key: "createItem",
	    value: function createItem(text, value) {
	      var isSelected = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : false;
	      return main_core.Dom.create('div', {
	        text: text,
	        props: {
	          className: "ui-section__item " + (isSelected ? 'ui-section__selected' : '')
	        },
	        dataset: {
	          value: value
	        },
	        events: {
	          click: this.onClickHandler.bind(this)
	        }
	      });
	    }
	  }, {
	    key: "select",
	    value: function select(node) {
	      var fireEvent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      if (!babelHelpers.classPrivateFieldGet(this, _isMulti)) {
	        this.unSelectAll();
	      }
	      var value = node.dataset['value'];
	      var optNode = babelHelpers.classPrivateFieldGet(this, _selectNode).querySelector('option[value="' + value + '"]');
	      if (main_core.Type.isDomNode(optNode)) {
	        main_core.Dom.addClass(node, 'ui-section__selected');
	        optNode.selected = true;
	        if (fireEvent) {
	          this.fireEvent();
	        }
	      }
	    }
	  }, {
	    key: "unSelect",
	    value: function unSelect(node) {
	      var fireEvent = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
	      var value = node.dataset['value'];
	      var optNode = babelHelpers.classPrivateFieldGet(this, _selectNode).querySelector('option[value="' + value + '"]');
	      if (main_core.Type.isDomNode(optNode)) {
	        main_core.Dom.removeClass(node, 'ui-section__selected');
	        optNode.selected = false;
	        if (fireEvent) {
	          this.fireEvent();
	        }
	      }
	    }
	  }, {
	    key: "unSelectAll",
	    value: function unSelectAll() {
	      var fireEvent = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : false;
	      if (main_core.Type.isDomNode(this.field)) {
	        var items = this.field.querySelectorAll('.ui-section__item.ui-section__selected');
	        items.forEach(function (item) {
	          main_core.Dom.removeClass(item, 'ui-section__selected');
	        });
	      }
	      var optsNodes = babelHelpers.classPrivateFieldGet(this, _selectNode).querySelectorAll('option');
	      optsNodes.forEach(function (node) {
	        if (main_core.Type.isDomNode(node)) {
	          node.selected = false;
	        }
	      });
	      if (fireEvent) {
	        this.fireEvent();
	      }
	    }
	  }, {
	    key: "getNodesByValue",
	    value: function getNodesByValue(data) {
	      var query;
	      if (main_core.Type.isArray(data)) {
	        var queryList = data.map(function (value) {
	          return '.ui-section__item[data-value="' + value + '"]';
	        });
	        query = queryList.join(', ');
	      } else {
	        query = '.ui-section__item[data-value="' + data + '"]';
	      }
	      return this.field.querySelectorAll(query);
	    }
	  }, {
	    key: "fireEvent",
	    value: function fireEvent() {
	      babelHelpers.classPrivateFieldGet(this, _selectNode).dispatchEvent(new Event('change'));
	      babelHelpers.classPrivateFieldGet(this, _selectNode).form.dispatchEvent(new Event('change'));
	    }
	  }]);
	  return ItemPicker;
	}(BaseField);
	function _buildSelector2() {
	  var options = [];
	  var _iterator = _createForOfIteratorHelper$1(babelHelpers.classPrivateFieldGet(this, _items)),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var _step$value = _step.value,
	        value = _step$value.value,
	        name = _step$value.name,
	        selected = _step$value.selected;
	      var selectedAttr = '';
	      if (selected === true) {
	        selectedAttr = 'selected';
	      }
	      options.push(main_core.Tag.render(_templateObject2$3 || (_templateObject2$3 = babelHelpers.taggedTemplateLiteral(["<option ", " value=\"", "\">", "</option>"])), selectedAttr, value, name));
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  return main_core.Dom.create('select', {
	    attrs: {
	      multiple: babelHelpers.classPrivateFieldGet(this, _isMulti) ? 'on' : '',
	      name: this.getName(),
	      disabled: !this.isEnable() ? 'disable' : ''
	    },
	    style: {
	      display: 'none'
	    },
	    children: options
	  });
	}
	function _buildItems2() {
	  var collectionNode = main_core.Tag.render(_templateObject3$1 || (_templateObject3$1 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-section__item-collection\"></div>"])));
	  var _iterator2 = _createForOfIteratorHelper$1(babelHelpers.classPrivateFieldGet(this, _items)),
	    _step2;
	  try {
	    for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	      var _step2$value = _step2.value,
	        value = _step2$value.value,
	        name = _step2$value.name,
	        selected = _step2$value.selected;
	      main_core.Dom.append(this.createItem(name, value, selected), collectionNode);
	    }
	  } catch (err) {
	    _iterator2.e(err);
	  } finally {
	    _iterator2.f();
	  }
	  return collectionNode;
	}

	var _templateObject$4, _templateObject2$4, _templateObject3$2, _templateObject4$1, _templateObject5$1, _templateObject6;
	function _createForOfIteratorHelper$2(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$2(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$2(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$2(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$2(o, minLen); }
	function _arrayLikeToArray$2(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$2(obj, privateSet) { _checkPrivateRedeclaration$4(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$4(obj, privateMap, value) { _checkPrivateRedeclaration$4(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$4(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$2(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _items$1 = /*#__PURE__*/new WeakMap();
	var _hintTitle = /*#__PURE__*/new WeakMap();
	var _hints = /*#__PURE__*/new WeakMap();
	var _hintTitleElement = /*#__PURE__*/new WeakMap();
	var _hintDescElement$1 = /*#__PURE__*/new WeakMap();
	var _inputNode = /*#__PURE__*/new WeakMap();
	var _selectorNode = /*#__PURE__*/new WeakMap();
	var _hintSeparatorElement = /*#__PURE__*/new WeakMap();
	var _buildSelector$1 = /*#__PURE__*/new WeakSet();
	var Selector = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(Selector, _BaseField);
	  function Selector(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, Selector);
	    params.inputName = params.name;
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Selector).call(this, params));
	    _classPrivateMethodInitSpec$2(babelHelpers.assertThisInitialized(_this), _buildSelector$1);
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _items$1, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _hintTitle, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _hints, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _hintTitleElement, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _hintDescElement$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _inputNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _selectorNode, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$4(babelHelpers.assertThisInitialized(_this), _hintSeparatorElement, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _items$1, params.items);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintTitle, main_core.Type.isString(params.hintTitle) ? params.hintTitle : '');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hints, main_core.Type.isObject(params.hints) ? params.hints : {});
	    _this.defaultValue = params.current;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintTitleElement, main_core.Tag.render(_templateObject$4 || (_templateObject$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-section__title\"></div>"]))));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintDescElement$1, main_core.Tag.render(_templateObject2$4 || (_templateObject2$4 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-section__description\"></div>"]))));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintSeparatorElement, main_core.Tag.render(_templateObject3$2 || (_templateObject3$2 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-section__field-inline-separator\"></div>"]))));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _inputNode, _classPrivateMethodGet$2(babelHelpers.assertThisInitialized(_this), _buildSelector$1, _buildSelector2$1).call(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(Selector, [{
	    key: "getHint",
	    value: function getHint(key) {
	      var hint = babelHelpers.classPrivateFieldGet(this, _hints)[key];
	      if (!main_core.Type.isString(hint) || hint === '') {
	        return null;
	      }
	      return hint;
	    }
	  }, {
	    key: "prefixId",
	    value: function prefixId() {
	      return 'selector_';
	    }
	  }, {
	    key: "setHint",
	    value: function setHint(key) {
	      var more = main_core.Type.isNil(this.getHelpdeskCode()) ? '' : this.renderMoreElement(this.getHelpdeskCode()).outerHTML;
	      var hint = this.getHint(key);
	      babelHelpers.classPrivateFieldGet(this, _hintTitleElement).innerText = !main_core.Type.isNil(hint) ? babelHelpers.classPrivateFieldGet(this, _hintTitle) : '';
	      babelHelpers.classPrivateFieldGet(this, _hintDescElement$1).innerHTML = !main_core.Type.isNil(hint) ? hint + ' ' + more : '';
	      main_core.Dom.removeClass(this.field, '--field-separator');
	      main_core.Dom.remove(babelHelpers.classPrivateFieldGet(this, _hintSeparatorElement));
	      if (!main_core.Type.isNil(hint)) {
	        main_core.Dom.addClass(this.field, '--field-separator');
	        var fieldContainer = this.field.querySelector('.ui-section__field-inline-box .ui-section__field');
	        main_core.Dom.insertAfter(babelHelpers.classPrivateFieldGet(this, _hintSeparatorElement), fieldContainer);
	      }
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      var lockElement = this.isEnable() ? null : this.renderLockElement();
	      return main_core.Tag.render(_templateObject4$1 || (_templateObject4$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__field-selector \">\n\t\t\t\t<div class=\"ui-section__field-container\">\n\t\t\t\t\t<div class=\"ui-section__field-label_box\">\n\t\t\t\t\t\t<label class=\"ui-section__field-label\" for=\"", "\">", "</label> \n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-section__field-inline-box\">\n\t\t\t\t\t\t<div class=\"ui-section__field\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-section__hint\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.getId(), this.getName(), this.getLabel(), lockElement, this.getSelector(), babelHelpers.classPrivateFieldGet(this, _hintTitleElement), babelHelpers.classPrivateFieldGet(this, _hintDescElement$1));
	    }
	  }, {
	    key: "render",
	    value: function render() {
	      var render = babelHelpers.get(babelHelpers.getPrototypeOf(Selector.prototype), "render", this).call(this);
	      this.setHint(this.getInputNode().value);
	      return render;
	    }
	  }, {
	    key: "getItems",
	    value: function getItems() {
	      return babelHelpers.classPrivateFieldGet(this, _items$1);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getInputNode().value;
	    }
	  }, {
	    key: "getSelector",
	    value: function getSelector() {
	      var _babelHelpers$classPr;
	      var disableClass = !this.isEnable() || this.isFieldDisabled() ? 'ui-ctl-disabled' : '';
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _selectorNode)) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : babelHelpers.classPrivateFieldSet(this, _selectorNode, main_core.Tag.render(_templateObject5$1 || (_templateObject5$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-ctl ui-ctl-w100 ui-ctl-after-icon ui-ctl-dropdown ", "\">\n\t\t\t\t<div class=\"ui-ctl-after ui-ctl-icon-angle\"></div>\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), disableClass, this.getInputNode()));
	      return babelHelpers.classPrivateFieldGet(this, _selectorNode);
	    }
	  }, {
	    key: "getInputNode",
	    value: function getInputNode() {
	      return babelHelpers.classPrivateFieldGet(this, _inputNode);
	    }
	  }]);
	  return Selector;
	}(BaseField);
	function _buildSelector2$1() {
	  var _this2 = this;
	  var options = [];
	  var _iterator = _createForOfIteratorHelper$2(babelHelpers.classPrivateFieldGet(this, _items$1)),
	    _step;
	  try {
	    for (_iterator.s(); !(_step = _iterator.n()).done;) {
	      var _step$value = _step.value,
	        value = _step$value.value,
	        name = _step$value.name,
	        selected = _step$value.selected,
	        disabled = _step$value.disabled,
	        hidden = _step$value.hidden;
	      var selectedAttr = '';
	      var disabledAttr = '';
	      var hiddenAttr = '';
	      if (selected === true) {
	        selectedAttr = 'selected';
	      }
	      if (disabled === true) {
	        disabledAttr = 'disabled';
	      }
	      if (hidden === true) {
	        hiddenAttr = 'hidden';
	      }
	      options.push(main_core.Tag.render(_templateObject6 || (_templateObject6 = babelHelpers.taggedTemplateLiteral(["<option ", " ", " ", " value=\"", "\">", "</option>"])), selectedAttr, disabledAttr, hiddenAttr, value, name));
	    }
	  } catch (err) {
	    _iterator.e(err);
	  } finally {
	    _iterator.f();
	  }
	  return main_core.Dom.create('select', {
	    attrs: {
	      name: this.getName(),
	      "class": 'ui-ctl-element'
	    },
	    events: {
	      change: function change(event) {
	        _this2.setHint(event.target.value);
	      },
	      click: function click(event) {
	        if (!_this2.isEnable()) {
	          if (!main_core.Type.isNil(_this2.getHelpMessage())) {
	            _this2.getHelpMessage().show();
	          }
	          event.preventDefault();
	        }
	      },
	      mousedown: function mousedown(event) {
	        if (!_this2.isEnable() || _this2.isFieldDisabled()) {
	          event.preventDefault();
	        }
	      }
	    },
	    children: options
	  });
	}

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }
	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	function _classPrivateMethodInitSpec$3(obj, privateSet) { _checkPrivateRedeclaration$5(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$5(obj, privateMap, value) { _checkPrivateRedeclaration$5(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$5(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$3(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _menu = /*#__PURE__*/new WeakMap();
	var _showMenu = /*#__PURE__*/new WeakSet();
	var SelectorMenu = /*#__PURE__*/function (_Selector) {
	  babelHelpers.inherits(SelectorMenu, _Selector);
	  function SelectorMenu(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, SelectorMenu);
	    params.items = params.items.map(function (item) {
	      return _objectSpread(_objectSpread({
	        value: item.id,
	        name: item.text
	      }, item), {}, {
	        onclick: function onclick(event, item) {
	          _this.getInputNode().value = item.value;
	          babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _menu).close();
	        }
	      });
	    });
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SelectorMenu).call(this, params));
	    _classPrivateMethodInitSpec$3(babelHelpers.assertThisInitialized(_this), _showMenu);
	    _classPrivateFieldInitSpec$5(babelHelpers.assertThisInitialized(_this), _menu, {
	      writable: true,
	      value: void 0
	    });
	    main_core.Dom.style(_this.getInputNode(), 'pointer-events', 'none');
	    main_core.Dom.style(_this.getSelector(), 'cursor', 'pointer');
	    main_core.Event.bind(_this.getSelector(), 'click', _classPrivateMethodGet$3(babelHelpers.assertThisInitialized(_this), _showMenu, _showMenu2).bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }
	  babelHelpers.createClass(SelectorMenu, [{
	    key: "prefixId",
	    value: function prefixId() {
	      return 'menu_';
	    }
	  }]);
	  return SelectorMenu;
	}(Selector);
	function _showMenu2() {
	  var _this2 = this;
	  var handleScroll = function handleScroll() {
	    var popup = babelHelpers.classPrivateFieldGet(_this2, _menu).getPopupWindow();
	    popup.adjustPosition();
	    var popupRect = popup.bindElement.getBoundingClientRect();
	    if (popupRect.top > window.innerHeight || popupRect.bottom < 0) {
	      babelHelpers.classPrivateFieldGet(_this2, _menu).close();
	    }
	  };
	  babelHelpers.classPrivateFieldSet(this, _menu, main_popup.MenuManager.create({
	    id: "ui-form-elements-menu".concat(this.getId()),
	    bindElement: this.getInputNode(),
	    items: this.getItems().map(function (item) {
	      var selected = item.value === _this2.getValue() ? 'ui-form-elements-menu-item --selected' : '';
	      return _objectSpread(_objectSpread({}, item), {}, {
	        className: item.className ? "".concat(item.className, " ").concat(selected) : "menu-popup-no-icon ".concat(selected)
	      });
	    }),
	    events: {
	      onShow: function onShow() {
	        var popup = babelHelpers.classPrivateFieldGet(_this2, _menu).getPopupWindow();
	        var elementWidth = popup.bindElement.offsetWidth;
	        popup.setOffset({
	          offsetLeft: 0,
	          offsetTop: 5
	        });
	        popup.setWidth(elementWidth);
	        popup.adjustPosition();
	        main_core.Event.bind(window, 'scroll', handleScroll, true);
	      },
	      onClose: function onClose() {
	        babelHelpers.classPrivateFieldGet(_this2, _menu).destroy();
	        main_core.Event.unbind(window, 'scroll', handleScroll, true);
	      }
	    }
	  }));
	  babelHelpers.classPrivateFieldGet(this, _menu).show();
	}

	var SingleChecker = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(SingleChecker, _BaseField);
	  function SingleChecker(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, SingleChecker);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SingleChecker).call(this, params));
	    _this.switcher = params.switcher;
	    main_core.Event.bind(_this.switcher.getNode(), 'click', function () {
	      if (!_this.isEnable() && !_this.switcher.isChecked()) {
	        _this.switcher.check(true, false);
	        if (!main_core.Type.isNil(_this.getHelpMessage())) {
	          _this.getHelpMessage().show();
	        }
	        return;
	      }
	      _this.switcher.inputNode.form.dispatchEvent(new window.Event('change'));
	      _this.emit('change', _this.switcher.isChecked());
	    });
	    return _this;
	  }
	  return SingleChecker;
	}(BaseField);

	var _templateObject$5, _templateObject2$5;
	function _classPrivateMethodInitSpec$4(obj, privateSet) { _checkPrivateRedeclaration$6(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$6(obj, privateMap, value) { _checkPrivateRedeclaration$6(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$6(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$4(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _inputNode$1 = /*#__PURE__*/new WeakMap();
	var _maxlength = /*#__PURE__*/new WeakMap();
	var _renderInputNode = /*#__PURE__*/new WeakSet();
	var TextInput = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextInput, _BaseField);
	  function TextInput(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, TextInput);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextInput).call(this, params));
	    _classPrivateMethodInitSpec$4(babelHelpers.assertThisInitialized(_this), _renderInputNode);
	    _classPrivateFieldInitSpec$6(babelHelpers.assertThisInitialized(_this), _inputNode$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$6(babelHelpers.assertThisInitialized(_this), _maxlength, {
	      writable: true,
	      value: void 0
	    });
	    _this.defaultValue = main_core.Type.isStringFilled(params.value) ? params.value : '';
	    _this.hintTitle = main_core.Type.isStringFilled(params.hintTitle) ? params.hintTitle : '';
	    _this.placeholder = main_core.Type.isStringFilled(params.placeholder) ? params.placeholder : '';
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _maxlength, main_core.Type.isInteger(params.maxlength) ? params.maxlength : 255);
	    _this.inputDefaultWidth = main_core.Type.isBoolean(params.inputDefaultWidth) ? params.inputDefaultWidth : '';
	    _this.inputType = main_core.Type.isStringFilled(params.type) ? params.type : 'text';
	    if (!_this.isEnable()) {
	      main_core.Event.bind(_this.getInputNode(), 'click', function (event) {
	        event.preventDefault();
	        if (!main_core.Type.isNil(_this.getHelpMessage())) {
	          _this.getHelpMessage().show();
	        }
	      });
	    }
	    if (_this.isEnable()) {
	      _this.getInputNode().addEventListener('input', function () {
	        _this.getInputNode().form.dispatchEvent(new window.Event('change'));
	      });
	    }
	    return _this;
	  }
	  babelHelpers.createClass(TextInput, [{
	    key: "prefixId",
	    value: function prefixId() {
	      return 'text_';
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getInputNode().value;
	    }
	  }, {
	    key: "getInputNode",
	    value: function getInputNode() {
	      var _babelHelpers$classPr;
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _inputNode$1)) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : babelHelpers.classPrivateFieldSet(this, _inputNode$1, _classPrivateMethodGet$4(this, _renderInputNode, _renderInputNode2).call(this));
	      return babelHelpers.classPrivateFieldGet(this, _inputNode$1);
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      var lockElement = !this.isEnable ? this.renderLockElement() : null;
	      return main_core.Tag.render(_templateObject$5 || (_templateObject$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__field-selector\">\n\t\t\t\t<div class=\"ui-section__field-container\">\n\t\t\t\t\t<div class=\"ui-section__field-label_box\">\n\t\t\t\t\t\t<label for=\"", "\" class=\"ui-section__field-label\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</label> \n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-block ", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-section__hint\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.getId(), this.getName(), this.getLabel(), lockElement, this.inputDefaultWidth ? '' : 'ui-ctl-w100', this.getInputNode(), this.renderErrors(), this.hintTitle);
	    }
	  }]);
	  return TextInput;
	}(BaseField);
	function _renderInputNode2() {
	  return main_core.Tag.render(_templateObject2$5 || (_templateObject2$5 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<input\n\t\t\t\tvalue=\"", "\" \n\t\t\t\tname=\"", "\" \n\t\t\t\ttype=\"", "\" \n\t\t\t\tclass=\"ui-ctl-element ", "\" \n\t\t\t\tplaceholder=\"", "\"\n\t\t\t\tmaxlength=\"", "\"\n\t\t\t\t", "\n\t\t\t>\n\t\t"])), main_core.Text.encode(this.defaultValue), main_core.Text.encode(this.getName()), this.inputType, this.isEnable() ? '' : '--readonly', main_core.Text.encode(this.placeholder), parseInt(babelHelpers.classPrivateFieldGet(this, _maxlength), 10), this.isEnable() ? '' : 'readonly');
	}

	var _templateObject$6, _templateObject2$6;
	function _classPrivateMethodInitSpec$5(obj, privateSet) { _checkPrivateRedeclaration$7(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$7(obj, privateMap, value) { _checkPrivateRedeclaration$7(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$7(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$5(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _defaultValue = /*#__PURE__*/new WeakMap();
	var _hintTitle$1 = /*#__PURE__*/new WeakMap();
	var _placeholder = /*#__PURE__*/new WeakMap();
	var _inputDefaultWidth = /*#__PURE__*/new WeakMap();
	var _resizeOnlyY = /*#__PURE__*/new WeakMap();
	var _resizeOnlyX = /*#__PURE__*/new WeakMap();
	var _node = /*#__PURE__*/new WeakMap();
	var _renderNode = /*#__PURE__*/new WeakSet();
	var TextArea = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextArea, _BaseField);
	  function TextArea(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, TextArea);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextArea).call(this, params));
	    _classPrivateMethodInitSpec$5(babelHelpers.assertThisInitialized(_this), _renderNode);
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _defaultValue, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _hintTitle$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _placeholder, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _inputDefaultWidth, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _resizeOnlyY, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _resizeOnlyX, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$7(babelHelpers.assertThisInitialized(_this), _node, {
	      writable: true,
	      value: void 0
	    });
	    _this.setEventNamespace('UI.Form.Textarea');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _defaultValue, main_core.Type.isStringFilled(params.value) ? params.value : '');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintTitle$1, main_core.Type.isStringFilled(params.hintTitle) ? params.hintTitle : '');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _placeholder, main_core.Type.isStringFilled(params.placeholder) ? params.placeholder : '');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _inputDefaultWidth, main_core.Type.isBoolean(params.inputDefaultWidth) ? params.inputDefaultWidth : false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _resizeOnlyY, main_core.Type.isBoolean(params.resizeOnlyY) ? params.resizeOnlyY : false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _resizeOnlyX, main_core.Type.isBoolean(params.resizeOnlyX) ? params.resizeOnlyX : false);
	    return _this;
	  }
	  babelHelpers.createClass(TextArea, [{
	    key: "prefixId",
	    value: function prefixId() {
	      return 'textarea_';
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.getNode().value;
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      var lockElement = this.isEnable ? null : this.renderLockElement();
	      var resizeUiClass = babelHelpers.classPrivateFieldGet(this, _resizeOnlyY) ? 'ui-ctl-resize-y' : '';
	      if (resizeUiClass === '') {
	        resizeUiClass = babelHelpers.classPrivateFieldGet(this, _resizeOnlyX) ? 'ui-ctl-resize-x' : '';
	      }
	      var defaultWidthUIClass = babelHelpers.classPrivateFieldGet(this, _inputDefaultWidth) ? '' : 'ui-ctl-w100';
	      return main_core.Tag.render(_templateObject$6 || (_templateObject$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__field-selector\">\n\t\t\t\t<div class=\"ui-section__field-container\">\n\t\t\t\t\t<div class=\"ui-section__field-label_box\">\n\t\t\t\t\t\t<label for=\"", "\" class=\"ui-section__field-label\">\n\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t</label> \n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textarea ui-form-textarea ", " ", "\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-section__hint\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.getId(), this.getName(), this.getLabel(), lockElement, resizeUiClass, defaultWidthUIClass, this.getNode(), this.renderErrors(), babelHelpers.classPrivateFieldGet(this, _hintTitle$1));
	    }
	  }, {
	    key: "getNode",
	    value: function getNode() {
	      var _babelHelpers$classPr;
	      (_babelHelpers$classPr = babelHelpers.classPrivateFieldGet(this, _node)) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : babelHelpers.classPrivateFieldSet(this, _node, _classPrivateMethodGet$5(this, _renderNode, _renderNode2).call(this));
	      return babelHelpers.classPrivateFieldGet(this, _node);
	    }
	  }]);
	  return TextArea;
	}(BaseField);
	function _renderNode2() {
	  var node = main_core.Tag.render(_templateObject2$6 || (_templateObject2$6 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<textarea\n\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\tname=\"", "\"\n\t\t\t\tplaceholder=\"", "\"\n\t\t\t\t", "\n\t\t\t></textarea>\n\t\t"])), main_core.Text.encode(this.getName()), main_core.Text.encode(babelHelpers.classPrivateFieldGet(this, _placeholder)), this.isEnable() ? '' : 'readonly');
	  node.value = babelHelpers.classPrivateFieldGet(this, _defaultValue);
	  return node;
	}

	var _templateObject$7, _templateObject2$7, _templateObject3$3;
	function _classPrivateFieldInitSpec$8(obj, privateMap, value) { _checkPrivateRedeclaration$8(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$8(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _hintDesc = /*#__PURE__*/new WeakMap();
	var _hintBlock = /*#__PURE__*/new WeakMap();
	var TextInputInline = /*#__PURE__*/function (_TextInput) {
	  babelHelpers.inherits(TextInputInline, _TextInput);
	  function TextInputInline(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, TextInputInline);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextInputInline).call(this, params));
	    _classPrivateFieldInitSpec$8(babelHelpers.assertThisInitialized(_this), _hintDesc, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$8(babelHelpers.assertThisInitialized(_this), _hintBlock, {
	      writable: true,
	      value: void 0
	    });
	    _this.valueColor = main_core.Type.isBoolean(params.valueColor) === true ? '--color-blue' : '';
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintDesc, main_core.Type.isStringFilled(params.hintDesc) ? params.hintDesc : '');
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _hintBlock, main_core.Tag.render(_templateObject$7 || (_templateObject$7 = babelHelpers.taggedTemplateLiteral(["<div></div>"]))));
	    _this.getInputNode().addEventListener('keyup', function (event) {
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _hintBlock));
	      main_core.Dom.append(_this.renderHint(), babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _hintBlock));
	    });
	    return _this;
	  }
	  babelHelpers.createClass(TextInputInline, [{
	    key: "renderContentField",
	    value: function renderContentField() {
	      var lockElement = this.isEnable ? null : this.renderLockElement();
	      var content = main_core.Tag.render(_templateObject2$7 || (_templateObject2$7 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__field-selector --field-separator\">\n\t\t\t\t<div class=\"ui-section__field-container\">\t\t\t\n\t\t\t\t\t<div class=\"ui-section__field-label_box\">\n\t\t\t\t\t\t<label for=\"", "\" class=\"ui-section__field-label\">", "</label> \n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-section__field-inline-box\">\n\t\t\t\t\t\t<div class=\"ui-section__field\">\n\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-block ui-ctl-w100\">\n\t\t\t\t\t\t\t\t", "\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"ui-section__field-inline-separator\"></div>\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.getId(), main_core.Text.encode(this.getName()), this.getLabel(), lockElement, this.getInputNode(), babelHelpers.classPrivateFieldGet(this, _hintBlock), this.renderErrors());
	      main_core.Dom.append(this.renderHint(), babelHelpers.classPrivateFieldGet(this, _hintBlock));
	      return content;
	    }
	  }, {
	    key: "prefixId",
	    value: function prefixId() {
	      return 'text_inline_';
	    }
	  }, {
	    key: "renderHint",
	    value: function renderHint() {
	      return main_core.Tag.render(_templateObject3$3 || (_templateObject3$3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-section__hint\">\n\t\t\t\t<div class=\"ui-section__title\">", "</div>\n\t\t\t\t<div class=\"ui-section__value ", "\">", "</div>\n\t\t\t\t<div class=\"ui-section__description\">", "</div>\n\t\t\t</div>\n\t\t"])), this.hintTitle, this.valueColor, main_core.Text.encode(this.getInputNode().value), babelHelpers.classPrivateFieldGet(this, _hintDesc));
	    }
	  }]);
	  return TextInputInline;
	}(TextInput);

	var _templateObject$8, _templateObject2$8;
	function _createForOfIteratorHelper$3(o, allowArrayLike) { var it = typeof Symbol !== "undefined" && o[Symbol.iterator] || o["@@iterator"]; if (!it) { if (Array.isArray(o) || (it = _unsupportedIterableToArray$3(o)) || allowArrayLike && o && typeof o.length === "number") { if (it) o = it; var i = 0; var F = function F() {}; return { s: F, n: function n() { if (i >= o.length) return { done: true }; return { done: false, value: o[i++] }; }, e: function e(_e) { throw _e; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var normalCompletion = true, didErr = false, err; return { s: function s() { it = it.call(o); }, n: function n() { var step = it.next(); normalCompletion = step.done; return step; }, e: function e(_e2) { didErr = true; err = _e2; }, f: function f() { try { if (!normalCompletion && it["return"] != null) it["return"](); } finally { if (didErr) throw err; } } }; }
	function _unsupportedIterableToArray$3(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray$3(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray$3(o, minLen); }
	function _arrayLikeToArray$3(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }
	function _classPrivateMethodInitSpec$6(obj, privateSet) { _checkPrivateRedeclaration$9(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$9(obj, privateMap, value) { _checkPrivateRedeclaration$9(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$9(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet$6(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _entitySelector = /*#__PURE__*/new WeakMap();
	var _defaultValues = /*#__PURE__*/new WeakMap();
	var _inputContainer = /*#__PURE__*/new WeakMap();
	var _encode = /*#__PURE__*/new WeakMap();
	var _decode = /*#__PURE__*/new WeakMap();
	var _defaultTags = /*#__PURE__*/new WeakMap();
	var _className = /*#__PURE__*/new WeakMap();
	var _enableUsers = /*#__PURE__*/new WeakMap();
	var _enableAll = /*#__PURE__*/new WeakMap();
	var _enableDepartments = /*#__PURE__*/new WeakMap();
	var _getDepartamentsSelectMode = /*#__PURE__*/new WeakSet();
	var _createInputElement = /*#__PURE__*/new WeakSet();
	var _initInput = /*#__PURE__*/new WeakSet();
	var _triggerEventChange = /*#__PURE__*/new WeakSet();
	var UserSelector = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(UserSelector, _BaseField);
	  function UserSelector(params) {
	    var _this;
	    babelHelpers.classCallCheck(this, UserSelector);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(UserSelector).call(this, params));
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _triggerEventChange);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _initInput);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _createInputElement);
	    _classPrivateMethodInitSpec$6(babelHelpers.assertThisInitialized(_this), _getDepartamentsSelectMode);
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _entitySelector, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _defaultValues, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _inputContainer, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _encode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _decode, {
	      writable: true,
	      value: null
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _defaultTags, {
	      writable: true,
	      value: []
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _className, {
	      writable: true,
	      value: ''
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _enableUsers, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _enableAll, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$9(babelHelpers.assertThisInitialized(_this), _enableDepartments, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _encode, main_core.Type.isFunction(params.encodeValue) ? params.encodeValue : null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _decode, main_core.Type.isFunction(params.decodeValue) ? params.decodeValue : null);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _inputContainer, main_core.Tag.render(_templateObject$8 || (_templateObject$8 = babelHelpers.taggedTemplateLiteral(["<div class=\"ui-section__input-container\"></div>"]))));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _className, params.className);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _enableUsers, params.enableUsers !== false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _enableAll, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableUsers) && params.enableAll !== false);
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _enableDepartments, params.enableDepartments === true);
	    _classPrivateMethodGet$6(babelHelpers.assertThisInitialized(_this), _initInput, _initInput2).call(babelHelpers.assertThisInitialized(_this), params.values);
	    var entities = [];
	    if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableUsers)) {
	      entities.push({
	        id: 'user',
	        options: {
	          intranetUsersOnly: true
	        }
	      });
	    }
	    if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableUsers) || babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableDepartments)) {
	      entities.push({
	        id: 'department',
	        options: {
	          selectMode: _classPrivateMethodGet$6(babelHelpers.assertThisInitialized(_this), _getDepartamentsSelectMode, _getDepartamentsSelectMode2).call(babelHelpers.assertThisInitialized(_this)),
	          allowFlatDepartments: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableDepartments),
	          allowSelectRootDepartment: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableDepartments)
	        }
	      });
	    }
	    if (babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableAll)) {
	      entities.push({
	        id: 'meta-user',
	        options: {
	          'all-users': babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _enableAll) // All users
	        }
	      });
	    }

	    if (params.entities) {
	      entities.push.apply(entities, babelHelpers.toConsumableArray(params.entities));
	    }
	    var multiple = params.multiple !== false;
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _entitySelector, new ui_entitySelector.TagSelector({
	      id: _this.getId(),
	      textBoxAutoHide: false,
	      textBoxWidth: 350,
	      maxHeight: 99,
	      dialogOptions: {
	        id: _this.getId(),
	        preselectedItems: babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _defaultValues),
	        multiple: multiple,
	        hideOnDeselect: !multiple,
	        events: {
	          'Item:onSelect': _this.onChangeSelector.bind(babelHelpers.assertThisInitialized(_this)),
	          'Item:onDeselect': _this.onChangeSelector.bind(babelHelpers.assertThisInitialized(_this))
	        },
	        entities: entities
	      },
	      multiple: multiple
	    }));
	    babelHelpers.classPrivateFieldSet(babelHelpers.assertThisInitialized(_this), _defaultTags, babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _entitySelector).getTags());
	    if (!_this.isEnable()) {
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _entitySelector).hideAddButton();
	      babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _entitySelector).getTextBox().readOnly = true;
	      main_core.Dom.adjust(babelHelpers.classPrivateFieldGet(babelHelpers.assertThisInitialized(_this), _entitySelector).getContainer(), {
	        events: {
	          click: function click(event) {
	            event.preventDefault();
	            if (!main_core.Type.isNil(_this.getHelpMessage())) {
	              _this.getHelpMessage().show();
	            }
	          }
	        }
	      });
	    }
	    return _this;
	  }
	  babelHelpers.createClass(UserSelector, [{
	    key: "getSelector",
	    value: function getSelector() {
	      return babelHelpers.classPrivateFieldGet(this, _entitySelector);
	    }
	  }, {
	    key: "getInputNode",
	    value: function getInputNode() {
	      return babelHelpers.classPrivateFieldGet(this, _entitySelector).getContainer();
	    }
	  }, {
	    key: "getErrorBox",
	    value: function getErrorBox() {
	      return babelHelpers.classPrivateFieldGet(this, _entitySelector).getOuterContainer();
	    }
	  }, {
	    key: "prefixId",
	    value: function prefixId() {
	      return 'user_selector_';
	    }
	  }, {
	    key: "renderContentField",
	    value: function renderContentField() {
	      var content = main_core.Tag.render(_templateObject2$8 || (_templateObject2$8 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div id=\"", "\" class=\"ui-section__field-user_selector ", "\">\n\t\t\t\t<div class=\"ui-section__field\">\n\t\t\t\t\t<div class=\"ui-section__field-label\">\n\t\t\t\t\t\t", "\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t", "\n\t\t\t\t<div class=\"ui-section__input-box\">\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t"])), this.getId(), babelHelpers.classPrivateFieldGet(this, _className), this.getLabel(), this.renderErrors(), babelHelpers.classPrivateFieldGet(this, _inputContainer));
	      babelHelpers.classPrivateFieldGet(this, _entitySelector).renderTo(content.querySelector('.ui-section__field'));
	      return content;
	    }
	  }, {
	    key: "onChangeSelector",
	    value: function onChangeSelector(event) {
	      var _this2 = this;
	      var selectedItems = event.target.getSelectedItems();
	      main_core.Dom.clean(babelHelpers.classPrivateFieldGet(this, _inputContainer));
	      if (main_core.Type.isArray(selectedItems)) {
	        selectedItems.forEach(function (item) {
	          var type = '';
	          switch (item.entityId) {
	            case 'meta-user':
	              type = 'AU';
	              break;
	            case 'department':
	              if (item.id.toString().split(':')[1] === 'F') {
	                type = 'D';
	              } else {
	                type = 'DR';
	              }
	              break;
	            case 'user':
	              type = 'U';
	              break;
	            default:
	              break;
	          }
	          if (type) {
	            var value = main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(_this2, _encode)) ? babelHelpers.classPrivateFieldGet(_this2, _encode).call(_this2, {
	              id: item.id,
	              type: type
	            }) : item.id;
	            if (value) {
	              main_core.Dom.append(_classPrivateMethodGet$6(_this2, _createInputElement, _createInputElement2).call(_this2, value), babelHelpers.classPrivateFieldGet(_this2, _inputContainer));
	            }
	          }
	        });
	      }
	      _classPrivateMethodGet$6(this, _triggerEventChange, _triggerEventChange2).call(this);
	    }
	  }, {
	    key: "setValues",
	    value: function setValues(values) {
	      if (main_core.Type.isArray(values)) {
	        var _iterator = _createForOfIteratorHelper$3(values),
	          _step;
	        try {
	          for (_iterator.s(); !(_step = _iterator.n()).done;) {
	            var userId = _step.value;
	            var value = main_core.Type.isFunction(babelHelpers.classPrivateFieldGet(this, _decode)) ? babelHelpers.classPrivateFieldGet(this, _decode).call(this, userId) : userId;
	            var item = [];
	            if (main_core.Type.isObject(value) && main_core.Type.isString(value.type) && main_core.Type.isString(value.id)) {
	              switch (value.type) {
	                case 'AU':
	                  item = ['meta-user', 'all-users'];
	                  break;
	                case 'DR':
	                  if (!babelHelpers.classPrivateFieldGet(this, _enableDepartments)) {
	                    continue;
	                  }
	                  item = ['department', value.id];
	                  break;
	                case 'D':
	                  if (!babelHelpers.classPrivateFieldGet(this, _enableDepartments)) {
	                    continue;
	                  }
	                  item = ['department', value.id.toString() + ':F'];
	                  break;
	                case 'U':
	                  item = ['user', value.id];
	                  break;
	                default:
	                  continue;
	              }
	            }
	            if (main_core.Type.isArrayFilled(value)) {
	              item = value;
	            }
	            babelHelpers.classPrivateFieldGet(this, _defaultValues).push(item);
	          }
	        } catch (err) {
	          _iterator.e(err);
	        } finally {
	          _iterator.f();
	        }
	      } else {
	        babelHelpers.classPrivateFieldSet(this, _defaultValues, []);
	      }
	    }
	  }]);
	  return UserSelector;
	}(BaseField);
	function _getDepartamentsSelectMode2() {
	  if (babelHelpers.classPrivateFieldGet(this, _enableUsers) && babelHelpers.classPrivateFieldGet(this, _enableDepartments)) {
	    return 'usersAndDepartments';
	  }
	  if (babelHelpers.classPrivateFieldGet(this, _enableUsers) && !babelHelpers.classPrivateFieldGet(this, _enableDepartments)) {
	    return 'usersOnly';
	  }
	  return 'departmentsOnly';
	}
	function _createInputElement2(value) {
	  return main_core.Dom.create('input', {
	    attrs: {
	      name: this.getName(),
	      value: main_core.Text.encode(value),
	      type: 'text'
	    },
	    style: {
	      display: 'none'
	    }
	  });
	}
	function _initInput2(values) {
	  if (main_core.Type.isArray(values)) {
	    var _iterator2 = _createForOfIteratorHelper$3(values),
	      _step2;
	    try {
	      for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
	        var value = _step2.value;
	        var input = _classPrivateMethodGet$6(this, _createInputElement, _createInputElement2).call(this, value);
	        main_core.Dom.append(input, babelHelpers.classPrivateFieldGet(this, _inputContainer));
	      }
	    } catch (err) {
	      _iterator2.e(err);
	    } finally {
	      _iterator2.f();
	    }
	    this.setValues(values);
	  }
	}
	function _triggerEventChange2() {
	  var input = babelHelpers.classPrivateFieldGet(this, _inputContainer).firstChild;
	  var form;
	  if (main_core.Type.isNil(input)) {
	    input = _classPrivateMethodGet$6(this, _createInputElement, _createInputElement2).call(this, '');
	    main_core.Dom.append(input, babelHelpers.classPrivateFieldGet(this, _inputContainer));
	    form = input.form;
	    main_core.Dom.remove(input);
	  } else {
	    form = input.form;
	  }
	  form.dispatchEvent(new Event('change'));
	}

	exports.Checker = Checker;
	exports.InlineChecker = InlineChecker;
	exports.ItemPicker = ItemPicker;
	exports.Selector = Selector;
	exports.SelectorMenu = SelectorMenu;
	exports.SingleChecker = SingleChecker;
	exports.TextInput = TextInput;
	exports.TextArea = TextArea;
	exports.TextInputInline = TextInputInline;
	exports.UserSelector = UserSelector;
	exports.BaseField = BaseField;

}((this.BX.UI.FormElements = this.BX.UI.FormElements || {}),BX.UI,BX.Event,BX.UI,BX.Main,BX.UI,BX,BX.UI.EntitySelector));
//# sourceMappingURL=view.bundle.js.map
