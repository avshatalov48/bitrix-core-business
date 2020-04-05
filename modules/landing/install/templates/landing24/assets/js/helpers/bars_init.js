;(function() {
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function(event) {
		var selector = event.makeRelativeSelector(".js-hr-progress-bar");
		if($(selector).length > 0)
		{
			$.HSCore.components.HSProgressBar.init('.js-hr-progress-bar', {
				direction: 'horizontal',
				indicatorSelector: '.js-hr-progress-bar-indicator'
			});
		}
	});


	BX.addCustomEvent("BX.Landing.Block:Node:update", function(event) {
		// dbg: test update attributes
	});

})();