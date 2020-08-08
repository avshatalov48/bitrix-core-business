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
			'sender-start-ad',
			'sender-start-rc',
			'sender-start-toloka'
		];
		for (var i = 0; i < tiles.length; i++)
		{
			var tileList = BX.UI.TileList.Manager.getById(tiles[i]);
			if (tileList)
				tileList.getTiles().forEach(this.initTile, this);
		}
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

		Page.open(tile.data.url);
	};

	BX.Sender.Start = new Manager();

})(window);