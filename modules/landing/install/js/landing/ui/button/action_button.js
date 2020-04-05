;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements concrete interface of action button
	 *
	 * @extends {BX.Landing.UI.Button.BaseButton}
	 *
	 * @param {?string} id
	 * @param {?object} [options]
	 * @constructor
	 */
	BX.Landing.UI.Button.Action = function(id, options)
	{
		BX.Landing.UI.Button.BaseButton.apply(this, arguments);
		this.layout.classList.add("landing-ui-button-action");
	};


	BX.Landing.UI.Button.Action.prototype = {
		constructor: BX.Landing.UI.Button.Action,
		__proto__: BX.Landing.UI.Button.BaseButton.prototype
	};
})();