;(function() {

'use strict';

BX.namespace('BX.TileGrid');

BX.TileGrid.DragDrop = function(options)
{
	this.gridTile = options;
	this.draggableContainer = null;
	this.droppableItem = null;
	this.draggableItems = [];
};

BX.TileGrid.DragDrop.prototype =
{
	registerItem: function(object)
	{
		object.onbxdragstart = BX.proxy(this.onDragStart, this);
		object.onbxdrag = BX.proxy(this.onDrag, this);
		object.onbxdragstop = BX.proxy(this.onDragStop, this);
		object.onbxdraghover = BX.proxy(this.onDragOver, this );
		object.onbxdraghout = BX.proxy(this.onDragOut, this );
		jsDD.registerObject(object);
	},

	registerDrop: function(object)
	{
		jsDD.registerDest(object, 10);
	},

	onDragStart: function()
	{
		this.setDraggableItems();
		this.setDraggableContainer();

		var div = BX.proxy_context;
		var item = this.gridTile.getItem(div.dataset.id);

		if(!item.selected)
		{
			this.gridTile.resetSelectAllItems();
			this.draggableItems = [];
			this.draggableItems.push(item);
		}

		var itemBlock;
		var widthItem = this.draggableItems[0].layout.container.offsetWidth;

		for (var i = 0; i < this.draggableItems.length; i++)
		{
			itemBlock = this.draggableItems[i].layout.container.cloneNode(true);
			itemBlock.style.width = widthItem + 'px';

			this.draggableContainer.appendChild(itemBlock);
			this.toggleActiveClass(this.draggableItems[i].layout.container);
		}

		BX.onCustomEvent(this.gridTile, "TileGrid.Grid:onItemDragStart", [this]);

		document.body.appendChild(this.draggableContainer);
	},

	onDrag: function(x, y)
	{
		this.draggableContainer.style.left = x + 'px';
		this.draggableContainer.style.top = y + 'px';
	},

	onDragOver: function(destination)
	{
		this.handlerDroppableClass();

		var itemId = destination.dataset.id;
		this.droppableItem = this.gridTile.getItem(itemId);

		this.handlerDroppableClass();

		BX.onCustomEvent(this.gridTile, "TileGrid.Grid:onItemDragOver", [this]);
	},

	onDragOut: function(destination)
	{
		this.handlerDroppableClass();
		this.droppableItem = null;

		BX.onCustomEvent(this.gridTile, "TileGrid.Grid:onItemDragOut", [this]);
	},

	handlerDroppableClass: function()
	{
		if(!this.droppableItem)
			return;

		BX.toggleClass(this.droppableItem.layout.container, 'ui-grid-tile-item-droppable');
	},

	onDragStop: function()
	{
		this.gridTile.resetSelectAllItems();
		this.moveDraggableItems();
		this.handlerDroppableClass();

		for (var i = 0; i < this.draggableItems.length; i++)
		{
			this.toggleActiveClass(this.draggableItems[i].layout.container)
		}

		this.draggableContainer.parentNode.removeChild(this.draggableContainer);
		this.draggableItems = [];
		this.droppableItem = null;
		this.draggableContainer = null;

		BX.onCustomEvent(this.gridTile, "TileGrid.Grid:onItemDragStop", [this]);
	},

	moveDraggableItems: function()
	{
		for (var z = 0; z < this.draggableItems.length; z++)
		{
			if(this.draggableItems[z] === this.droppableItem)
				return
		}

		if(!this.droppableItem)
			return;

		if(this.droppableItem.isDroppable)
		{
			this.droppableItem.animateNode();

			for (var i = 0; i < this.draggableItems.length; i++)
			{
				this.gridTile.moveItem(this.draggableItems[i], this.droppableItem);
			}
		}
	},

	setDraggableItems: function()
	{
		for (var i = 0; i < this.gridTile.items.length; i++)
		{
			this.gridTile.items[i].selected ? this.draggableItems.push(this.gridTile.items[i]) : null
		}
	},

	setDraggableContainer: function()
	{
		this.draggableContainer = BX.create('div', {
			attrs: {
				className: this.draggableItems.length <= 1 ?
					'ui-grid-tile-item-draggable-single' :
					'ui-grid-tile-item-draggable'
			},
			style: {
				width: this.draggableItems.length > 1 ? this.draggableItems[0].layout.container.offsetWidth + 'px' : null,
				height: this.draggableItems.length > 1 ? this.draggableItems[0].layout.container.offsetHeight + 'px' : null
			}
		});
	},

	toggleActiveClass: function(itemBlock)
	{
		itemBlock.classList.contains('ui-grid-tile-item-active') ?
			itemBlock.classList.remove('ui-grid-tile-item-active') :
			itemBlock.classList.add('ui-grid-tile-item-active')
	}
}
})();