;(function() {

"use strict";

BX.namespace("BX.Kanban");

/**
 *
 * @param {object} options
 * @param {string|number} options.id
 * @param {string} [options.name]
 * @param {string} [options.color]
 * @param {object} [options.data]
 * @constructor
 */
BX.Kanban.DropZone = function(options)
{
	options = options || {};
	if (!BX.Kanban.Utils.isValidId(options.id))
	{
		throw new Error("BX.Kanban.DropZone: 'id' parameter is not valid.")
	}

	this.id = options.id;
	this.name = null;
	this.color = null;
	this.data = Object.create(null);

	/** @var {BX.Kanban.DropZoneArea} **/
	this.dropZoneArea = null;

	this.setOptions(options);

	this.layout = {
		container: null,
		name: null,
		bg: null,
		cancel: null
	};

	/** @var {BX.Kanban.Item} **/
	this.droppedItem = null;
	this.captureTimeout = null;
};

BX.Kanban.DropZone.DEFAULT_COLOR = "1eae43";

BX.Kanban.DropZone.prototype =
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
	 * @param {object} options
	 * @param {string} [options.name]
	 * @param {string} [options.color]
	 * @param {object} [options.data]
	 */
	setOptions: function(options)
	{
		if (!options)
		{
			return;
		}

		this.setName(options.name);
		this.setColor(options.color);
		this.setData(options.data);
	},

	/**
	 *
	 * @param {string} name
	 */
	setName: function(name)
	{
		if (BX.type.isNotEmptyString(name))
		{
			this.name = name;
		}
	},

	/**
	 *
	 * @returns {string|null}
	 */
	getName: function()
	{
		return this.name;
	},

	/**
	 *
	 * @param {string} color
	 */
	setColor: function(color)
	{
		if (BX.Kanban.Utils.isValidColor(color))
		{
			this.color = color.toLowerCase();
		}
	},

	/**
	 *
	 * @returns {string}
	 */
	getColor: function()
	{
		return this.color !== null ? this.color : BX.Kanban.DropZone.DEFAULT_COLOR;
	},

	/**
	 *
	 * @returns {object}
	 */
	getData: function()
	{
		return this.data;
	},

	/**
	 *
	 * @param {object} data
	 */
	setData: function(data)
	{
		if (BX.type.isPlainObject(data))
		{
			this.data = data;
		}
	},

	/**
	 *
	 * @returns {object}
	 */
	getGridData: function()
	{
		return this.getGrid().getData();
	},

	/**
	 *
	 * @param {BX.Kanban.DropZoneArea} dropZoneArea
	 */
	setDropZoneArea: function(dropZoneArea)
	{
		this.dropZoneArea = dropZoneArea;
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
	 * @returns {BX.Kanban.Grid}
	 */
	getGrid: function()
	{
		return this.getDropZoneArea().getGrid();
	},

	makeDroppable: function()
	{
		var container = this.getContainer();

		container.onbxdestdraghover = BX.delegate(this.onDragEnter, this);
		container.onbxdestdraghout = BX.delegate(this.onDragLeave, this);
		container.onbxdestdragfinish = BX.delegate(this.onDragDrop, this);

		jsDD.registerDest(container, 10);
	},

	setActive: function()
	{
		this.getContainer().classList.add("main-kanban-dropzone-active");
	},

	unsetActive: function()
	{
		this.getContainer().classList.remove("main-kanban-dropzone-active");
	},

	setCaptured: function()
	{
		this.getContainer().classList.add("main-kanban-dropzone-captured");
	},

	unsetCaptured: function()
	{
		this.getContainer().classList.remove("main-kanban-dropzone-captured");
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onDragEnter: function(itemNode, x, y)
	{
		this.setActive();
		this.getDropZoneArea().setActive();
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onDragLeave: function(itemNode, x, y)
	{
		this.unsetActive();
		this.getDropZoneArea().unsetActive();
	},

	/**
	 *
	 * @param {Element} itemNode
	 * @param {number} x
	 * @param {number} y
	 */
	onDragDrop: function(itemNode, x, y)
	{
		var draggableItem = this.getGrid().getItemByElement(itemNode);
		this.captureItem(draggableItem);
		this.getDropZoneArea().unsetActive();
	},

	/**
	 *
	 * @param {BX.Kanban.Item} item
	 */
	captureItem: function(item)
	{
		var event = new BX.Kanban.DropZoneEvent();
		event.setItem(item);
		event.setDropZone(this);
		BX.onCustomEvent(this.getGrid(), "Kanban.DropZone:onBeforeItemCaptured", [event]);

		if (!event.isActionAllowed())
		{
			return;
		}

		this.empty();

		this.droppedItem = item;
		this.getDropZoneArea().show();
		this.setCaptured();
		this.unsetActive();
		this.animateRemove(item.layout.container);

		this.getGrid().hideItem(item);

		BX.onCustomEvent(this.getGrid(), "Kanban.DropZone:onItemCaptured", [item, this]);

		this.captureTimeout = setTimeout(
			function() {
				this.empty();
				this.getDropZoneArea().hide();
			}.bind(this),
			this.getDropZoneArea().getDropZoneTimeout()
		);
	},

	animateRemove: function(itemNode)
	{
		// itemNode.style.width = itemNode.offsetWidth + "px";
		// itemNode.style.top = "-" + (itemNode.offsetHeight / 2 + 20) + "px";
		// itemNode.style.left = "-" + itemNode.offsetWidth / 2 + "px";

		this.dropZoneArea.layout.container.parentNode.style.overflow = "hidden";
		// this.layout.container.appendChild(itemNode);

		setTimeout(function()
		{
			this.dropZoneArea.layout.container.parentNode.style.overflow = "inherit";
		}.bind(this), 250);

		// BX.bind(itemNode, "animationend",function()
		// {
		// 	itemNode.removeAttribute("style");
		// 	itemNode.parentNode.removeChild(itemNode);
		// 	BX.unbindAll(itemNode);
		// }.bind(this));
	},

	restore: function()
	{
		if (this.captureTimeout)
		{
			clearTimeout(this.captureTimeout);
		}

		if (this.droppedItem === null)
		{
			return;
		}

		var event = new BX.Kanban.DropZoneEvent();
		event.setItem(this.droppedItem);
		event.setDropZone(this);
		BX.onCustomEvent(this.getGrid(), "Kanban.DropZone:onBeforeItemRestored", [event]);

		if (!event.isActionAllowed())
		{
			return;
		}

		this.unsetActive();
		this.unsetCaptured();

		this.getGrid().unhideItem(this.droppedItem);

		BX.onCustomEvent(this.getGrid(), "Kanban.DropZone:onItemRestored", [this.droppedItem, this]);

		this.droppedItem = null;
	},

	empty: function()
	{
		if (this.captureTimeout)
		{
			clearTimeout(this.captureTimeout);
		}

		if (this.droppedItem === null)
		{
			return;
		}

		this.unsetActive();
		this.unsetCaptured();

		this.getGrid().removeItem(this.droppedItem);

		BX.onCustomEvent(this.getGrid(), "Kanban.DropZone:onItemEmptied", [this.droppedItem, this]);

		this.droppedItem = null;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getContainer: function()
	{
		if (this.layout.container !== null)
		{
			return this.layout.container
		}

		this.layout.container = BX.create("div", {
			attrs: {
				className: "main-kanban-dropzone",
				"data-id": this.getId()
			},
			children: [
				this.getNameContainer(),
				this.getCancelLink(),
				this.getBgContainer()
			]
		});

		this.makeDroppable();

		return this.layout.container;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getNameContainer: function()
	{
		if (!this.layout.name)
		{
			var isDark = BX.Kanban.Utils.isDarkColor(this.getColor());

			this.layout.name = BX.create("div", {
				attrs: {
					className: isDark ? "main-kanban-dropzone-title" : "main-kanban-dropzone-title main-kanban-dropzone-title-light"
				}
			})
		}

		return this.layout.name;
	},

	/**
	 *
	 * @returns {Element}
	 */
	getCancelLink: function()
	{
		if (!this.layout.cancel)
		{
			this.layout.cancel = BX.create("div", {
				attrs: {
					className: "main-kanban-dropzone-cancel"
				},
				children: [
					BX.create("span", {
						attrs: {
							className: "main-kanban-dropzone-cancel-link"
						},
						events: {
							click: this.handleCancelClick.bind(this)
						},
						html: this.getGrid().getMessage("DROPZONE_CANCEL")
					})
				]
			});
		}

		return this.layout.cancel;
	},

	handleCancelClick: function(event)
	{
		this.restore();
		this.getDropZoneArea().hide();
	},

	/**
	 *
	 * @returns {Element}
	 */
	getBgContainer: function()
	{
		if (!this.layout.bg)
		{
			this.layout.bg = BX.create("div", {
				attrs: {
					className: "main-kanban-dropzone-bg"
				}
			})
		}

		return this.layout.bg;
	},

	/**
	 *
	 * @returns {Element}
	 */
	render: function()
	{
		this.getNameContainer().innerHTML = this.getName();
		this.getBgContainer().style.backgroundColor = "#" + this.getColor();

		return this.getContainer();
	}
};

BX.Kanban.DropZoneEvent = function(options)
{
	options = BX.type.isPlainObject(options) || {};
	this.action = BX.type.isBoolean(options.action) ? options.action : true;

	this.item = null;
	this.dropZone = null;
};

BX.Kanban.DropZoneEvent.prototype =
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
	 * @param {BX.Kanban.DropZone} dropZone
	 */
	setDropZone: function(dropZone)
	{
		this.dropZone = dropZone;
	},

	/**
	 *
	 * @returns {BX.Kanban.DropZone}
	 */
	getDropZone: function()
	{
		return this.dropZone;
	}
}

})();
