;(function() {
	"use strict";

	var scheduledPlayers = [];

	BX.addCustomEvent(window, "BX.Landing.Block:init", function(event) {
		var embedElements = [].slice.call(event.block.querySelectorAll("iframe[data-source]"));

		if (embedElements.length)
		{
			if (typeof YT === "undefined" || typeof YT.Player === "undefined")
			{
				embedElements.forEach(function(element) {
					if (!scheduledPlayers.includes(element))
					{
						scheduledPlayers.push(element);
					}
				});

				window.onYouTubeIframeAPIReady = function()
				{
					scheduledPlayers.forEach(BX.Landing.MediaPlayer.Factory.create);
				};

				return;
			}

			embedElements.forEach(BX.Landing.MediaPlayer.Factory.create);
		}
	});

	BX.addCustomEvent(window, "BX.Landing.Block:Node:update", function(event) {
		if (event.node.matches("iframe[data-source]"))
		{
			void BX.Landing.MediaPlayer.Factory.create(event.node);
		}
	});
})();
