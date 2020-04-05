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

			var pseudoLinks = [].slice.call(document.querySelectorAll("[data-pseudo-url*=\"{\"]"));

			if (pseudoLinks.length)
			{
				pseudoLinks.forEach(function(link) {
					var linkOptions = BX.Landing.Utils.data(link, "data-pseudo-url");

					if (linkOptions.href &&
						linkOptions.target !== "_popup" &&
						linkOptions.enabled)
					{
						link.addEventListener("click", function(event) {
							event.preventDefault();
							top.open(linkOptions.href, linkOptions.target);
						});
					}
				});
			}
		}
	});
})();