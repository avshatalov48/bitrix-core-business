;(function() {
	"use strict";

	BX(function() {
		if (typeof BX.Landing === "undefined" || typeof BX.Landing.Main === "undefined")
		{
			BX.namespace("BX.Landing");

			BX.Landing.getMode = function()
			{
				return window.top === window ? "view" : "design";
			};

			var blocks = [].slice.call(document.getElementsByClassName("block-wrapper"));

			if (!!blocks && blocks.length)
			{
				blocks.forEach(function(block) {
					var event = new BX.Landing.Event.Block({block: block});
					BX.onCustomEvent("BX.Landing.Block:init", [event]);
				});
			}

			if (BX.Landing.EventTracker)
			{
				BX.Landing.EventTracker.getInstance().run();
			}
		}
	});
})();