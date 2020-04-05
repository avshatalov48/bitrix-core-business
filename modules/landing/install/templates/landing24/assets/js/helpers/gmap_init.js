;(function() {
	"use strict";


	BX.namespace("BX.Landing.GmapHelper");


	BX.addCustomEvent("BX.Landing.Block:init", function(event) {
		var selector = event.makeRelativeSelector(".js-g-map");

		if (event.block.querySelectorAll(selector).length > 0)
		{
			BX.adjust(event.block.querySelectorAll(selector)[0], {'props' : {'id': event.block.id + '_gmap'}});
			BX.Landing.GmapHelper.selector = selector;

			var script = document.createElement('script');
			script.async = 1;
			script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyAtt1z99GtrHZt_IcnK-wryNsQ30A112J0&callback=BX.Landing.GmapHelper.initMap';
			var h = document.getElementsByTagName('script')[0];
			h.parentNode.insertBefore(script, h);





		}
	});


	BX.Landing.GmapHelper.initMap = function()
	{
		$.HSCore.components.HSGMap.init(BX.Landing.GmapHelper.selector);
	}

})();