;(function() {

'use strict';

BX.namespace('BX.TileGrid');

BX.TileGrid.Item = function(options)
{
	this.id = options.id;
	this.isDraggable = options.isDraggable || false;
	this.isDroppable = options.isDroppable || false;
	this.name = options.name;
	this.type = options.type;
	this.layout = {
		container: null,
		checkbox: null
	};
	this.gridTile = null;
	this.dblClickDelay = 180;
};

BX.TileGrid.Item.prototype =
{
	bindEvents: function()
	{
		BX.addCustomEvent(window, 'BX.TileGrid.Grid:lastSelectedItem', function()
		{

		});
	},

	getId: function()
	{
		return this.id;
	},

	render: function()
	{
		var clickTimer = null;
		var preventClick = false;

		this.layout.container = BX.create('div', {
			attrs: {
				className: this.gridTile.itemHeight ? 'ui-grid-tile-item ui-grid-tile-item-fixed-height' : 'ui-grid-tile-item'
			},
			style: {
				height: this.gridTile.itemHeight ? this.gridTile.itemHeight + 'px' : null,
				margin: this.gridTile.getTileMargin() ? this.gridTile.getTileMargin() + 'px' : null
			},
			dataset: {
				id: this.id
			},
			children: [
				this.gridTile.checkBoxing ? this.getCheckBox() : null,
				this.layout.content = BX.create('div', {
					attrs: {
						className: 'ui-grid-tile-item-content'
					},
					children: [
						this.getContent()
					]
				})
			],
			events: {
				dblclick: function(event)
				{
					clickTimer && clearTimeout(clickTimer);
					preventClick = true;
					this.handleDblClick.call(this, event);
					this.gridTile.resetSetMultiSelectMode();
					this.gridTile.resetSelectAllItems();
					this.gridTile.resetFromToItems();
				}.bind(this),
				click: function(event)
				{
					clickTimer = setTimeout(function () {
						if(!preventClick)
						{
							this.handleClick.call(this, event);
						}
						preventClick = false;
					}.bind(this), this.dblClickDelay);
				}.bind(this)
			}
		});

		if (this.isDraggable)
		{
			this.gridTile.dragger.registerItem(this.layout.container);
		}
		if (this.isDroppable)
		{
			this.gridTile.dragger.registerDrop(this.layout.container);
		}

		return this.layout.container
	},

	isVisibleItem: function()
	{
		var rect = this.layout.container.getBoundingClientRect();
		var rectBody = document.body.getBoundingClientRect();

		if (rect.top < 0 || rect.bottom < 0)
		{
			return false;
		}

		return rectBody.height > rect.top && rectBody.height >= rect.bottom;
	},

	afterRender: function()
	{},

	handleClick: function(event)
	{
		this.focusItem();
		this.resetFocusItem();

		var grid = this.gridTile;

		if(grid.isKeyControlKey())
		{
			grid.setMultiSelectMode();
			grid.checkItem(grid.getFirstCurrentItem())
		}

		if(!grid.isLastSelectedItem())
		{
			if(!grid.isMultiSelectMode())
			{
				grid.unSelectItem(grid.getCurrentItem())
			}
		}

		if(!grid.getFirstCurrentItem())
		{
			grid.setFirstCurrentItem(this);
		}

		if(grid.isKeyPressedShift())
		{
			grid.selectFromToItems(grid.getFirstCurrentItem(), this);
			return;
		}

		if(grid.isMultiSelectMode() || grid.isKeyPressedSelectAll())
		{
			if(!this.checked)
			{
				grid.checkItem(this);
				grid.selectItem(this);
				grid.setCurrentItem(this);
				grid.setFirstCurrentItem(this);
			}
			else
			{
				grid.unCheckItem(this);
				grid.unSelectItem(this);

				if(grid.isLastSelectedItem())
					grid.resetSetMultiSelectMode();

			}

			return;
		}

		if(this.selected)
		{
			grid.unSelectItem(this)
		}
		else
		{
			grid.selectItem(this);
			grid.unCheckItem(this);
			grid.setCurrentItem(this);
			grid.setFirstCurrentItem(this);

			if(grid.isLastSelectedItem())
				grid.resetSetMultiSelectMode();
		}
	},

	handleDblClick: function(event)
	{},

	handleEnter: function()
	{},

	getContainer: function()
	{
		return this.layout.container;
	},

	getCheckBox: function()
	{
		return this.layout.checkbox = BX.create('div', {
			props: {
				className: 'ui-grid-tile-item-checkbox'
			},
			events: {
				click: function(event)
				{
					if(this.gridTile.isLastSelectedItem())
						this.gridTile.resetSetMultiSelectMode();

					if(this !== this.gridTile.getCurrentItem() && this.gridTile.isMultiSelectMode())
					{
						this.gridTile.checkItem(this.gridTile.getCurrentItem());
						this.gridTile.selectItem(this.gridTile.getCurrentItem());

					}

					this.gridTile.checkItem(this);
					this.gridTile.selectItem(this);
					this.gridTile.setCurrentItem(this);
					this.gridTile.setFirstCurrentItem(this);

					if(!this.gridTile.isLastSelectedItem())
					{
						if(this.gridTile.isMultiSelectMode())
							this.gridTile.checkItem(this.gridTile.getFirstCurrentItem());
					}

					this.focusItem();
					this.resetFocusItem();
					event.stopPropagation();
				}.bind(this)
			}
		})
	},

	getContent: function() {
		return null
	},

	focusItem: function()
	{
		this.layout.container.setAttribute('tabindex', '1');
		this.layout.container.focus();
	},

	resetFocusItem: function()
	{
		this.layout.container.removeAttribute('tabindex')
	},

	removeNode: function(withAnimation)
	{
		withAnimation = withAnimation !== false;
		var itemContainer = this.layout.container;

		if(!itemContainer.parentNode)
			return;

		if(!withAnimation)
		{
			itemContainer.parentNode.removeChild(itemContainer);
			return;
		}

		itemContainer.classList.add('ui-grid-tile-item-to-fade');
		itemContainer.style.width = itemContainer.offsetWidth + 'px';

		BX.bind(itemContainer, 'transitionend', function()
		{
			itemContainer.classList.add('ui-grid-tile-item-to-remove');
		});

		setTimeout(function()
		{
			itemContainer.parentNode.removeChild(itemContainer);
		}, 500);
	},

	animateNode: function()
	{
		var itemContainer = this.layout.container;
		itemContainer.classList.add('ui-grid-tile-item-to-receive');

		setTimeout(function()
		{
			itemContainer.classList.remove('ui-grid-tile-item-to-receive');
		},500);
	}
}

})();