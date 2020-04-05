;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Adapter");

	BX.Landing.UI.Adapter.CSSProperty = function() {};

	function useFallback()
	{
		return BX.browser.IsFirefox();
	}

	/**
	 * Gets property
	 * @param {string} property - CSS property
	 * @return {string}
	 */
	BX.Landing.UI.Adapter.CSSProperty.get = function(property)
	{
		if (useFallback() && property in BX.Landing.UI.Adapter.CSSProperty.map)
		{
			property = BX.Landing.UI.Adapter.CSSProperty.map[property];
		}

		return property;
	};

	BX.Landing.UI.Adapter.CSSProperty.map = {
		"border-color": useFallback() ? "border-bottom-color" : "border-color"
	};
})();