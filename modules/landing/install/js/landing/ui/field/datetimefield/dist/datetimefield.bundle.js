this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,main_core,landing_ui_field_basefield,landing_loc) {
	'use strict';

	var DateTimeField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(DateTimeField, _BaseField);

	  function DateTimeField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, DateTimeField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DateTimeField).call(this, babelHelpers.objectSpread({}, options, {
	      content: options.content || options.value || '',
	      textOnly: true
	    })));
	    main_core.Event.bind(_this.input, 'click', _this.onInputClick.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(DateTimeField, [{
	    key: "getFormat",
	    value: function getFormat() {
	      return BX.date.convertBitrixFormat(landing_loc.Loc.getMessage(this.options.time ? 'FORMAT_DATETIME' : 'FORMAT_DATE'));
	    }
	  }, {
	    key: "showDatepicker",
	    value: function showDatepicker() {
	      var _this2 = this;

	      this.getContext().BX.calendar({
	        node: this.input,
	        field: this.input,
	        bTime: this.options.time,
	        value: BX.date.format(this.getFormat(), this.getValue() || new Date()),
	        bHideTime: !this.options.time,
	        callback_after: function callback_after(date) {
	          _this2.setValue(BX.date.format(_this2.getFormat(), date));
	        }
	      });
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      babelHelpers.get(babelHelpers.getPrototypeOf(DateTimeField.prototype), "setValue", this).call(this, value);
	      this.emit('onChange');
	    }
	  }, {
	    key: "onInputClick",
	    value: function onInputClick() {
	      this.showDatepicker();
	    }
	  }]);
	  return DateTimeField;
	}(landing_ui_field_basefield.BaseField);

	exports.DateTimeField = DateTimeField;

}((this.BX.Landing.Ui.Field = this.BX.Landing.Ui.Field || {}),BX,BX.Landing.UI.Field,BX.Landing));
//# sourceMappingURL=datetimefield.bundle.js.map
