this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_button_basebutton) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Button
	 */
	var AiButton = /*#__PURE__*/function (_BaseButton) {
	  babelHelpers.inherits(AiButton, _BaseButton);
	  function AiButton(id, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, AiButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AiButton).call(this, id, options));
	    _this.layout.classList.add("landing-ui-button-ai");
	    return _this;
	  }
	  return AiButton;
	}(landing_ui_button_basebutton.BaseButton);

	exports.AiButton = AiButton;

}((this.BX.Landing.UI.Button = this.BX.Landing.UI.Button || {}),BX.Landing.UI.Button));
//# sourceMappingURL=aibutton.bundle.js.map
