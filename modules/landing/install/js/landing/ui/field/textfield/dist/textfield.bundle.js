/* eslint-disable */
this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,main_core,main_core_events,landing_ui_component_internal) {
	'use strict';

	var _templateObject;
	function _classPrivateMethodInitSpec(obj, privateSet) { _checkPrivateRedeclaration(obj, privateSet); privateSet.add(obj); }
	function _checkPrivateRedeclaration(obj, privateCollection) { if (privateCollection.has(obj)) { throw new TypeError("Cannot initialize the same private elements twice on an object"); } }
	function _classPrivateMethodGet(receiver, privateSet, fn) { if (!privateSet.has(receiver)) { throw new TypeError("attempted to get private field on non-instance"); } return fn; }
	var _createFooter = /*#__PURE__*/new WeakSet();
	var TextField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextField, _BaseField);
	  function TextField(options) {
	    var _this$options$footerT;
	    var _this;
	    babelHelpers.classCallCheck(this, TextField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextField).call(this, options));
	    _classPrivateMethodInitSpec(babelHelpers.assertThisInitialized(_this), _createFooter);
	    _this.setEventNamespace('BX.Landing.UI.Field.TextField');
	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));
	    _this.bind = _this.options.bind;
	    _this.changeTagButton = _this.options.changeTagButton;
	    _this.onInputHandler = main_core.Type.isFunction(_this.options.onInput) ? _this.options.onInput : function () {};
	    _this.onValueChangeHandler = main_core.Type.isFunction(_this.options.onValueChange) ? _this.options.onValueChange : function () {};
	    _this.textOnly = main_core.Type.isBoolean(_this.options.textOnly) ? _this.options.textOnly : false;
	    _this.content = _this.textOnly ? main_core.Text.encode(_this.content) : _this.content;
	    _this.input.innerHTML = _this.content;
	    _classPrivateMethodGet(babelHelpers.assertThisInitialized(_this), _createFooter, _createFooter2).call(babelHelpers.assertThisInitialized(_this));
	    _this.setFooterText((_this$options$footerT = _this.options.footerText) !== null && _this$options$footerT !== void 0 ? _this$options$footerT : '');
	    _this.onInputClick = _this.onInputClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onInputMousedown = _this.onInputMousedown.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDocumentMouseup = _this.onDocumentMouseup.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onInputInput = _this.onInputInput.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDocumentClick = _this.onDocumentClick.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDocumentKeydown = _this.onDocumentKeydown.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onInputKeydown = _this.onInputKeydown.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(_this.input, 'click', _this.onInputClick);
	    main_core.Event.bind(_this.input, 'mousedown', _this.onInputMousedown);
	    main_core.Event.bind(_this.input, 'input', _this.onInputInput);
	    main_core.Event.bind(_this.input, 'keydown', _this.onInputKeydown);
	    main_core.Event.bind(document, 'click', _this.onDocumentClick);
	    main_core.Event.bind(document, 'keydown', _this.onDocumentKeydown);
	    main_core.Event.bind(document, 'mouseup', _this.onDocumentMouseup);
	    return _this;
	  }
	  babelHelpers.createClass(TextField, [{
	    key: "onInputInput",
	    value: function onInputInput() {
	      this.onInputHandler(this.input.innerText);
	      this.onValueChangeHandler(this);
	      var event = new main_core_events.BaseEvent({
	        data: {
	          value: this.getValue()
	        },
	        compatData: [this.getValue()]
	      });
	      this.emit('onChange', event);
	    }
	  }, {
	    key: "onDocumentKeydown",
	    value: function onDocumentKeydown(event) {
	      if (event.keyCode === 27) {
	        if (this.isEditable()) {
	          if (this === BX.Landing.UI.Field.BaseField.currentField) {
	            BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	          }
	          this.disableEdit();
	        }
	      }
	    }
	  }, {
	    key: "onInputKeydown",
	    value: function onInputKeydown(event) {
	      if (event.keyCode === 13) {
	        if (this.isTextOnly()) {
	          event.preventDefault();
	        }
	      }
	    }
	  }, {
	    key: "enableTextOnly",
	    value: function enableTextOnly() {
	      this.textOnly = true;
	      this.input.innerHTML = "".concat(this.input.innerText).trim();
	    }
	  }, {
	    key: "disableTextOnly",
	    value: function disableTextOnly() {
	      this.textOnly = false;
	    }
	  }, {
	    key: "isTextOnly",
	    value: function isTextOnly() {
	      return this.textOnly;
	    }
	  }, {
	    key: "isContentEditable",
	    value: function isContentEditable() {
	      return this.contentEditable !== false;
	    }
	  }, {
	    key: "onDocumentClick",
	    value: function onDocumentClick() {
	      if (this.isEditable() && !this.fromInput) {
	        if (this === BX.Landing.UI.Field.BaseField.currentField) {
	          BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	        }
	        this.disableEdit();
	      }
	      this.fromInput = false;
	    }
	  }, {
	    key: "onDocumentMouseup",
	    value: function onDocumentMouseup() {
	      var _this2 = this;
	      setTimeout(function () {
	        _this2.fromInput = false;
	      }, 10);
	    }
	  }, {
	    key: "onInputClick",
	    value: function onInputClick(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.fromInput = false;
	    }
	  }, {
	    key: "onInputMousedown",
	    value: function onInputMousedown(event) {
	      this.enableEdit();
	      BX.Landing.UI.Tool.ColorPicker.hideAll();
	      requestAnimationFrame(function () {
	        if (event.target.nodeName === 'A') {
	          var range = document.createRange();
	          range.selectNode(event.target);
	          window.getSelection().removeAllRanges();
	          window.getSelection().addRange(range);
	        }
	      });
	      this.fromInput = true;
	      event.stopPropagation();
	    }
	  }, {
	    key: "enableEdit",
	    value: function enableEdit() {
	      if (!this.isEditable()) {
	        if (this !== BX.Landing.UI.Field.BaseField.currentField && BX.Landing.UI.Field.BaseField.currentField !== null) {
	          BX.Landing.UI.Field.BaseField.currentField.disableEdit();
	        }
	        BX.Landing.UI.Field.BaseField.currentField = this;
	        if (!this.isTextOnly()) {
	          if (this.changeTagButton) {
	            this.changeTagButton.onChangeHandler = this.onChangeTag.bind(this);
	          }
	          BX.Landing.UI.Panel.EditorPanel.getInstance().show(this.layout, null, this.changeTagButton ? [this.changeTagButton] : null);
	          this.input.contentEditable = true;
	        } else {
	          BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
	          this.input.contentEditable = true;
	        }
	        if (!this.isContentEditable()) {
	          this.input.contentEditable = false;
	        }
	      }
	    }
	  }, {
	    key: "onChangeTag",
	    value: function onChangeTag(value) {
	      this.tag = value;
	    }
	  }, {
	    key: "disableEdit",
	    value: function disableEdit() {
	      this.input.contentEditable = false;
	    }
	  }, {
	    key: "isEditable",
	    value: function isEditable() {
	      return this.input.isContentEditable;
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this.setValue('');
	    }
	  }, {
	    key: "adjustTags",
	    value: function adjustTags(element) {
	      if (element.lastChild && element.lastChild.nodeName === 'BR') {
	        main_core.Dom.remove(element.lastChild);
	        this.adjustTags(element);
	      }
	      return element;
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      if (this.textOnly) {
	        return this.input.innerText;
	      }
	      return this.adjustTags(main_core.Runtime.clone(this.input)).innerHTML.replace(/&nbsp;/g, '');
	    }
	  }, {
	    key: "setFooterText",
	    value: function setFooterText(text) {
	      this.footer.innerText = text;
	    }
	  }, {
	    key: "showFooter",
	    value: function showFooter() {
	      main_core.Dom.show(this.footer);
	    }
	  }, {
	    key: "hideFooter",
	    value: function hideFooter() {
	      main_core.Dom.hide(this.footer);
	    }
	  }, {
	    key: "setWarningStatus",
	    value: function setWarningStatus() {
	      main_core.Dom.addClass(this.getLayout(), 'landing-ui-field-warning');
	    }
	  }, {
	    key: "unsetWarningStatus",
	    value: function unsetWarningStatus() {
	      main_core.Dom.removeClass(this.layout, 'landing-ui-field-warning');
	    }
	  }]);
	  return TextField;
	}(landing_ui_field_basefield.BaseField);
	function _createFooter2() {
	  this.footer = main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["<div class=\"landing-ui-field-bottom ui-ctl-bottom\" hidden></div>"])));
	  main_core.Dom.append(this.footer, this.getLayout());
	}

	exports.TextField = TextField;
	exports.Text = TextField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX,BX.Event,BX.Landing.UI.Component));
//# sourceMappingURL=textfield.bundle.js.map
