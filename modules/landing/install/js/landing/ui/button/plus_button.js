;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * Implements concrete interface of plus button
	 *
	 * @extends {BX.Landing.UI.Button.BaseButton}
	 *
	 * @param {?string} id
	 * @param {?object} [options]
	 * @constructor
	 */
	BX.Landing.UI.Button.Plus = function(id, options)
	{
		BX.Landing.UI.Button.BaseButton.apply(this, arguments);
		this.buttonClass = "landing-ui-button-plus";
		this.init();
	};


	BX.Landing.UI.Button.Plus.prototype = {
		constructor: BX.Landing.UI.Button.Plus,
		__proto__: BX.Landing.UI.Button.BaseButton.prototype,

		init: function()
		{
			this.layout.classList.add(this.buttonClass);
		}
	};
})();