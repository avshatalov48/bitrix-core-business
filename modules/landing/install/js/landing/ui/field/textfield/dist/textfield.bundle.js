this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,main_core,main_core_events,landing_ui_component_internal) {
	'use strict';

	var TextField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextField, _BaseField);

	  function TextField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, TextField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextField).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.TextField');

	    _this.subscribeFromOptions(landing_ui_component_internal.fetchEventsFromOptions(options));

	    _this.bind = _this.options.bind;
	    _this.changeTagButton = _this.options.changeTagButton;
	    _this.onInputHandler = main_core.Type.isFunction(_this.options.onInput) ? _this.options.onInput : function () {};
	    _this.onValueChangeHandler = main_core.Type.isFunction(_this.options.onValueChange) ? _this.options.onValueChange : function () {};
	    _this.textOnly = main_core.Type.isBoolean(_this.options.textOnly) ? _this.options.textOnly : false;
	    _this.content = _this.textOnly ? main_core.Text.encode(_this.content) : _this.content;
	    _this.input.innerHTML = _this.content;
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
	      BX.Landing.UI.Button.FontAction.hideAll();
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
	  }]);
	  return TextField;
	}(landing_ui_field_basefield.BaseField);

	exports.TextField = TextField;
	exports.Text = TextField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX,BX.Event,BX.Landing.UI.Component));
//# sourceMappingURL=textfield.bundle.js.map
