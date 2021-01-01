this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_field_switch,main_core,landing_loc) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Field
	 */

	var SmallSwitch = /*#__PURE__*/function (_Switch) {
	  babelHelpers.inherits(SmallSwitch, _Switch);

	  function SmallSwitch(options) {
	    var _this;

	    babelHelpers.classCallCheck(this, SmallSwitch);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(SmallSwitch).call(this, options));

	    _this.setEventNamespace('BX.Landing.UI.Field.SmallSwitch');

	    main_core.Dom.addClass(_this.layout, 'landing-ui-field-small-switch');
	    main_core.Dom.attr(_this.slider, 'data-text', landing_loc.Loc.getMessage('LANDING_SMALL_SWITCHER_ON'));

	    _this.adjustLabel();

	    return _this;
	  }

	  babelHelpers.createClass(SmallSwitch, [{
	    key: "adjustLabel",
	    value: function adjustLabel() {
	      if (this.getValue()) {
	        main_core.Dom.attr(this.slider, 'data-text', landing_loc.Loc.getMessage('LANDING_SMALL_SWITCHER_ON'));
	      } else {
	        main_core.Dom.attr(this.slider, 'data-text', landing_loc.Loc.getMessage('LANDING_SMALL_SWITCHER_OFF'));
	      }
	    }
	  }, {
	    key: "onChange",
	    value: function onChange() {
	      babelHelpers.get(babelHelpers.getPrototypeOf(SmallSwitch.prototype), "onChange", this).call(this);
	      this.adjustLabel();
	    }
	  }]);
	  return SmallSwitch;
	}(landing_ui_field_switch.Switch);

	exports.SmallSwitch = SmallSwitch;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX.Landing.UI.Field,BX,BX.Landing));
//# sourceMappingURL=smallswitch.bundle.js.map
