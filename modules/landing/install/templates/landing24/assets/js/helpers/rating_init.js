;(function() {
	"use strict";

	// todo: rating
	BX.addCustomEvent("BX.Landing.Block:init", function(event) {
		var selector = event.makeRelativeSelector(".js-rating");
		if($(selector).length > 0)
		{
			$.HSCore.components.HSRating.init($(".js-rating"), {
				spacing: 4
			});
		}
	});


	BX.addCustomEvent("BX.Landing.Block:Node:updateAttr", function(event) {
		var selector = event.makeRelativeSelector(".js-rating");
		if($(selector).length > 0)
		{
			$.HSCore.components.HSRating.init($(".js-rating"), {
				spacing: 4
			});
		}
	});

})();