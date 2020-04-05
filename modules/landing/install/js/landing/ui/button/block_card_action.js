;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements concrete interface of card action button
	 *
	 * @extends {BX.Landing.UI.Button.Action}
	 *
	 * @param {?string} id
	 * @param {?object} [options]
	 * @constructor
	 */
	BX.Landing.UI.Button.CardAction = function(id, options)
	{
		BX.Landing.UI.Button.Action.apply(this, arguments);
		this.layout.classList.add("landing-ui-button-card-action");
	};


	BX.Landing.UI.Button.CardAction.prototype = {
		constructor: BX.Landing.UI.Button.Action,
		__proto__: BX.Landing.UI.Button.Action.prototype
	};
})();