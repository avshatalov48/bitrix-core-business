;(function (window)
{
	BX.namespace('BX.Sender.UI');
	if (BX.Sender.UI.TileSelector)
	{
		return;
	}

	var selectorList = [];

	/**
	 * Tile.
	 *
	 */
	function Tile(params)
	{
		this.id = params.id;
		this.node = params.node;
		this.data = params.data;

		this.nameNode = Helper.getNode('tile-item-name', this.node);
	}

	/**
	 * TileSelector.
	 *
	 */
	function TileSelector(params)
	{
		this.init(params);
	}
	TileSelector.prototype.events = {
		containerClick: 'container-click',
		tileClick: 'tile-click',
		tileRemove: 'tile-remove',
		tileEdit: 'tile-edit',
		tileAdd: 'tile-add',
		buttonAdd: 'add',
		buttonSelect: 'select',
		buttonSelectFirst: 'select-first',
		search: 'search',
		input: 'input',
		searcherCategoryClick: 'popup-category-click',
		searcherItemClick: 'popup-item-click'
	};
	TileSelector.getById = function (id)
	{
		var filtered = selectorList.filter(function (item) {
			return item.id === id;
		});
		return filtered.length > 0 ? filtered[0] : null;
	};
	TileSelector.getList = function ()
	{
		return selectorList;
	};

	TileSelector.prototype.init = function (params)
	{
		this.list = [];
		this.id = params.id;
		this.context = BX(params.containerId);
		this.duplicates = params.duplicates;
		this.multiple = params.multiple;
		this.readonly = params.readonly;
		this.manualInputEnd = params.manualInputEnd;

		this.attributeId = 'data-bx-id';
		this.attributeData = 'data-bx-data';
		this.tileContainer = Helper.getNode('tile-container', this.context);
		this.tileTemplate = Helper.getNode('tile-template', this.context);
		this.input = Helper.getNode('tile-input', this.context);
		this.buttonAdd = Helper.getNode('tile-add', this.context);
		this.buttonSelect = Helper.getNode('tile-select', this.context);

		if (!this.context || !this.input)
		{
			return;
		}

		Helper.getNodes('tile-item', this.context).forEach(this.initNode.bind(this));

		if (!this.readonly)
		{
			this.initEventHandlers();
		}

		this.searcher = null;

		selectorList.push(this);
	};
	TileSelector.prototype.initEventHandlers = function ()
	{
		if (this.buttonAdd)
		{
			BX.bind(this.buttonAdd, 'click', this.onButtonAdd.bind(this));
		}
		if (this.context)
		{
			BX.bind(this.context, 'click', this.onContainerClick.bind(this));
		}
		if (this.buttonSelect)
		{
			BX.bind(this.buttonSelect, 'click', this.onButtonSelect.bind(this));
			BX.bind(this.tileContainer, 'click', this.onButtonSelect.bind(this));
		}
		BX.bind(this.input, 'input', this.onInput.bind(this));

		if (!this.manualInputEnd)
		{
			BX.bind(this.input, 'blur', this.onInputEnd.bind(this));
			Helper.handleKeyEnter(this.input, this.onInputEnd.bind(this));
		}
	};
	TileSelector.prototype.getSearchInput = function ()
	{
		return this.input;
	};
	TileSelector.prototype.isSearcherInit = function ()
	{
		return !!this.searcher;
	};
	TileSelector.prototype.clearSearcher = function ()
	{
		this.isButtonSelectFired = false;
		if (this.searcher)
		{
			this.searcher.hide();
			this.searcher = null;
		}
	};
	TileSelector.prototype.hideSearcher = function ()
	{
		this.searcher.hide();
	};
	TileSelector.prototype.showSearcher = function (title)
	{
		if (!this.searcher)
		{
			this.searcher = new Searcher({
				'id': this.id,
				'caller': this,
				'context': this.context,
				'title': title || ''
			});
		}

		this.searcher.filterByName();
		this.searcher.show();
	};
	TileSelector.prototype.setSearcherData = function (dataList)
	{
		if (!this.searcher)
		{
			this.showSearcher();
		}

		this.searcher.setCategories(dataList);
	};
	TileSelector.prototype.initNode = function (node)
	{
		if (!node)
		{
			return null;
		}

		var id = node.getAttribute(this.attributeId);
		var data = node.getAttribute(this.attributeData);
		try
		{
			data = JSON.parse(data);
		}
		catch (e)
		{
			try
			{
				data = JSON.parse(BX.util.htmlspecialcharsback(data));
			}
			catch (e)
			{
				data = {};
			}
		}

		var tile = new Tile({
			'id': id,
			'node': node,
			'data': data
		});
		if (tile.id && !this.duplicates && this.findDuplicates(tile.id))
		{
			tile = null;
			return null;
		}

		var removeButton = Helper.getNode('remove', node);
		if (removeButton)
		{
			BX.bind(removeButton, 'click', this.onRemove.bind(this, tile));
		}

		BX.bind(node, 'click', this.onClick.bind(this, tile));

		this.list.push(tile);

		return tile;
	};

	TileSelector.prototype.onRemove = function (tile, e)
	{
		e.preventDefault();
		e.stopPropagation();
		this.removeTile(tile);
		return false;
	};
	TileSelector.prototype.onClick = function (tile, e)
	{
		e.preventDefault();
		e.stopPropagation();
		this.fire(this.events.tileClick, [tile]);
	};


	TileSelector.prototype.removeTiles = function ()
	{
		var list = this.list;
		list.forEach(this.removeTile.bind(this));
	};
	TileSelector.prototype.removeTile = function (tile)
	{
		this.list = BX.util.deleteFromArray(this.list, this.list.indexOf(tile));
		BX.remove(tile.node);
		this.fire(this.events.tileRemove, [tile]);
	};
	TileSelector.prototype.getTile = function (id)
	{
		var filtered = this.list.filter(function (item) {
			return item.id === id;
		});
		return filtered.length > 0 ? filtered[0] : null;
	};
	TileSelector.prototype.getTilesData = function ()
	{
		return this.list.map(function (tile) {
			return tile.data;
		});
	};
	TileSelector.prototype.getTilesId = function ()
	{
		return this.list.map(function (tile) {
			return tile.id;
		}).filter(function (id) {
			return !!id;
		});
	};
	TileSelector.prototype.getTiles = function ()
	{
		return this.list;
	};
	TileSelector.prototype.findDuplicates = function (id)
	{
		var tile = this.getTile(id);
		if (!tile)
		{
			return false;
		}

		this.removeTile(tile);
	};
	TileSelector.prototype.addTile = function (name, data, id, background, color)
	{
		if (!name || this.readonly)
		{
			return null;
		}

		if (!this.multiple)
		{
			this.removeTiles();
			if (this.isSearcherInit())
			{
				this.hideSearcher();
			}
		}

		data = data || {};
		id = id || '';
		color = color || '';
		background = background || '';

		var template = this.tileTemplate;
		if (!template)
		{
			return null;
		}

		template = template.innerHTML;
		var style = '';
		if (color)
		{
			style += 'color: ' + BX.util.htmlspecialchars(color) + '; ';
		}
		if (background)
		{
			style += 'background-color: ' + BX.util.htmlspecialchars(background) + '; ';
		}
		template = Helper.replace(template, {
			'id': BX.util.htmlspecialchars(id + ''),
			'name': BX.util.htmlspecialchars(name),
			'data': BX.util.htmlspecialchars(JSON.stringify(data)),
			'style': style
		});


		var node = document.createElement('div');
		node.innerHTML = template;
		node = node.children[0];

		var tile = this.initNode(node);
		if (!tile)
		{
			return null;
		}

		this.input.parentNode.insertBefore(node, this.input);
		this.fire(this.events.tileAdd, [tile]);

		return tile;
	};
	TileSelector.prototype.updateTile = function (tile, name, data, bgcolor, color)
	{
		if (!tile || this.readonly)
		{
			return null;
		}

		name = name || null;
		data = data || null;
		bgcolor = bgcolor || null;
		color = color || null;

		if (name)
		{
			tile.nameNode.textContent = name;
		}

		if (data)
		{
			tile.data = data;
		}

		if (bgcolor || bgcolor === null)
		{
			tile.node.style.backgroundColor = bgcolor;
		}

		if (color)
		{
			tile.node.style.color = color;
		}

		this.fire(this.events.tileEdit, [tile]);

		return tile;
	};

	TileSelector.prototype.fire = function (eventName, data)
	{
		BX.onCustomEvent(this, eventName, data);
	};
	TileSelector.prototype.onInput = function ()
	{
		var value = this.input.value;
		if (this.searcher && value.length > 0)
		{
			this.searcher.filterByName(value);
		}

		this.fire(this.events.input, [this.input.value]);
	};
	TileSelector.prototype.onInputEnd = function ()
	{
		var value = this.input.value;
		this.input.value = '';
		Helper.changeDisplay(this.input, false);
		Helper.changeDisplay(this.buttonSelect, true);

		this.fire(this.events.search, [value]);
	};
	TileSelector.prototype.onButtonAdd = function (e)
	{
		e.preventDefault();
		e.stopPropagation();

		this.fire(this.events.buttonAdd, []);
	};
	TileSelector.prototype.onContainerClick = function ()
	{
		this.fire(this.events.containerClick, []);
	};
	TileSelector.prototype.onButtonSelect = function (e)
	{
		e.preventDefault();
		e.stopPropagation();

		Helper.changeDisplay(this.buttonSelect, false);
		Helper.changeDisplay(this.input, true);
		this.input.focus();

		this.fire(this.events.buttonSelect, []);
		if (!this.isButtonSelectFired)
		{
			this.fire(this.events.buttonSelectFirst, []);
			this.isButtonSelectFired = true;
		}
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
		handleKeyEnter: function (inputNode, callback)
		{
			if (!callback)
			{
				return;
			}

			var handler = function (event)
			{
				event = event || window.event;
				if ((event.keyCode === 0xA)||(event.keyCode === 0xD))
				{
					event.preventDefault();
					event.stopPropagation();
					callback();
					return false;
				}
			};
			BX.bind(inputNode, 'keyup', handler);
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



	/**
	 * Popup.
	 *
	 */
	function Searcher(params)
	{
		this.init(params);
	}
	Searcher.prototype.classNameCategoryActive = 'sender-ui-tile-searcher-sidebar-item-selected';
	Searcher.prototype.classNameItemActive = 'sender-ui-tile-searcher-content-item-selected';
	Searcher.prototype.init = function (params)
	{
		this.id = params.id;
		this.context = params.context;
		this.caller = params.caller;

		this.categories = [];
		this.items = [];

		this.currentCategory = null;

		this.categoryTemplate = Helper.getNode('popup-category-template', this.context);
		this.itemTemplate = Helper.getNode('popup-item-template', this.context);

		this.content = Helper.getTemplatedNode(Helper.getNode('popup-template', this.context));
		this.loader = Helper.getNode('popup-loader', this.content);
		this.categoryContainer = Helper.getNode('popup-category-list', this.content);
		this.itemContainer = Helper.getNode('popup-item-list', this.content);

		this.itemContainer.innerHTML = '';
		this.categoryContainer.innerHTML = '';
		this.title = Helper.getNode('popup-title', this.content);
		if (this.title)
		{
			this.title.textContent = params.title;
		}

		if (params.dataList)
		{
			this.setCategories(params.dataList);
		}

		BX.addCustomEvent(this.caller, this.caller.events.tileAdd, this.onTileAdd.bind(this));
		BX.addCustomEvent(this.caller, this.caller.events.tileRemove, this.onTileRemove.bind(this));
	};
	Searcher.prototype.onTileAdd = function (tile)
	{
		var item = Helper.getObjectByKey(this.items, 'id', tile.id);
		if (!item)
		{
			return;
		}

		Helper.changeClass(item.node, this.classNameItemActive, true);
	};
	Searcher.prototype.onTileRemove = function (tile)
	{
		var item = Helper.getObjectByKey(this.items, 'id', tile.id);
		if (!item)
		{
			return;
		}

		Helper.changeClass(item.node, this.classNameItemActive, false);
	};
	/*
	Searcher.prototype.getItemById = function (id)
	{
		this.items.filter(function (item) {

		})
	};
	*/
	Searcher.prototype.filterByName = function (name)
	{
		name = name || '';
		if (name.length < 3)
		{
			Helper.changeDisplay(this.categoryContainer, true);
			this.setCurrentCategory();
			return;
		}

		var regexp = new RegExp(BX.util.escapeRegExp(name), 'i');
		this.items.forEach(function (item) {
			Helper.changeDisplay(item.node, regexp.test(item.name));
		});

		Helper.changeDisplay(this.categoryContainer, false);

	};
	Searcher.prototype.onCategoryClick = function (category)
	{
		this.setCurrentCategory(category);
		this.caller.fire(this.caller.events.searcherCategoryClick, [category]);
	};
	Searcher.prototype.setCurrentCategory = function (category)
	{
		category = category || this.categories[0];
		if (this.currentCategory)
		{
			BX.removeClass(this.currentCategory.node, this.classNameCategoryActive);
		}
		this.currentCategory = category;

		if (!category)
		{
			return;
		}

		BX.addClass(this.currentCategory.node, this.classNameCategoryActive);

		// show only current category items
		this.items.forEach(function (item) {
			Helper.changeDisplay(item.node, item.category === category);
		});
	};
	Searcher.prototype.onItemClick = function (item)
	{
		this.caller.addTile(item.name, item.data, item.id, item.bgcolor, item.color);
		this.caller.fire(this.caller.events.searcherItemClick, [item]);
	};
	Searcher.prototype.getCategory = function (id)
	{
		return Helper.getObjectByKey(this.categories, 'id', id);
	};
	Searcher.prototype.getItem = function (id)
	{
		return Helper.getObjectByKey(this.items, 'id', id);
	};
	Searcher.prototype.updateItem = function (item, name, data)
	{
		if (name)
		{
			item.node.textContent = name;
			item.node.title = name;
		}

		if (data)
		{
			item.data = data;
		}
	};
	Searcher.prototype.addItem = function (category, id, name, data)
	{
		var node = Helper.getTemplatedNode(this.itemTemplate, {
			'name': name
		});
		var item = {
			'category': category,
			'node': node,
			'id': id,
			'name': name,
			'data': data || {}
		};

		this.items.push(item);
		this.itemContainer.appendChild(node);
		BX.bind(node, 'click', this.onItemClick.bind(this, item));
		return item;
	};
	Searcher.prototype.setItems = function (category, list)
	{
		this.items = [];
		list.forEach(function (item) {
			this.addItem(category, item.id, item.name, item.data);
		}, this);
	};
	Searcher.prototype.addItems = function (category, list)
	{
		list.forEach(function (item) {
			this.addItem(category, item.id, item.name, item.data);
		}, this);
	};
	Searcher.prototype.addCategory = function (id, name, data, items)
	{
		var node = Helper.getTemplatedNode(this.categoryTemplate, {
			'name': name
		});
		var category = {
			'node': node,
			'id': id,
			'name': name,
			'data': data || {}
		};
		this.categories.push(category);
		this.categoryContainer.appendChild(node);
		BX.bind(node, 'click', this.onCategoryClick.bind(this, category));

		this.addItems(category, items);

		return category;
	};
	Searcher.prototype.setCategories = function (list)
	{
		this.categories = [];
		list.forEach(function (item) {
			this.addCategory(item.id, item.name, item.data, item.items);
		}, this);

		if (this.categories.length > 0)
		{
			this.setCurrentCategory(this.categories[0]);
		}

		var ids = this.caller.getTilesId();
		this.items.filter(function (item) {
			var isSelected = (item.id && BX.util.in_array(item.id, ids));
			Helper.changeClass(item.node, this.classNameItemActive, isSelected);
		}, this);

		Helper.changeDisplay(this.loader, false);
		Helper.changeDisplay(this.itemContainer, true);
		Helper.changeDisplay(this.categoryContainer, true);
	};
	Searcher.prototype.showLoader = function ()
	{
		Helper.changeDisplay(this.loader, true);
	};
	Searcher.prototype.hide = function ()
	{
		if (!this.popup)
		{
			return;
		}

		this.popup.close();
	};
	Searcher.prototype.show = function ()
	{
		if (this.popup)
		{
			this.popup.show();
			return;
		}

		this.popup = BX.Main.PopupManager.create(
			this.id,
			this.context,
			{
				width: 620,
				height: 300,
				autoHide: true,
				lightShadow: true,
				closeByEsc: true,
				closeIcon: false,
				offsetLeft: 40,
				angle: true,
				buttons: [
					new BX.UI.CloseButton({
						onclick: function() {
							this.popup.close();
						}.bind(this),
					})
				]
			}
		);

		this.popup.setContent(this.content);
		Helper.changeDisplay(this.content, true);
		this.popup.show();
	};


	BX.Sender.UI.TileSelector = TileSelector;

})(window);