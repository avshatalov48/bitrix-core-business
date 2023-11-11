(function() {
	'use strict';

	BX.namespace('BX.Catalog');

	BX.Catalog.CatalogController = function()
	{
		this.init();
	};

	BX.Catalog.CatalogController.prototype.init = function()
	{
		if (BX.SidePanel.Instance)
		{
			BX.SidePanel.Instance.bindAnchors({
				rules: [
					{
						condition: [
							'/shop/settings/cat_section_edit/',
							'/shop/settings/cat_product_edit/',
						],
						handler: this.adjustSidePanelOpener,
					},
				],
			});
		}

		if (!top.window.adminSidePanel || !BX.is_subclass_of(top.window.adminSidePanel, top.BX.adminSidePanel))
		{
			top.window.adminSidePanel = new top.BX.adminSidePanel({
				publicMode: true,
			});
		}
	};

	BX.Catalog.CatalogController.prototype.adjustSidePanelOpener = function(event, link)
	{
		if (BX.SidePanel.Instance)
		{
			const isSidePanelParams = (link.url.includes('IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER'));
			if (!isSidePanelParams || (isSidePanelParams && !BX.SidePanel.Instance.getTopSlider()))
			{
				event.preventDefault();
				link.url =	BX.util.add_url_param(link.url, { publicSidePanel: 'Y' });
				BX.SidePanel.Instance.open(link.url, {
					allowChangeHistory: false,
				});
			}
		}
	};
})();
