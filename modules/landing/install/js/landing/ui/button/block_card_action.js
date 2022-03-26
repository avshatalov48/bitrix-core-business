;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements concrete interface of card action button
	 *
	 * @extends {BX.Landing.UI.Button.ActionButton}
	 *
	 * @param {?string} id
	 * @param {?object} [options]
	 * @constructor
	 */
	BX.Landing.UI.Button.CardAction = function(id, options)
	{
		BX.Landing.UI.Button.ActionButton.apply(this, arguments);
		this.layout.classList.add("landing-ui-button-card-action");
	};


	BX.Landing.UI.Button.CardAction.prototype = {
		constructor: BX.Landing.UI.Button.ActionButton,
		__proto__: BX.Landing.UI.Button.ActionButton.prototype
	};
})();