;(function() {
	"use strict";

	BX.addCustomEvent(window, "BX.Landing.Block:init", function(event) {
		var container = event.block.querySelector("[data-map]");

		if (container)
		{
			if (BX.Landing.getMode() === "edit")
			{
				event.forceInit();
			}
			else
			{
				void new BX.Landing.Provider.Map.GoogleMap({
					mapContainer: container,
					theme: BX.Landing.Utils.data(container, "data-map-theme"),
					roads: BX.Landing.Utils.data(container, "data-map-roads") || [],
					landmarks: BX.Landing.Utils.data(container, "data-map-landmarks") || [],
					labels: BX.Landing.Utils.data(container, "data-map-labels") || [],
					mapOptions: BX.Landing.Utils.data(container, "data-map")
				});
			}
		}
	});

})();