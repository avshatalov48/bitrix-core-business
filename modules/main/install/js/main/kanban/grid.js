;(function() {

"use strict";

BX.namespace("BX.Kanban");

/**
 *
 * @param {object} options
 * @param {Element} options.renderTo
 * @param {BX.Kanban.Column[]} [options.columns]
 * @param {BX.Kanban.Item[]} [options.items]
 * @param {BX.Kanban.DropZone[]} [options.dropzones]
 * @param {object} [options.events]
 * @param {string} [options.itemType]
 * @param {string} [options.columnType]
 * @param {boolean} [options.canAddColumn]
 * @param {boolean} [options.canEditColumn]
 * @param {boolean} [options.canSortColumn]
 * @param {boolean} [options.canRemoveColumn]
 * @param {boolean} [options.canAddItem]
 * @param {boolean} [options.canSortItem]
 * @param {string} [options.dropZoneType]
 * @param {number} [options.dropZoneTimeout]
 * @param {string} [options.bgColor]
 * @param {object} [options.data] Custom Data For Grid
 * @param {object} [options.messages] Custom Messages For Grid
 * @param {boolean} [options.multiSelect]
 * @constructor
 */
BX.Kanban.Grid = function(options)
{
	if (!BX.type.isPlainObject(options))
	{
		throw new Error("BX.Kanban.Grid: 'options' is not an object.");
	}

	this.options = options;

	if (!BX.type.isDomNode(options.renderTo))
	{
		throw new Error("BX.Kanban.Grid: 'renderTo' is not a DOMNode.");
	}

	this.renderTo = options.renderTo;
	this.rendered = false;

	this.layout = {
		outerContainer: null,
		innerContainer: null,
		gridContainer: null,
		earLeft: null,
		earRight: null,
		emptyStub: null,
		loader: null,
		leftShadow: null,
		rightShadow: null
	};

	this.emptyStubItems = options.emptyStubItems;
	this.itemType = this.getItemType(options.itemType);
	this.columnType = this.getColumnType(options.columnType);

	this.messages = BX.type.isPlainObject(options.messages) ? options.messages : Object.create(null);

	this.columns = Object.create(null);
	this.columnsOrder = [];

	/** @type {Object.<string, BX.Kanban.Item>} */
	this.items = Object.create(null);

	this.data = BX.type.isPlainObject(options.data) ? options.data : Object.create(null);
	this.bgColor =
		BX.Kanban.Utils.isValidColor(options.bgColor) || options.bgColor === "transparent" ? options.bgColor : "ffffff";

	this.earTimer = null;
	this.firstRenderComplete = null;
	this.dragMode = BX.Kanban.DragMode.NONE;

	this.multiSelect = options.multiSelect;
	this.ahaMode = null;
	this.selectedItems = [];
	this.addItemTitleText = options.addItemTitleText;
	this.addDraftItemInfo = options.addDraftItemInfo;

	/** @private **/
	this.canAddColumn = false;
	/** @private **/
	this.canEditColumn = false;
	/** @private **/
	this.canSortColumn = false;
	/** @private **/
	this.canRemoveColumn = false;
	/** @private **/
	this.canAddItem = false;
	/** @private **/
	this.canSortItem = false;

	this.dropZoneArea = new BX.Kanban.DropZoneArea(this, {
		dropZoneType: options.dropZoneType,
		dropZoneTimeout: options.dropZoneTimeout
	});

	this.data = Object.create(null);
	this.setData(options.data);

	this.loadData(options);

	if (options.events)
	{
		for (var eventName in options.events)
		{
			if (options.events.hasOwnProperty(eventName))
			{
				BX.addCustomEvent(this, eventName, options.events[eventName]);
			}
		}
	}

	this.bindEvents();

	BX.addCustomEvent(this, "Kanban.Grid:onItemDragStart", BX.delegate(this.onItemDragStart, this));
	BX.addCustomEvent(this, "Kanban.Grid:onItemsDragStart", BX.delegate(this.onItemDragStart, this));
	BX.addCustomEvent(this, "Kanban.Grid:onItemDragStop", BX.delegate(this.onItemDragStop, this));
	BX.addCustomEvent(this, "Kanban.Grid:onColumnDragStart", BX.delegate(this.onColumnDragStart, this));
	BX.addCustomEvent(this, "Kanban.Grid:onColumnDragStop", BX.delegate(this.onColumnDragStop, this));

	if(this.multiSelect)
	{
		BX.addCustomEvent(this, "Kanban.Item:select", this.addSelectedItem.bind(this));
		BX.addCustomEvent(this, "Kanban.Item:select", this.adjustMultiSelectMode.bind(this));
		BX.addCustomEvent(this, "Kanban.Item:unSelect", this.removeSelectedItem.bind(this));
		BX.addCustomEvent(this, "Kanban.Item:unSelect", this.adjustMultiSelectMode.bind(this));
		window.addEventListener('keydown', function(event) {
			if(BX.Kanban.Utils.getKeyDownName(event.keyCode) === "Escape" && this.getSelectedItems().length > 0)
			{
				this.cleanSelectedItems();
				this.adjustMultiSelectMode();
				BX.PreventDefault(event);
			}
		}.bind(this));
		window.addEventListener('click', function(event) {
			if(
				BX.findParent(event.target, {attr: {"data-element": "kanban-element"}})
				|| event.target.getAttribute("data-element") === "kanban-element"
			)
			{
				return;
			}

			this.cleanSelectedItems();
			this.adjustMultiSelectMode();
		}.bind(this));
	}
};

/**
 *
 * @enum {number}
 */
BX.Kanban.DragMode = {
	NONE: 0,
	ITEM: 1,
	COLUMN: 2
};

BX.Kanban.Grid.prototype =
{
	/**
	 *
	 * @param {object} options
	 * @returns {BX.Kanban.Column|null}
	 */
	addColumn: function(options)
	{
		options = options || {};

		if (this.getColumn(options.id) !== null)
		{
			return null;
		}

		var columnType = this.getColumnType(options.type);
		var column = new columnType(options);
		if (!(column instanceof BX.Kanban.Column))
		{
			throw new Error("Column type must be an instance of BX.Kanban.Column");
		}

		column.setGrid(this);
		this.columns[column.getId()] = column;

		var targetColumn = this.getColumn(options.targetId);
		var targetIndex = BX.util.array_search(targetColumn, this.columnsOrder);
		if (targetIndex >= 0)
		{
			this.columnsOrder.splice(targetIndex, 0, column);
		}
		else
		{
			this.columnsOrder.push(column);
		}

		if (this.isRendered())
		{
			if (targetColumn)
			{
				this.getGridContainer().insertBefore(column.render(), targetColumn.getContainer());
			}
			else
			{
				this.getGridContainer().appendChild(column.render());
			}
		}

		return column;
	},

	getAddItemTitleText: function()
	{
		return this.addItemTitleText;
	},

	getAddDraftItemInfo: function() 
	{
		return this.addDraftItemInfo;
	},

	isAhaMode: function()
	{
		return this.ahaMode;
	},

	onAhaMode: function()
	{
		this.getGridContainer().classList.add("main-kanban-aha");
		this.ahaMode = true;
	},

	offAhaMode: function()
	{
		this.getGridContainer().classList.remove("main-kanban-aha");
		this.ahaMode = false;
	},

	/**
	 *
	 * @param {BX.Kanban.Column|string|number} column
	 * @returns {boolean}
	 */
	removeColumn: function(column)
	{
		column = this.getColumn(column);
		if (!column)
		{
			return false;
		}

		this.removeColumnItems(column);

		this.columnsOrder = this.columnsOrder.filter(function(element) {
			return column !== element;
		});

		delete this.columns[column.getId()];

		BX.remove(column.getContainer());

		return true;
	},

	bindEvents: function() {},

	updateColumn: function(column, options)
	{
		column = this.getColumn(column);
		if (!column)
		{
			return false;
		}

		column.setOptions(options);
		column.render();

		return true;
	},

	/**
	 *
	 * @param {BX.Kanban.Column} currentColumn
	 * @returns {BX.Kanban.Column}
	 */
	getNextColumnSibling: function(currentColumn)
	{
		var columnIndex = this.getColumnIndex(currentColumn);
		var columns = this.getColumns();

		return columnIndex !== -1 && columns[columnIndex + 1] ? columns[columnIndex + 1] : null;
	},

	/**
	 *
	 * @param {BX.Kanban.Column} currentColumn
	 * @returns {BX.Kanban.Column}
	 */
	getPreviousColumnSibling: function(currentColumn)
	{
		var columnIndex = this.getColumnIndex(currentColumn);
		var columns = this.getColumns();

		return columnIndex > 0 && columns[columnIndex - 1] ? columns[columnIndex - 1] : null;
	},

	adjustMultiSelectMode: function()
	{
		if(this.selectedItems.length > 0) {
			this.onMultiSelect();
		}
		else
		{
			this.offMultiSelect();
		}
	},

	/**
	 *
	 * @param {object} item
	 */
	addSelectedItem: function(item)
	{
		if (!(item instanceof BX.Kanban.Item))
		{
			throw new Error("Item type must be an instance of BX.Kanban.Item");
		}

		this.selectedItems.push(item);

	},

	/**
	 *
	 * @param {object} item
	 */
	removeSelectedItem: function(item)
	{
		if (!(item instanceof BX.Kanban.Item))
		{
			throw new Error("Item type must be an instance of BX.Kanban.Item");
		}

		if (this.selectedItems.indexOf(item) >= 0)
		{
			this.selectedItems.splice(this.selectedItems.indexOf(item), 1);
		}

	},

	/**
	 *
	 * @param {object} options
	 * @param {string|number} options.id
	 * @param {string|number} options.columnId
	 * @param {string} [options.type]
	 * @param {string|number} [options.targetId]
	 * @returns {BX.Kanban.Item|null}
	 */
	addItem: function(options)
	{
		options = options || {};
		var column = this.getColumn(options.columnId);
		if (!column)
		{
			return null;
		}

		var itemType = this.getItemType(options.type);
		var item = new itemType(options);
		if (!(item instanceof BX.Kanban.Item))
		{
			throw new Error("Item type must be an instance of BX.Kanban.Item");
		}

		if (this.items[item.getId()])
		{
			return null;
		}

		item.setGrid(this);
		this.items[item.getId()] = item;

		var targetItem = this.getItem(options.targetId);
		column.addItem(item, targetItem);

		options.type === 'BX.Kanban.DraftItem'
			? BX.onCustomEvent(this, "Kanban.Grid:addDraftItem", [item])
			: BX.onCustomEvent(this, "Kanban.Grid:addItem", [item]);

		return item;
	},

	/**
	 *
	 * @param {BX.Kanban.Item|string|number} itemId
	 * @returns {BX.Kanban.Item}
	 */
	removeItem: function(itemId)
	{
		var item = this.getItem(itemId);
		if (item)
		{
			var column = item.getColumn();
			delete this.items[item.getId()];
			column.removeItem(item);
			item.dispose();
		}

		return item;
	},

	removeColumnItems: function(column)
	{
		column = this.getColumn(column);

		var items = column.getItems();
		column.removeItems();

		items.forEach(function(item) {
			this.removeItem(item);
		}, this);
	},

	removeItems: function()
	{
		this.getColumns().forEach(function(column) {
			this.removeColumnItems(column);
		}, this);
	},

	updateItem: function(item, options)
	{
		item = this.getItem(item);
		if (!item)
		{
			return false;
		}

		if (BX.Kanban.Utils.isValidId(options.columnId) && options.columnId !== item.getColumn().getId())
		{
			this.moveItem(item, this.getColumn(options.columnId), this.getItem(options.targetId));
		}

		var eventArgs = ['UPDATE', { task: item, options: options }];

		BX.onCustomEvent(window, 'tasksTaskEvent', eventArgs);

		item.setOptions(options);
		item.render();

		return true;
	},

	/**
	 *
	 * @param {BX.Kanban.Item|string|number} item
	 * @returns {boolean}
	 */
	hideItem: function(item)
	{
		item = this.getItem(item);
		if (!item || !item.isVisible())
		{
			return false;
		}

		item.setOptions({ visible: false });

		if (item.isCountable())
		{
			item.getColumn().decrementTotal();
		}

		item.getColumn().render();

		return true;
	},

	/**
	 *
	 * @param {BX.Kanban.Item|string|number} item
	 * @returns {boolean}
	 */
	unhideItem: function(item)
	{
		item = this.getItem(item);
		if (!item || item.isVisible())
		{
			return false;
		}

		item.setOptions({ visible: true });

		if (item.isCountable())
		{
			item.getColumn().incrementTotal();
		}

		item.getColumn().render();

		return true;
	},

	getSelectedItems: function()
	{
		return this.selectedItems;
	},

	cleanSelectedItems: function()
	{
		for (var i = 0; i < this.getSelectedItems().length; i++)
		{
			this.getSelectedItems()[i].unSelect(true);
		}

		this.selectedItems = [];
	},

	isMultiSelect: function()
	{
		return this.multiSelect;
	},

	onMultiSelect: function()
	{
		this.getGridContainer().classList.add("main-kanban-multiselect-mode")
	},

	offMultiSelect: function()
	{
		this.getGridContainer().classList.remove("main-kanban-multiselect-mode")
	},

	/**
	 *
	 * @param {BX.Kanban.Column|string|number} column
	 * @returns {BX.Kanban.Column}
	 */
	getColumn: function(column)
	{
		var columnId = column instanceof BX.Kanban.Column ? column.getId() : column;

		return this.columns[columnId] ? this.columns[columnId] : null;
	},

	/**
	 *
	 * @returns {BX.Kanban.Column[]}
	 */
	getColumns: function()
	{
		return this.columnsOrder;
	},

	/**
	 * @returns {number}
	 */
	getColumnsCount: function()
	{
		return this.columnsOrder.length;
	},

	/**
	 *
	 * @param column
	 * @returns {number}
	 */
	getColumnIndex: function(column)
	{
		column = this.getColumn(column);

		return BX.util.array_search(column, this.getColumns());
	},

	/**
	 *
	 * @param {string|number} item
	 * @returns {BX.Kanban.Item}
	 */
	getItem: function(item)
	{
		var itemId = item instanceof BX.Kanban.Item ? item.getId() : item;

		return this.items[itemId] ? this.items[itemId] : null;
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @returns {BX.Kanban.Item|null}
	 */
	getItemByElement: function(itemNode)
	{
		if (BX.type.isDomNode(itemNode) && itemNode.dataset.id && itemNode.dataset.type === "item")
		{
			return this.getItem(itemNode.dataset.id);
		}

		return null;
	},

	/**
	 *
	 * @returns {Object.<string, BX.Kanban.Item>}
	 */
	getItems: function()
	{
		return this.items;
	},

	/**
	 *
	 * @param {string} [className]
	 * @returns {BX.Kanban.Item}
	 */
	getItemType: function(className)
	{
		var classFn = BX.Kanban.Utils.getClass(className);
		if (BX.type.isFunction(classFn))
		{
			return classFn;
		}

		return this.itemType || BX.Kanban.Item;
	},

	/**
	 *
	 * @param {string} [className]
	 * @returns {BX.Kanban.Column}
	 */
	getColumnType: function(className)
	{
		var classFn = BX.Kanban.Utils.getClass(className);
		if (BX.type.isFunction(classFn))
		{
			return classFn;
		}

		return this.columnType || BX.Kanban.Column;
	},

	/**
	 *
	 * @returns {BX.Kanban.DropZoneArea}
	 */
	getDropZoneArea: function()
	{
		return this.dropZoneArea;
	},

	/**
	 *
	 * @returns {object}
	 */
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

	getBgColor: function()
	{
		return this.bgColor;
	},

	getBgColorStyle: function()
	{
		return this.getBgColor() === "transparent" ? this.getBgColor() : "#" + this.getBgColor();
	},

	/**
	 *
	 * @returns {object}
	 */
	getOptions: function()
	{
		return this.options;
	},

	/**
	 *
	 * @param {object} json
	 * @param {BX.Kanban.Column[]} [json.columns]
	 * @param {BX.Kanban.Item[]} [json.items]
	 * @param {BX.Kanban.DropZone[]} [json.dropZones]
	 */
	loadData: function(json)
	{
		var needToDraw = this.isRendered();
		this.setRenderStatus(false);

		var boolOptions = [
			"canAddColumn", "canEditColumn", "canSortColumn", "canRemoveColumn", "canAddItem", "canSortItem"
		];

		boolOptions.forEach(function(boolOption) {
			if (BX.type.isBoolean(json[boolOption]))
			{
				this[boolOption] = json[boolOption];
			}
		}, this);

		if (BX.type.isArray(json.columns))
		{
			json.columns.forEach(function(column) {

				if (column && BX.Kanban.Utils.isValidId(column.id) && this.getColumn(column.id))
				{
					this.updateColumn(column.id, column);
				}
				else
				{
					this.addColumn(column);
				}

			}, this);
		}

		if (BX.type.isArray(json.items))
		{
			json.items.forEach(function(item) {

				if (item && BX.Kanban.Utils.isValidId(item.id) && this.getItem(item.id))
				{
					this.updateItem(item.id, item);
				}
				else
				{
					this.addItem(item);
				}

			}, this);
		}

		if (BX.type.isArray(json.dropZones))
		{
			json.dropZones.forEach(function(dropzone) {

				if (dropzone && BX.Kanban.Utils.isValidId(dropzone.id) && this.getDropZoneArea().getDropZone(dropzone.id))
				{
					this.getDropZoneArea().updateDropZone(dropzone.id, dropzone);
				}
				else
				{
					this.getDropZoneArea().addDropZone(dropzone);
				}

			}, this);
		}

		if (needToDraw)
		{
			this.draw();
		}
	},

	/**
	 * Draws Kanban on the page
	 *
	 */
	draw: function()
	{
		var docFragment = document.createDocumentFragment();
		var columns = this.getColumns();
		for (var i = 0; i < columns.length; i++)
		{
			var column = columns[i];
			docFragment.appendChild(column.render());
		}

		BX.cleanNode(this.getGridContainer());
		this.getGridContainer().appendChild(docFragment);

		this.getDropZoneArea().render();

		if (!this.isRendered())
		{
			this.renderLayout();
			this.adjustLayout();
			this.setRenderStatus(true);
			BX.onCustomEvent(this, "Kanban.Grid:onFirstRender", [this]);
		}
		else
		{
			this.adjustLayout();
		}

		this.adjustEmptyStub();

		BX.onCustomEvent(this, "Kanban.Grid:onRender", [this]);

		this.firstRenderComplete = true;

		if(this.isAhaMode())
		{
			this.renderTo.classList.add("main-kanban-aha-mode");
		}
	},

	renderLayout: function()
	{
		if (this.getOuterContainer().parentNode)
		{
			return;
		}

		var innerContainer = this.getInnerContainer();
		innerContainer.appendChild(this.getEmptyStub());
		innerContainer.appendChild(this.getLeftEar());
		innerContainer.appendChild(this.getRightEar());
		innerContainer.appendChild(this.getDropZoneArea().getContainer());
		innerContainer.appendChild(this.getLoader());
		innerContainer.appendChild(this.getGridContainer());

		var outerContainer = this.getOuterContainer();
		outerContainer.appendChild(innerContainer);

		this.renderTo.appendChild(this.getOuterContainer());

		BX.bind(window, "resize", this.adjustLayout.bind(this));
		BX.bind(window, "scroll", this.adjustHeight.bind(this));
	},

	isRendered: function()
	{
		return this.rendered;
	},

	setRenderStatus: function(status)
	{
		if (BX.type.isBoolean(status))
		{
			this.rendered = status;
		}
	},

	/**
	 *
	 * @returns {Element}
	 */
	getLeftEar: function()
	{
		if (this.layout.earLeft)
		{
			return this.layout.earLeft;
		}

		this.layout.earLeft = BX.create("div", {
			attrs: {
				className: "main-kanban-ear-left"
			},
			events: {
				mouseenter: this.scrollToLeft.bind(this),
				mouseleave: this.stopAutoScroll.bind(this)
			}
		});

		return this.layout.earLeft;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getRightEar: function()
	{
		if (this.layout.earRight)
		{
			return this.layout.earRight;
		}

		this.layout.earRight = BX.create("div", {
			attrs: {
				className: "main-kanban-ear-right"
			},
			events: {
				mouseenter: this.scrollToRight.bind(this),
				mouseleave: this.stopAutoScroll.bind(this)
			}
		});

		return this.layout.earRight;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getRenderToContainer: function()
	{
		return this.renderTo;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getOuterContainer: function()
	{
		if (this.layout.outerContainer)
		{
			return this.layout.outerContainer;
		}

		this.layout.outerContainer = BX.create("div", {
			props: {
				className: "main-kanban"
			},
			style: {
				backgroundColor: this.getBgColorStyle()
			}
		});

		return this.layout.outerContainer;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getInnerContainer: function()
	{
		if (this.layout.innerContainer)
		{
			return this.layout.innerContainer;
		}

		this.layout.innerContainer = BX.create("div", {
			props: {
				className: "main-kanban-inner"
			},
			children: [
				this.getLeftShadowContainer(),
				this.getRightShadowContainer()
			],
			style: {
				backgroundColor: this.getBgColorStyle()
			}
		});

		return this.layout.innerContainer;
	},

	getRightShadowContainer: function()
	{
		if(!this.layout.rightShadow)
		{
			this.layout.rightShadow = BX.create("div", {
				props: {
					className: "main-kanban-inner-shadow main-kanban-inner-shadow-right"
				}
			});
		}

		return this.layout.rightShadow;
	},

	getLeftShadowContainer: function()
	{
		if(!this.layout.leftShadow)
		{
			this.layout.leftShadow = BX.create("div", {
				props: {
					className: "main-kanban-inner-shadow main-kanban-inner-shadow-left"
				}
			});
		}

		return this.layout.leftShadow;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getGridContainer: function()
	{
		if (!this.layout.gridContainer)
		{
			this.layout.gridContainer = BX.create("div", {
				props: {
					className: "main-kanban-grid"
				},
				events: {
					scroll: this.adjustEars.bind(this)
				}
			});
		}

		return this.layout.gridContainer;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getEmptyStub: function()
	{
		if(this.layout.emptyStub)
		{
			return this.layout.emptyStub;
		}

		if(this.emptyStubItems && typeof this.emptyStubItems === 'object')
		{
			this.layout.emptyStub = BX.create("div", {
				attrs: {
					className: "main-kanban-no-data"
				},
				children: [
					BX.create("div", {
						attrs: {
							className: "main-kanban-no-data-inner"
						},
						children: [
							BX.create("div", {
								attrs: {
									className: "main-kanban-no-data-title"
								},
								text: this.emptyStubItems['title']
							}),
							BX.create("div", {
								attrs: {
									className: "main-kanban-no-data-description"
								},
								text: this.emptyStubItems['description']
							})
						]
					})
				]
			});

			return this.layout.emptyStub;
		}

		// default empty layout
		this.layout.emptyStub = BX.create("div", {
			attrs: {
				className: "main-kanban-no-data"
			},
			children: [
				BX.create("div", {
					attrs: {
						className: "main-kanban-no-data-inner"
					},
					children: [
						BX.create("div", {
							attrs: {
								className: "main-kanban-no-data-image"
							}
						}),
						BX.create("div", {
							attrs: {
								className: "main-kanban-no-data-text"
							},
							text: this.getMessage("NO_DATA")
						})
					]
				})
			]
		});

		return this.layout.emptyStub;
	},

	getLoader: function()
	{
		if (this.layout.loader)
		{
			return this.layout.loader;
		}

		this.layout.loader = BX.create("div", {
			props: {
				className: "main-kanban-loader-container"
			},
			html:
			'<svg class="main-kanban-loader-circular" viewBox="25 25 50 50">' +
				'<circle class="main-kanban-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>' +
			'</svg>'
		});

		return this.layout.loader;
	},

	adjustLayout: function()
	{
		this.adjustWidth();
		this.adjustHeight();
		this.adjustEars();
	},

	adjustEars: function()
	{
		var grid = this.getGridContainer();
		var scroll = grid.scrollLeft;

		var isLeftVisible = scroll > 0;
		var isRightVisible = grid.scrollWidth > (Math.round(scroll + grid.offsetWidth));

		this.getOuterContainer().classList[isLeftVisible ? "add" : "remove"]("main-kanban-left-ear-shown");
		this.getOuterContainer().classList[isRightVisible ? "add" : "remove"]("main-kanban-right-ear-shown");
	},

	adjustWidth: function()
	{
		this.getOuterContainer().style.width = this.renderTo.offsetWidth + "px";
	},

	adjustHeight: function()
	{
		var outerContainer = this.getOuterContainer();
		var innerContainer = this.getInnerContainer();

		if (outerContainer.getBoundingClientRect().top >= 15) //@see .main-kanban-fixed:top
		{
			var height = document.documentElement.clientHeight - innerContainer.getBoundingClientRect().top;
			innerContainer.style.height = height + "px";

			if (innerContainer.classList.contains("main-kanban-fixed"))
			{
				BX.onCustomEvent(this, "Kanban.Grid:onFixedModeEnd", [this]);
			}

			outerContainer.style.minHeight = document.documentElement.clientHeight + "px";
			innerContainer.style.removeProperty("top");
			innerContainer.style.removeProperty("left");
			innerContainer.style.removeProperty("width");
			innerContainer.classList.remove("main-kanban-fixed");
		}
		else
		{
			if (!innerContainer.classList.contains("main-kanban-fixed"))
			{
				BX.onCustomEvent(this, "Kanban.Grid:onFixedModeStart", [this]);
			}

			var rectArea = this.renderTo.getBoundingClientRect();
			innerContainer.style.left = rectArea.left + "px";
			innerContainer.style.width = rectArea.width + "px";
			innerContainer.style.removeProperty("height");
			innerContainer.classList.add("main-kanban-fixed");
		}
	},

	adjustEmptyStub: function()
	{
		var isVisible = true;

		var items = this.getItems();
		for (var itemId in items)
		{
			var item = items[itemId];
			if (item.isVisible())
			{
				isVisible = false;
				break;
			}
		}

		this.getInnerContainer().classList[isVisible ? "add" : "remove"]("main-kanban-no-data-mode");
	},

	moveSelectedItems: function(targetColumn, beforeItem)
	{
		targetColumn = this.getColumn(targetColumn);
		beforeItem = this.getItem(beforeItem);

		if((this.selectedItems.length > 0) || !targetColumn || !beforeItem)
		{
			return false;
		}

		targetColumn.addSelectedItems(this.selectedItems, beforeItem)
	},

	moveItem: function(item, targetColumn, beforeItem)
	{
		item = this.getItem(item);
		targetColumn = this.getColumn(targetColumn);
		beforeItem = this.getItem(beforeItem);

		if (!item || !targetColumn || item === beforeItem)
		{
			return false;
		}

		var currentColumn = item.getColumn();
		currentColumn.removeItem(item);
		targetColumn.addItem(item, beforeItem);

		return true;
	},

	moveItems: function(items, targetColumn, startBeforeItem)
	{
		var currentColumns = [];

		for (var itemId in items)
		{
			var column = this.getColumn(items[itemId].columnId);
			
			if(currentColumns.indexOf(column) === -1)
			{
				currentColumns.push(column);
			}
		}

		for (var columnId in currentColumns)
		{
			var columnItems = [];
			for(var keyId in items)
			{
				if(currentColumns[columnId].getId() === items[keyId].getColumnId())
				{
					columnItems.push(items[keyId]);
				}
			}

			currentColumns[columnId].removeSelectedItems(columnItems);
		}

		targetColumn.addItems(items, startBeforeItem);

		return true;
	},

	/**
	 *
	 * @param {BX.Kanban.Column|string|number} column
	 * @param {BX.Kanban.Column|string|number} [targetColumn]
	 * @returns {boolean}
	 */
	moveColumn: function(column, targetColumn)
	{
		column = this.getColumn(column);
		targetColumn = this.getColumn(targetColumn);
		if (!column || column === targetColumn)
		{
			return false;
		}

		var columnIndex = BX.util.array_search(column, this.columnsOrder);
		this.columnsOrder.splice(columnIndex, 1);

		var targetIndex = BX.util.array_search(targetColumn, this.columnsOrder);
		if (targetIndex >= 0)
		{
			this.columnsOrder.splice(targetIndex, 0, column);
			if (this.isRendered())
			{
				column.getContainer().parentNode.insertBefore(column.getContainer(), targetColumn.getContainer());
			}
		}
		else
		{
			this.columnsOrder.push(column);
			if (this.isRendered())
			{
				column.getContainer().parentNode.appendChild(column.getContainer());
			}
		}

		return true;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	canAddColumns: function()
	{
		return this.canAddColumn;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	canEditColumns: function()
	{
		return this.canEditColumn;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	canSortColumns: function()
	{
		return this.canSortColumn;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	canRemoveColumns: function()
	{
		return this.canRemoveColumn;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	canAddItems: function()
	{
		return this.canAddItem;
	},

	/**
	 *
	 * @returns {boolean}
	 */
	canSortItems: function()
	{
		return this.canSortItem;
	},

	scrollToRight: function()
	{
		this.earTimer = setInterval(function() {
			this.getGridContainer().scrollLeft += 10;
		}.bind(this), 20)
	},

	scrollToLeft: function()
	{
		this.earTimer = setInterval(function() {
			this.getGridContainer().scrollLeft -= 10;
		}.bind(this), 20)
	},

	stopAutoScroll: function()
	{
		clearInterval(this.earTimer);

		//?
		jsDD.refreshDestArea();
	},

	/**
	 *
	 * @returns {BX.Kanban.DragMode}
	 */
	getDragMode: function()
	{
		return this.dragMode;
	},

	getDragModeCode: function(mode)
	{
		for (var code in BX.Kanban.DragMode)
		{
			if (BX.Kanban.DragMode[code] === mode)
			{
				return code;
			}
		}

		return null;
	},

	/**
	 *
	 * @param {BX.Kanban.DragMode} mode
	 */
	setDragMode: function(mode)
	{
		var code = this.getDragModeCode(mode);
		if (code !== null)
		{
			this.getOuterContainer().classList.add("main-kanban-drag-mode-" + code.toLowerCase());
			this.dragMode = mode;
		}
	},

	resetDragMode: function()
	{
		var code = this.getDragModeCode(this.getDragMode());
		if (code !== null)
		{
			this.getOuterContainer().classList.remove("main-kanban-drag-mode-" + code.toLowerCase());
		}

		this.dragMode = BX.Kanban.DragMode.NONE;
	},

	onItemDragStart: function(item)
	{
		if(this.multiSelect && this.selectedItems.length > 0)
		{
			for (var item in this.selectedItems)
			{
				this.selectedItems[item].disabledItem();
			}
		}

		this.setDragMode(BX.Kanban.DragMode.ITEM);

		var items = this.getItems();
		for (var itemId in items)
		{
			items[itemId].enableDropping();
		}

		this.getColumns().forEach(function(/*BX.Kanban.Column*/column) {
			column.enableDropping();
		});

		this.getDropZoneArea().emptyAll();
		this.getDropZoneArea().show();
	},

	onItemDragStop: function(item)
	{
		if(this.multiSelect && this.selectedItems.length > 0)
		{
			for (var item in this.selectedItems)
			{
				this.selectedItems[item].unDisabledItem();
			}
		}
		
		this.resetDragMode();
		this.getDropZoneArea().hide();

		//@see onItemDragEnd
		// var items = this.getItems();
		// for (var itemId in items)
		// {
		// 	items[itemId].disableDropping();
		// }
		//
		// this.getColumns().forEach(function(/*BX.Kanban.Column*/column) {
		// 	column.disableDropping();
		// });
	},

	onColumnDragStart: function(column)
	{
		this.setDragMode(BX.Kanban.DragMode.COLUMN);
	},

	onColumnDragStop: function(column)
	{
		this.resetDragMode();
	},

	/**
	 *
	 * @param {string} eventName
	 * @param {array} eventArgs
	 * @param {function} onFulfilled
	 * @param {function} onRejected
	 */
	getEventPromise: function(eventName, eventArgs, onFulfilled, onRejected)
	{
		var promises = [];

		eventArgs = BX.type.isArray(eventArgs) ? eventArgs : [];
		BX.onCustomEvent(this, eventName, [promises].concat(eventArgs));

		var promise = new BX.Promise();
		var firstPromise = promise;

		for (var i = 0; i < promises.length; i++)
		{
			promise = promise.then(promises[i]);
		}

		promise.then(
			BX.type.isFunction(onFulfilled) ? onFulfilled : null,
			BX.type.isFunction(onRejected) ? onRejected : null
		);

		return firstPromise;
	},

	fadeOut: function()
	{
		this.getOuterContainer().classList.add("main-kanban-faded");
	},

	fadeIn: function()
	{
		this.getOuterContainer().classList.remove("main-kanban-faded");
	},

	getMessage: function(messageId)
	{
		return messageId in this.messages ? this.messages[messageId] : BX.message("MAIN_KANBAN_" + messageId);
	}
};

BX.Kanban.DragEvent = function(options)
{
	this.item = null;
	this.items = [];
	this.targetColumn = null;
	this.targetItem = null;
	this.action = true;
};

BX.Kanban.DragEvent.prototype =
{
	allowAction: function()
	{
		this.action = true;
	},

	denyAction: function()
	{
		this.action = false;
	},

	isActionAllowed: function()
	{
		return this.action;
	},

	/**
	 *
	 * @param {object} items
	 */
	setItems: function(items)
	{
		this.items = items;
	},

	/**
	 *
	 * @param {BX.Kanban.Item} item
	 */
	setItem: function(item)
	{
		this.item = item;
	},

	/**
	 *
	 * @returns {BX.Kanban.Item}
	 */
	getItem: function()
	{
		return this.item;
	},

	/**
	 *
	 * @param {BX.Kanban.Item} item
	 */
	setTargetItem: function(item)
	{
		this.targetItem = item;
	},

	/**
	 *
	 * @returns {BX.Kanban.Item}
	 */
	getTargetItem: function()
	{
		return this.targetItem;
	},

	/**
	 *
	 * @param {BX.Kanban.Column} targetColumn
	 */
	setTargetColumn: function(targetColumn)
	{
		this.targetColumn = targetColumn;
	},

	/**
	 *
	 * @returns {BX.Kanban.Column}
	 */
	getTargetColumn: function()
	{
		return this.targetColumn;
	}
};

})();
