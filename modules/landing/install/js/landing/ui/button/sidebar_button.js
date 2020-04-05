;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	var addClass = BX.Landing.Utils.addClass;


	/**
	 * Implements interface of sidebar button
	 *
	 * @extends {BX.Landing.UI.Button.BaseButton}
	 *
	 * @param {?string} id
	 * @param {?object} [options]
	 * @constructor
	 */
	BX.Landing.UI.Button.SidebarButton = function(id, options)
	{
		BX.Landing.UI.Button.BaseButton.apply(this, arguments);

		addClass(this.layout, "landing-ui-button-sidebar");

		if (options.child === true)
		{
			addClass(this.layout, "landing-ui-button-sidebar-child");
		}

		if (options.empty === true)
		{
			addClass(this.layout, "landing-ui-button-sidebar-empty");
		}
	};


	BX.Landing.UI.Button.SidebarButton.prototype = {
		constructor: BX.Landing.UI.Button.SidebarButton,
		__proto__: BX.Landing.UI.Button.BaseButton.prototype
	};
})();