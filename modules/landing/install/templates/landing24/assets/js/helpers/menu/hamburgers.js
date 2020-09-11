;(function() {
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function(event)
	{
		var relativeSelector = event.makeRelativeSelector(".hamburger");
		var hamburger = document.querySelector(relativeSelector);
		if (hamburger)
		{
			BX.bind(hamburger, 'click', function(event){
				event.preventDefault();
				hamburger.classList.toggle('is-active');
			});
		}
	});
})();