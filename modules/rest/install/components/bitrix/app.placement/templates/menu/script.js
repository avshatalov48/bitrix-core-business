;(function()
{
	BX.namespace('BX.rest');

	if(!!BX.rest.PlacementMenu)
	{
		return;
	}

	BX.rest.PlacementMenu = function(param)
	{
		BX.rest.PlacementMenu.superclass.constructor.apply(this, arguments);
	};

	BX.extend(BX.rest.PlacementMenu, BX.rest.Placement);

	BX.rest.PlacementMenu.prototype.load = function(placementId, appId, placementOptions, reloadCallback)
	{
		this.initializeInterface(reloadCallback);

		BX.rest.AppLayout.openApplication(
			appId,
			placementOptions,
			{
				'PLACEMENT_ID': placementId,
				'PLACEMENT': this.param.placement
			}
		);
	};


	BX.rest.PlacementMenu.prototype.initializeInterface = function(reloadCallback)
	{
		var PlacementInterface = top.BX.rest.AppLayout.initializePlacement(this.param.placement);

		PlacementInterface.prototype.reloadData = function(params, cb)
		{
			reloadCallback();
			cb();
		};
	};

})();