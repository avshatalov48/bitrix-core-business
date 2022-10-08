this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,ui_designTokens,landing_ui_form_baseform,main_core) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Form
	 */

	var BalloonForm = /*#__PURE__*/function (_BaseForm) {
	  babelHelpers.inherits(BalloonForm, _BaseForm);

	  function BalloonForm() {
	    var _this;

	    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, BalloonForm);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(BalloonForm).call(this, options));
	    main_core.Dom.addClass(_this.layout, 'landing-ui-form-balloon');
	    return _this;
	  }

	  return BalloonForm;
	}(landing_ui_form_baseform.BaseForm);

	exports.BalloonForm = BalloonForm;

}((this.BX.Landing.UI.Form = this.BX.Landing.UI.Form || {}),BX,BX.Landing.UI.Form,BX));
//# sourceMappingURL=balloonform.bundle.js.map
