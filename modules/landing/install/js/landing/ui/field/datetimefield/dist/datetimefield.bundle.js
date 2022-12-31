this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.Ui = this.BX.Landing.Ui || {};
(function (exports,main_core,landing_ui_field_basefield,landing_loc) {
	'use strict';

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }
	var DateTimeField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(DateTimeField, _BaseField);

	  function DateTimeField() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, DateTimeField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(DateTimeField).call(this, _objectSpread(_objectSpread({}, options), {}, {
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
