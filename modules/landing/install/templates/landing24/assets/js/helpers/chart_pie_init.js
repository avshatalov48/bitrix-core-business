;(function() {
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function(event) {
		var selector = event.makeRelativeSelector(".js-pie");
		if($(selector).length > 0)
		{
			$.HSCore.components.HSChartPie.init('.js-pie');
		}
	});


	BX.addCustomEvent("BX.Landing.Block:Node:update", function(event) {
		// dbg: test update attributes
		// var selector = event.makeRelativeSelector(".cbp");
		//
		// if($(selector).length > 0)
		// {
		// 	$.HSCore.components.HSCubeportfolio.init('.cbp');
		// }
	});

})();