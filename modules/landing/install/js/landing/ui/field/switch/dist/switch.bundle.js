this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_basefield,main_core) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var Switch = /*#__PURE__*/function (_BaseField) {
	  babelHelpers.inherits(Switch, _BaseField);

	  function Switch(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, Switch);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Switch).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.Switch');

	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-switch');
	    _this.value = options.value;
	    _this.id = "switch_".concat(main_core.Text.getRandom());
	    _this.onValueChangeHandler = main_core.Type.isFunction(options.onValueChange) ? options.onValueChange : function () {};
	    _this.label = main_core.Dom.create('label', {
	      props: {
	        className: 'landing-ui-field-switch-label'
	      },
	      attrs: {
	        "for": _this.id
	      },
	      html: _this.title
	    });
	    _this.checkbox = main_core.Dom.create('input', {
	      props: {
	        className: 'landing-ui-field-switch-checkbox'
	      },
	      attrs: {
	        type: 'checkbox',
	        id: _this.id
	      }
	    });
	    _this.slider = main_core.Dom.create('div', {
	      props: {
	        className: 'landing-ui-field-switch-slider'
	      }
	    });
	    main_core.Dom.append(_this.checkbox, _this.label);
	    main_core.Dom.append(_this.slider, _this.label);
	    main_core.Dom.append(_this.label, _this.input);

	    _this.setValue(_this.value);

	    main_core.Event.bind(_this.checkbox, 'change', _this.onChange.bind(babelHelpers.assertThisInitialized(_this)));
	    return _this;
	  }

	  babelHelpers.createClass(Switch, [{
	    key: "onChange",
	    value: function onChange() {
	      this.onValueChangeHandler();
	      this.emit('onChange');
	    }
	  }, {
	    key: "setValue",
	    value: function setValue(value) {
	      this.checkbox.checked = main_core.Text.toBoolean(value);
	    }
	  }, {
	    key: "getValue",
	    value: function getValue() {
	      return main_core.Text.toBoolean(this.checkbox.checked);
	    }
	  }]);
	  return Switch;
	}(landing_ui_field_basefield.BaseField);

	exports.Switch = Switch;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX));
//# sourceMappingURL=switch.bundle.js.map
