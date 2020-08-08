;(function ()
{
	"use strict";

	var sheduldedMapEvents = [];

	BX.addCustomEvent(window, "BX.Landing.Block:init", function (event)
	{
		if (event.block.querySelector("[data-map]"))
		{
			if (BX.Landing.Provider.Map.GoogleMap.isApiLoaded())
			{
				initGmapBlock(event);
			}
			else
			{
				if (!sheduldedMapEvents.includes(event))
				{
					sheduldedMapEvents.push(event);
				}

				window.onGoogleMapApiLoaded = function ()
				{
					sheduldedMapEvents.forEach(function(event){
						initGmapBlock(event)
					});
				}
			}
		}
	});

	function initGmapBlock(event)
	{
		if (BX.Landing.getMode() === "edit")
		{
			event.forceInit();
		}
		else
		{
			var container = event.block.querySelector("[data-map]");
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

})();