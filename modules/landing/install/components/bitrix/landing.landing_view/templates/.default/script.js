BX.ready(function()
{
	/**
	 * Event on app install.
	 */
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

	/**
	 * For open app pages in slider.
	 */
	if (
		typeof BX.rest !== "undefined" &&
		typeof BX.rest.Marketplace !== "undefined"
	)
	{
		BX.rest.Marketplace.bindPageAnchors({});
	}

	/**
	 * On required links click.
	 */
	var onRequiredLinkClick = function(element)
	{
		var linkTpl = element.getAttribute("href");
		var urlParams = {};
		linkTpl = linkTpl.substr(1).toUpperCase();

		if (linkTpl === "PAGE_URL_CATALOG_EDIT")
		{
			linkTpl = "PAGE_URL_SITE_EDIT";
			urlParams.tpl = "catalog";
		}

		if (
			typeof landingParams[linkTpl] !== "undefined" &&
			typeof BX.SidePanel !== "undefined"
		)
		{
			BX.SidePanel.Instance.open(
				BX.util.add_url_param(
					landingParams[linkTpl],
					urlParams
				)
			);
		}
	};

	BX.addCustomEvent("BX.Landing.Block:init", function(event)
	{
		if (event.data.requiredUserActionIsShown)
		{
			BX.bind(event.data.button, "click", function()
			{
				onRequiredLinkClick(this);
			});
		}
	});

	var requiredLinks = [].slice.call(document.querySelectorAll(".landing-required-link"));
	requiredLinks.forEach(function(element, index)
	{
		BX.bind(element, "click", function()
		{
			onRequiredLinkClick(this);
		});
	});
});