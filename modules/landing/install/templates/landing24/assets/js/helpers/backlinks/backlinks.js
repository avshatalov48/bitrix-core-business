;(function() {
	"use strict";

	BX.addCustomEvent("BX.Landing.Block:init", function(event)
	{
		if (document.querySelector('.landing-edit-mode') === null)
		{
			init(event.block);
		}
	});

	function init(block)
	{
		// emulate browser back button
		var backLinks = [].slice.call(block.querySelectorAll('.js-link-back'));
		if (backLinks.length > 0)
		{
			backLinks.forEach(function(link)
			{
				var referrer = document.referrer;
				if (
					window.history.length > 1
					&& referrer !== ""
					&& referrer.includes(location.hostname)
				)
				{
					link.addEventListener('click', function (event)
					{
						event.preventDefault();
						window.history.back();
					})
					link.href = '#';
				}
			});
		}
	}
})();