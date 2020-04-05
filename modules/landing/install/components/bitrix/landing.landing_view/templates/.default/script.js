BX.ready(function()
{
	// event on app install
	BX.addCustomEvent(
		window, 
		"Rest:AppLayout:ApplicationInstall", 
		function(installed)
		{
			if (installed)
			{
				//
			}
		}
	);
	// for open app pages in slider
	if (
		typeof BX.rest !== "undefined" &&
		typeof BX.rest.Marketplace !== "undefined"
	)
	{
		BX.rest.Marketplace.bindPageAnchors({});
	}
});