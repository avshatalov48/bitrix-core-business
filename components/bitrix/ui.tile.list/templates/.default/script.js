;(function ()
{
	var namespace = BX.namespace('BX.UI.TileList');
	if (namespace.Manager)
	{
		return;
	}

	var managerList = [];

	/**
	 * Tile.
	 *
	 */
	function Tile(options)
	{
		this.id = options.id;
		this.name = options.name;
		this.bgColor = options.bgColor;
		this.color = options.color;
		this.selected = options.selected;
		this.node = options.node;
		this.data = options.data;

		this.nameNode = Helper.getNode('tile/item/name', this.node);
		this.iconNode = Helper.getNode('tile/item/icon', this.node);
		this.iconColorNode = Helper.getNode('tile/item/icon/color', this.node);

		if (this.name)
		{
			this.nameNode.textContent = this.name;
		}
		if (options.iconClass)
		{
			BX.addClass(this.iconNode, options.iconClass);
		}

		this.changeSelection(this.selected);
	}
	Tile.prototype = {
		classSelected: 'ui-tile-list-item-selected',
		changeSelection: function (isSelected)
		{
			Helper.changeClass(this.node, this.classSelected, isSelected);
			this.nameNode.style.color = (isSelected && this.color) ? this.color : '';
			this.node.style.background = isSelected
				? (this.bgColor
						? this.bgColor
						: getComputedStyle(this.iconColorNode).backgroundColor
				)
				: '';

			this.selected = isSelected;
		},

		onClick: function ()
		{

		}
	};


	/**
	 * Manager.
	 *
	 */
	function Manager(params)
	{
		this.init(params);
	}
	Manager.prototype.events = {
		tileClick: 'tile-click',
		tileRemove: 'tile-remove',
		tileEdit: 'tile-edit',
		tileAdd: 'tile-add',
		buttonAdd: 'add'
	};
	Manager.getById = function (id)
	{
		var filtered = managerList.filter(function (item) {
			return item.id === id;
		});
		return filtered.length > 0 ? filtered[0] : null;
	};
	Manager.getList = function ()
	{
		return managerList;
	};

	Manager.prototype.init = function (params)
	{
		this.list = [];
		this.context = BX(params.containerId);
		if (!this.context)
		{
			return;
		}

		this.id = params.id;

		this.tileContainer = Helper.getNode('tile/items', this.context);
		this.tileTemplate = Helper.getNode('tile/template', this.context);
		this.buttonAdd = Helper.getNode('tile/add', this.context);

		Helper.getNodes('tile/item', this.context).forEach(this.initNode.bind(this, params.tileOptionsList || []));

		managerList.push(this);
		this.initEventHandlers();
	};
	Manager.prototype.initEventHandlers = function ()
	{
		if (this.buttonAdd)
		{
			BX.bind(this.buttonAdd, 'click', this.onButtonAdd.bind(this));
		}
	};
	Manager.prototype.initNode = function (tileOptionsList, node)
	{
		if (!node)
		{
			return null;
		}

		var id = node.getAttribute('data-id');
		var filtered = tileOptionsList.filter(function (tileOptions) {
			return tileOptions.id.toString() === id;
		}, this);
		if (filtered.length === 0)
		{
			return;
		}

		var tileOptions = filtered[0];
		tileOptions.node = node;
		this.addTile(tileOptions);
	};

	Manager.prototype.onRemove = function (tile, e)
	{
		e.preventDefault();
		e.stopPropagation();
		this.removeTile(tile);
		return false;
	};
	Manager.prototype.onTileClick = function (tile, e)
	{
		e.preventDefault();
		e.stopPropagation();
		this.fire(this.events.tileClick, [tile]);
	};


	Manager.prototype.removeTiles = function ()
	{
		var list = this.list;
		list.forEach(this.removeTile.bind(this));
	};
	Manager.prototype.removeTile = function (tile)
	{
		this.list = BX.util.deleteFromArray(this.list, this.list.indexOf(tile));
		BX.remove(tile.node);
		this.fire(this.events.tileRemove, [tile]);
	};
	Manager.prototype.getTile = function (id)
	{
		var filtered = this.list.filter(function (item) {
			return item.id === id;
		});
		return filtered.length > 0 ? filtered[0] : null;
	};
	Manager.prototype.getTiles = function ()
	{
		return this.list;
	};
	Manager.prototype.addTile = function (options)
	{
		if (!options.node)
		{
			options.node = Helper.getTemplatedNode(this.tileTemplate, {});
		}
		var tile = new namespace.Tile(options);
		if (!tile)
		{
			return null;
		}

		if (!this.tileContainer.contains(tile.node))
		{
			this.tileContainer.appendChild(tile.node);
		}

		BX.bind(tile.node, 'click', this.onTileClick.bind(this, tile));


		this.list.push(tile);
		this.fire(this.events.tileAdd, [tile]);

		return tile;
	};

	Manager.prototype.fire = function (eventName, data)
	{
		BX.onCustomEvent(this, eventName, data);
	};
	Manager.prototype.onButtonAdd = function (e)
	{
		e.preventDefault();
		e.stopPropagation();

		this.fire(this.events.buttonAdd, []);
	};

	var Helper = {
		getObjectByKey:  function (list, key, value)
		{
			var filtered = list.filter(function (item) {
				return (item.hasOwnProperty(key) && item[key] === value);
			});
			return filtered.length > 0 ? filtered[0] : null;
		},
		getNode:  function (role, context)
		{
			var nodes = this.getNodes(role, context);
			return nodes.length > 0 ? nodes[0] : null;
		},
		getNodes: function (role, context)
		{
			if (!context)
			{
				return [];
			}

			return BX.convert.nodeListToArray(context.querySelectorAll('[data-role="' + role + '"]'));
		},
		changeClass: function (node, className, isAdd)
		{
			if (!node)
			{
				return;
			}

			if (isAdd)
			{
				BX.addClass(node, className);
			}
			else
			{
				BX.removeClass(node, className);
			}
		},
		changeDisplay: function (node, isShow)
		{
			if (!node)
			{
				return;
			}

			node.style.display = isShow ? '' : 'none';
		},
		replace: function (text, data, isDataSafe)
		{
			data = data || {};
			isDataSafe = isDataSafe || false;

			if (!text)
			{
				return '';
			}

			for (var key in data)
			{
				if (!data.hasOwnProperty(key))
				{
					continue;
				}

				var value = data[key];
				value = value || '';
				if (!isDataSafe && value)
				{
					value = BX.util.htmlspecialchars(value);
				}
				text = text.replace(new RegExp('%' + key + '%', 'g'), value);
			}
			return text;
		},
		getTemplatedNode: function (templateNode, replaceData, isDataSafe)
		{
			if (!templateNode)
			{
				return null;
			}

			var template = Helper.replace(templateNode.innerHTML, replaceData, isDataSafe);
			var node = document.createElement('div');
			node.innerHTML = template;

			return node.children[0];
		}
	};


	namespace.Manager = Manager;
	namespace.Tile = Tile;

})();