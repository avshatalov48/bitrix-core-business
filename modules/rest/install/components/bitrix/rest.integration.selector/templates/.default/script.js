;(function ()
{
	BX.namespace('BX.rest.integration');
	if (BX.rest.integration.Selector)
	{
		return;
	}

	/**
	 * Selector.
	 *
	 */
	function Selector(params)
	{
		this.init(params);
	}

	Selector.prototype.init = function (params)
	{
		this.manager = params.manager;
		this.id = params.id;
		this.scopeSelectorName = params.scopeSelectorName;
		this.pathToAdd = params.pathToAdd;
		this.pathToEdit = params.pathToEdit;
		this.onChange = params.onChange;
		this.context = BX(params.containerId);
		this.signetParameters = params.signetParameters;
		this.action = params.action;
		this.mess = params.mess || {searchTitle: ''};
		this.ajaxUses = false;

		this.initSelector();
		top.BX.addCustomEvent(top, 'rest-integration-edit-change', this.onCampaignChange.bind(this));
	};
	Selector.prototype.initSelector = function ()
	{
		this.selector = BX.UI.TileSelector.getById(this.id);
		if (!this.selector)
		{
			throw new Error('Tile selector `' + this.id + '` not found.');
		}
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.onButtonSelect.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelectFirst, this.onButtonSelectFirst.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonAdd, this.onButtonAdd.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileClick, this.onTileClick.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.input, this.onInput.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.search, this.onSearch.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.onTileRemove.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileAdd, this.onTileAdd.bind(this));
	};

	Selector.prototype.onTileAdd = function (tile)
	{
		if (this.scopeSelectorName !== '')
		{
			var scopeTile = BX.UI.TileSelector.getById(this.scopeSelectorName);
			if (!!scopeTile)
			{
				BX.ajax.runComponentAction(
					'bitrix:rest.integration.selector',
					'getNeededScope',
					{
						mode: 'class',
						signedParameters: this.signetParameters,
						data: {
							code: tile.id,
							action: this.action
						}
					}
				).then(
					function (response)
					{
						if (
							response.status === 'success' &&
							typeof response.data == 'object' &&
							response.data.length > 0
						)
						{
							var uses = null;
							response.data.forEach(
								function (item)
								{
									uses = scopeTile.getTile(item.id);
									if (uses === null)
									{
										scopeTile.addTile(item.name, {}, item.id);
									}
								}
							);
						}
					}
				);
			}
		}
		if (this.onChange !== '')
		{
			eval(this.onChange);
		}
	};
	Selector.prototype.onTileRemove = function (tile)
	{
		if(this.onChange !== '')
		{
			eval(this.onChange);
		}
	};
	Selector.prototype.getSearcherData = function ()
	{
		if(this.ajaxUses === false)
		{
			var selector = this.selector;
			BX.ajax.runComponentAction(
				'bitrix:rest.integration.selector',
				'get'+this.action,
				{
					mode: 'class',
					signedParameters: this.signetParameters
				}
			).then(
				function (response)
				{
					if (
						response.status === 'success' &&
						typeof response.data == 'object' &&
						typeof response.data.list == 'object'
					)
					{
						selector.setSearcherData(response.data.list || []);
					}
					else
					{
						selector.hideSearcher.bind(selector);
					}
				}
			);

			this.ajaxUses = true;
		}
	};
	Selector.prototype.onButtonSelect = function ()
	{
		this.selector.showSearcher(this.mess.searchTitle);
	};
	Selector.prototype.onButtonSelectFirst = function ()
	{
		this.getSearcherData();
	};
	Selector.prototype.onButtonAdd = function ()
	{
		//this.pathToAdd;
	};
	Selector.prototype.onTileClick = function (tile)
	{
		this.getSearcherData();
		this.selector.showSearcher(this.mess.searchTitle);
		//this.pathToEdit.replace('#id#', tile.id)
	};
	Selector.prototype.onInput = function (value)
	{
	};
	Selector.prototype.onSearch = function (value)
	{
	};
	Selector.prototype.fire = function (eventName, parameters)
	{
		parameters = parameters || {};
		BX.onCustomEvent(this, eventName, parameters);
	};
	Selector.prototype.actualizeTiles = function (tile, needAdd)
	{
		var existedTile = this.selector.getTile(tile.id);
		if (existedTile)
		{
			this.selector.updateTile(existedTile, tile.name, tile.data, tile.bgcolor, tile.color);
		}
		else if (needAdd)
		{
			this.selector.addTile(tile.name, tile.data, tile.id, tile.bgcolor, tile.color);
		}
	};
	Selector.prototype.actualize = function (tile, isAddTile)
	{
		this.selector.clearSearcher();
		this.actualizeTiles(tile, isAddTile);
	};
	Selector.prototype.onCampaignChange = function (tile)
	{
		this.actualize(tile, this.isAdding);
		this.isAdding = false;
	};

	/**
	 * Selector.
	 *
	 */
	function Manager()
	{
	}
	Manager.prototype.create = function (params)
	{
		return new Selector(params);
	};

	BX.rest.integration.Selector = Selector;
	BX.rest.integration.SelectorManager = new Manager();

})(window);