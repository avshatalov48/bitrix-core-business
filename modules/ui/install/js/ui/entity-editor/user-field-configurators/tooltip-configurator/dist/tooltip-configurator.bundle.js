/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,main_core) {
	'use strict';

	var _templateObject, _templateObject2, _templateObject3, _templateObject4;
	function _classPrivateFieldInitSpec(obj, privateMap, value) { _checkPrivateRedeclaration(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	var _id = /*#__PURE__*/new WeakMap();
	var _inputText = /*#__PURE__*/new WeakMap();
	var _labelText = /*#__PURE__*/new WeakMap();
	var _configurator = /*#__PURE__*/new WeakMap();
	var _errorLayout = /*#__PURE__*/new WeakMap();
	var _inputLayout = /*#__PURE__*/new WeakMap();
	var _wrapper = /*#__PURE__*/new WeakMap();
	var TextInput = /*#__PURE__*/function () {
	  function TextInput(id, configurator, inputText) {
	    babelHelpers.classCallCheck(this, TextInput);
	    _classPrivateFieldInitSpec(this, _id, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _inputText, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _labelText, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _configurator, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _errorLayout, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _inputLayout, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec(this, _wrapper, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id, id !== '' ? id : main_core.util.getRandomString(4));
	    babelHelpers.classPrivateFieldSet(this, _configurator, configurator);
	    babelHelpers.classPrivateFieldSet(this, _inputText, inputText);
	    babelHelpers.classPrivateFieldSet(this, _labelText, main_core.Loc.getMessage('UI_ENTITY_EDITOR_UF_CONFIGURATORS_TOOLTIP_INPUT_LABEL'));
	    babelHelpers.classPrivateFieldSet(this, _errorLayout, null);
	    babelHelpers.classPrivateFieldSet(this, _inputLayout, null);
	    babelHelpers.classPrivateFieldSet(this, _wrapper, null);
	  }
	  babelHelpers.createClass(TextInput, [{
	    key: "prepareLayout",
	    value: function prepareLayout() {
	      babelHelpers.classPrivateFieldSet(this, _inputLayout, main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<textarea \n\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\trows=\"3\"\n\t\t\t\tid=\"", "\"\n\t\t\t>", "</textarea>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _id), babelHelpers.classPrivateFieldGet(this, _inputText)));
	      var label = main_core.Tag.render(_templateObject2 || (_templateObject2 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<label class=\"ui-entity-editor-block-title\">\n\t\t\t\t", "\n\t\t\t</label>\n\t\t"])), babelHelpers.classPrivateFieldGet(this, _labelText));
	      babelHelpers.classPrivateFieldSet(this, _wrapper, main_core.Tag.render(_templateObject3 || (_templateObject3 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"ui-entity-editor-new-field-visibility-wrapper\">\n\t\t\t\t", "\n\t\t\t\t", "\n\t\t\t</div>\n\t\t"])), label, babelHelpers.classPrivateFieldGet(this, _inputLayout)));
	      return babelHelpers.classPrivateFieldGet(this, _wrapper);
	    }
	  }, {
	    key: "renderError",
	    value: function renderError(errorText) {
	      if (main_core.Type.isNull(babelHelpers.classPrivateFieldGet(this, _errorLayout))) {
	        babelHelpers.classPrivateFieldSet(this, _errorLayout, main_core.Tag.render(_templateObject4 || (_templateObject4 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<label class=\"ui-entity-editor-user-field-setting-error-text\"></label>\n\t\t\t"]))));
	        babelHelpers.classPrivateFieldGet(this, _wrapper).append(babelHelpers.classPrivateFieldGet(this, _errorLayout));
	      }
	      babelHelpers.classPrivateFieldGet(this, _errorLayout).innerHTML = errorText;
	    }
	  }, {
	    key: "adjustVisibility",
	    value: function adjustVisibility() {
	      if (babelHelpers.classPrivateFieldGet(this, _configurator).isInputEnabled()) {
	        main_core.Dom.show(babelHelpers.classPrivateFieldGet(this, _wrapper));
	      } else {
	        main_core.Dom.hide(babelHelpers.classPrivateFieldGet(this, _wrapper));
	      }
	    }
	  }, {
	    key: "getInputText",
	    value: function getInputText() {
	      return babelHelpers.classPrivateFieldGet(this, _inputLayout).value;
	    }
	  }]);
	  return TextInput;
	}();

	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration$1(obj, privateSet); privateSet.add(obj); }
	function _classPrivateFieldInitSpec$1(obj, privateMap, value) { _checkPrivateRedeclaration$1(obj, privateMap); privateMap.set(obj, value); }
	function _checkPrivateRedeclaration$1(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var MAX_TOOLTIP_LENGTH = 250;
	var _id$1 = /*#__PURE__*/new WeakMap();
	var _editor = /*#__PURE__*/new WeakMap();
	var _field = /*#__PURE__*/new WeakMap();
	var _previousTooltip = /*#__PURE__*/new WeakMap();
	var _caption = /*#__PURE__*/new WeakMap();
	var _textInput = /*#__PURE__*/new WeakMap();
	var _checkBox = /*#__PURE__*/new WeakMap();
	var _isShowInput = /*#__PURE__*/new WeakMap();
	var _checkBoxHandler = /*#__PURE__*/new WeakMap();
	var _onCheckBoxClick = /*#__PURE__*/new WeakSet();
	var TooltipConfigurator = /*#__PURE__*/function () {
	  function TooltipConfigurator(id, editor, field) {
	    var _babelHelpers$classPr, _babelHelpers$classPr2, _babelHelpers$classPr3;
	    babelHelpers.classCallCheck(this, TooltipConfigurator);
	    _classPrivateMethodInitSpec(this, _onCheckBoxClick);
	    _classPrivateFieldInitSpec$1(this, _id$1, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _editor, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _field, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _previousTooltip, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _caption, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _textInput, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _checkBox, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _isShowInput, {
	      writable: true,
	      value: void 0
	    });
	    _classPrivateFieldInitSpec$1(this, _checkBoxHandler, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldSet(this, _id$1, id !== '' ? id : main_core.util.getRandomString(4));
	    babelHelpers.classPrivateFieldSet(this, _editor, editor);
	    babelHelpers.classPrivateFieldSet(this, _field, field);
	    babelHelpers.classPrivateFieldSet(this, _previousTooltip, (_babelHelpers$classPr = (_babelHelpers$classPr2 = babelHelpers.classPrivateFieldGet(this, _field)) === null || _babelHelpers$classPr2 === void 0 ? void 0 : (_babelHelpers$classPr3 = _babelHelpers$classPr2._schemeElement) === null || _babelHelpers$classPr3 === void 0 ? void 0 : _babelHelpers$classPr3._hint) !== null && _babelHelpers$classPr !== void 0 ? _babelHelpers$classPr : '');
	    babelHelpers.classPrivateFieldSet(this, _isShowInput, Boolean(babelHelpers.classPrivateFieldGet(this, _previousTooltip)));
	    babelHelpers.classPrivateFieldSet(this, _checkBoxHandler, _classPrivateMethodGet(this, _onCheckBoxClick, _onCheckBoxClick2).bind(this));
	    babelHelpers.classPrivateFieldSet(this, _textInput, null);
	    babelHelpers.classPrivateFieldSet(this, _checkBox, null);
	    babelHelpers.classPrivateFieldSet(this, _caption, main_core.Loc.getMessage('UI_ENTITY_EDITOR_UF_CONFIGURATORS_TOOLTIP_CHECKBOX_LABEL'));
	  }
	  babelHelpers.createClass(TooltipConfigurator, [{
	    key: "validateInputText",
	    value: function validateInputText() {
	      var input = this.getInput();
	      var text = input.getInputText();
	      if (text.length > MAX_TOOLTIP_LENGTH) {
	        var errorText = main_core.Loc.getMessage('UI_ENTITY_EDITOR_UF_CONFIGURATORS_TOOLTIP_LENGTH_VALIDATION_ERROR', {
	          '#MAX_LENGTH#': MAX_TOOLTIP_LENGTH
	        });
	        input.renderError(errorText);
	        return false;
	      }
	      input.renderError('');
	      return true;
	    }
	  }, {
	    key: "setCheckBox",
	    value: function setCheckBox(checkBox) {
	      if (!main_core.Type.isNull(babelHelpers.classPrivateFieldGet(this, _checkBox))) {
	        main_core.Event.unbind(babelHelpers.classPrivateFieldGet(this, _checkBox), 'click', babelHelpers.classPrivateFieldGet(this, _checkBoxHandler));
	      }
	      babelHelpers.classPrivateFieldSet(this, _checkBox, checkBox);
	      if (this.isInputEnabled()) {
	        babelHelpers.classPrivateFieldGet(this, _checkBox).checked = true;
	      }
	      if (!main_core.Type.isNull(babelHelpers.classPrivateFieldGet(this, _checkBox))) {
	        main_core.Event.bind(babelHelpers.classPrivateFieldGet(this, _checkBox), 'click', babelHelpers.classPrivateFieldGet(this, _checkBoxHandler));
	      }
	    }
	  }, {
	    key: "getTooltip",
	    value: function getTooltip() {
	      return this.isInputEnabled() ? this.getInput().getInputText() : '';
	    }
	  }, {
	    key: "getCaption",
	    value: function getCaption() {
	      return babelHelpers.classPrivateFieldGet(this, _caption);
	    }
	  }, {
	    key: "isInputEnabled",
	    value: function isInputEnabled() {
	      return babelHelpers.classPrivateFieldGet(this, _isShowInput);
	    }
	  }, {
	    key: "getInput",
	    value: function getInput() {
	      if (main_core.Type.isNull(babelHelpers.classPrivateFieldGet(this, _textInput))) {
	        babelHelpers.classPrivateFieldSet(this, _textInput, new TextInput(babelHelpers.classPrivateFieldGet(this, _id$1), this, babelHelpers.classPrivateFieldGet(this, _previousTooltip)));
	      }
	      return babelHelpers.classPrivateFieldGet(this, _textInput);
	    }
	  }]);
	  return TooltipConfigurator;
	}();
	function _onCheckBoxClick2() {
	  babelHelpers.classPrivateFieldSet(this, _isShowInput, !babelHelpers.classPrivateFieldGet(this, _isShowInput));
	  babelHelpers.classPrivateFieldGet(this, _textInput).adjustVisibility();
	}

	exports.TooltipConfigurator = TooltipConfigurator;

}((this.BX.UI.EntityEditorUfConfigurators = this.BX.UI.EntityEditorUfConfigurators || {}),BX));
//# sourceMappingURL=tooltip-configurator.bundle.js.map
