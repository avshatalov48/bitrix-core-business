;(function() {

"use strict";

BX.namespace("BX.Kanban");

/**
 *
 * @param {object} options
 * @param {string|number} options.id
 * @param {object} options.data
 * @constructor
 */
BX.Kanban.Item = function(options)
{
	if (!BX.type.isPlainObject(options))
	{
		throw new Error("BX.Kanban.Item: 'options' is not an object.");
	}

	this.options = options;

	if (!BX.Kanban.Utils.isValidId(options.id))
	{
		throw new Error("BX.Kanban.Item: 'id' parameter is not valid.")
	}

	this.id = options.id;

	/** @var {BX.Kanban.Grid} **/
	this.grid = null;

	this.columnId = null;

	this.layout = {
		container: null,
		dragTarget: null,
		bodyContainer: null
	};

	/** @var {Element} **/
	this.dragElement = null;
	this.draggable = true;
	this.droppable = true;
	this.selectable = null;

	this.countable = true;
	this.visible = true;
	this.select = null;

	this.data = Object.create(null);

	this.setOptions(options);
};

BX.Kanban.Item.prototype =
{
	/**
	 *
	 * @returns {number|string}
	 */
	getId: function()
	{
		return this.id;
	},

	/**
	 *
	 * @returns {number|string}
	 */
	getColumnId: function()
	{
		return this.columnId;
	},

	setColumnId: function(columnId)
	{
		this.columnId = columnId;
	},

	/**
	 *
	 * @returns {BX.Kanban.Column|null}
	 */
	getColumn: function()
	{
		if (this.getGrid())
		{
			return this.getGrid().getColumn(this.getColumnId());
		}

		return null;
	},

	/**
	 * @param {BX.Kanban.Grid} grid
	 */
	setGrid: function(grid)
	{
		if (grid instanceof BX.Kanban.Grid)
		{
			this.grid = grid;
		}
	},

	/**
	 * @returns {BX.Kanban.Grid}
	 */
	getGrid: function()
	{
		return this.grid;
	},

	setOptions: function(options)
	{
		if (!options)
		{
			return;
		}

		this.setData(options.data);
		this.droppable = BX.type.isBoolean(options.droppable) ? options.droppable : this.droppable;
		this.draggable = BX.type.isBoolean(options.draggable) ? options.draggable : this.draggable;
		this.countable = BX.type.isBoolean(options.countable) ? options.countable : this.countable;
		this.visible = BX.type.isBoolean(options.visible) ? options.visible : this.visible;
		this.selectable = BX.type.isBoolean(options.selectable) ? options.selectable : this.selectable;
	},

	getData: function()
	{
		return this.data;
	},

	setData: function(data)
	{
		if (BX.type.isPlainObject(data))
		{
			this.data = data;
		}
	},

	selectItem: function()
	{
		BX.addClass(this.layout.container, "main-kanban-item-checked");
		this.select = true;

		BX.onCustomEvent("Kanban.Grid:selectItem", [this]);
	},

	unSelectItem: function()
	{
		BX.removeClass(this.layout.container, "main-kanban-item-checked");
		this.select = false;

		BX.onCustomEvent("Kanban.Grid:unSelectItem", [this]);
	},

	isSelect: function()
	{
		return this.select;
	},

	isCountable: function()
	{
		return this.countable;
	},

	isVisible: function()
	{
		return this.visible;
	},

	getGridData: function()
	{
		return this.getGrid().getData();
	},

	/**
	 *
	 * @returns {Element}
	 */
	renderLayout: function()
	{
		var bodyContainer = this.getBodyContainer();
		BX.cleanNode(bodyContainer);
		bodyContainer.appendChild(this.render());
		return this.getContainer();
	},

	/**
	 * @returns {Element}
	 */
	getContainer: function()
	{
		if (this.layout.container !== null)
		{
			return this.layout.container;
		}

		this.layout.container = BX.create("div", {
			attrs: {
				className: this.grid.firstRenderComplete ? "main-kanban-item main-kanban-item-new" : "main-kanban-item",
				"data-id": this.getId(),
				"data-type": "item"
			},
			children: [
				this.getDragTarget(),
				this.getBodyContainer()
			],
			events: {
				click: this.handleClick.bind(this)
			}
		});

		this.makeDraggable();
		this.makeDroppable();

		return this.layout.container;
	},

	handleClick: function(ev)
	{
		var grid = this.getGrid();

		if(this.selectable)
		{
			if(ev.target === this.getContainer())
			{
				return
			}

			if(this.isSelect())
			{
				grid.removeItemFromSelected(this);
				this.unSelectItem(this);
			}
			else
			{
				grid.addItemToSelected(this);
				this.selectItem();
			}

			// multiselect mode controller
			if(grid.getSelectedItems().size === 0)
			{
				grid.resetMultiSelectMode();
			}
			else if(
				grid.getSelectedItems().size > 0 &&
				!grid.isMultiSelectMode()
			)
			{
				grid.setMultiSelectMode();
			}
		}
	},

	/**
	 *
	 * @returns {Element}
	 */
	getDragTarget: function()
	{
		if (!this.layout.dragTarget)
		{
			this.layout.dragTarget = BX.create("div", {
				attrs: {
					className: "main-kanban-item-drag-target"
				}
			});
		}

		return this.layout.dragTarget;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getDragElement: function()
	{
		return this.dragElement;
	},

	getBodyContainer: function()
	{
		if (!this.layout.bodyContainer)
		{
			this.layout.bodyContainer = BX.create("div", {
				attrs: {
					className: "main-kanban-item-wrapper"
				}
			});
		}

		return this.layout.bodyContainer;
	},

	/**
	 * Default Item Render
	 *
	 * @returns {Element}
	 */
	render: function()
	{
		if (!this.layout.content)
		{
			this.layout.content = BX.create("div", {
				props: {
					className: "main-kanban-item-default"
				}
			});
		}

		this.layout.content.style.borderLeft = "2px solid #" + this.getColumn().getColor();
		this.layout.content.textContent = "#" + this.getId();

		return this.layout.content;
	},

	disabledItem: function()
	{
		this.getContainer().classList.add("main-kanban-item-disabled");
	},

	unDisabledItem: function()
	{
		this.getContainer().classList.remove("main-kanban-item-disabled");
	},

	dispose: function()
	{
		jsDD.unregisterDest(this.getContainer());
		jsDD.unregisterObject(this.getContainer());
	},

	makeDraggable: function()
	{
		if (!this.isDraggable())
		{
			return;
		}

		var itemContainer = this.getContainer();

		//main events
		itemContainer.onbxdragstart = BX.delegate(this.onDragStart, this);
		itemContainer.onbxdrag = BX.delegate(this.onDrag, this);
		itemContainer.onbxdragstop = BX.delegate(this.onDragStop, this);

		jsDD.registerObject(itemContainer);
	},

	makeDroppable: function()
	{
		if (!this.isDroppable())
		{
			return;
		}

		var itemContainer = this.getContainer();

		itemContainer.onbxdestdraghover = BX.delegate(this.onDragEnter, this);
		itemContainer.onbxdestdraghout = BX.delegate(this.onDragLeave, this);
		itemContainer.onbxdestdragfinish = BX.delegate(this.onDragDrop, this);

		itemContainer.onbxdestdragstop = BX.delegate(this.onItemDragEnd, this);

		jsDD.registerDest(itemContainer, 30);

		if (this.getGrid().getDragMode() !== BX.Kanban.DragMode.ITEM)
		{
			//when we load new items in drag mode
			this.disableDropping();
		}
	},

	disableDragging: function()
	{
		if (this.isDraggable())
		{
			jsDD.unregisterObject(this.getContainer());
		}
	},

	enableDragging: function()
	{
		if (this.isDraggable())
		{
			jsDD.registerObject(this.getContainer());
		}
	},

	disableDropping: function()
	{
		if (this.isDroppable())
		{
			jsDD.disableDest(this.getContainer());
		}
	},

	enableDropping: function()
	{
		if (this.isDroppable())
		{
			jsDD.enableDest(this.getContainer());
		}
	},

	/**
	 *
	 * @returns {boolean}
	 */
	isDraggable: function()
	{
		return this.draggable && this.getGrid().canSortItems();
	},

	/**
	 *
	 * @returns {boolean}
	 */
	isDroppable: function()
	{
		return this.droppable;
	},

	onDragStart: function()
	{
		if(this.isSelect() && this.getGrid().getSelectedItems().size > 1)
		{
			return this.onDragStartMultiple();
		}
		else
		{
			this.getGrid().resetMultiSelectMode();
			this.getGrid().cleanSelectedItems();
		}
		
		this.disabledItem();

		if (!this.dragElement)
		{
			var itemContainer = this.getContainer();
			var bodyContainer = this.getBodyContainer();

			this.dragElement = itemContainer.cloneNode(true);

			this.dragElement.style.position = "absolute";
			this.dragElement.style.width = bodyContainer.offsetWidth + "px";
			this.dragElement.className = "main-kanban-item main-kanban-item-drag";

			document.body.appendChild(this.dragElement);
		}

		BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onItemDragStart", [this]);
	},

	onDragStartMultiple: function()
	{
		var checkedItems = [];

		this.getGrid().getSelectedItems().forEach(function(item) {
			checkedItems.push(item);
		});

		if(!this.dragElement)
		{
			var bodyContainer = this.getBodyContainer(),
				mainItem = this.getContainer().cloneNode(true),
				itemContainer;

			this.dragElement = BX.create("div");
			this.dragElement.style.position = "absolute";
			this.dragElement.style.width = bodyContainer.offsetWidth + "px";
			this.dragElement.className = "main-kanban-item main-kanban-item-drag-multi";
			mainItem.classList.remove("main-kanban-item-checked");
			this.dragElement.appendChild(mainItem);

			for (var i = 0;
				 		checkedItems.length >= 3 ?
					 	i < 3 :
					 	i < checkedItems.length; i++)
			{
				if(checkedItems[i] !== this)
				{
					itemContainer = checkedItems[i].getContainer().cloneNode(true);
					itemContainer.classList.remove("main-kanban-item-checked");
					this.dragElement.appendChild((itemContainer));
				}

			}

			document.body.appendChild(this.dragElement);
		}

		checkedItems.forEach(function(item) {
			item.getContainer().classList.add("main-kanban-item-disabled");
		}, this);

		BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onItemDragStartMultiple", [this.getGrid().getSelectedItems()]);
	},

	/**
	 *
	 * @param {number} x
	 * @param {number} y
	 */
	onDragStop: function(x, y)
	{
		if(this.selectable && this.getGrid().getSelectedItems().size > 1)
		{
			return this.onDragStopMultiple();
		}

		BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onItemDragStop", [this]);

		this.unDisabledItem();
		BX.remove(this.dragElement);
		this.dragElement = null;
	},

	onDragStopMultiple: function()
	{
		BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onItemDragStopMultiple", [this.getGrid().getSelectedItems()]);

		BX.remove(this.dragElement);
		this.dragElement = null;
	},

	/**
	 *
	 * @param {number} x
	 * @param {number} y
	 */
	onDrag: function(x, y)
	{
		if (this.dragElement)
		{
			this.dragElement.style.left = x + "px";
			this.dragElement.style.top = y + "px";
		}
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onDragEnter: function(itemNode, x, y)
	{
		var draggableItem = this.getGrid().getItemByElement(itemNode);
		if (draggableItem !== this || this.getGrid().isMultiSelectMode())
		{
			this.showDragTarget(draggableItem.getBodyContainer().offsetHeight);
		}
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onDragLeave: function(itemNode, x, y)
	{
		this.hideDragTarget();
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onDragDrop: function(itemNode, x, y)
	{
		if(this.selectable && this.getGrid().getSelectedItems().size > 1)
		{
			return this.onDragDropMultiple();
		}

		this.hideDragTarget();
		var draggableItem = this.getGrid().getItemByElement(itemNode);

		var event = new BX.Kanban.DragEvent();
		event.setItem(draggableItem);
		event.setTargetColumn(this.getColumn());
		event.setTargetItem(this);

		BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onBeforeItemMoved", [event]);
		if (!event.isActionAllowed())
		{
			return;
		}

		var success = this.getGrid().moveItem(draggableItem, this.getColumn(), this);
		if (success)
		{
			BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onItemMoved", [draggableItem, this.getColumn(), this]);
		}
	},

	onDragDropMultiple: function()
	{
		this.hideDragTarget();
		var draggableItems = this.getGrid().getSelectedItems();

		var event = new BX.Kanban.DragEvent();
		// event.setItem(draggableItem);
		event.setTargetColumn(this.getColumn());
		event.setTargetItem(this);

		BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onBeforeItemMovedMultiple", [event]);
		if (!event.isActionAllowed())
		{
			return;
		}

		var success = this.getGrid().moveItems(draggableItems, this.getColumn(), this);
		if (success)
		{
			BX.onCustomEvent(this.getGrid(), "Kanban.Grid:onItemMovedMultiple", [draggableItems, this.getColumn(), this]);
		}
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onItemDragEnd: function(itemNode, x, y)
	{
		this.disableDropping();
	},

	/**
	 *
	 * @param {number} height
	 */
	showDragTarget: function(height)
	{
		this.getContainer().classList.add("main-kanban-item-target-shown");
		this.getDragTarget().style.height = height + "px";
	},

	hideDragTarget: function()
	{
		this.getContainer().classList.remove("main-kanban-item-target-shown");
		this.getDragTarget().style.removeProperty("height");
	}

};


/**
 *
 * @param options
 * @extends {BX.Kanban.Item}
 * @constructor
 */
BX.Kanban.DraftItem = function(options)
{
	BX.Kanban.Item.apply(this, arguments);
	this.asyncEventStarted = false;
	this.draftContainer = null;
	this.draftTextArea = null;
};

BX.Kanban.DraftItem.prototype = {
	__proto__: BX.Kanban.Item.prototype,
	constructor: BX.Kanban.DraftItem,

	/**
	 * @override
	 */
	render: function()
	{
		if (this.draftContainer)
		{
			return this.draftContainer;
		}

		this.draftContainer = BX.create("div", {
			props: {
				className: "main-kanban-item-draft"
			},
			children: [
				this.getDraftTextArea()
			]
		});

		return this.draftContainer;
	},

	/**
	 * @inheritDoc
	 * @override
	 * @param {BX.Kanban.Grid} grid
	 */
	setGrid: function(grid)
	{
		BX.Kanban.Item.prototype.setGrid.apply(this, arguments);
		BX.addCustomEvent(this.getGrid(), "Kanban.Grid:onItemDragStart", BX.proxy(this.applyDraftEditMode, this));
	},

	/**
	 *
	 * @returns {Element}
	 */
	getDraftTextArea: function()
	{
		if (this.draftTextArea)
		{
			return this.draftTextArea;
		}

		this.draftTextArea = BX.create("textarea", {
			attrs: {
				className: "main-kanban-item-draft-textarea",
				placeholder: this.getGrid().getMessage("ITEM_TITLE_PLACEHOLDER")
			},
			events: {
				blur: this.handleDraftTextAreaBlur.bind(this),
				keydown: this.handleDraftTextAreaKeyDown.bind(this)
			}
		});

		return this.draftTextArea;
	},

	applyDraftEditMode: function()
	{
		if (this.asyncEventStarted)
		{
			return;
		}

		this.asyncEventStarted = true;

		var title = BX.util.trim(this.getDraftTextArea().value);
		if (!title.length)
		{
			this.removeDraftItem();
			return;
		}

		this.setData({ title: title });
		this.getContainer().classList.add("main-kanban-item-draft-disabled");
		this.getDraftTextArea().disabled = true;

		var promise = this.getGrid().getEventPromise(
			"Kanban.Grid:onItemAddedAsync",
			null,
			this.onItemAddedFulfilled.bind(this),
			this.onItemAddedRejected.bind(this)
		);

		promise.fulfill(this);
	},

	onItemAddedFulfilled: function(result)
	{
		if (!BX.type.isPlainObject(result))
		{
			this.removeDraftItem();
			return;
		}

		if (!BX.Kanban.Utils.isValidId(result.targetId))
		{
			var targetItem = this.getColumn().getNextItemSibling(this);
			if (targetItem)
			{
				result.targetId = targetItem.getId();
			}
		}

		this.removeDraftItem();
		var newItem = this.getGrid().addItem(result);
		if (newItem && this.getGrid().getDragMode() === BX.Kanban.DragMode.NONE)
		{
			var draftItemExists = this.getGrid().getColumns().some(function(column) {
				return column.getDraftItem() !== null;
			});

			if (!draftItemExists)
			{
				var nextItem = newItem.getColumn().getNextItemSibling(newItem);
				newItem.getColumn().addDraftItem(nextItem);
			}
		}
	},

	onItemAddedRejected: function(error)
	{
		this.removeDraftItem();
	},

	removeDraftItem: function()
	{
		this.asyncEventStarted = true;
		BX.removeCustomEvent(this.getGrid(), "Kanban.Grid:onItemDragStart", BX.proxy(this.applyDraftEditMode, this));
		this.getColumn().removeDraftItem();
	},

	focusDraftTextArea: function()
	{
		this.getDraftTextArea().focus();
	},

	handleDraftTextAreaBlur: function(event)
	{
		//Blur event can be fired when `render` cleans items container.
		//It causes DOM exception.
		setTimeout(function() {
			this.applyDraftEditMode();
		}.bind(this), 0);
	},

	handleDraftTextAreaKeyDown: function(event)
	{
		if (event.keyCode === 13)
		{
			this.applyDraftEditMode();
		}
		else if (event.keyCode === 27)
		{
			this.removeDraftItem();
		}
	}
};


})();