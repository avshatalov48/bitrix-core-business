;(function ()
{
	BX.namespace('BX.Sender.ContactSet');
	if (BX.Sender.ContactSet.Selector)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

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
		this.pathToAdd = params.pathToAdd;
		this.pathToEdit = params.pathToEdit;
		this.context = BX(params.containerId);
		this.actionUri = params.actionUri;
		this.mess = params.mess || {searchTitle: ''};

		this.isAdding = false;
		this.popupContent = Helper.getNode('popup-content', this.context);
		this.siteName = Helper.getNode('site-name', this.context);
		this.counter = Helper.getNode('counter', this.context);

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.initSelector();
		Helper.hint.init(this.context);

		top.BX.addCustomEvent(top, 'sender-contact-edit-change', this.onContactChange.bind(this));
	};
	Selector.prototype.initSelector = function ()
	{
		this.selector = BX.Sender.UI.TileSelector.getById(this.id);
		if (!this.selector)
		{
			throw new Error('Tile selector `' + this.id + '` not found.');
		}
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.onButtonSelect.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonSelectFirst, this.onButtonSelectFirst.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.buttonAdd, this.onButtonAdd.bind(this));
		//BX.addCustomEvent(this.selector, this.selector.events.tileClick, this.onTileClick.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.input, this.onInput.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.search, this.onSearch.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.onTileChange.bind(this));
		BX.addCustomEvent(this.selector, this.selector.events.tileAdd, this.onTileChange.bind(this));
	};
	Selector.prototype.onTileChange = function (tile)
	{
	};
	Selector.prototype.onButtonSelect = function ()
	{
		this.selector.showSearcher(this.mess.searchTitle);
	};
	Selector.prototype.onButtonSelectFirst = function ()
	{
		var selector = this.selector;
		this.ajaxAction.request({
			action: 'getSets',
			onsuccess: function (data)
			{
				selector.setSearcherData(data.list || []);
			},
			onfailure: selector.hideSearcher.bind(selector),
			data: {}
		});
	};
	Selector.prototype.onButtonAdd = function ()
	{
		this.isAdding = true;
		BX.Sender.Page.open(this.pathToAdd);
	};
	Selector.prototype.onTileClick = function (tile)
	{
		BX.Sender.Page.open(this.pathToEdit.replace('#id#', tile.id));
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
	Selector.prototype.onContactChange = function (tile)
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

	BX.Sender.ContactSet.Selector = Selector;
	BX.Sender.ContactSet.SelectorManager = new Manager();

})(window);