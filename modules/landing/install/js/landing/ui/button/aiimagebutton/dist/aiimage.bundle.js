this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,landing_ui_button_basebutton) {
	'use strict';

	/**
	 * @memberOf BX.Landing.UI.Button
	 */
	var AiImageButton = /*#__PURE__*/function (_BaseButton) {
	  babelHelpers.inherits(AiImageButton, _BaseButton);
	  function AiImageButton(id, options) {
	    var _this;
	    babelHelpers.classCallCheck(this, AiImageButton);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(AiImageButton).call(this, id, options));
	    _this.layout.classList.add("landing-ui-button-ai-image");
	    return _this;
	  }
	  return AiImageButton;
	}(landing_ui_button_basebutton.BaseButton);

	exports.AiImageButton = AiImageButton;

}((this.BX.Landing.UI.Button = this.BX.Landing.UI.Button || {}),BX.Landing.UI.Button));
//# sourceMappingURL=aiimage.bundle.js.map
