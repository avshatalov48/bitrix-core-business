;(function() {
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function(event) {
		var relativeSelector = event.makeRelativeSelector(".hamburger");
		if($(relativeSelector).length > 0)
		{
			$.HSCore.helpers.HSHamburgers.init(relativeSelector);
		}
	});

})();