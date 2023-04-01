;(function ()
{
	BX.namespace('BX.Sender');
	if (BX.Sender.Start)
	{
		return;
	}

	var Page = BX.Sender.Page;

	/**
	 * Manager.
	 *
	 */
	function Manager()
	{
	}
	Manager.prototype.init = function (options)
	{
		this.context = BX(options.containerId);

		var tiles = [
			'sender-start-mailings',
			'sender-start-ads',
			'sender-start-marketing',
			'sender-start-rc',
			'sender-start-yandex'
		];
		for (var i = 0; i < tiles.length; i++)
		{
			var tileList = BX.UI.TileList.Manager.getById(tiles[i]);
			if (tileList)
				tileList.getTiles().forEach(this.initTile, this);
		}
		// conversion init
		var tileManager = BX.UI.TileList.Manager.getById('sender-start-conversion');
		if (tileManager)
		{
			tileManager.getTiles().forEach(
				function(tile) {
					BX.bind(tile.node, 'click',this.onConversionClick.bind(this,tile));
				},
				this
			)
		}
	};

	Manager.prototype.onConversionClick = function(tile)
	{

		if (!tile.selected && BX.Sender.B24License)
		{
			BX.Sender.B24License.showPopup('Ad');
			return;
		}
		BX.Crm.Ads.Registry.conversion(tile.data.code).show();
	};

	Manager.prototype.initTile = function (tile)
	{
		BX.bind(tile.node, 'click', this.onClick.bind(this, tile));
	};

	Manager.prototype.onClick = function (tile)
	{
		if (!tile.selected && BX.Sender.B24License)
		{
			BX.Sender.B24License.showPopup('Ad');
			return;
		}

		var width = null;

		if(tile.id === 'instagram' || tile.id === 'facebook')
		{
			width = 1045;
		}

		Page.open(tile.data.url, false,{"width" : width});
	};

	BX.Sender.Start = new Manager();

})(window);