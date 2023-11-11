;(function() {
	'use strict';

	BX.addCustomEvent('BX.Landing.Block:init', function(event)
	{
		if (document.querySelector('.landing-edit-mode') === null)
		{
			init(event.block);
		}
	});

	function init(block)
	{
		// emulate browser back button
		let backLinks = [].slice.call(block.querySelectorAll('.js-link-back'));
		if (backLinks.length > 0)
		{
			backLinks.forEach(function(link)
			{
				let referrer = document.referrer;
				let topBackButton = document.querySelector('.landing-pub-top-panel-back');

				// for KB
				if (topBackButton)
				{
					link.addEventListener('click', function (event)
					{
						BX.PreventDefault();
						topBackButton.click();
					})
				}
				// for sites
				else if (
					window.history.length > 1
					&& referrer !== ''
					&& referrer.includes(location.hostname)
				)
				{
					link.addEventListener('click', function (event)
					{
						event.preventDefault();
						window.history.back();
					})
				}

				link.href = '#';
			});
		}
	}
})();
