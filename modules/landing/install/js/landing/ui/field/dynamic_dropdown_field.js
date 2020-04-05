;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	BX.Landing.UI.Field.DynamicDropdown = function(options)
	{
		BX.Landing.UI.Field.DynamicImage.apply(this, arguments);
		BX.addClass(this.layout, "landing-ui-field-dynamic-dropdown");
	};

	BX.Landing.UI.Field.DynamicDropdown.prototype = {
		constructor: BX.Landing.UI.Field.DynamicDropdown,
		__proto__: BX.Landing.UI.Field.DynamicImage.prototype
	};
})();