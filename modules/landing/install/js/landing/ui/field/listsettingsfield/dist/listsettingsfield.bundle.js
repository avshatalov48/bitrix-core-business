this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,landing_ui_field_textfield,main_core) {
	'use strict';

	var _templateObject;
	var ListItem = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ListItem, _BaseField);

	  function ListItem(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ListItem);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ListItem).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.ListSettingsField.ListItem');

	    _this.setValue(options);

	    return _this;
	  }

	  babelHelpers.createClass(ListItem, [{
	    key: "getTextField",
	    value: function getTextField() {
	      var _this2 = this;

	      return this.cache.remember('textField', function () {
	        return new landing_ui_field_textfield.TextField({
	          selector: 'label',
	          textOnly: true,
	          onChange: _this2.onTextChange.bind(_this2)
	        });
	      });
	    }
	  }, {
	    key: "onTextChange",
	    value: function onTextChange() {
	      this.emit('onChange');
	    }
	  }, {
	    key: "createInput",
	    value: function createInput() {
	      var _this3 = this;

	      return this.cache.remember('layout', function () {
	        return main_core.Tag.render(_templateObject || (_templateObject = babelHelpers.taggedTemplateLiteral(["\n\t\t\t\t<div class=\"landing-ui-field-list-settings-item-container\">\n\t\t\t\t\t", "\n\t\t\t\t\t", "\n\t\t\t\t</div>\n\t\t\t"])), _this3.getTextField().getLayout(), _this3.getCheckboxField().getLayout());
	      });
	    }
	  }, {
	    key: "getCheckboxField",
	    value: function getCheckboxField() {
	      var _this4 = this;

	      return this.cache.remember('checkbox', function () {
	        return new BX.Landing.UI.Field.Checkbox({
	          compact: true,
	          items: [{
	            name: '',
	            value: _this4.options.value
	          }],
	          onChange: _this4.onCheckboxChange.bind(_this4)
	        });
	      });
	    }
	  }, {
	    key: "onCheckboxChange",
	    value: function onCheckboxChange() {
	      this.emit('onChange');
	      this.adjustState();
	    }
	  }, {
	    key: "adjustState",
	    value: function adjustState() {
	      var checkboxField = this.getCheckboxField();
	      var textField = this.getTextField();

	      if (checkboxField.getValue().length > 0) {
	        textField.enable();
	      } else {
	        textField.disable();
	      }
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.getTextField().setValue(value.name);
	      this.getCheckboxField().setValue([value.checked ? value.value : '']);
	      this.adjustState();
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return {
	        label: this.getTextField().getValue(),
	        value: this.options.value,
	        checked: this.getCheckboxField().getValue().length > 0
	      };
	    }
	  }]);
	  return ListItem;
	}(landing_ui_field_basefield.BaseField);

	var _templateObject$1;

	function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }

	function _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { babelHelpers.defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }

	/**
	 * @memberOf BX.Landing.UI.Field
	 */
	var ListSettingsField = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(ListSettingsField, _BaseField);

	  function ListSettingsField(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, ListSettingsField);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(ListSettingsField).call(this, _objectSpread(_objectSpread({}, options), {}, {
	      textOnly: true
	    })));

	    _this.setEventNamespace('BX.Landing.UI.Field.ListSettingsField');

	    _this.onChange = _this.onChange.bind(babelHelpers.assertThisInitialized(_this));
	    _this.items = [];

	    _this.options.items.forEach(function (item) {
	      _this.addItem(item);
	    });

	    return _this;
	  }

	  babelHelpers.createClass(ListSettingsField, [{
	    key: "createInput",
	    value: function createInput() {
	      return main_core.Tag.render(_templateObject$1 || (_templateObject$1 = babelHelpers.taggedTemplateLiteral(["\n\t\t\t<div class=\"landing-ui-field-list-settings\"></div>\n\t\t"])));
	    }
	  }, {
	    key: "addItem",
	    value: function addItem(options) {
	      var item = new ListItem(options);
	      item.subscribe('onChange', this.onChange);
	      main_core.Dom.append(item.getLayout(), this.input);
	      this.items.push(item);
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      this.emit('onChange');
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return this.items.map(function (item) {
	        return item.getValue();
	      }).filter(function (item) {
	        return item.checked;
	      });
	    }
	  }]);
	  return ListSettingsField;
	}(landing_ui_field_basefield.BaseField);

	exports.ListSettingsField = ListSettingsField;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX.Landing.UI.Field,BX));
//# sourceMappingURL=listsettingsfield.bundle.js.map
