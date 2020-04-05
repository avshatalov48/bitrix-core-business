;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");

	/**
	 * Implements interface for works with card form
	 *
	 * @extends {BX.Landing.UI.Form.BaseForm}
	 * @param {{[title]: ?string, [label]: string, [labelBindings]: string|array, [preset]: object}} data
	 * @constructor
	 */
	BX.Landing.UI.Form.BalloonForm = function(data)
	{
		BX.Landing.UI.Form.BaseForm.apply(this, arguments);
		this.layout.classList.add("landing-ui-form-balloon");
	};


	BX.Landing.UI.Form.BalloonForm.prototype = {
		constructor: BX.Landing.UI.Form.BalloonForm,
		__proto__: BX.Landing.UI.Form.BaseForm.prototype
	};
})();