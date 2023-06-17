;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements interface for works with color picker button
	 *
	 * @extends {BX.Landing.UI.Button.ColorAction}
	 *
	 * @param {string} id - Action id
	 * @param {?object} [options]
	 *
	 * @constructor
	 */
	BX.Landing.UI.Button.TextBackgroundAction = function(id, options)
	{
		BX.Landing.UI.Button.ColorAction.apply(this, arguments);
		this.layout.classList.remove("landing-ui-button-editor-action-color");
		this.layout.classList.add("landing-ui-button-editor-action-background");
	};

	BX.Landing.UI.Button.TextBackgroundAction.prototype = {
		constructor: BX.Landing.UI.Button.TextBackgroundAction,
		__proto__: BX.Landing.UI.Button.ColorAction.prototype,
	};
})();