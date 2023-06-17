;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements interface for works with AI (text) button.
	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @param {string} id
	 * @param {object} data
	 * @constructor
	 */
	BX.Landing.UI.Button.AiText = function(id, data)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
	};

	BX.Landing.UI.Button.AiText.prototype = {
		constructor: BX.Landing.UI.Button.AiText,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype
	};
})();