;(function ()
{
	"use strict";

	BX.namespace("BX.Landing.MapHelper");

	BX.Landing.MapHelper.sheduldedMapEvents = [];

	BX.Landing.MapHelper.init = function (event)
	{
		const node = event.block.querySelector("[data-map]");
		if (node)
		{
			node.style.position = 'relative';
			if (BX.Landing.getMode() === 'edit')
			{
				event.forceInit();
			}
			else
			{
				BX.Landing.MapHelper.onBeforeApiLoaded(event);
				BX.Landing.Provider.Map.create(node, {
					mapContainer: node,
					theme: BX.Landing.Utils.data(node, "data-map-theme"),
					roads: BX.Landing.Utils.data(node, "data-map-roads") || [],
					landmarks: BX.Landing.Utils.data(node, "data-map-landmarks") || [],
					labels: BX.Landing.Utils.data(node, "data-map-labels") || [],
					mapOptions: BX.Landing.Utils.data(node, "data-map"),
					onProviderInit: () =>
					{
						BX.show(event.block);
					},
				});
			}
		}
	}
	BX.addCustomEvent(window, "BX.Landing.Block:init", BX.Landing.MapHelper.init);

	BX.Landing.MapHelper.onBeforeApiLoaded = function (event)
	{
		if (BX.Landing.getMode() !== 'edit')
		{
			BX.hide(event.block);
		}
	}

})();
