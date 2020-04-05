;(function() {

'use strict';

BX.namespace('BX.UI');

BX.UI.ActionPanel = function(options)
{
	this.groupActions = options.groupActions;
	this.layout = {
		container: null,
		itemContainer: null,
		more: null,
		reset: null,
		totalSelected: null,
		totalSelectedItem: null
	};
	this.itemContainer = null;
	this.renderContainer = options.renderTo;
	this.items = [];
	this.hiddenItems = [];
	this.grid = null;
	this.tileGrid = null;
	this.params = options.params || {};

	this.buildPanelContainer();
	this.bindEvents();
};

BX.UI.ActionPanel.prototype =
{
	bindEvents: function()
	{
		BX.addCustomEvent('Grid::ready', this.handleGridReady.bind(this));
		BX.addCustomEvent('BX.TileGrid.Grid::ready', this.handleTileGridReady.bind(this));

		BX.addCustomEvent(window, 'BX.TileGrid.Grid:selectItem', this.handleTileSelectItem.bind(this));
		BX.addCustomEvent(window, 'BX.TileGrid.Grid:checkItem', this.handleTileSelectItem.bind(this));
		BX.addCustomEvent(window, 'BX.TileGrid.Grid:unSelectItem', this.handleTileUnSelectItem.bind(this));

		BX.addCustomEvent('Grid::thereSelectedRows', this.handleGridSelectItem.bind(this));
		BX.addCustomEvent('Grid::allRowsSelected', this.handleGridSelectItem.bind(this));

		BX.addCustomEvent(window, 'BX.TileGrid.Grid:redraw', this.hidePanel.bind(this));
		BX.addCustomEvent(window, 'BX.TileGrid.Grid:defineEscapeKey', this.hidePanel.bind(this));
		BX.addCustomEvent(window, 'BX.TileGrid.Grid:lastSelectedItem', this.hidePanel.bind(this));
		BX.addCustomEvent(window, 'BX.UI.ActionPanel:clickResetAllBlock', this.hidePanel.bind(this));
		BX.addCustomEvent(window, 'BX.TileGrid.Grid:multiSelectModeOff', this.hidePanel.bind(this));
		BX.addCustomEvent('Grid::updated', this.hidePanel.bind(this));
		BX.addCustomEvent('Grid::noSelectedRows', this.hidePanel.bind(this));
		BX.addCustomEvent('Grid::allRowsUnselected', this.hidePanel.bind(this));

		BX.bind(window, 'click', this.handleOuterClick.bind(this));
		BX.bind(window, 'scroll', this.handleScroll.bind(this));
		BX.bind(window, 'resize', BX.throttle(this.adjustPanelStyle, 20, this));
	},

	addItems: function(items)
	{
		items.forEach(function (item) {
			this.appendItem(item);
		}.bind(this));

		this.items.forEach(function (item) {
			if (!item.isVisible() && !this.layout.more)
			{
				this.appendMoreBlock();
			}
		}, this);

		this.items.forEach(function (item) {
			if (!item.isVisible())
			{
				this.addHiddenItem(item);
			}
		}, this);
	},

	buildItem: function(options)
	{
		options.actionPanel = this;

		return new BX.UI.ActionPanel.Item(options);
	},

	appendItem: function(options)
	{
		var item = this.buildItem(options);

		this.items.push(item);
		this.layout.itemContainer.appendChild(item.render());

	},

	addHiddenItem: function(item)
	{
		this.hiddenItems.push(item);
	},

	removeHiddenItem: function(item)
	{
		for (var i = 0; i < this.hiddenItems.length; i++)
		{
			if (this.hiddenItems[i].id === item.id)
			{
				delete this.hiddenItems[i];
				this.hiddenItems.splice(i, 1);

				return;
			}
		}
	},

	removeItems: function ()
	{
		this.items.forEach(function (item) {
			item.destroy();
		});

		this.items = [];
		this.hiddenItems = [];
	},

	appendMoreBlock: function()
	{
		this.layout.more = BX.create("div", {
			props: {
				className: "ui-action-panel-more"
			},
			text: BX.message('JS_UI_ACTIONPANEL_MORE_BLOCK'),
			events: {
				click: this.handleClickMoreBlock.bind(this)
			}
		});

		this.layout.container.appendChild(this.layout.more);
	},

	getResetAllBlock: function()
	{
		this.layout.reset = BX.create("div", {
			props: {
				className: "ui-action-panel-reset"
			}
		});

		BX.bind(this.layout.reset, "click", function()
		{
			BX.onCustomEvent('BX.UI.ActionPanel:clickResetAllBlock');
			this.resetAllSection();
		}.bind(this));

		return this.layout.reset
	},

	resetAllSection: function()
	{
		if (this.grid)
		{
			this.grid.getRows().unselectAll();
		}
		else if (this.tileGrid)
		{
			this.tileGrid.resetSetMultiSelectMode();
			this.tileGrid.resetSelectAllItems();
			this.tileGrid.resetFromToItems();
		}
	},

	handleScroll: function ()
	{
		if (this.getDistanceFromTop() > 0)
		{
			this.unfixPanel();
		}
		else
		{
			this.fixPanel();
		}

 		var moreMenu = BX.PopupMenu.getMenuById('ui-action-panel-item-popup-menu');
		if (moreMenu)
		{
			moreMenu.popupWindow.adjustPosition();
		}
	},

	handleOuterClick: function (event)
	{
		var target = BX.getEventTarget(event);

		if (BX.hasClass(target, "ui-action-panel"))
		{
			return;
		}

		if (BX.findParent(target, {className: "ui-action-panel"}))
		{
			return;
		}

		if (BX.findParent(target, {className: "main-grid-container"}))
		{
			return;
		}

		if (BX.findParent(target, {className: "ui-grid-tile-item"}))
		{
			return;
		}

		this.hidePanel();
		if (this.grid)
		{
			this.resetAllSection();
		}
	},

	handleClickMoreBlock: function (event)
	{
		var bindElement = this.layout.more;
		var popupMenu = BX.PopupMenu.create("ui-action-panel-item-popup-menu", bindElement, this.hiddenItems, {
			className: "ui-action-panel-item-popup-menu",
			angle: true,
			offsetLeft: bindElement.offsetWidth / 2,
			closeByEsc: true,
			events: {
				onPopupShow: function() {
					BX.bind(popupMenu.popupWindow.popupContainer, 'click', function(event) {
						var target = BX.getEventTarget(event);
						var item = BX.findParent(target, {
							className: 'menu-popup-item'
						}, 10);

						if (!item || !item.dataset.preventCloseContextMenu)
						{
							popupMenu.close();
						}
					});
				},
				onPopupClose: function() {
					popupMenu.destroy();
					BX.removeClass(bindElement, "ui-action-panel-item-active");
				}
			}
		});

		popupMenu.layout.menuContainer.setAttribute("data-tile-grid", "tile-grid-stop-close");
		popupMenu.show();
	},

	removeMoreBlock: function()
	{
		if(!this.layout.more)
			return;

		this.layout.more.parentNode.removeChild(this.layout.more);
		this.layout.more = null;
	},

	getDistanceFromTop: function()
	{
		return this.renderContainer.getBoundingClientRect().top;
	},

	fixPanel: function()
	{
		BX.addClass(this.layout.container, "ui-action-panel-fixed");
	},

	unfixPanel: function()
	{
		BX.removeClass(this.layout.container, "ui-action-panel-fixed");
	},

	buildPanelContainer: function()
	{
		this.layout.container = BX.create("div", {
			attrs: {
				className: "ui-action-panel"
			},
			dataset: {
				tileGrid: "tile-grid-stop-close"
			},
			children: [
				this.getTotalSelectedBlock(),
				this.getItemContainer(),
				this.getResetAllBlock()
			]
		});
	},

	getItemContainer: function()
	{
		return this.layout.itemContainer = BX.create('div', {
			props: {
				className: 'ui-action-panel-wrapper'
			}
		})
	},

	getTotalSelectedBlock: function()
	{
		return this.layout.totalSelected = BX.create('div', {
			props: {
				className: 'ui-action-panel-total'
			},
			children: [
				BX.create('span', {
					props: {
						className: 'ui-action-panel-total-label'
					},
					text: BX.message('JS_UI_ACTIONPANEL_IS_SELECTED')
				}),
				this.layout.totalSelectedItem = BX.create('span', {
					props: {
						className: 'ui-action-panel-total-param'
					}
				})
			]
		})
	},

	getPanelContainer: function()
	{
		return this.layout.container
	},

	adjustPanelStyle: function()
	{
		var parentContainerParam = BX.pos(this.renderContainer);

		this.layout.container.style.width = parentContainerParam.width + "px";
		this.layout.container.style.top = parentContainerParam.top + "px";
		this.layout.container.style.left = parentContainerParam.left + "px";
	},

	/**
	 * @param {BX.Main.grid} grid
	 */
	handleGridReady: function(grid)
	{
		if (!this.grid && grid.getContainerId() === this.params.gridId)
		{
			this.grid = grid;
		}
	},

	/**
	 * @param {BX.TileGrid.Grid} tileGrid
	 */
	handleTileGridReady: function(tileGrid)
	{
		if (!this.tileGrid && tileGrid.getId() === this.params.tileGridId)
		{
			this.tileGrid = tileGrid;
		}
	},

	/**
	 * @param {BX.Grid.Row} item
	 * @param {BX.Main.grid} grid
	 */
	handleGridUnSelectItem: function(item, grid)
	{
		if (grid.getRows().getSelectedIds().length === 1)
		{
			this.buildPanelByItem(grid.getRows().getSelected().pop());
		}
	},

	/**
	 * @param {BX.Disk.TileGrid.Item} item
	 * @param {BX.TileGrid.Grid} tileGrid
	 */
	handleTileUnSelectItem: function(item, tileGrid)
	{
		if (tileGrid.getSelectedItems().length === 1)
		{
			this.buildPanelByItem(tileGrid.getSelectedItems().pop());
		}
		this.setTotalSelectedItems(tileGrid.getSelectedItems().length);
	},
	
	handleGridSelectItem: function()
	{
		this.setTotalSelectedItems(this.grid.getRows().getSelectedIds().length);
		if (this.grid.getRows().getSelectedIds().length > 1)
		{
			this.buildPanelByGroup();
		}
		else
		{
			this.buildPanelByItem(this.grid.getRows().getSelected().pop());
		}
	},

	/**
	 * @param {BX.Disk.TileGrid.Item} item
	 * @param {BX.TileGrid.Grid} tileGrid
	 */
	handleTileSelectItem: function(item, tileGrid)
	{
		this.setTotalSelectedItems(tileGrid.getSelectedItems().length);
		if (tileGrid.isMultiSelectMode() && tileGrid.getSelectedItems().length > 1)
		{
			this.buildPanelByGroup();
		}
		else
		{
			this.buildPanelByItem(item);
		}
	},

	/**
	 * @param {BX.Disk.TileGrid.Item|BX.Grid.Row} item
	 */
	buildPanelByItem: function(item)
	{
		var actions = item.getActions();
		var buttons = [];
		actions.forEach(function (action) {
			buttons.push(action);
		}.bind(this));

		this.removeItems();
		this.addItems(buttons);

		this.showPanel();

		if(this.hiddenItems.length <= 0)
			this.removeMoreBlock();
	},

	buildPanelByGroup: function()
	{
		if (!this.groupActions)
		{
			return;
		}

		var buttons = this.extractButtonsFromGroupActions(this.groupActions);
		this.removeItems();
		this.addItems(buttons);

		this.showPanel();
	},

	setTotalSelectedItems: function(totalSelectedItems)
	{
		this.layout.totalSelectedItem.innerHTML = totalSelectedItems;
	},

	extractButtonsFromGroupActions: function (groupActions)
	{
		var clonedGroupActions = BX.clone(groupActions);
		if (!clonedGroupActions['GROUPS'] || !clonedGroupActions['GROUPS'][0] ||  !clonedGroupActions['GROUPS'][0]['ITEMS'])
		{
			return [];
		}

		var buttons = [];
		var items = clonedGroupActions['GROUPS'][0]['ITEMS'];
		items.forEach(function (item) {
			if (item.TYPE === 'BUTTON')
			{
				var onclick = item.ONCHANGE.pop();
				if (onclick && onclick.ACTION === 'CALLBACK')
				{
					var firstHandler = onclick.DATA.pop();
					buttons.push({
						id: item.ID || item.VALUE,
						text: item.TEXT || item.NAME,
						icon: item.ICON,
						onclick: firstHandler.JS
					});
				}
			}
		});

		return buttons;
	},

	showPanel: function()
	{
		if (BX.hasClass(this.layout.container, "ui-action-panel-show"))
			return;

		BX.addClass(this.layout.container, "ui-action-panel-show");
		BX.addClass(this.layout.container, "ui-action-panel-show-animate");

		var parentContainerParam = BX.pos(this.renderContainer);
		this.layout.container.style.height = parentContainerParam.height + "px";

		setTimeout(function() {
			BX.removeClass(this.layout.container, "ui-action-panel-show-animate");
		}.bind(this), 300)
	},

	hidePanel: function()
	{
		BX.removeClass(this.layout.container, "ui-action-panel-show");
		BX.removeClass(this.layout.container, "ui-action-panel-show-animate");
		BX.addClass(this.layout.container, "ui-action-panel-hide-animate");

		setTimeout(function() {
			BX.removeClass(this.layout.container, "ui-action-panel-hide-animate");
		}.bind(this), 300)
	},

	draw: function()
	{
		document.body.appendChild(this.getPanelContainer());
		this.adjustPanelStyle();
	}
}
})();