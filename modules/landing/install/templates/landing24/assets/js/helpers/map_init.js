;(function ()
{
	"use strict";

	BX.namespace("BX.Landing.MapHelper");

	BX.Landing.MapHelper.sheduldedMapEvents = [];

	BX.Landing.MapHelper.init = function (event)
	{
		var node = event.block.querySelector("[data-map]");
		if (node)
		{
			BX.Landing.MapHelper.onBeforeApiLoaded(event);

			BX.Landing.Provider.Map.create(node, {
				mapContainer: node,
				theme: BX.Landing.Utils.data(node, "data-map-theme"),
				roads: BX.Landing.Utils.data(node, "data-map-roads") || [],
				landmarks: BX.Landing.Utils.data(node, "data-map-landmarks") || [],
				labels: BX.Landing.Utils.data(node, "data-map-labels") || [],
				mapOptions: BX.Landing.Utils.data(node, "data-map"),
				onProviderInit: function() {
					BX.Landing.MapHelper.onApiLoaded(event);
					BX.Landing.MapHelper.onProviderInit(event, this);
				}
			});
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

	/**
	 * When map API was loaded
	 * @param event
	 */
	BX.Landing.MapHelper.onApiLoaded = function (event)
	{
		if (BX.Landing.getMode() !== 'edit')
		{
			BX.show(event.block);
		}
	}

	/**
	 * Different actions in edit and public when provider is init
	 * @param event
	 * @param {BX.Landing.Provider.Map.BaseProvider} provider
	 */
	BX.Landing.MapHelper.onProviderInit = function (event, provider)
	{
		if (BX.Landing.getMode() === "edit")
		{
			event.forceInit();
		}
		else
		{
			provider.init();
		}
	}
})();
