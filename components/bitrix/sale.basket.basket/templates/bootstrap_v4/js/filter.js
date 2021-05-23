;(function(){
	'use strict';

	BX.namespace('BX.Sale.BasketFilter');

	BX.Sale.BasketFilter = function(component)
	{
		this.component = component;

		this.activeFilterMode = false;
		this.filterTimer = null;
		this.mouseOverClearFilter = false;

		this.realShownItems = [];
		this.realSortedItems = [];
		this.realScrollTop = 0;

		this.lastShownItemsHash = '';
		this.currentFilter = {
			query: '',
			similarHash: '',
			warning: false,
			notAvailable: false,
			delayed: false
		};

		if (this.component.useItemsFilter)
		{
			this.bindEvents();
		}
	};

	BX.Sale.BasketFilter.prototype.bindEvents = function()
	{
		var entity;
		var filterNode = this.component.getEntity(
			this.component.getCacheNode(this.component.ids.itemListWrapper),
			'basket-filter'
		);

		entity = this.component.getEntity(filterNode, 'basket-filter-input');
		if (BX.type.isDomNode(entity))
		{
			BX.bind(entity, 'focus', function() {
				filterNode.style.flex = 3;
			});
			BX.bind(entity, 'blur', BX.delegate(function() {
				if (!this.mouseOverClearFilter)
				{
					filterNode.style.flex = '';
				}
			}, this));

			BX.bind(entity, 'keyup', BX.proxy(this.onFilterInput, this));
			BX.bind(entity, 'cut', BX.proxy(this.onFilterInput, this));
			BX.bind(entity, 'paste', BX.proxy(this.onFilterInput, this));
		}

		entity = this.component.getEntity(filterNode, 'basket-filter-clear-btn');
		if (BX.type.isDomNode(entity))
		{
			BX.bind(entity, 'mouseenter', BX.delegate(function() {
				this.mouseOverClearFilter = true;
			}, this));
			BX.bind(entity, 'mouseout', BX.delegate(function() {
				this.mouseOverClearFilter = false;
			}, this));
			BX.bind(entity, 'click', BX.delegate(function() {
				if (!this.filterInputEmpty())
				{
					this.clearFilterInput();
					this.onFilterChange();
				}

				filterNode.style.flex = '';
			}, this));
		}
	};

	BX.Sale.BasketFilter.prototype.isActive = function()
	{
		return this.activeFilterMode;
	};

	BX.Sale.BasketFilter.prototype.showFilterByName = function(name)
	{
		if (!name)
			return;

		switch (name)
		{
			case 'not-available':
				this.showNotAvailableItemsFilter();
				break;
			case 'delayed':
				this.showDelayItemsFilter();
				break;
			case 'warning':
				this.showWarningItemsFilter();
				break;
			case 'similar':
				this.showSimilarItemsFilter();
				break;
			case 'all':
			default:
				this.clearAllFiltersExcept([]);
				this.onFilterChange();
		}
	};

	BX.Sale.BasketFilter.prototype.onFilterInput = function()
	{
		var value = BX.type.isDomNode(BX.proxy_context) ? BX.util.trim(BX.proxy_context.value).toLowerCase() : '';

		if (this.currentFilter.query !== value)
		{
			this.currentFilter.query = value;

			this.onFilterChange();
		}
	};

	BX.Sale.BasketFilter.prototype.clearAllFiltersExcept = function(names)
	{
		if (!names || !BX.type.isArray(names))
			return;

		!BX.util.in_array('input', names) && this.clearFilterInput();
		!BX.util.in_array('warning', names) && this.clearWarningItemsFilter();
		!BX.util.in_array('delayed', names) && this.clearDelayItemsFilter();
		!BX.util.in_array('not-available', names) && this.clearNotAvailableItemsFilter();

		if (!BX.util.in_array('similar', names))
		{
			this.clearSimilarItemsFilter();
			this.component.showSimilarCount(false);
		}
	};

	BX.Sale.BasketFilter.prototype.filterInputEmpty = function()
	{
		return this.currentFilter.query.length === 0;
	};

	BX.Sale.BasketFilter.prototype.clearFilterInput = function()
	{
		this.currentFilter.query = '';

		var input = this.component.getEntity(
			this.component.getCacheNode(this.component.ids.itemListWrapper),
			'basket-filter-input'
		);
		if (BX.type.isDomNode(input))
		{
			input.value = '';
		}
	};

	BX.Sale.BasketFilter.prototype.addWarningItemsFilter = function()
	{
		this.currentFilter.warning = true;
	};

	BX.Sale.BasketFilter.prototype.clearWarningItemsFilter = function()
	{
		this.currentFilter.warning = false;
	};

	BX.Sale.BasketFilter.prototype.showWarningItemsFilter = function()
	{
		if (!this.currentFilter.warning)
		{
			this.clearAllFiltersExcept(['warning']);
			this.addWarningItemsFilter();
			this.onFilterChange();
		}
	};

	BX.Sale.BasketFilter.prototype.addDelayItemsFilter = function()
	{
		this.currentFilter.delayed = true;
	};

	BX.Sale.BasketFilter.prototype.clearDelayItemsFilter = function()
	{
		this.currentFilter.delayed = false;
	};

	BX.Sale.BasketFilter.prototype.showDelayItemsFilter = function()
	{
		if (!this.currentFilter.delayed)
		{
			this.clearAllFiltersExcept(['delayed']);
			this.addDelayItemsFilter();
			this.onFilterChange();
		}
	};

	BX.Sale.BasketFilter.prototype.addNotAvailableItemsFilter = function()
	{
		this.currentFilter.notAvailable = true;
	};

	BX.Sale.BasketFilter.prototype.clearNotAvailableItemsFilter = function()
	{
		this.currentFilter.notAvailable = false;
	};

	BX.Sale.BasketFilter.prototype.showNotAvailableItemsFilter = function()
	{
		if (!this.currentFilter.notAvailable)
		{
			this.clearAllFiltersExcept(['not-available']);
			this.addNotAvailableItemsFilter();
			this.onFilterChange();
		}
	};

	BX.Sale.BasketFilter.prototype.addSimilarItemsFilter = function(item)
	{
		this.currentFilter.similarHash = item.HASH;
	};

	BX.Sale.BasketFilter.prototype.clearSimilarItemsFilter = function()
	{
		this.currentFilter.similarHash = '';
	};

	BX.Sale.BasketFilter.prototype.showSimilarItemsFilter = function()
	{
		var item = this.component.getItemDataByTarget(BX.proxy_context);

		if (this.currentFilter.similarHash !== item.HASH)
		{
			this.clearAllFiltersExcept(['similar']);
			this.addSimilarItemsFilter(item);
			this.onFilterChange();
		}
	};

	BX.Sale.BasketFilter.prototype.getTimeoutDuration = function()
	{
		return this.component.duration.filterTimer;
	};

	BX.Sale.BasketFilter.prototype.onFilterChange = function()
	{
		this.component.showItemsOverlay();

		if (
			this.currentFilter.query.length
			|| this.currentFilter.similarHash.length
			|| this.currentFilter.warning
			|| this.currentFilter.notAvailable
			|| this.currentFilter.delayed
		)
		{
			clearTimeout(this.filterTimer);
			this.filterTimer = setTimeout(BX.proxy(this.enableFilterMode, this), this.getTimeoutDuration());
		}
		else
		{
			this.disableFilterMode();
		}
	};

	BX.Sale.BasketFilter.prototype.enableFilterMode = function()
	{
		var foundItemsHash;

		if (!this.activeFilterMode)
		{
			this.activeFilterMode = true;
			this.realShownItems = BX.util.array_values(this.component.shownItems);
			this.realSortedItems = BX.util.array_values(this.component.sortedItems);
			this.realScrollTop = this.component.getDocumentScrollTop();
		}

		this.component.scrollToFirstItem();

		this.component.sortedItems = this.searchItems();

		foundItemsHash = JSON.stringify(this.component.sortedItems);

		if (this.lastShownItemsHash !== foundItemsHash)
		{
			this.lastShownItemsHash = foundItemsHash;

			this.component.deleteBasketItems(BX.util.array_values(this.component.shownItems), false);

			if (this.component.sortedItems.length)
			{
				this.component.initializeBasketItems();
				this.hideEmptyFilterResult();
			}
			else
			{
				this.showEmptyFilterResult();
			}

			if (this.currentFilter.similarHash.length)
			{
				this.component.showSimilarCount(true);
			}
		}
		else
		{
			this.highlightFoundItems();
		}

		this.component.hideItemsOverlay();
	};

	BX.Sale.BasketFilter.prototype.disableFilterMode = function()
	{
		clearTimeout(this.filterTimer);
		this.lastShownItemsHash = '';

		if (this.activeFilterMode)
		{
			this.activeFilterMode = false;
			this.component.sortedItems = BX.util.array_values(this.realSortedItems);

			this.component.deleteBasketItems(BX.util.array_values(this.component.shownItems), false);
			this.hideEmptyFilterResult();

			this.component.editBasketItems(BX.util.array_values(this.realShownItems));
			window.scrollTo(0, this.realScrollTop);
		}

		this.component.hideItemsOverlay();
	};

	BX.Sale.BasketFilter.prototype.searchItems = function()
	{
		var items = [];

		for (var i = 0; i < this.realSortedItems.length; i++)
		{
			var item = this.component.items[this.realSortedItems[i]];

			if (item && this.searchItemMatch(item))
			{
				items.push(item.ID);
			}
		}

		return items;
	};

	BX.Sale.BasketFilter.prototype.highlightFoundItems = function()
	{
		if (!this.activeFilterMode)
			return;

		for (var i in this.component.shownItems)
		{
			if (this.component.shownItems.hasOwnProperty(i))
			{
				this.highlightSearchMatch(this.component.items[this.component.shownItems[i]]);
			}
		}
	};

	BX.Sale.BasketFilter.prototype.searchItemMatch = function(item)
	{
		var match = false,
			found = false;

		if (this.currentFilter.notAvailable)
		{
			found = !!item.NOT_AVAILABLE;
			if (!found)
			{
				return match;
			}
		}
		else if (this.currentFilter.delayed)
		{
			found = !!item.DELAYED;
			if (!found)
			{
				return match;
			}
		}
		else if (this.currentFilter.warning)
		{
			found = BX.util.in_array(item.ID, this.component.warningItems);
			if (!found)
			{
				return match;
			}
		}
		else if (BX.type.isNotEmptyString(this.currentFilter.similarHash))
		{
			found = this.currentFilter.similarHash === item.HASH;
			if (!found)
			{
				return match;
			}
		}

		if (BX.type.isNotEmptyString(this.currentFilter.query))
		{
			if (item.NAME.toLowerCase().indexOf(this.currentFilter.query) !== -1)
			{
				match = 'NAME';
			}

			if (!match)
			{
				var floatValue = parseFloat(this.currentFilter.query);
				if (!isNaN(floatValue))
				{
					if (parseFloat(item.PRICE) === floatValue)
					{
						match = 'PRICE';
					}
					else if (parseFloat(item.SUM_PRICE) === floatValue)
					{
						match = 'SUM_PRICE';
					}
				}
			}

			if (!match && this.currentFilter.query.length >= 3)
			{
				if (item.PRICE_FORMATED.toLowerCase().indexOf(this.currentFilter.query) !== -1)
				{
					match = 'PRICE';
				}
				else if (item.SUM_PRICE_FORMATED.toLowerCase().indexOf(this.currentFilter.query) !== -1)
				{
					match = 'SUM_PRICE';
				}
			}

			var k, lcValue;

			if (!match && item.PROPS.length)
			{
				for (k in item.PROPS)
				{
					if (item.PROPS.hasOwnProperty(k))
					{
						lcValue = item.PROPS[k].VALUE.toLowerCase();

						if (
							lcValue === this.currentFilter.query
							|| (this.currentFilter.query.length >= 3 && lcValue.indexOf(this.currentFilter.query) !== -1)
						)
						{
							match = 'PROPS';
							break;
						}
					}
				}
			}

			if (!match && item.COLUMN_LIST.length)
			{
				for (k in item.COLUMN_LIST)
				{
					if (item.COLUMN_LIST.hasOwnProperty(k) && BX.type.isString(item.COLUMN_LIST[k].VALUE))
					{
						lcValue = item.COLUMN_LIST[k].VALUE.toLowerCase();

						if (
							lcValue === this.currentFilter.query
							|| (this.currentFilter.query.length >= 3 && lcValue.indexOf(this.currentFilter.query) !== -1)
						)
						{
							match = 'COLUMNS';
							break;
						}
					}
				}
			}
		}
		else if (found)
		{
			match = true;
		}

		return match;
	};

	BX.Sale.BasketFilter.prototype.highlightSearchMatch = function(itemData)
	{
		var searchMatch = this.searchItemMatch(itemData);

		if (searchMatch)
		{
			var entity, i, k, code;

			switch (searchMatch)
			{
				case 'NAME':
					entity = this.component.getEntity(BX(this.component.ids.item + itemData.ID), 'basket-item-name');
					if (BX.type.isDomNode(entity))
					{
						entity.innerHTML = itemData.NAME.replace(
							new RegExp('(.*)(' + this.currentFilter.query.replace(/[.*+?^${}()|[\]\\]/g, "\\$&") + ')(.*)', 'gi'),
							function (full, match1 , match2, match3)
							{
								return BX.util.htmlspecialchars(match1)
									+ '<span class="basket-item-highlighted">'
									+ BX.util.htmlspecialchars(match2)
									+ '</span>' + BX.util.htmlspecialchars(match3);
							}
						);
					}
					break;
				case 'PRICE':
					entity = BX(this.component.ids.price + itemData.ID);
					BX.addClass(entity, 'basket-item-highlighted');
					break;
				case 'SUM_PRICE':
					entity = BX(this.component.ids.sumPrice + itemData.ID);
					BX.addClass(entity, 'basket-item-highlighted');
					break;
				case 'PROPS':
					entity = this.component.getEntities(BX(this.component.ids.item + itemData.ID), 'basket-item-property-value');

					for (i = 0; i < entity.length; i++)
					{
						code = entity[i].getAttribute('data-property-code');

						for (k in itemData.PROPS)
						{
							if (itemData.PROPS.hasOwnProperty(k) && itemData.PROPS[k].CODE === code)
							{
								entity[i].innerHTML = itemData.PROPS[k].VALUE.replace(
									new RegExp('(' + this.currentFilter.query + ')', 'gi'),
									'<span class="basket-item-highlighted">$1</span>'
								);
							}
						}
					}
					break;
				case 'COLUMNS':
					entity = this.component.getEntities(BX(this.component.ids.item + itemData.ID), 'basket-item-property-column-value');

					for (i = 0; i < entity.length; i++)
					{
						code = entity[i].getAttribute('data-column-property-code');

						for (k in itemData.COLUMN_LIST)
						{
							if (itemData.COLUMN_LIST.hasOwnProperty(k) && itemData.COLUMN_LIST[k].CODE === code)
							{
								entity[i].innerHTML = itemData.COLUMN_LIST[k].VALUE.replace(
									new RegExp('(' + this.currentFilter.query + ')', 'gi'),
									'<span class="basket-item-highlighted">$1</span>'
								);
							}
						}
					}
					break;
			}
		}
	};

	BX.Sale.BasketFilter.prototype.showEmptyFilterResult = function()
	{
		var itemListNode = this.component.getCacheNode(this.component.ids.itemList);

		if (BX.type.isDomNode(itemListNode) && itemListNode.clientHeight >= 500)
		{
			var emptyResultNode = this.component.getCacheNode(this.component.ids.itemListEmptyResult);

			if (BX.type.isDomNode(emptyResultNode))
			{
				emptyResultNode.style.display = '';
			}
		}
	};

	BX.Sale.BasketFilter.prototype.hideEmptyFilterResult = function()
	{
		var emptyResultNode = this.component.getCacheNode(this.component.ids.itemListEmptyResult);

		if (BX.type.isDomNode(emptyResultNode))
		{
			emptyResultNode.style.display = 'none';
		}
	};
})();