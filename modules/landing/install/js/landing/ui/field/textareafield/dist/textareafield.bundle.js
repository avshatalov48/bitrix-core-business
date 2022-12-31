this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_ui_field_basefield) {
	'use strict';

	var _templateObject;
	var TextareaField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(TextareaField, _BaseField);

	  function TextareaField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, TextareaField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(TextareaField).call(this, options));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-textarea');
	    _this.onContentChange = _this.onContentChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onMousewheel = _this.onMousewheel.bind(babelHelpers.assertThisInitialized(_this));
	    main_core.Event.bind(_this.input, 'input', _this.onContentChange);
	    main_core.Event.bind(_this.input, 'keydown', _this.onContentChange);
	    main_core.Event.bind(_this.input, 'mousewheel', _this.onMousewheel);
	    _this.input.value = main_core.Text.encode(_this.content);

	    if (main_core.Type.isNumber(_this.options.height)) {
	      main_core.Dom.style(_this.input, 'min-height', "".concat(_this.options.height, "px"));
	    }

	    setTimeout(function () {
	      _this.adjustHeight();
	    }, 20);
	    return _this;
	  }

	  babelHelpers.createClass(TextareaField, [{
	    key: "createInput",
	    value: function createInput() {
	      return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<textarea class=\"landing-ui-field-input\">", "</textarea>\n\t\t"])), this.content);
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onMousewheel",
	    value: function onMousewheel(event) {
	      event.stopPropagation();
	    } // eslint-disable-next-line class-methods-use-this

	  }, {
	    key: "onPaste",
	    value: function onPaste() {// Prevent BX.Landing.UI.Field.BaseField.onPaste
	    }
	  }, {
	    key: "onContentChange",
	    value: function onContentChange() {
	      this.adjustHeight();
	      this.onValueChangeHandler(this);
	    }
	  }, {
	    key: "adjustHeight",
	    value: function adjustHeight() {
	      this.input.style.height = '0px';
	      this.input.style.height = "".concat(Math.min(this.input.scrollHeight, 180), "px");
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.input.value;
	    }
	  }]);
	  return TextareaField;
	}(landing_ui_field_basefield.BaseField);

	exports.TextareaField = TextareaField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing.UI.Field));
//# sourceMappingURL=textareafield.bundle.js.map
